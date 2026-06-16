<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'code', 'description', 'discount_type', 'discount_value',
        'min_purchase', 'max_discount', 'expires_at',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
