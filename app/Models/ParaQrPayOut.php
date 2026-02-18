<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParaQrPayOut extends Model
{
    protected $fillable = [
        'client_order_no',
        'receiver_full_name',
        'receiver_iban',
        'description',
        'amount',
        'response',
        'system_order_no',
        'payout_message',
        'message',
        'status',
        'hash',
        'reason',
        'direction',
        'callback_response'
    ];

    protected $casts = [
        'amount' => 'float',
        'response' => 'array',
        'callback_response' => 'array'
    ];
}
