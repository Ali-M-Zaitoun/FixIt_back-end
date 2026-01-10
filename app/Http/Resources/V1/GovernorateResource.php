<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GovernorateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $translation = $this->translation($locale);
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $translation ? $translation->name : null
        ];
    }
}
