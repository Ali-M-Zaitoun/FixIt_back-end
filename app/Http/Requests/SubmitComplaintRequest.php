<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'               => 'required|string|max:255',
            'description'        => 'required|string',
            'ministry_branch_id' => 'required|exists:ministry_branches,id',
            'governorate_id'     => 'required|exists:governorates,id',
            'city_name'          => 'nullable|string|max:255',
            'street_name'        => 'nullable|string|max:255',
            'media'              => 'nullable|array',
            'media.*'            => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
            'locked_by'          => 'nullable',
            'locked_at'          => 'nullable'
        ];
    }
}
