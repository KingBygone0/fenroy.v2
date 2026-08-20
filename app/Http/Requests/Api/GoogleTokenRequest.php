<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GoogleTokenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_token' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
