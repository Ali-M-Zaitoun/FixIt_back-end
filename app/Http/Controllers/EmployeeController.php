<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\UserResource;
use App\Services\EmployeeService;
use App\Services\MinistryBranchService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ResponseTrait;

    protected EmployeeService $service;

    public function __construct(EmployeeService $employeeService)
    {
        $this->service = $employeeService;
    }

    public function store(AddEmployeeRequest $request)
    {
        $data = $request->validated();

        $result = $this->service->store($data, app(MinistryBranchService::class));

        if (!$result['status']) {
            return $this->errorResponse(__('messages.ministry_branch_mismatch'), 400);
        }

        if (!$result['user'] || !$result['user']->employee) {
            $result['user']?->delete();
            return $this->errorResponse(__('messages.employee_creation_failed'), 500);
        }

        return $this->successResponse(
            ['employee' => new UserResource($result['user'])],
            __('messages.employee_stored'),
            201
        );
    }

    public function read()
    {
        $data = $this->service->read();

        if (!$data || $data->isEmpty()) {
            return $this->successResponse([], __('messages.empty'));
        }

        $data->load('user');

        $employees = $data->pluck('user')->filter();
        return $this->successResponse(UserResource::collection($employees), __('messages.employees_retrieved'));
    }

    public function readOne($id)
    {
        $data = $this->service->readOne($id);

        if (!$data) {
            return $this->successResponse([], __('messages.not_found'));
        }

        return $this->successResponse(new UserResource($data->user), __('messages.employee_retrieved'));
    }

    public function getByBranch($branch_id)
    {
        $data = $this->service->getByBranch($branch_id);

        if ($data->isEmpty()) {
            return $this->successResponse([], __('messages.empty'));
        }
        $data->load('user');

        $employees = $data->pluck('user')->filter();
        return $this->successResponse(UserResource::collection($employees), __('messages.employees_retrieved'));
    }

    public function getByMinistry($ministry_id)
    {
        $data = $this->service->getByMinistry($ministry_id);

        if ($data->isEmpty()) {
            return $this->successResponse([], __('messages.empty'));
        }
        $data->load('user');

        $employees = $data->pluck('user')->filter();
        return $this->successResponse(UserResource::collection($employees), __('messages.employees_retrieved'));
    }


    public function promoteEmployee(Request $request, $id)
    {
        $request->validate([
            'new_role' => 'required|string|max:255',
            'new_end_date' => 'nullable|date',
            'ministry_id' => 'nullable|exists:ministries,id',
            'ministry_branch_id' => 'nullable|exists:ministry_branches,id'
        ]);

        $new_role = $request->input('new_role');
        $new_end_date = $request->input('new_end_date');

        $employee = $this->service->promoteEmployee($id, $new_role, $new_end_date);

        return $this->successResponse(
            ['employee' => $employee],
            __('messages.employee_promoted'),
            200
        );
    }
}
