<?php

namespace App\Services;

use App\DAO\MinistryDAO;
use App\Models\Employee;
use App\Models\Ministry;
use Illuminate\Support\Facades\DB;

class MinistryService
{
    public function __construct(
        protected MinistryDAO $ministrtDAO,
        protected CacheManagerService $cacheManager
    ) {}

    public function store(array $data)
    {
        $ministry = $this->ministrtDAO->store($data);
        $this->cacheManager->clearMinistries();
        return $ministry;
    }

    public function read()
    {
        return $this->cacheManager->getMinistries(
            fn() => $this->ministrtDAO->read()
        );
    }

    public function readOne(Ministry $ministry)
    {
        return $this->cacheManager->getMinistry(
            $ministry->id,
            fn() => $ministry
        );
    }

    public function update(Ministry $ministry, $data)
    {
        return DB::transaction(function () use ($ministry, $data) {
            return $this->ministrtDAO->update($ministry, $data);
        });
    }

    public function assignManager($ministry, $employee)
    {
        if ($ministry->id != $employee->ministry_id)
            return false;

        $this->cacheManager->clearMinistry($ministry->id);
        $ministry = $this->ministrtDAO->assignManager($ministry, $employee->id);
        $employee->user->syncRoles(['employee', 'ministry_manager']);
        $employee->user->update(['role' => 'ministry_manager']);
        return $ministry;
    }

    public function removeManager($ministry)
    {
        $this->cacheManager->clearMinistry($ministry->id);

        $employee = Employee::find($ministry->manager_id);
        $employee->user->update(['role' => 'employee']);
        $employee->user->assignRole('employee');
        $ministry = $this->ministrtDAO->removeManager($ministry);
        return $ministry;
    }

    public function delete($ministry)
    {
        $this->cacheManager->clearMinistry($ministry->id);
        return $this->ministrtDAO->delete($ministry);
    }
}
