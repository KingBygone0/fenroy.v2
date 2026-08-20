<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'   => ['nullable', 'string', 'min:2', 'max:100'],
            'phone'  => ['nullable', 'regex:/^(\+233|0233|0)[0-9]{9}$/'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
