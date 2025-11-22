<?php

namespace App\DAO;

use App\Models\Citizen;

class CitizenDAO
{
    public function read()
    {
        return Citizen::all();
    }

    public function findById($id)
    {
        return Citizen::where('id', $id)->first();
    }

    public function completeInfo($id, array $data)
    {
        $citizen = $this->findById($id);
        $citizen->fill([
            'national_id' => $data['national_id'] ?? $citizen->national_id,
            'nationality' => $data['nationality'] ?? $citizen->nationality,
        ]);

        if ($citizen->isDirty()) {
            $citizen->save();
        }
    }
}
