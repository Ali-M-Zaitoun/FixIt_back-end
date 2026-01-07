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

    public function read()
    {
        return Ministry::all();
    }

    public function update(Ministry $ministry, $data)
    {
        $ministry->update(
            collect($data)
                ->only(['status', 'abbreviation'])
                ->filter(fn($value) => $value != null)
                ->toArray()
        );

        if (isset($data['translations'])) {
            foreach ($data['translations'] as $locale => $trans) {
                $values = ['name' => $trans['name']];
                if (array_key_exists('description', $trans))
                    $values['description'] = $trans['description'];

                $ministry->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $values
                );
            }
        }
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
