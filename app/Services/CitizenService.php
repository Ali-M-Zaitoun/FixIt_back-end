<?php

namespace App\Services;

use App\DAO\CitizenDAO;
use App\Models\Citizen;

class CitizenService
{
    public function __construct(
        protected CitizenDAO $citizenDAO,
        protected CacheManagerService $cacheManager
    ) {}

    public function read()
    {
        return $this->cacheManager->getAllCitizens(
            fn() => $this->citizenDAO->read()
        );
    }

    public function readOne(Citizen $citizen)
    {
        return $this->cacheManager->getCitizenProfile(
            $citizen->id,
            fn() => $citizen
        );
    }

    public function delete($citizen)
    {
        $this->cacheManager->clearCitizens();
        $this->cacheManager->clearCitizenProfile($citizen->id);

        $this->citizenDAO->delete($citizen);
    }
}
