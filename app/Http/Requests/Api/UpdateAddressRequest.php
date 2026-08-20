<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'full_name' => ['nullable', 'string', 'min:2', 'max:100'],
            'phone'     => ['nullable', 'regex:/^(\+233|0233|0)[0-9]{9}$/'],
            'line1'     => ['nullable', 'string', 'min:5', 'max:255'],
            'city'      => ['nullable', 'string', 'max:100'],
            'region'    => ['nullable', 'string', 'max:100'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
