<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ResponseTrait;

    public function add(AddEmployeeRequest $request)
    {
        $data = $request->validated();
        $employee = Employee::create($data);
        $user = $employee->user;
        $user->assignRole('employee');

        return $this->successResponse(
            __('messages.employee_added'),
            ['employee' => $employee],
            201
        );
    }

    public function getEmployees()
    {
        $employees = EmployeeResource::collection(Employee::all());
        return $this->successResponse(
            __('messages.employees_retrieved'),
            ['employees' => $employees],
            200
        );
    }

    public function getEmployeesInBranch($ministry_branch_id)
    {
        $employees = EmployeeResource::collection(Employee::where('ministry_branch_id', $ministry_branch_id)->get());
        return $this->successResponse(
            __('messages.employees_retrieved'),
            ['employees' => $employees],
            200
        );
    }
}
