<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PratikWallet extends Model
{
    protected $fillable = [
        'wallet_id', //Own wallet ID
        'walletId', // Pratik Wallet ID
        'totalBalance',
        'unavailableBalance',
        'dailyIncomingLimit',
        'dailyOutgoingLimit',
        'iban',
        'bankName',
        'currencyCode'
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}
