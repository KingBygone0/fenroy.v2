<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'zone_id'         => ['required', 'integer', 'exists:delivery_zones,id'],
            'name'            => ['required', 'string', 'min:2', 'max:100'],
            'phone'           => ['required', 'regex:/^(\+233|0233|0)[0-9]{9}$/'],
            'email'           => ['required', 'email'],
            'address'         => ['required', 'string', 'min:5', 'max:500'],
            'delivery_window' => ['required', 'in:morning,afternoon,evening'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ];
    }
}
