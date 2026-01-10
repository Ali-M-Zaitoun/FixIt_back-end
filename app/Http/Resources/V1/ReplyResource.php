<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'sender'        => $this->sender_type,
            'sender_id'     => $this->sender_id,
            'content'       => $this->content,
            'created_at'    => $this->created_at->format('Y-m-d H:i A'),
            'media'         => MediaResource::collection($this->media),
        ];
    }
}
