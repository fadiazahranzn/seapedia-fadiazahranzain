<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Store extends Model
{
    protected $fillable = ['user_id', 'name', 'slug', 'description'];

    protected static function booted(): void
    {
        static::creating(function (Store $store) {
            $store->slug = Str::slug($store->name);
        });

        static::updating(function (Store $store) {
            if ($store->isDirty('name')) {
                $store->slug = Str::slug($store->name);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sellerTransactions()
    {
        return $this->hasMany(SellerTransaction::class);
    }
}
