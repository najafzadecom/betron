<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletFile extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
