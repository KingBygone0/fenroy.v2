<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
