<?php

namespace App\Models;

use App\Models\Scopes\BlacklistScope;
use App\Observers\BlacklistObserver;
use App\Policies\BlacklistPolicy;
use App\Traits\Sortable;
use Database\Factories\BlacklistFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([BlacklistObserver::class])]
#[UsePolicy(BlacklistPolicy::class)]
#[ScopedBy([BlacklistScope::class])]
class Blacklist extends Model
{
    /** @use HasFactory<BlacklistFactory> */
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;
    use Sortable;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Check if a user ID is blacklisted
     */
    public static function isUserBlacklisted(int $userId, int $site_id = 0): bool
    {
        return self::where('user_id', $userId)
            ->where('type', 'user_id')
            ->where('site_id', $site_id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if an IP address is blacklisted
     */
    public static function isIpBlacklisted(string $ipAddress, int $site_id = 0): bool
    {
        return self::where('ip_address', $ipAddress)
            ->where('type', 'ip_address')
            ->where('site_id', $site_id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Add a user to blacklist
     */
    public static function addUserToBlacklist(int $userId, ?string $reason = null, int $site_id = 0): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'user_id',
            'reason' => $reason,
            'site_id' => $site_id,
            'is_active' => true,
        ]);
    }

    /**
     * Add an IP address to blacklist
     */
    public static function addIpToBlacklist(string $ipAddress, ?string $reason = null, int $site_id = 0): self
    {
        return self::create([
            'ip_address' => $ipAddress,
            'type' => 'ip_address',
            'reason' => $reason,
            'site_id' => $site_id,
            'is_active' => true,
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
