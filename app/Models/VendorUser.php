<?php

namespace App\Models;

use App\Models\Scopes\VendorUserScope;
use App\Traits\HasStatusHtml;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Blade;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[ScopedBy([VendorUserScope::class])]
class VendorUser extends Authenticatable
{
    use HasFactory;
    use HasRoles;
    use HasStatusHtml;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;
    use Sortable;

    protected $guard_name = 'vendor';

    protected $guarded = [];

    protected $with = ['roles', 'vendor.parent'];

    protected $appends = ['coloredRoleNames'];

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
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    /**
     * Get the vendor that owns the user
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get colored role names for display
     */
    public function getColoredRoleNamesAttribute(): string
    {
        return $this->roles->map(function ($role) {
            return Blade::render('<x-badge :color="$color" :title="$title" />', [
                'title' => $role->name,
                'color' => $role->color ?? 'bg-dark',
            ]);
        })->implode('<br/>');
    }

    /**
     * Get wallets created by this vendor user
     */
    public function createdWallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'created_by_vendor_user_id');
    }

    /**
     * Get wallets managed by this vendor user
     */
    public function managedWallets(): BelongsToMany
    {
        return $this->belongsToMany(
            Wallet::class,
            'wallet_vendor_users',
            'vendor_user_id',
            'wallet_id'
        );
    }

    /**
     * Check if user has access to specific wallet
     */
    public function hasWalletAccess(int $walletId): bool
    {
        // VendorUser can access wallets they created or manage
        return $this->createdWallets()->where('id', $walletId)->exists()
            || $this->managedWallets()->where('wallet_id', $walletId)->exists();
    }
}
