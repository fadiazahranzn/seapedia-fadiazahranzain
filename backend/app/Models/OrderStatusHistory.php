<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    public $timestamps = false;
    const UPDATED_AT = null;

    protected $fillable = ['order_id', 'status', 'note', 'created_at'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
