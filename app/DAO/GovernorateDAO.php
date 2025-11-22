<?php

namespace App\DAO;

use App\Models\Governorate;
use App\Models\Ministry;

class GovernorateDAO
{
    public function read()
    {
        return Governorate::all();
    }

    public function readOne($id)
    {
        return Governorate::where('id', $id)->first();
    }
}
