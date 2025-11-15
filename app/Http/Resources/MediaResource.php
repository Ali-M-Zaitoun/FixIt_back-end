<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'type' => $this->type,
            'mediableable_id' => $this->mediable_id,
            'mediableable_type' => $this->mediable_type,
            'created_at' => $this->created_at->format('Y-m-d H:i A'),
        ];
    }
}
