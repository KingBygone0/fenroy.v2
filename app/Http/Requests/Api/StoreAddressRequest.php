<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:100'],
            'phone'     => ['required', 'regex:/^(\+233|0233|0)[0-9]{9}$/'],
            'line1'     => ['required', 'string', 'min:5', 'max:255'],
            'city'      => ['required', 'string', 'max:100'],
            'region'    => ['required', 'string', 'max:100'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
