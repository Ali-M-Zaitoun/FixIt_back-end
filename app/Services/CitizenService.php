<?php

namespace App\Services;

use App\DAO\CitizenDAO;
use App\Http\Resources\MinistryResource;
use App\Models\Citizen;
use Illuminate\Support\Facades\Cache;

class CitizenService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new CitizenDAO();
    }

    public function read()
    {
        $cacheKey = "citizenn";
        return Cache::remember($cacheKey, 1800, function () {
            return $this->dao->read();
        });
    }

    public function readOne($id)
    {
        $cacheKey = "citizen {$id}";
        return Cache::remember($cacheKey, 1800, function () use ($id) {
            return $this->dao->findById($id);
        });
    }

    public function completeInfo($data, $citizen)
    {
        $this->dao->completeInfo($citizen->id, $data);

        if (isset($data['img'])) {
            app(FileManagerService::class)->storeFile(
                $citizen,
                $data['img'],
                "citizen/{$citizen->id}",
                'image',
                fn() => 'img'
            );
        }

        return $citizen;
    }
}
