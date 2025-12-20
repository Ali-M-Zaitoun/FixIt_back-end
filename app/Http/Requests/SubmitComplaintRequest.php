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
            'ministry_id'        => 'required|exists:ministries,id',
            'ministry_branch_id' => 'nullable|exists:ministry_branches,id',
            'city_name'          => 'nullable|string|max:255',
            'street_name'        => 'nullable|string|max:255',
            'media'              => 'nullable|array',
            'media.*'            => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
            // 'governorate_id'     => 'required|exists:governorates,id',
            // 'locked_by'          => 'nullable',
            // 'locked_at'          => 'nullable'
        ];
    }
}
