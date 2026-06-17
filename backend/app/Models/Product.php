<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'store_id', 'name', 'category', 'brand', 'description',
        'price', 'original_price', 'stock', 'weight', 'image_url', 'specifications',
    ];

    protected function casts(): array
    {
        return ['specifications' => 'array'];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function favorites()
    {
        return $this->hasMany(ProductFavorite::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
