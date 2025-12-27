<?php

namespace App\Services;

use App\DAO\ComplaintDAO;
use App\DAO\GovernorateDAO;
use App\DTO\ComplaintContext;
use App\Events\NotificationRequested;
use App\Exceptions\BranchMismatchException;
use App\Exceptions\ComplaintAlreadyLockedException;
use App\Exceptions\ComplaintLockedByOtherException;
use App\Exceptions\MinistryRequiresBranchException;
use App\Models\Complaint;

use App\Models\Employee;
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
        protected ReplyService $replyService,
        protected EmployeeService $employeeService,
        protected FirebaseNotificationService $firebase,
        protected MinistryService $ministryService,
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

            $this->cacheManager->clearComplaintCache($data['citizen_id']);

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
            ministryAbbr: $branch->ministry->abbreviation,
            governorateCode: $branch->governorate->code,
            governorateId: $branch->governorate->id,
            employees: $branch->employees ?? []
        );
    }

    private function resolveFromMinistry($ministryId): ComplaintContext
    {
        $ministry = $this->ministryService->readOne($ministryId);
        if ($ministry->branches->isEmpty())
            throw new MinistryRequiresBranchException();
        return new ComplaintContext(
            ministryAbbr: $ministry->abbreviation,
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

    public function getByBranch($branch_id)
    {
        return $this->cacheManager->getByBranch(
            $branch_id,
            fn() => $this->complaintDAO->getByBranch($branch_id)
        );
    }

    public function getByMinistry($ministry_id)
    {
        $ministry = $this->ministryService->readOne($ministry_id);

        $branchIds = $ministry->branches->pluck('id');
        return $this->cacheManager->getByMinistry(
            $ministry_id,
            fn() => $this->complaintDAO->getByMinistry($branchIds)
        );
    }

    public function readOne($id)
    {
        return $this->cacheManager->getOne(
            $id,
            fn() => $this->complaintDAO->readOne($id)
        );
    }

    public function updateStatus(Complaint $complaint, string $status, Employee $employee, string $reason = ""): void
    {
        $lockExpired = $complaint->locked_at <= now()->subMinutes(15);
        $lockedByOther = $complaint->locked_by && $complaint->locked_by != $employee->id;

        if ($lockedByOther && !$lockExpired) {
            throw new ComplaintLockedByOtherException();
        }


        $originalLocale = app()->getLocale();

        $messageKey = $status === 'resolved'
            ? 'complaint_resolved'
            : 'complaint_rejected';

        $message = __(
            "messages.$messageKey",
            ['reason' => $reason]
        );

        app()->setLocale('ar');

        $complaint = $this->complaintDAO->updateStatus($complaint, $status, __("messages.$messageKey"));

        app()->setLocale($originalLocale);

        activity()
            ->performedOn($complaint)
            ->event($status)
            ->log($message);

        event(new NotificationRequested($complaint->citizen->user, __('messages.complaint_status_changed'), $message));

        $this->replyService->addReply($complaint, $employee, ['content' => $message]);
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

        if ($complaint->status !== 'in_progress')
            $complaint->update(['status' => 'in_progress']);
    }

    public function delete($complaint)
    {
        return $this->complaintDAO->delete($complaint);
    }
}
