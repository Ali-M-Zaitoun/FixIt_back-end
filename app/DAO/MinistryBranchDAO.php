<?php

namespace App\DAO;

use App\Models\MinistryBranch;

class MinistryBranchDAO
{
    public function store($data)
    {
        $branch = MinistryBranch::create([
            'ministry_id' => $data['ministry_id'],
            'governorate_id' => $data['governorate_id'],
        ]);

        foreach ($data['translations'] as $locale => $trans) {
            $branch->translations()->create([
                'locale' => $locale,
                'name' => $trans['name']
            ]);
        }
        return $branch;
    }

    public function read()
    {
        return MinistryBranch::all();
    }

    public function readOne($id)
    {
        return MinistryBranch::where('id', $id)->first();
    }

    public function update(MinistryBranch $branch, $data)
    {
        $branch->update(
            collect($data)
                ->only(['ministry_id', 'governorate_id'])
                ->filter(fn($value) => $value != null)
                ->toArray()
        );

        if (isset($data['translations'])) {
            foreach ($data['translations'] as $locale => $trans) {
                $branch->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name'        => $trans['name'],
                        'description' => $trans['description'] ?? null,
                    ]
                );
            }
        }
    }

    public function assignManager($branch, $employee_id)
    {
        return $branch->update(['manager_id' => $employee_id]);
    }

    public function removeManager($branch)
    {
        return $branch->update(['manager_id' => null]);
    }

    public function delete($branch)
    {
        return $branch->delete();
    }
}
