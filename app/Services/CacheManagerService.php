<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheManagerService
{
    protected const TTL_MIN      = 1800;
    protected const TTL_HOUR     = 3600;
    protected const TTL_DAY      = 86400;
    protected const TTL_EMPLOYEE = 4000;

    protected function resolve(string $key, int $ttl, Closure $resolver)
    {
        return Cache::remember($key, $ttl, $resolver);
    }

    /////////////////////////////
    //        Complaints       //
    /////////////////////////////

    public function clearComplaintCache(?int $citizenId = null, ?int $branchId = null, ?int $ministryId = null, ?int $single = null)
    {
        Cache::forget("complaints:all");
        if ($citizenId)  Cache::forget("complaints:citizen:{$citizenId}");
        if ($branchId)   Cache::forget("complaints:branch:{$branchId}");
        if ($ministryId) Cache::forget("complaints:ministry:{$ministryId}");
        if ($single) {
            Cache::forget("complaints:id:{$single}");
            Cache::forget("complaints:id:{$single}:replies");
        }
    }

    public function getByBranch(int $id, Closure $res)
    {
        return $this->resolve("complaints:branch:{$id}", self::TTL_HOUR, $res);
    }

    public function getByMinistry(int $id, Closure $res)
    {
        return $this->resolve("complaints:ministry:{$id}", self::TTL_HOUR, $res);
    }

    public function getMyComplaints(int $id, Closure $res)
    {
        return $this->resolve("complaints:citizen:{$id}", self::TTL_HOUR, $res);
    }

    public function getAll(Closure $res)
    {
        return $this->resolve("complaints:all", self::TTL_HOUR, $res);
    }

    public function getOne(int $id, Closure $res)
    {
        return $this->resolve("complaints:id:{$id}", self::TTL_HOUR, $res);
    }

    public function getReplies(int $id, Closure $res)
    {
        return $this->resolve("complaints:id:{$id}:replies", self::TTL_HOUR, $res);
    }

    /////////////////////////////
    //        Ministry         //
    /////////////////////////////

    public function clearMinistries()
    {
        return Cache::forget("ministries:all");
    }

    public function clearMinistry($id)
    {
        return Cache::forget("ministries:id:{$id}");
    }

    public function clearMinistriesTrashed()
    {
        return Cache::forget('ministries:trashed');
    }

    public function getMinistries(Closure $res)
    {
        return $this->resolve("ministries:all", self::TTL_DAY, $res);
    }

    public function getTrashedMinstries(Closure $res)
    {
        return $this->resolve("ministries:trashed", self::TTL_DAY, $res);
    }

    public function getMinistry(int $id, $res)
    {
        return $this->resolve("ministries:id:{$id}", self::TTL_HOUR, $res);
    }

    /////////////////////////////
    //        Branch           //
    /////////////////////////////

    public function clearBranches()
    {
        return Cache::forget("branches:all");
    }

    public function clearBranch($id)
    {
        return Cache::forget("branches:id:{$id}");
    }

    public function clearMinistryBranches($id)
    {
        return Cache::forget("branches:ministry:{$id}");
    }

    public function clearBranchesTrashed()
    {
        return Cache::forget('branches:trashed');
    }

    public function getBranches(Closure $res)
    {
        return $this->resolve("branches:all", self::TTL_DAY, $res);
    }

    public function getTrashedBranches(Closure $res)
    {
        return $this->resolve("branches:trashed", self::TTL_DAY, $res);
    }

    public function getMinistryBranches(int $id, $res)
    {
        return $this->resolve("branches:ministry:{$id}", self::TTL_HOUR, $res);
    }

    public function getBranchInfo($id, Closure $res)
    {
        return $this->resolve("branches:id:{$id}", self::TTL_HOUR, $res);
    }

    /////////////////////////////
    //        Employee         //
    /////////////////////////////

    public function clearEmployees()
    {
        return Cache::forget("employees:all");
    }

    public function clearBranchEmployees($id)
    {
        return Cache::forget("employees:branch:{$id}");
    }

    public function clearMinistryEmployees($id)
    {
        return Cache::forget("employees:ministry:{$id}");
    }

    public function getEmployees(Closure $res)
    {
        return $this->resolve("employees:all", self::TTL_EMPLOYEE, $res);
    }

    public function getTrashedEmployees(Closure $res)
    {
        return $this->resolve("employees:trashed", self::TTL_EMPLOYEE, $res);
    }

    public function getEmployeesInBranch($id, Closure $res)
    {
        return $this->resolve("employees:branch:{$id}", self::TTL_EMPLOYEE, $res);
    }

    public function getEmployeesInMinistry($id, Closure $res)
    {
        return $this->resolve("employees:ministry:{$id}", self::TTL_EMPLOYEE, $res);
    }

    /////////////////////////////
    //        Citizen          //
    /////////////////////////////

    public function clearCitizens()
    {
        return Cache::forget("citizens:all");
    }

    public function clearCitizenProfile($id)
    {
        return Cache::forget("citizens:profile:{$id}");
    }

    public function getAllCitizens(Closure $res)
    {
        return $this->resolve("citizens:all", self::TTL_MIN, $res);
    }

    public function getCitizenProfile(int $id, Closure $res)
    {
        return $this->resolve("citizens:profile:{$id}", self::TTL_MIN, $res);
    }
}
