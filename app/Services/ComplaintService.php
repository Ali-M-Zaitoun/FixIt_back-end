<?php

namespace App\Services;

use App\DAO\ComplaintDAO;
use App\DAO\GovernorateDAO;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Governorate;
use App\Models\MinistryBranch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new ComplaintDAO();
    }

    public function submitComplaint(array $data, FileManagerService $fileManagerService)
    {
        $media = $data['media'] ?? null;
        unset($data['media'], $data['locked_by'], $data['locked_at']);

        $data['citizen_id'] = Auth::user()->citizen->id;

        $ministryBranch = app(MinistryBranchService::class)->readOne($data['ministry_branch_id']);
        $ministryAbbr = $ministryBranch->ministry->abbreviation;

        $governorateCode = app(GovernorateDAO::class)->readOne($data['governorate_id'])->code;

        $data['reference_number'] = sprintf(
            '%s_%s_%s',
            $ministryAbbr,
            $governorateCode,
            Str::random(8)
        );

        $complaint = $this->dao->submit($data);

        $fileManagerService->storeFile(
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
            typeResolver: fn($file) => $fileManagerService->detectFileType($file)
        );
        return $complaint;
    }

    public function getMyComplaints($citizen_id)
    {
        $cacheKey = 'citizen_complaints_' . $citizen_id;
        return Cache::remember($cacheKey, 3600, function () use ($citizen_id) {
            return $this->dao->getMyComplaints($citizen_id);
        });
    }

    public function read()
    {
        $cacheKey = 'all_complaints';
        return Cache::remember($cacheKey, 3600, function () {
            return $this->dao->read();
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

        $cacheKey = 'ministry_branch_complaints_' . $ministry_branch_id;
        return Cache::remember($cacheKey, 3600, function () use ($ministry_branch_id) {
            return $this->dao->getByBranch($ministry_branch_id);
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
        $cacheKey = "ministry_complaints_{$ministry_id}";

        return Cache::remember($cacheKey, 3600, function () use ($ministry_id) {
            $ministryService = app(MinistryService::class);
            $ministry = $ministryService->readOne($ministry_id);

            if (!$ministry) {
                return collect();
            }

            $branchIds = $ministry->branches->pluck('id');

            return $this->dao->getByMinistry($branchIds);
        });
    }

    public function readOne($id)
    {
        $cacheKey = "complaint {$id}";
        return Cache::remember($cacheKey, 3600, function () use ($id) {
            return $this->dao->readOne($id);
        });
    }

    public function updateStatus($id, $status, $reason = "", $user_id)
    {
        $complaint = $this->dao->updateStatus($id, $status);

        $message = $status === 'resolved'
            ? __('messages.complaint_resolved')
            : __('messages.complaint_rejected') . $reason;

        $this->dao->addReply($complaint->id, Employee::where('user_id', $user_id)->first(), $message);
        return true;
    }

    public function startProcessing($id, $emp_id)
    {
        $complaint = $this->dao->readOne($id);
        if (
            $complaint->locked_by &&
            $complaint->locked_by != $emp_id &&
            $complaint->locked_at > now()->subMinutes(15)
        ) {
            return false;
        }

        $this->dao->lock($complaint, $emp_id);

        if ($complaint->status !== 'in_progress')
            $complaint->update(['status' => 'in_progress']);

        return true;
    }

    public function addReply($id, $data)
    {
        $complaint = $this->dao->readOne($id);
    }
}
