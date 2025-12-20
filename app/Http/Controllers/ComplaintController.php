<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Requests\SubmitComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Services\ComplaintService;
use App\Traits\ResponseTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    use ResponseTrait, AuthorizesRequests;

    protected $service;

    public function __construct(ComplaintService $complaintService)
    {
        $this->service = $complaintService;
    }

    public function submit(SubmitComplaintRequest $request)
    {
        $data = $request->validated();
        $data['citizen_id'] = Auth::user()->citizen->id;
        $complaint = $this->service->submitComplaint($data);

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
        $this->authorize('viewByMinistry', [Complaint::class, $id]);
        $complaints = $this->service->getByMinistry($id);

        if ($complaints->isEmpty()) {
            return $this->successResponse([], __('messages.empty'));
        }

        return $this->successResponse(ComplaintResource::collection($complaints), __('messages.complaints_retrieved'));
    }

    public function getByBranch($id)
    {
        $this->authorize('viewByBranch', [Complaint::class, $id]);
        $complaints = $this->service->getByBranch($id);

        if ($complaints->isEmpty()) {
            return $this->successResponse([], __('messages.empty'));
        }

        return $this->successResponse(ComplaintResource::collection($complaints), __('messages.complaints_retrieved'));
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

        $this->authorize('view', $complaint);

        $this->service->updateStatus($complaint, $request->status, $request->input('reason'), Auth::user()->employee);
        return $this->successResponse([], __('messages.complaint_status_updated'));
    }

    public function startProcessing(Complaint $complaint)
    {
        $this->authorize('view', $complaint);
        try {
            $employee = Auth::user()->employee;
            $this->service->startProcessing($complaint, $employee);
            return $this->successResponse(__('messages.complaint_started_processing'));
        } catch (BusinessException $e) {
            return $this->errorResponse(
                __('messages.' . $e->messageKey()),
                409
            );
        }
    }

    public function delete(Complaint $complaint)
    {
        $this->authorize('view', $complaint);
        $result = $this->service->delete($complaint);
        if (!$result) {
            return $this->errorResponse(__('messages.not_found'), 404);
        }
        return $this->successResponse([], __('messages.deleted_successfully'));
    }
}
