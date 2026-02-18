<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransactionBank extends Model
{
    protected $fillable = [
        'wallet_id',
        'bank_id',
    ];
}
