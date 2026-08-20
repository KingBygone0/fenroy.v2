<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'email'              => $this->email,
            'phone'              => $this->phone,
            'avatar'             => $this->avatar,
            'email_verified'     => (bool) $this->email_verified_at,
            'has_google'         => (bool) $this->google_id,
            'has_password'       => (bool) $this->getAttributes()['password'],
            'two_factor_enabled' => (bool) $this->two_factor_enabled_at,
        ];
    }
}
