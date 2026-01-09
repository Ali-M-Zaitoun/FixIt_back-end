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

    public function readTrashed()
    {
        return Employee::onlyTrashed()->get();
    }

    public function getByBranch($branch_id)
    {
        return Employee::where('ministry_branch_id', $branch_id)->get();
    }

    public function getByMinistry($ministry_id)
    {
        return Employee::where('ministry_id', $ministry_id)->get();
    }

    public function delete($employee)
    {
        return $employee->delete();
    }
}
