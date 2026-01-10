<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Exceptions\BusinessException;
use App\Http\Requests\V1\SubmitComplaintRequest;
use App\Http\Resources\V1\ComplaintResource;
use App\Models\Complaint;
use App\Models\Ministry;
use App\Models\MinistryBranch;
use App\Services\ComplaintService;
use App\Traits\ResponseTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    use ResponseTrait, AuthorizesRequests;
    public function __construct(
        protected ComplaintService $complaintService
    ) {}

    public function submit(SubmitComplaintRequest $request)
    {
        $data = $request->validated();
        $data['citizen_id'] = Auth::user()->citizen->id;

        $complaint = $this->complaintService->callWithLogging('submitComplaint', $data);

        return $this->successResponse(
            ['complaint' => new ComplaintResource($complaint)],
            __('messages.complaint_submitted'),
            201
        );
    }

    public function read()
    {
        $complaints = $this->complaintService->read();

        return $this->successCollection(
            $complaints,
            ComplaintResource::class,
            'messages.complaints_retrieved'
        );
    }

    public function getMyComplaints()
    {
        $user = Auth::user();
        $citizen_id = $user->citizen->id;

        $complaints = $this->complaintService->getMyComplaints($citizen_id);

        return $this->successResponse(
            ['complaints' => ComplaintResource::collection($complaints)],
            __('messages.complaints_retrieved')
        );
    }

    public function getByMinistry(Ministry $ministry)
    {
        $this->authorize('viewByMinistry', [Complaint::class, $ministry->id]);
        $complaints = $this->complaintService->getByMinistry($ministry);

        return $this->successCollection(
            $complaints,
            ComplaintResource::class,
            'messages.complaints_retrieved'
        );
    }

    public function getByBranch(MinistryBranch $branch)
    {
        $this->authorize('viewByBranch', [Complaint::class, $branch->id]);
        $complaints = $this->complaintService->getByBranch($branch);

        return $this->successCollection(
            $complaints,
            ComplaintResource::class,
            'messages.complaints_retrieved'
        );
    }

    public function readOne(Complaint $complaint)
    {
        $this->authorize('view', $complaint);

        return $this->successResponse(
            new ComplaintResource($complaint),
            __('messages.complaint_retrieved'),
        );
    }

    public function updateStatus(Complaint $complaint, Request $request)
    {
        $request->validate([
            'status' => 'required|in:resolved,rejected',
            'reason' => 'nullable|string|max:500'
        ]);

        $reason = $request->reason ?? "";
        $this->authorize('view', $complaint);

        $this->complaintService->callWithLogging('updateStatus', $complaint, $request->status, Auth::user()->employee, $reason);
        return $this->successResponse([], __('messages.complaint_status_updated'));
    }

    public function startProcessing(Complaint $complaint)
    {
        $this->authorize('view', $complaint);
        $employee = Auth::user()?->employee;
        $this->complaintService->callWithLogging('startProcessing', $complaint, $employee);
        return $this->successResponse([], __('messages.complaint_started_processing'));
    }

    public function delete(Complaint $complaint)
    {
        $this->authorize('view', $complaint);
        $this->complaintService->callWithLogging('delete', $complaint);
        return $this->successResponse([], __('messages.deleted_successfully'));
    }
}
