<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AddEmployeeRequest extends BaseUserRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->commonRules(), [
            'role'                 => 'required',
            'ministry_id'          => 'required|exists:ministries,id',
            'ministry_branch_id'   => 'nullable|exists:ministry_branches,id',
            'start_date'           => 'required|date',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
        ]);
    }
}
