<?php

namespace App\Services;

use App\DAO\EmployeeDAO;
use App\Exceptions\MinistryMismatchException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function __construct(
        protected EmployeeDAO $dao,
        protected OTPService $otpService,
        protected CacheManagerService $cacheManager,
        protected MinistryBranchService $ministryBranchService
    ) {}

    public function store($data)
    {
        $this->validateMinistryBranch($data['ministry_id'] ?? null, $data['ministry_branch_id'] ?? null);

        return DB::transaction(function () use ($data) {
            $dataUser = Arr::only($data, ['first_name', 'last_name', 'email', 'phone', 'role', 'address']);
            $dataUser['password'] = bcrypt($dataUser['first_name'] . '12345');
            $dataUser['status'] = true;

            $user = $this->dao->store($data, $dataUser);

            $user->syncRoles($data['role']);

            return $user->employee;
        });
    }

    private function validateMinistryBranch($ministry_id, $ministry_branch_id)
    {
        if ($ministry_branch_id) {
            $branch = $this->ministryBranchService->readOne($ministry_branch_id);
            if ($branch->ministry_id != $ministry_id) {
                throw new MinistryMismatchException();
            }
        }
    }

    public function read()
    {
        return $this->cacheManager->getEmployees(
            fn() => $this->dao->read()
        );
    }

    public function readTrashed()
    {
        return $this->cacheManager->getTrashedEmployees(
            fn() => $this->dao->readTrashed()
        );
    }

    public function readOne($id)
    {
        return $this->dao->readOne($id);
    }

    public function getByBranch($branch_id)
    {
        return $this->cacheManager->getEmployeesInBranch(
            $branch_id,
            fn() => $this->dao->getByBranch($branch_id)
        );
    }

    public function getByMinistry($ministry_id)
    {
        return $this->cacheManager->getEmployeesInMinistry(
            $ministry_id,
            fn() => $this->dao->getByMinistry($ministry_id)
        );
    }

    public function delete($employee)
    {
        $this->cacheManager->clearEmployees();
        return $this->dao->delete($employee);
    }
}
