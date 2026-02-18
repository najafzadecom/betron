<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PaymentProvider;
use App\Enums\WithdrawalStatus;
use App\Models\Scopes\WithdrawalScope;
use App\Observers\WithdrawalObserver;
use App\Policies\WithdrawalPolicy;
use App\Traits\Sortable;
use Database\Factories\WithdrawalFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Blade;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Str;

#[ObservedBy([WithdrawalObserver::class])]
#[UsePolicy(WithdrawalPolicy::class)]
#[ScopedBy([WithdrawalScope::class])]
class Withdrawal extends Model
{
    /** @use HasFactory<WithdrawalFactory> */
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;
    use Sortable;

    protected $guarded = [];

    protected $with = ['vendor'];

    protected $appends = ['status_html', 'receiver', 'sender', 'site_name'];

    protected $casts = [
        'paid_status' => 'boolean',
        'status' => WithdrawalStatus::class,
        'currency' => Currency::class,
        'payment_method' => PaymentProvider::class,
        'accepted_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function getStatusHtmlAttribute(): string
    {
        return Blade::render('<x-badge :color="$color" :title="$title" />', [
            'title' => $this->status?->label(),
            'color' => $this->status?->color() ?? 'bg-dark',
        ]);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    public function getReceiverAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getSenderAttribute(): string
    {
        return $this->wallet
            ? ($this->wallet?->name . ' ' . $this->wallet?->iban)
            : ($this->sender_name . ' ' . $this->sender_iban);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function getSiteNameAttribute(): string
    {
        return $this->site?->name ?? 'Unknown';
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
