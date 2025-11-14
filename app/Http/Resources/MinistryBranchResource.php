<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MinistryBranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_name' => $this->name,
            'location' => $this->location,
            'ministry_id' => $this->ministry_id,
            'ministry_name' => $this->ministry->ministry_name,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
