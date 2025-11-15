<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'position' => 'required|string|max:255',
            'start_date' => 'required|date',
            'branch_id' => 'required|exists:ministry_branches,id',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
