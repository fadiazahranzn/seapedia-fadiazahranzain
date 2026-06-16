<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'store_id', 'address_snapshot', 'voucher_id', 'promo_id',
        'delivery_method', 'subtotal', 'discount_amount', 'delivery_fee',
        'ppn_amount', 'total', 'status',
    ];

    protected function casts(): array
    {
        return ['address_snapshot' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function sellerTransactions()
    {
        return $this->hasMany(SellerTransaction::class);
    }
}
