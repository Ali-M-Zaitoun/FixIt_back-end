<?php

namespace App\DAO;

use App\Models\Employee;
use App\Models\User;

class EmployeeDAO
{
    public function store($data, $dataUser)
    {
        $user = User::create($dataUser);
        $user->employee()->create([
            'ministry_id'         => $data['ministry_id'],
            'ministry_branch_id'  => $data['ministry_branch_id'] ?? null,
            'start_date'          => $data['start_date'],
            'end_date'            => $data['end_date'] ?? null,
        ]);

        return $user;
    }

    public function readOne($id)
    {
        return Employee::where('id', $id)->first();
    }

    public function read()
    {
        return Employee::all();
    }

    public function updatePosition($employee, $new_position, $new_end_date = null)
    {
        $employee->position = $new_position;
        if ($new_end_date) {
            $employee->end_date = $new_end_date;
        }
        $employee->save();
        return $employee;
    }

    public function getEmployeesInBranch($branch_id)
    {
        return Employee::where('ministry_branch_id', $branch_id)->get();
    }
}
