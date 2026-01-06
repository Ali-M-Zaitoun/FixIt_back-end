<?php

namespace App\Services;

use App\DAO\EmployeeDAO;
use App\Http\Requests\BaseUserRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\MinistryBranch;
use App\Models\User;
use App\Models\UserOTP;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class EmployeeService
{
    public function __construct(
        protected EmployeeDAO $dao,
        protected OTPService $otpService,
        protected CacheManagerService $cacheManager
    ) {}

    public function store($data, MinistryBranchService $ministryBranchService)
    {
        $dataUser = Arr::only($data, ['first_name', 'last_name', 'email', 'phone', 'role', 'address']);
        $dataUser['password'] = bcrypt($dataUser['first_name'] . '12345');

        $ministryId = $data['ministry_id'] ?? null;
        $branchId   = $data['ministry_branch_id'] ?? null;

        if ($branchId) {
            $branch = $ministryBranchService->readOne($branchId);
            if ($branch->ministry_id != $ministryId) {
                return [
                    'status' => false,
                ];
            }
        }

        $dataUser['status'] = true;
        $user = $this->dao->store($data, $dataUser);
        $user->syncRoles($data['role']);

        return [
            'status' => true,
            'user' => $user
        ];
    }

    public function read()
    {
        return $this->cacheManager->getEmployees(
            fn() => $this->dao->read()
        );
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

    public function readOne($id)
    {
        return $this->dao->readOne($id);
    }

    // public function promoteEmployee($id, $new_role, $new_end_date = null)
    // {
    //     $employee = $this->dao->readOne($id);
    //     $user = Auth::user();

    //     $promotionRules = [
    //         'ministry_manager' => [
    //             'allowed_roles' => ['super_admin'],
    //             'sync_roles'    => ['employee', 'ministry_manager'],
    //         ],
    //         'branch_manager' => [
    //             'allowed_roles' => ['super_admin', 'ministry_manager'],
    //             'sync_roles'    => ['employee', 'branch_manager'],
    //         ],
    //     ];

    //     if (!isset($promotionRules[$new_role])) {
    //         throw new \Exception(__('messages.invalid_promotion_position'), 403);
    //     }

    //     $allowedRoles = $promotionRules[$new_role]['allowed_roles'];
    //     if (!$user->hasAnyRole($allowedRoles)) {
    //         throw new \Exception(__('messages.unauthorized_promotion'), 403);
    //     }

    //     $updatedEmployee = $this->dao->updatePosition($employee, $new_role, $new_end_date);
    //     $updatedEmployee->user->syncRoles($promotionRules[$new_role]['sync_roles']);

    //     return $updatedEmployee;
    // }
}
