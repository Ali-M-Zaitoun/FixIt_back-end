<?php

namespace App\Http\Controllers;

use App\DAO\EmployeeDAO;
use App\Http\Requests\AddEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\UserResource;
use App\Models\Employee;
use App\Models\User;
use App\Services\EmployeeService;
use App\Services\OTPService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use function PHPUnit\Framework\isEmpty;

class EmployeeController extends Controller
{
    use ResponseTrait;

    protected EmployeeService $service;

    public function __construct()
    {
        $this->service = new EmployeeService();
    }

    public function store(AddEmployeeRequest $request)
    {
        $data = $request->validated();

        $result = $this->service->store($data);

        if (!$result['status']) {
            return $this->errorResponse(__('messages.ministry_branch_mismatch'), 400);
        }

        if (!$result['user'] || !$result['user']->employee) {
            $result['user']?->delete();
            return $this->errorResponse(__('messages.employee_creation_failed'), 500);
        }

        return $this->successResponse(
            ['employee' => new EmployeeResource($result['user']->employee)],
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

        return $this->successResponse(EmployeeResource::collection($data), __('messages.employees_retrieved'));
    }

    public function readOne($id)
    {
        $data = $this->service->readOne($id);

        if (!$data) {
            return $this->successResponse([], __('messages.not_found'));
        }

        return $this->successResponse(new EmployeeResource($data), __('messages.employee_retrieved'));
    }

    public function getByBranch($branch_id)
    {
        $data = $this->service->getByBranch($branch_id);

        if ($data->isEmpty()) {
            return $this->successResponse([], __('messages.empty'));
        }
        return $this->successResponse(EmployeeResource::collection($data), __('messages.employees_retrieved'));
    }

    public function getByMinistry($ministry_id)
    {
        $data = $this->service->getByMinistry($ministry_id);

        if ($data->isEmpty()) {
            return $this->successResponse([], __('messages.empty'));
        }
        return $this->successResponse(EmployeeResource::collection($data), __('messages.employees_retrieved'));
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
