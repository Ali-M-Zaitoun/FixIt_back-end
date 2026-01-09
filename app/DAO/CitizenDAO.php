<?php

namespace App\DAO;

use App\Models\Citizen;

class CitizenDAO
{
    public function store($user, $data)
    {
        return $user->citizen()->create($data);
    }

    public function read()
    {
        return Citizen::all();
    }

    public function findById($id)
    {
        return Citizen::find($id);
    }

    public function delete($citizen)
    {
        return $citizen->delete();
    }
}
