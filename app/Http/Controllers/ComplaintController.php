<?php

namespace App\Http\Controllers;

use App\DAO\CitizenDAO;
use App\DAO\ComplaintDAO;
use App\Http\Requests\SubmitComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Citizen;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\MinistryBranch;
use App\Models\User;
use App\Services\ComplaintService;
use App\Services\EmployeeService;
use App\Services\FileManagerService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    use ResponseTrait;

    protected $service;

    public function __construct()
    {
        $this->service = new ComplaintService();
    }

    public function submit(SubmitComplaintRequest $request, ComplaintService $complaintService)
    {
        $data = $request->validated();
        $complaint = $complaintService->submitComplaint($data, new FileManagerService());

        return $this->successResponse(
            ['complaint' => new ComplaintResource($complaint)],
            __('messages.complaint_submitted'),
            201
        );
    }

    public function read()
    {
        $complaints = $this->service->read();
        if ($complaints->isEmpty()) {
            return $this->successResponse([], __('messages.empty'));
        }
        return $this->successResponse(ComplaintResource::collection($complaints), __('messages.complaints_retrieved'));
    }

    public function getMyComplaints()
    {
        $user = Auth::user();
        $citizen_id = $user->citizen->id;

        $complaints = $this->service->getMyComplaints($citizen_id);

        return $this->successResponse(
            ['complaints' => ComplaintResource::collection($complaints)],
            __('messages.complaints_retrieved')
        );
    }

    public function getByMinistry($id)
    {
        $user = Auth::user();
        $complaints = $this->service->getByMinistry($id, $user);
        if ($complaints->isEmpty()) {
            return $this->successResponse([], __('messages.empty'));
        }

        return $this->successResponse(ComplaintResource::collection($complaints), __('messages.complaints_retrieved'));
    }

    public function getByBranch($id)
    {
        $user = Auth::user();
        $complaints = $this->service->getByBranch($id, $user);
        if ($complaints->isEmpty()) {
            return $this->successResponse([], __('messages.empty'));
        }

        return $this->successResponse(ComplaintResource::collection($complaints), __('messages.complaints_retrieved'));
    }

    public function readOne($id)
    {
        $complaint = $this->service->readOne($id);
        if (!$complaint) {
            return $this->errorResponse(
                __('messages.complaint_not_found'),
                404
            );
        }
        return $this->successResponse(
            new ComplaintResource($complaint),
            __('messages.complaint_retrieved'),
        );
    }

    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:resolved,rejected',
            'reason' => 'nullable|string|max:500'
        ]);

        $result = $this->service->updateStatus($id, $request->status, $request->reason, Auth::id());
        if ($result)
            return $this->successResponse([], __('messages.complaint_status_updated'));

        return $this->errorResponse(__('messages.error'));
    }
}
