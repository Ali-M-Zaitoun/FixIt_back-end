<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = "";
        $id = "";
        if ($this->citizen) {
            $type = 'citizen_id';
            $id = $this->citizen->id;
        } else if ($this->employee) {
            $type = 'employee_id';
            $id = $this->employee->id;
        }
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'role'       => $this->role,
            'address'    => $this->address,
            'created_at' => $this->created_at->format('Y-m-d H:i A'),
            "$type"        => $id
        ];
    }
}
