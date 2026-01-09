<?php

namespace App\DAO;

use App\Models\Ministry;

class MinistryDAO
{
    public function store(array $ministryData, array $translations)
    {
        $ministry = Ministry::create($ministryData);

        foreach ($translations as $locale => $trans) {
            $ministry->translations()->create([
                'locale'      => $locale,
                'name'        => $trans['name'],
                'description' => $trans['description'] ?? null,
            ]);
        }
        return $ministry;
    }

    public function read()
    {
        return Ministry::all();
    }

    public function readTrashed()
    {
        return Ministry::onlyTrashed()->get();
    }

    public function update(Ministry $ministry, array $ministryData, array $translations = [])
    {
        $ministry->update($ministryData);

        foreach ($translations as $locale => $trans) {
            $values = ['name' => $trans['name']];

            if (array_key_exists('description', $trans))
                $values['description'] = $trans['description'];

            $ministry->translations()->updateOrCreate(
                ['locale' => $locale],
                $values
            );
        }
        return $ministry;
    }

    public function delete(Ministry $ministry)
    {
        $ministry->delete();
    }

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
