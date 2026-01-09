<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Traits\ResponseTrait;

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

        $employee = $this->service->store($data);

        return $this->successResponse(
            ['employee' => new EmployeeResource($employee)],
            __('messages.employee_stored'),
            201
        );
    }

    public function read()
    {
        $data = $this->service->read();

        return $this->successResponse(
            EmployeeResource::collection($data),
            $data->isEmpty() ? __('messages.employees_retrieved') : __('messages.empty')
        );
    }

    public function readAll()
    {
        $data['active'] = EmployeeResource::collection($this->service->read());
        $data['trashed'] = EmployeeResource::collection($this->service->readTrashed());
        return $this->successResponse(
            $data,
            blank($data) ? __('messages.employees_retrieved') : __('messages.empty')
        );
    }

    public function readOne(Employee $employee)
    {
        return $this->successResponse(new EmployeeResource($employee), __('messages.employee_retrieved'));
    }

    public function getByBranch($branch_id)
    {
        $data = $this->service->getByBranch($branch_id);

        return $this->successResponse(
            EmployeeResource::collection($data),
            $data->isEmpty() ? __('messages.employees_retrieved') : __('messages.empty')
        );
    }

    public function getByMinistry($ministry_id)
    {
        $data = $this->service->getByMinistry($ministry_id);

        return $this->successResponse(
            EmployeeResource::collection($data),
            $data->isEmpty() ? __('messages.employees_retrieved') : __('messages.empty')
        );
    }

    public function delete(Employee $employee)
    {
        $this->service->delete($employee);
        return $this->successResponse([], __('messsages.success'));
    }
}
