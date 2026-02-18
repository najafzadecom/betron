<?php

namespace App\Models;

use App\Models\Scopes\VendorScope;
use App\Traits\HasStatusHtml;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([VendorScope::class])]
class Vendor extends Authenticatable
{
    use HasFactory;
    use HasStatusHtml;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;
    use Sortable;

    protected $guard = 'vendor';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
            'deposit_enabled' => 'boolean',
            'withdrawal_enabled' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    /**
     * Get wallets belonging to this vendor
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    /**
     * Get transactions through wallets
     */
    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Transaction::class,
            Wallet::class,
            'vendor_id', // Foreign key on wallets table
            'wallet_id', // Foreign key on transactions table
            'id', // Local key on vendors table
            'id'  // Local key on wallets table
        );
    }

    /**
     * Get vendor users (sub-users)
     */
    public function vendorUsers(): HasMany
    {
        return $this->hasMany(VendorUser::class);
    }

    /**
     * Get parent vendor
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'parent_id');
    }

    /**
     * Get child vendors (sub-vendors)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Vendor::class, 'parent_id');
    }

    /**
     * Get deposit transactions
     */
    public function depositTransactions(): HasMany
    {
        return $this->hasMany(VendorDepositTransaction::class);
    }

    /**
     * Get all descendants (recursive)
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Check if vendor is a descendant of another vendor
     */
    public function isDescendantOf(int $vendorId): bool
    {
        $current = $this;
        while ($current && $current->parent_id) {
            if ($current->parent_id == $vendorId) {
                return true;
            }
            $current = $current->parent;
            if (!$current) {
                break;
            }
        }
        return false;
    }
}
