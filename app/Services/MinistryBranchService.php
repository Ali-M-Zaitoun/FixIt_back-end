<?php

namespace App\Services;

use App\DAO\MinistryBranchDAO;
use App\Http\Resources\MinistryBranchResource;
use Illuminate\Support\Facades\Cache;

class MinistryBranchService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new MinistryBranchDAO();
    }

    public function store(array $data)
    {
        $branch = $this->dao->store($data);
        Cache::forget("all_branches");
        Cache::forget("branches_for_ministry {$data['ministry_id']}");
        return $branch;
    }

    public function read()
    {
        $cacheKey = "all_branches";
        $branches = Cache::remember($cacheKey, 86400, function () {
            return $this->dao->read();
        });
        return $branches;
    }

    public function readOne($id)
    {
        $cacheKey = "Branch {$id}";
        $branch = Cache::remember($cacheKey, 86400, function () use ($id) {
            return $this->dao->readOne($id);
        });
        return $branch;
    }

    public function setManager($id, $manager_id)
    {
        Cache::forget("Branch {$id}");
        $ministry = $this->dao->setManager($id, $manager_id);

        return $ministry;
    }
}
