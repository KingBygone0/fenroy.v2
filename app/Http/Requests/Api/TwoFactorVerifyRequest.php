<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorVerifyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'digits:6'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
