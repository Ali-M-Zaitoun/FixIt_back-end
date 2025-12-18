<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheManagerService
{
    public function clearComplaintCache(
        int $citizenId = null,
        int $branchId = null,
        int $ministryId = null,
        int $single = null,
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
}
