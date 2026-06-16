<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'description', 'discount_type', 'discount_value',
        'min_purchase', 'max_discount', 'usage_limit', 'used_count', 'expires_at',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function isValid(): bool
    {
        return $this->used_count < $this->usage_limit && $this->expires_at->isFuture();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
