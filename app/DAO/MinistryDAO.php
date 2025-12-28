<?php

namespace App\DAO;

use App\Models\Ministry;

class MinistryDAO
{
    public function store($data)
    {
        $ministry = Ministry::create([
            'abbreviation' => $data['abbreviation'],
            'status'       => true
        ]);

        foreach ($data['translations'] as $locale => $trans) {
            $ministry->translations()->create([
                'locale'      => $locale,
                'name'        => $trans['name'],
                'description' => $trans['description'] ?? null,
            ]);
        }
        return $ministry;
    }

    public function readAll()
    {
        return Ministry::all();
    }

    public function update($id, $data) {}

    public function delete($id) {}

    public function readOne($id)
    {
        return Ministry::where('id', $id)->first();
    }

    public function assignManager($ministry, $employee_id)
    {
        return $ministry->update(['manager_id' => $employee_id]);
    }

    public function removeManager($ministry)
    {
        return $ministry->update(['manager_id' => null]);
    }
}
