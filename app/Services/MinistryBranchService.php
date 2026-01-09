<?php

namespace App\Services;

use App\DAO\MinistryBranchDAO;
use App\Models\Employee;
use App\Models\MinistryBranch;
use App\Traits\Loggable;
use Exception;
use Illuminate\Support\Facades\DB;

class MinistryBranchService
{
    use Loggable;
    public function __construct(
        protected MinistryBranchDAO $dao,
        protected CacheManagerService $cacheManager
    ) {}

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $branchData = [
                'ministry_id' => $data['ministry_id'],
                'governorate_id' => $data['governorate_id'],
            ];

            $branch = $this->dao->store($branchData, $data['translations']);

            $this->cacheManager->clearBranches();
            $this->cacheManager->clearMinistryBranches($data['ministry_id']);

            return $branch;
        });
    }

    public function read()
    {
        return $this->cacheManager->getBranches(
            fn() => $this->dao->read()
        );
    }

    public function readTrashed()
    {
        return $this->cacheManager->getTrashedBranches(
            fn() => $this->dao->readTrashed()
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
        if ($employee->ministry_id !== $branch->ministry_id) {
            throw new \Exception(__('messages.employee_ministry_mismatch'));
        }

        return DB::transaction(function () use ($branch, $employee) {
            $branch = $this->dao->assignManager($branch, $employee->id);

            $employee->user->update(['role' => 'branch_manager']);
            $employee->user->syncRoles(['employee', 'branch_manager']);

            $this->cacheManager->clearBranch($branch->id);
            return $branch->fresh();
        });
    }

    public function removeManager($branch)
    {
        if (!$branch->manager_id) {
            throw new Exception(__('messages.manager_removed_failed'), 409);
        }

        return DB::transaction(function () use ($branch) {
            $employee = Employee::find($branch->manager_id);
            if ($employee) {
                $employee->user->update(['role' => 'employee']);
                $employee->user->syncRoles(['employee']);
            }
            $branch = $this->dao->removeManager($branch);

            $this->cacheManager->clearBranch($branch->id);
            $this->cacheManager->clearBranches();

            return $branch->fresh();
        });
    }

    public function update(MinistryBranch $branch, $data)
    {
        $branchData = collect($data)
            ->only('ministry_id', 'governorate_id')
            ->filter(fn($value) => $value != null)
            ->toArray();

        $translations = $data['translations'];
        return DB::transaction(function () use ($branch, $branchData, $translations) {
            $ministryId = $branchData['ministry_id'] ?? $branch->ministry_id;

            $updatedBranch = $this->dao->update($branch, $branchData, $translations);

            $this->cacheManager->clearBranches();
            $this->cacheManager->clearMinistryBranches($ministryId);

            return $updatedBranch;
        });
    }

    public function delete(MinistryBranch $branch)
    {
        $this->cacheManager->clearBranches();
        $this->cacheManager->clearMinistryBranches($branch->ministry_id);
        $this->cacheManager->clearBranchesTrashed();
        return $this->dao->delete($branch);
    }
}
