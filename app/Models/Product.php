<?php

namespace App\Models;

use App\Support\ProductImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'sku', 'description', 'unit', 'image',
        'price', 'old_price', 'stock', 'category', 'type',
        'is_active', 'is_featured', 'is_best_seller',
    ];

    protected $casts = [
        'price'          => 'float',
        'old_price'      => 'float',
        'is_active'      => 'boolean',
        'is_featured'    => 'boolean',
        'is_best_seller' => 'boolean',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image ? asset('storage/' . $this->image) : ProductImages::get($this->slug);
    }

    public function toCardArray(): array
    {
        return [
            'slug'      => $this->slug,
            'name'      => $this->name,
            'unit'      => $this->unit ?? '',
            'price'     => $this->price,
            'old_price' => $this->old_price,
            'stock'     => $this->stock,
            'image'     => $this->image_url,
        ];
    }
}
