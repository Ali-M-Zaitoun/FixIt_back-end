<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userName = null;

        if ($this->causer && $this->causer_type === 'App\\Models\\User') {
            $userName = $this->causer->first_name . ' ' . $this->causer->last_name;
        }

        return [
            'id'           => $this->id,
            'subject_type' => $this->subject_type,
            'subject_id'   => $this->subject_id,
            'causer_name'  => $userName,
            'description'  => $this->description,
            'created_at'   => $this->created_at->toDateTimeString(),
        ];
    }
}
