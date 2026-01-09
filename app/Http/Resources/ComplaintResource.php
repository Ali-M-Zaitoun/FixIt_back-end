<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'description' => $this->description,
            'status' => $this->status,
            'media' => MediaResource::collection($this->media),
            'ministry_branch' => new MinistryBranchResource($this->ministryBranch),
            'citizen' => new CitizenResource($this->citizen),
            'created_at' => $this->created_at->format('Y-m-d H:i A'),
            'replies'   => ReplyResource::collection($this->replies)
        ];
    }
}
