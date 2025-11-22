<?php

namespace App\Services;

use App\DAO\MinistryDAO;
use App\Http\Resources\MinistryResource;
use Illuminate\Support\Facades\Cache;

class MinistryService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new MinistryDAO();
    }

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

    public function assignManager($id, $manager_id)
    {
        $emp = (new EmployeeService())->readOne($manager_id);
        if ($id != $emp->ministry_id)
            return false;

        Cache::forget("Ministry {$id}");
        $ministry = $this->dao->assignManager($id, $manager_id);
        $emp->user->syncRoles(['employee', 'ministry_manager']);
        return $ministry;
    }
}
