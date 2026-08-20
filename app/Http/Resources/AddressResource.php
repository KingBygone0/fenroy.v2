<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'full_name'  => $this->full_name,
            'phone'      => $this->phone,
            'line1'      => $this->line1,
            'city'       => $this->city,
            'region'     => $this->region,
            'is_default' => (bool) $this->is_default,
        ];
    }
}
