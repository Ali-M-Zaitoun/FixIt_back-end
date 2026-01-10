<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends BaseUserRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->commonRules(), [
            'password'      => 'required|string|min:8|confirmed',
            'nationality'   => 'required|string',
            'national_id'   => 'required|string|unique:citizens,national_id',
            'img'           => 'nullable|file|mimes:jpg,jpeg,png'
        ]);
    }
}
