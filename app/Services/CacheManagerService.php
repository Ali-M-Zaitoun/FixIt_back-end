<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheManagerService
{
    public function clearComplaintCache(
        ?int $citizenId = null,
        ?int $branchId = null,
        ?int $ministryId = null,
        ?int $single = null,
    ) {
        Cache::forget("complaints");
        if ($citizenId) {
            Cache::forget("complaints:citizen:{$citizenId}");
        }

        if ($branchId) {
            Cache::forget("complaints:branch:{$branchId}");
        }

        if ($ministryId) {
            Cache::forget("complaints:ministry:{$ministryId}");
        }

        if ($single) {
            Cache::forget("complaints:single:{$single}");
            Cache::forget("complaint:{$single}:replies");
        }
    }

    public function getByBranch(int $branchId, Closure $resolver)
    {
        $key = "complaints:branch:{$branchId}";

        return Cache::remember($key, 3600, $resolver);
    }

    public function getByMinistry(int $ministryId, Closure $resolver)
    {
        $key = "complaints:ministry:{$ministryId}";

        return Cache::remember($key, 3600, $resolver);
    }

    public function getMyComplaints(int $citizenId, Closure $resolver)
    {
        $key = "complaints:citizen:{$citizenId}";

        return Cache::remember($key, 3600, $resolver);
    }

    public function getAll(Closure $resolver)
    {
        return Cache::remember('complaints:all', 3600, $resolver);
    }

    public function getOne(int $id, Closure $resolver)
    {
        return Cache::remember("complaint:{$id}", 3600, $resolver);
    }

    public function getReplies(int $complaint_id, Closure $resolver)
    {
        $key = "complaint:{$complaint_id}:replies";

        return Cache::remember($key, 3600, $resolver);
    }

    /////////////////////////////
    //        Ministry         //
    /////////////////////////////

    public function clearMinistries()
    {
        Cache::forget("all_ministries");
    }

    public function clearMinistry($id)
    {
        Cache::forget("Ministry {$id}");
    }

    public function getMinistries(Closure $resolver)
    {
        $cacheKey = "all_ministries";
        return Cache::remember($cacheKey, 86400, $resolver);
    }

    public function getMinistry(int $id, $resolver)
    {
        $cacheKey = "Ministry {$id}";
        return Cache::remember($cacheKey, 3600, $resolver);
    }

    /////////////////////////////
    //        Branch           //
    /////////////////////////////

    public function clearBranches()
    {
        return Cache::forget("all_branches");
    }

    public function clearMinistryBranches($id)
    {
        return Cache::forget("branches_for_ministry {$id}");
    }

    public function getBranches(Closure $resolver)
    {
        $cacheKey = "all_branches";
        return Cache::remember($cacheKey, 86400, $resolver);
    }

    public function getMinistryBranches(int $id, $resolver)
    {
        $cacheKey = "branches_for_ministry {$id}";
        return Cache::remember($cacheKey, 3600, $resolver);
    }

    public function clearBranch($id)
    {
        return Cache::forget("Branch {$id}");
    }

    public function getBranchInfo($id, Closure $resolver)
    {
        $cacheKey = "Branch {$id}";
        return Cache::remember($cacheKey, 3600, $resolver);
    }

    /////////////////////////////
    //        Employee         //
    /////////////////////////////

    public function clearEmployees()
    {
        Cache::forget("all_employees");
    }

    public function clearBranchEmployees($id)
    {
        Cache::forget("employees_in_branch {$id}");
    }

    public function clearMinistryEmployees($id)
    {
        Cache::forget("employees_in_ministry {$id}");
    }

    public function getEmployees(Closure $resolver)
    {
        $cacheKey = "all_employees";
        return Cache::remember($cacheKey, 4000, $resolver);
    }

    public function getEmployeesInBranch($branch_id, Closure $resolver)
    {
        $cacheKey = "employees_in_branch {$branch_id}";
        return Cache::remember($cacheKey, 4000, $resolver);
    }

    public function getEmployeesInMinistry($ministry_id, Closure $resolver)
    {
        $cacheKey = "employees_in_ministry {$ministry_id}";
        return Cache::remember($cacheKey, 4000, $resolver);
    }
}
