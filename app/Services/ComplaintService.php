<?php

namespace App\Services;

use App\DAO\ComplaintDAO;
use App\DAO\GovernorateDAO;
use App\DAO\MinistryDAO;
use App\DAO\ReplyDAO;
use App\DTO\ComplaintContext;
use App\Events\NotificationRequested;
use App\Exceptions\BranchMismatchException;
use App\Exceptions\ComplaintAlreadyLockedException;
use App\Exceptions\ComplaintLockedByOtherException;
use App\Exceptions\MinistryRequiresBranchException;
use App\Models\Complaint;

use App\Models\Employee;
use App\Models\Ministry;
use App\Traits\Loggable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintService
{
    use Loggable;
    public function __construct(
        protected ComplaintDAO $complaintDAO,
        protected FileManagerService $fileService,
        protected CacheManagerService $cacheManager,
        protected MinistryBranchService $ministryBranchService,
        protected ReplyDAO $replyDAO,
        protected EmployeeService $employeeService,
        protected FirebaseNotificationService $firebase,
        protected MinistryDAO $ministryDAO,
        protected GovernorateDAO $governorateDAO,
        protected NotificationService $notificationSerivce
    ) {}

    public function submitComplaint(array $data)
    {
        return DB::transaction(function () use ($data) {
            $media = $data['media'] ?? null;
            unset($data['media']);

            $context = $this->contextResolver($data);
            $data['governorate_id'] = $context->governorateId;
            $data['reference_number'] = $this->generateRefNum($context->ministryAbbr, $context->governorateCode);

            $complaint = $this->complaintDAO->submit($data);

            if ($media) {
                $this->storeComplaintMedia($complaint, $context->ministryAbbr, $context->governorateCode, $data['reference_number'], $media);
            }

            $this->cacheManager->clearComplaintCache(
                citizenId: $complaint->citizen_id,
                branchId: $complaint?->ministry_branch_id,
                ministryId: $complaint->ministry_id,
                single: $complaint->id
            );

            DB::afterCommit(
                fn() =>
                $this->notificationSerivce->notifyEmployees($context->employees, $data['type'])
            );

            return $complaint;
        });
    }

    private function contextResolver(array $data): ComplaintContext
    {
        if (!empty($data['ministry_branch_id'])) {
            return $this->resolveFromBranch($data);
        }
        return $this->resolveFromMinistry($data['ministry_id']);
    }

    private function resolveFromBranch(array $data): ComplaintContext
    {
        $branch = $this->ministryBranchService->readOne($data['ministry_branch_id']);
        if ($branch->ministry_id != $data['ministry_id'])
            throw new BranchMismatchException();

        return new ComplaintContext(
            ministryAbbr: $branch->ministry?->abbreviation ?? "UNK",
            governorateCode: $branch->governorate->code,
            governorateId: $branch->governorate->id,
            employees: $branch->employees ?? []
        );
    }

    private function resolveFromMinistry($ministryId): ComplaintContext
    {
        $ministry = $this->ministryDAO->readOne($ministryId);
        if (!$ministry->branches->isEmpty())
            throw new MinistryRequiresBranchException();

        return new ComplaintContext(
            ministryAbbr: $ministry?->abbreviation,
            governorateCode: "UNK",
            governorateId: NULL,
            employees: collect([$ministry->manager])
        );
    }

    private function generateRefNum($ministryAbbr, $governorateCode): string
    {
        return sprintf(
            '%s_%s_%s',
            $ministryAbbr,
            $governorateCode,
            Str::random(8)
        );
    }

    # Helper functions
    private function storeComplaintMedia($complaint, $ministryAbbr, $governorateCode, $ref_number, $media): void
    {
        $path = sprintf(
            'complaints/%s/%s/%s/%s',
            now()->format('Y/m/d'),
            $ministryAbbr,
            $governorateCode,
            $ref_number
        );

        $this->fileService->storeFile(
            $complaint,
            $media,
            folderPath: $path,
            relationName: 'media',
            typeResolver: fn($file) => $this->fileService->detectFileType($file)
        );
        $this->cacheManager->clearComplaintCache(single: $complaint->id);
    }

    public function getMyComplaints($citizen_id)
    {
        return $this->cacheManager->getMyComplaints(
            $citizen_id,
            fn() => $this->complaintDAO->getMyComplaints($citizen_id)
        );
    }

    public function read()
    {
        return $this->cacheManager->getAll(
            fn() => $this->complaintDAO->read()
        );
    }

    public function getByBranch($branch)
    {
        return $this->cacheManager->getByBranch(
            $branch->id,
            fn() => $this->complaintDAO->getByBranch($branch->id)
        );
    }

    public function getByMinistry(Ministry $ministry)
    {
        $branchIds = $ministry->branches->pluck('id');
        return $this->cacheManager->getByMinistry(
            $ministry->id,
            fn() => $this->complaintDAO->getByMinistry($branchIds)
        );
    }

    public function readOne($complaint)
    {
        return $this->cacheManager->getOne(
            $complaint->id,
            fn() => $complaint
        );
    }

    public function updateStatus(Complaint $complaint, string $status, Employee $employee, string $reason = ""): void
    {
        $lockExpired = $complaint->locked_at <= now()->subMinutes(15);
        $lockedByOther = $complaint->locked_by && $complaint->locked_by != $employee->id;

        if ($lockedByOther && !$lockExpired) {
            throw new ComplaintLockedByOtherException();
        }

        DB::transaction(function () use ($complaint, $status, $reason, $employee) {
            $originalLocale = app()->getLocale();

            $messageKey = $status === 'resolved'
                ? 'complaint_resolved'
                : 'complaint_rejected';

            $message = __(
                "messages.$messageKey",
                ['reason' => $reason]
            );

            app()->setLocale('ar');

            $complaint = $this->complaintDAO->updateStatus($complaint, $status, $messageKey);

            app()->setLocale($originalLocale);

            activity()
                ->performedOn($complaint)
                ->event($status)
                ->log($message);

            $this->cacheManager->clearComplaintCache(
                citizenId: $complaint->citizen_id,
                branchId: $complaint?->ministry_branch_id,
                ministryId: $complaint->ministry_id,
                single: $complaint->id
            );

            event(new NotificationRequested(
                $complaint->citizen->user,
                'complaint_status_changed',
                $messageKey,
                $complaint->reference_number,
                ['reason' => $reason]
            ));

            $this->replyDAO->addReply($complaint->id, $employee, $message);
        });
    }

    public function startProcessing(Complaint $complaint, Employee $employee): void
    {

        if ($complaint->ministry_branch_id !== $employee->ministry_branch_id) {
            throw new BranchMismatchException();
        }

        $lockExpired = $complaint->locked_at <= now()->subMinutes(15);
        $lockedByOther = $complaint->locked_by && $complaint->locked_by != $employee->id;

        if ($lockedByOther && !$lockExpired) {
            throw new ComplaintLockedByOtherException();
        }

        if (!$lockedByOther && !$lockExpired) {
            throw new ComplaintAlreadyLockedException();
        }

        $this->complaintDAO->lock($complaint, $employee->id);

        event(new NotificationRequested(
            $complaint->citizen->user,
            'complaint_status_changed',
            'complaint_in_progress_body',
            $complaint->reference_number,
            []
        ));

        $complaint->update(['status' => 'in_progress']);

        $this->cacheManager->clearComplaintCache(
            citizenId: $complaint->citizen_id,
            branchId: $complaint?->ministry_branch_id,
            ministryId: $complaint->ministry_id,
            single: $complaint->id
        );
    }

    public function delete($complaint)
    {
        $this->cacheManager->clearComplaintCache(
            citizenId: $complaint->citizen_id,
            branchId: $complaint?->ministry_branch_id,
            ministryId: $complaint->ministry_id,
            single: $complaint->id
        );
        return $this->complaintDAO->delete($complaint);
    }
}
