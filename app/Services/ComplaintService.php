<?php

namespace App\Services;

use App\DAO\ComplaintDAO;
use App\DAO\GovernorateDAO;
use App\DAO\UserDAO;
use App\Events\ComplaintCreated;
use App\Events\NotificationRequested;
use App\Exceptions\BranchMismatchException;
use App\Exceptions\ComplaintAlreadyLockedException;
use App\Exceptions\ComplaintLockedByOtherException;
use App\Models\Complaint;;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintService
{
    protected $complaintDAO, $fileService, $cacheManager, $ministryBranchService, $replyService, $employeeService, $firebase, $ministryService, $governorateDAO;

    public function __construct(
        ComplaintDAO $complaintDAO,
        FileManagerService $fileService,
        CacheManagerService $cacheManager,
        MinistryBranchService $ministryBranchService,
        ReplyService $replyService,
        EmployeeService $employeeService,
        FirebaseNotificationService $firebase,
        MinistryService $ministryService,
        GovernorateDAO $governorateDAO
    ) {
        $this->complaintDAO = $complaintDAO;
        $this->fileService = $fileService;
        $this->cacheManager = $cacheManager;
        $this->ministryBranchService = $ministryBranchService;
        $this->ministryService = $ministryService;
        $this->replyService = $replyService;
        $this->employeeService = $employeeService;
        $this->firebase = $firebase;
        $this->governorateDAO = $governorateDAO;
    }

    public function submitComplaint(array $data)
    {
        $complaint = null;
        $employees = [];
        DB::transaction(function () use ($data, &$complaint, &$employees) {
            $media = $data['media'] ?? null;
            unset($data['media'], $data['locked_by'], $data['locked_at']);

            $ministryAbbr = null;
            if (!empty($data['ministry_branch_id'])) {
                $ministryBranch = $this->ministryBranchService->readOne($data['ministry_branch_id']);
                $employees = $ministryBranch->employees;
                $ministryAbbr = $ministryBranch->ministry->abbreviation;
            } else {
                $ministry = $this->ministryService->readOne($data['ministry_id']);
                $employees = collect([$ministry->manager]);
                $ministryAbbr = $ministry->abbreviation;
            }

            $governorateCode = $this->governorateDAO->readOne($data['governorate_id'])->code;

            $data['reference_number'] = sprintf(
                '%s_%s_%s',
                $ministryAbbr,
                $governorateCode,
                Str::random(8)
            );

            $complaint = $this->complaintDAO->submit($data);

            $this->cacheManager->clearComplaintCache($data['citizen_id']);

            $this->storeComplaintMedia($complaint, $ministryAbbr, $governorateCode, $data['reference_number'], $media);
        });
        DB::afterCommit(function () use ($employees, $data) {
            foreach ($employees as $employee) {
                event(new NotificationRequested(
                    $employee->user,
                    __('messages.complaint_received'),
                    $data['type']
                ));
            }
        });
        return $complaint;
    }

    # Helper function
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

    public function updateStatus(Complaint $complaint, string $status, string $reason = "", Employee $employee): void
    {
        $lockExpired = $complaint->locked_at <= now()->subMinutes(15);
        $lockedByOther = $complaint->locked_by && $complaint->locked_by != $employee->id;

        if ($lockedByOther && !$lockExpired) {
            throw new ComplaintLockedByOtherException();
        }

        $complaint = $this->complaintDAO->updateStatus($complaint, $status);

        $messageKey = $status === 'resolved'
            ? 'complaint_resolved'
            : 'complaint_rejected';

        $message = __(
            "messages.$messageKey",
            ['reason' => $reason]
        );

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
