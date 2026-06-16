<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerTransaction extends Model
{
    public $timestamps = false;
    const UPDATED_AT = null;

    protected $fillable = ['store_id', 'order_id', 'type', 'amount', 'description'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
