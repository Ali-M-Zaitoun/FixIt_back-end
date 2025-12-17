<?php

namespace App\Services;

use App\DAO\ComplaintDAO;
use App\DAO\GovernorateDAO;
use App\DAO\UserDAO;
use App\Events\ComplaintCreated;;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ComplaintService
{
    protected $complaintDAO, $fileService, $cacheManager, $ministryBranchService, $replyService, $employeeService, $firebase;

    public function __construct(
        ComplaintDAO $complaintDAO,
        FileManagerService $fileService,
        CacheManagerService $cacheManager,
        MinistryBranchService $ministryBranchService,
        ReplyService $replyService,
        EmployeeService $employeeService,
        FirebaseNotificationService $firebase
    ) {
        $this->complaintDAO = $complaintDAO;
        $this->fileService = $fileService;
        $this->cacheManager = $cacheManager;
        $this->ministryBranchService = $ministryBranchService;
        $this->replyService = $replyService;
        $this->employeeService = $employeeService;
        $this->firebase = $firebase;
    }

    public function submitComplaint(array $data)
    {
        $media = $data['media'] ?? null;
        unset($data['media'], $data['locked_by'], $data['locked_at']);

        $data['citizen_id'] = Auth::user()->citizen->id;

        $ministryBranch = $this->ministryBranchService->readOne($data['ministry_branch_id']);
        $ministryAbbr = $ministryBranch->ministry->abbreviation;

        $governorateCode = app(GovernorateDAO::class)->readOne($data['governorate_id'])->code;

        $data['reference_number'] = sprintf(
            '%s_%s_%s',
            $ministryAbbr,
            $governorateCode,
            Str::random(8)
        );

        $complaint = $this->complaintDAO->submit($data);

        $this->cacheManager->clearComplaintCache($data['citizen_id']);
        $this->fileService->storeFile(
            $complaint,
            $media,
            folderPath: sprintf(
                'complaints/%s/%s/%s/%s',
                now()->format('Y/m/d'),
                $ministryAbbr,
                $governorateCode,
                $data['reference_number']
            ),
            relationName: 'media',
            typeResolver: fn($file) => $this->fileService->detectFileType($file)
        );

        $employees = $this->employeeService->getByBranch($data['ministry_branch_id']);

        event(new ComplaintCreated($complaint));

        return $complaint;
    }

    public function getMyComplaints($citizen_id)
    {
        $cacheKey = "complaints:citizen:{$citizen_id}";

        return Cache::remember($cacheKey, 3600, function () use ($citizen_id) {
            return $this->complaintDAO->getMyComplaints($citizen_id);
        });
    }

    public function read()
    {
        $cacheKey = 'complaints';
        return Cache::remember($cacheKey, 3600, function () {
            return $this->complaintDAO->read();
        });
    }

    public function getByBranch($ministry_branch_id, $user)
    {
        $isAuthorized =
            $user->hasRole('super_admin') ||
            (
                $user->hasRole('employee') &&
                $user->employee->ministry_branch_id == $ministry_branch_id
            );

        if (!$isAuthorized) {
            return false;
        }

        $cacheKey = "complaints:branch:{$ministry_branch_id}";

        return Cache::remember($cacheKey, 3600, function () use ($ministry_branch_id) {
            return $this->complaintDAO->getByBranch($ministry_branch_id);
        });
    }

    public function getByMinistry($ministry_id, $user)
    {
        $isAuthorized =
            $user->hasRole('super_admin') ||
            (
                $user->hasRole('ministry_manager') &&
                $user->employee->ministry_id == $ministry_id
            );

        if (!$isAuthorized) {
            return false;
        }

        $cacheKey = "complaints:ministry:{$ministry_id}";

        return Cache::remember($cacheKey, 3600, function () use ($ministry_id) {
            $ministryService = app(MinistryService::class);
            $ministry = $ministryService->readOne($ministry_id);

            if (!$ministry) {
                return collect();
            }

            $branchIds = $ministry->branches->pluck('id');

            return $this->complaintDAO->getByMinistry($branchIds);
        });
    }

    public function readOne($id)
    {
        $cacheKey = "complaint:single:{$id}";
        return Cache::remember($cacheKey, 3600, function () use ($id) {
            return $this->complaintDAO->readOne($id);
        });
    }

    public function updateStatus($id, $status, $reason = "", $user_id)
    {
        $complaint = $this->complaintDAO->readOne($id);
        $employee = Employee::where('user_id', $user_id)->first();
        $lockExpired = $complaint->locked_at <= now()->subMinutes(15);
        $lockedByOther = $complaint->locked_by && $complaint->locked_by != $employee->id;

        if ($lockedByOther && !$lockExpired) {
            return false;
        }

        $complaint = $this->complaintDAO->updateStatus($id, $status);
        $message = $status === 'resolved'
            ? __('messages.complaint_resolved')
            : __('messages.complaint_rejected') . $reason;

        $user = $complaint->citizen->user;
        foreach ($user->fcmTokens as $token) {
            $this->firebase->sendToToken($token->token, __('messages.complaint_status_changed'), $message);
        }
        $data['content'] = $message;
        $this->replyService->addReply($complaint->id, $employee, $data);
        return true;
    }

    public function startProcessing($id, $user_id)
    {
        $complaint = $this->complaintDAO->readOne($id);
        $user = app(UserDAO::class)->findById($user_id);
        $employee = $user->employee;
        if (!$employee) {
            return [
                'status' => false,
                'reason' => 'employee_not_found'
            ];
        }

        $lockExpired = $complaint->locked_at <= now()->subMinutes(15);
        $lockedByOther = $complaint->locked_by && $complaint->locked_by != $employee->id;
        $sameBranch = $complaint->ministry_branch_id === $employee->ministry_branch_id;


        if (!$sameBranch) {
            return [
                'status' => false,
                'reason' => 'branch_mismatch'
            ];
        }

        if ($lockedByOther && !$lockExpired) {
            return [
                'status' => false,
                'reason' => 'complaint_locked_by_other'
            ];
        }

        if (!$lockedByOther && !$lockExpired) {
            return [
                'status' => false,
                'reason' => 'complaint_already_locked'
            ];
        }

        $this->complaintDAO->lock($complaint, $employee->id);

        if ($complaint->status !== 'in_progress')
            $complaint->update(['status' => 'in_progress']);

        return ['status' => true];
    }

    public function delete($id)
    {
        $complaint = $this->complaintDAO->readOne($id);
        if (!$complaint) {
            return false;
        }
        return $this->complaintDAO->delete($complaint);
    }
}
