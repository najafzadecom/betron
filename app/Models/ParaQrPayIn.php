<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParaQrPayIn extends Model
{
    protected $fillable = [
        'client_order_no',
        'system_order_no',
        'sender_full_name',
        'amount',
        'reveiver_account_name',
        'reveiver_account_iban',
        'response',
        'status',
        'hash',
        'reason',
        'direction',
        'callback_response',
        'transaction_uuid'
    ];

    protected $casts = [
        'amount' => 'float',
        'response' => 'array',
        'callback_response' => 'array',
    ];
}
