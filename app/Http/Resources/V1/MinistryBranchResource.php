<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MinistryBranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $translation = $this->translation($locale);
        return [
            'id' => $this->id,
            'name' => $translation ? $translation->name : null,
            'governorate' => new GovernorateResource($this->governorate),
            'ministry_id' => $this->ministry_id,
            'ministry_name' => $this?->ministry?->translation($locale)->name,
            'created_at' => $this->created_at->format('Y-m-d H:i A'),
        ];
    }
}
