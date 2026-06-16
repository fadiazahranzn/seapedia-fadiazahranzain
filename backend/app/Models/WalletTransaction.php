<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    public $timestamps = false;
    const UPDATED_AT = null;

    protected $fillable = ['wallet_id', 'type', 'amount', 'reference_id', 'description', 'created_at'];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
