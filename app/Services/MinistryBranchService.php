<?php

namespace App\Services;

use App\DAO\MinistryBranchDAO;
use App\Models\Employee;
use Illuminate\Support\Facades\Cache;

class MinistryBranchService
{
    public function __construct(
        protected MinistryBranchDAO $dao
    ) {}

    public function store(array $data)
    {
        $branch = $this->dao->store($data);
        Cache::forget("all_branches");
        Cache::forget("branches_for_ministry {$data['ministry_id']}");
        return $branch;
    }

    public function read()
    {
        $cacheKey = "all_branches";
        $branches = Cache::remember($cacheKey, 86400, function () {
            return $this->dao->read();
        });
        return $branches;
    }

    public function readOne($id)
    {
        $cacheKey = "Branch {$id}";
        $branch = Cache::remember($cacheKey, 86400, function () use ($id) {
            return $this->dao->readOne($id);
        });
        return $branch;
    }

    public function assignManager($branch, $employee)
    {
        Cache::forget("Branch {$branch->id}");
        $branch = $this->dao->assignManager($branch, $employee->id);

        $employee->user->syncRoles(['employee', 'branch_manager']);
        $employee->user->update(['role' => 'branch_manager']);
        return $branch;
    }

    public function removeManager($branch)
    {
        Cache::forget("Branch {$branch->id}");
        $employee = Employee::find($branch->manager_id);
        $employee->user->update(['role' => 'employee']);
        $employee->user->assignRole('employee');
        $branch = $this->dao->removeManager($branch);
        return $branch;
    }
}
