<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MinistryResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ministry_name' => $this->ministry_name,
            'abbreviation' => $this->abbreviation,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d h:i A'),
            'branches' => MinistryBranchResource::collection($this->branches),
        ];
    }
}
