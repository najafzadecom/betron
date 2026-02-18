<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Scopes\UserScope;
use App\Observers\UserObserver;
use App\Policies\UserPolicy;
use App\Traits\HasStatusHtml;
use App\Traits\Sortable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Blade;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([UserObserver::class])]
#[UsePolicy(UserPolicy::class)]
#[ScopedBy([UserScope::class])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use HasStatusHtml;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;
    use Sortable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    protected $with = ['roles'];

    protected $appends = ['coloredRoleNames'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getColoredRoleNamesAttribute(): string
    {
        return $this->roles->map(function ($role) {
            return Blade::render('<x-badge :color="$color" :title="$title" />', [
                'title' => $role->name,
                'color' => $role->color ?? 'bg-dark',
            ]);
        })->implode('<br/>');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    /**
     * Get wallets associated with the user (for vendor users)
     */
    public function wallets(): BelongsToMany
    {
        return $this->belongsToMany(
            Wallet::class,
            'wallet_users',
            'user_id',
            'wallet_id'
        );
    }
}
