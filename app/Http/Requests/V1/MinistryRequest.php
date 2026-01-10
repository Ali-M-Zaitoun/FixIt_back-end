<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class MinistryRequest extends FormRequest
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
            'abbreviation'               => 'required|string|max:50',
            'translations'               => 'required|array',
            'translations.*'             => 'required|array',
            'translations.*.name'        => 'required|string|max:255',
            'translations.*.description' => 'nullable|string',
            'status'                     => 'required|boolean',
            'manager_id'                 => 'nullable|integer|exists:employees,id',
        ];
    }
}
