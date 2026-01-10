<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CitizenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'basic_info' => new UserResource($this->user),
            'national_id' => $this->national_id,
            'nationality' => $this->nationality,
            'created_at' => $this->created_at->format('Y-m-d H:i A')
        ];
    }
}
