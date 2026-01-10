<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class MinistryBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ministry_id'         => 'required|integer|exists:ministries,id',
            'translations'        => 'required|array',
            'translations.*'      => 'required|array',
            'translations.*.name' => 'required|string',
            'governorate_id'      => 'required|integer|exists:governorates,id',
            'manager_id'          => 'nullable|integer|exists:employees,id',
        ];
    }
}
