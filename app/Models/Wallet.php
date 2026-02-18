<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\WalletStatus;
use App\Models\Scopes\WalletScope;
use App\Observers\WalletObserver;
use App\Policies\WalletPolicy;
use App\Traits\Sortable;
use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([WalletObserver::class])]
#[UsePolicy(WalletPolicy::class)]
#[ScopedBy([WalletScope::class])]
class Wallet extends Model
{
    /** @use HasFactory<WalletFactory> */
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;
    use Sortable;

    protected $guarded = [];

    protected $casts = [
        'status' => WalletStatus::class,
        'last_sync_date' => 'datetime',
        'currency' => Currency::class,
    ];

    protected $with = [
        'transactionBanks',
        'users',
        'vendor',
        'creator',
        'managers'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function transactionBanks(): BelongsToMany
    {
        return $this->belongsToMany(
            Bank::class,
            'wallet_transaction_banks',
            'wallet_id',
            'bank_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'wallet_users',
            'wallet_id',
            'user_id'
        );
    }

    public function files(): HasMany
    {
        return $this->hasMany(WalletFile::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the vendor user who created this wallet
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(VendorUser::class, 'created_by_vendor_user_id');
    }

    /**
     * Get the vendor users who manage this wallet
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(
            VendorUser::class,
            'wallet_vendor_users',
            'wallet_id',
            'vendor_user_id'
        );
    }
}
