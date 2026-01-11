<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MinistryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $translation = $this->translation($locale);

        $allBranches = $this->branches;

        return [
            'id'           => $this->id,
            'name'         => $translation?->name,
            'abbreviation' => $this->abbreviation,
            'description'  => $translation?->description,
            'status'       => (bool) $this->status,
            'created_at'   => $this->created_at->format('Y-m-d h:i A'),

            'manager' => $this->manager ? [
                'id'         => $this->manager_id,
                'first_name' => $this->manager->user?->first_name,
                'last_name'  => $this->manager->user?->last_name,
            ] : null,

            'active_branches'  => MinistryBranchResource::collection(
                $allBranches->filter(fn($branch) => !$branch->trashed())
            ),
            'trashed_branches' => MinistryBranchResource::collection(
                $allBranches->filter(fn($branch) => $branch->trashed())
            ),
        ];
    }
}
