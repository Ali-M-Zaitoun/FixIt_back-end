<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MinistryResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        $translation = $this->translation($locale);
        return [
            'id' => $this->id,
            'name' => $translation ? $translation->name : null,
            'abbreviation' => $this->abbreviation,
            'description' => $translation ? $translation->description : null,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d h:i A'),
            'manager' => $this->manager ? [
                'id' => $this->manager_id,
                'first_name' => $this->manager->user->first_name,
                'last_name' => $this->manager->user->last_name,
            ] : null,
            'branches' => MinistryBranchResource::collection($this->branches),
        ];
    }
}
