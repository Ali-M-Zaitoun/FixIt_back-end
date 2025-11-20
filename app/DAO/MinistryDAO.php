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

    public function setManager($id, $manager_id)
    {
        $ministry = $this->readOne($id);
        $ministry->update(['manager_id' => $manager_id]);
        return $ministry;
    }
}
