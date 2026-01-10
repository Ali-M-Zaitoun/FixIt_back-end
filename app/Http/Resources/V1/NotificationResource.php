<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => __('messages.' . $this->data['title']),
            'body'         => __('messages.' . $this->data['body']),
            'read_at'      => $this->read_at
                ? \Carbon\Carbon::parse($this->read_at)->diffForHumans()
                : null,
            'created_at'   => $this->created_at->format('Y-m-d H:i A'),
        ];
    }
}
