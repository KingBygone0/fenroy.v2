<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'areas'      => $this->areas,
            'fee'        => (float) $this->fee,
            'free_above' => $this->free_above ? (float) $this->free_above : null,
        ];
    }
}
