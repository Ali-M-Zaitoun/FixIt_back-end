<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'ministry_branch_id' => 'required|exists:ministry_branches,id',
            'governorate_id' => 'required|exists:governorates,id',
            'city_name' => 'nullable|string|max:255',
            'street_name' => 'nullable|string|max:255',
            'media.*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx',
        ];
    }
}
