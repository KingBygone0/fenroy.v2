<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $fillable = [
        'name', 'areas', 'fee', 'free_above', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'areas'      => 'array',
            'is_active'  => 'boolean',
            'fee'        => 'decimal:2',
            'free_above' => 'decimal:2',
        ];
    }
}
