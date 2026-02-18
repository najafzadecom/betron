<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletUser extends Model
{

    protected $fillable = [
        'wallet_id',
        'user_id',
    ];
}
