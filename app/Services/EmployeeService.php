<?php

namespace App\Services;

use App\DAO\EmployeeDAO;
use App\Http\Requests\BaseUserRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\User;
use App\Models\UserOTP;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class EmployeeService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new EmployeeDAO();
    }

    public function store($data, OTPService $otpService)
    {
        $dataUser = Arr::only($data, ['first_name', 'last_name', 'email', 'phone', 'role', 'address']);
        $dataUser['password'] = bcrypt($dataUser['first_name'] . '12345');

        $user = $this->dao->store($data, $dataUser);
        $user->syncRoles($data['role']);
        $otpService->resendOTP($user->id);
        return $user;
    }

    public function read()
    {
        $cacheKey = "all_employees";
        $employees = Cache::remember($cacheKey, 3600, function () {
            return $this->dao->read();
        });

        return $employees;
    }

    public function getByBranch($branch_id)
    {
        $cacheKey = "employees_in_branch {$branch_id}";

        $employees = Cache::remember($cacheKey, 3600, function () use ($branch_id) {
            return $this->dao->getByBranch($branch_id);
        });

        return $employees;
    }

    public function getByMinistry($ministry_id)
    {
        $cacheKey = "employees_in_ministry {$ministry_id}";

        $employees = Cache::remember($cacheKey, 3600, function () use ($ministry_id) {
            return $this->dao->getByMinistry($ministry_id);
        });

        return $employees;
    }

    public function readOne($id)
    {
        $cacheKey = "employee {$id}";
        $employee = Cache::remember($cacheKey, 3600, function () use ($id) {
            return $this->dao->readOne($id);
        });

        return $employee;
    }

    public function promoteEmployee($id, $new_role, $new_end_date = null)
    {
        $employee = $this->dao->readOne($id);
        $user = Auth::user();

        $promotionRules = [
            'ministry_manager' => [
                'allowed_roles' => ['super_admin'],
                'sync_roles'    => ['employee', 'ministry_manager'],
            ],
            'branch_manager' => [
                'allowed_roles' => ['super_admin', 'ministry_manager'],
                'sync_roles'    => ['employee', 'branch_manager'],
            ],
        ];

        if (!isset($promotionRules[$new_role])) {
            throw new \Exception(__('messages.invalid_promotion_position'), 403);
        }

        $allowedRoles = $promotionRules[$new_role]['allowed_roles'];
        if (!$user->hasAnyRole($allowedRoles)) {
            throw new \Exception(__('messages.unauthorized_promotion'), 403);
        }

        $updatedEmployee = $this->dao->updatePosition($employee, $new_role, $new_end_date);
        $updatedEmployee->user->syncRoles($promotionRules[$new_role]['sync_roles']);

        return $updatedEmployee;
    }
}
