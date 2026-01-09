<?php

namespace App\DAO;

use App\Models\MinistryBranch;

class MinistryBranchDAO
{
    public function store(array $branchData, array $translations)
    {
        $branch = MinistryBranch::create($branchData);

        foreach ($translations as $locale => $trans) {
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

    public function readTrashed()
    {
        return MinistryBranch::onlyTrashed()->get();
    }

    public function readOne($id)
    {
        return MinistryBranch::where('id', $id)->first();
    }

    public function update(MinistryBranch $branch, array $branchData, array $translations = [])
    {
        $branch->update($branchData);

        foreach ($translations as $locale => $trans) {
            $branch->translations()->updateOrCreate(
                ['locale' => $locale],
                ['name' => $trans['name']]
            );
        }
        return $branch;
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
