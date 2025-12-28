<?php

namespace App\Services;

use App\DAO\MinistryDAO;
use App\Http\Resources\MinistryResource;
use App\Models\Employee;
use Illuminate\Support\Facades\Cache;

class MinistryService
{
    public function __construct(
        protected MinistryDAO $dao
    ) {}

    public function store(array $data)
    {
        $ministry = $this->dao->store($data);
        Cache::forget('all_ministries');
        return $ministry;
    }

    public function read()
    {
        $cacheKey = "all_ministries";
        $ministries = Cache::remember($cacheKey, 86400, function () {
            return $this->dao->readAll();
        });

        return $ministries;
    }

    public function readOne($id)
    {
        $cacheKey = "Ministry {$id}";
        $ministry = Cache::remember($cacheKey, 3600, function () use ($id) {
            return $this->dao->readOne($id);
        });

        return $ministry;
    }

    public function assignManager($ministry, $employee)
    {
        if ($ministry->id != $employee->ministry_id)
            return false;

        Cache::forget("Ministry {$ministry->id}");
        $ministry = $this->dao->assignManager($ministry, $employee->id);
        $employee->user->syncRoles(['employee', 'ministry_manager']);
        $employee->user->update(['role' => 'ministry_manager']);
        return $ministry;
    }

    public function removeManager($ministry)
    {
        Cache::forget("Ministry {$ministry->id}");

        $employee = Employee::find($ministry->manager_id);
        $employee->user->update(['role' => 'employee']);
        $employee->user->assignRole('employee');
        $ministry = $this->dao->removeManager($ministry);
        return $ministry;
    }
}
