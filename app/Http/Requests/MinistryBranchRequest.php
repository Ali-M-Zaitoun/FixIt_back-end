<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MinistryBranchRequest extends FormRequest
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
            'ministry_id' => 'required|integer|exists:ministries,id',
            'name' => 'required|string|max:255',
            'governorate_id' => 'required|integer|exists:governorates,id',
            'manager_id' => 'nullable|integer|exists:employees,id',
        ];
    }
}
