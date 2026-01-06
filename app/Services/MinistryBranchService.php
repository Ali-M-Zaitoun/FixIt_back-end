<?php

namespace App\Services;

use App\DAO\MinistryBranchDAO;
use App\Models\Employee;
use App\Models\MinistryBranch;
use Illuminate\Support\Facades\Cache;

class MinistryBranchService
{
    public function __construct(
        protected MinistryBranchDAO $dao,
        protected CacheManagerService $cacheManager
    ) {}

    public function store(array $data)
    {
        $branch = $this->dao->store($data); {
            $this->cacheManager->clearBranches();
            $this->cacheManager->clearMinistryBranches($data['ministry_id']);
        }

        return $branch;
    }

    public function read()
    {
        return $this->cacheManager->getBranches(
            fn() => $this->dao->read()
        );
    }

    public function readOne($id)
    {
        return $this->cacheManager->getBranchInfo(
            $id,
            fn() => $this->dao->readOne($id)
        );
    }

    public function assignManager($branch, $employee)
    {
        $this->cacheManager->clearBranch($branch->id);

        $branch = $this->dao->assignManager($branch, $employee->id);
        $employee->user->syncRoles(['employee', 'branch_manager']);
        $employee->user->update(['role' => 'branch_manager']);
        return $branch;
    }

    public function removeManager($branch)
    {
        $this->cacheManager->clearBranch($branch->id);

        $employee = Employee::find($branch->manager_id);
        $employee->user->update(['role' => 'employee']);
        $employee->user->assignRole('employee');
        $branch = $this->dao->removeManager($branch);
        return $branch;
    }

    public function delete(MinistryBranch $branch)
    {
        return $this->dao->delete($branch);
    }
}
