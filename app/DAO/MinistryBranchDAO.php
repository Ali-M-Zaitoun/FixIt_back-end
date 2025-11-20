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

    public function setManager($id, $manager_id)
    {
        $branch = $this->readOne($id);
        $branch->update(['manager_id' => $manager_id]);
        return $branch;
    }
}
