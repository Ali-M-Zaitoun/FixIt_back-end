<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'user'               => new UserResource($this->user),
            'start_date'         => $this->start_date,
            'end_date'           => $this->end_date ? $this->end_date : null,
            'ministry'           => new MinistryResource($this->ministry),
            'ministry_branch_id' => $this->ministry_branch_id ? new MinistryBranchResource($this->branch) : null
        ];
    }
}
