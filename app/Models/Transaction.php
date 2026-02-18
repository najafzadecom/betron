<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PaidStatus;
use App\Enums\PaymentProvider;
use App\Enums\TransactionStatus;
use App\Models\Scopes\TransactionScope;
use App\Observers\TransactionObserver;
use App\Policies\TransactionPolicy;
use App\Traits\Sortable;
use Database\Factories\TransactionFactory;
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

#[ObservedBy([TransactionObserver::class])]
#[UsePolicy(TransactionPolicy::class)]
#[ScopedBy([TransactionScope::class])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;
    use Sortable;

    protected $guarded = [];

    protected $casts = [
        'status' => TransactionStatus::class,
        'paid_status' => 'boolean',
        'currency' => Currency::class,
        'payment_method' => PaymentProvider::class,
        'accepted_at' => 'datetime',
    ];

    protected $with = ['wallet', 'site', 'bank', 'vendor'];

    protected $appends = [
        'sender', 'receiver', 'site_name', 'bank_name'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }

            // Automatically set receiver_name and receiver_iban from wallet_id
            if (!empty($model->wallet_id) && (empty($model->receiver_name) || empty($model->receiver_iban))) {
                $wallet = Wallet::withoutGlobalScopes()
                    ->select('id', 'name', 'iban')
                    ->find($model->wallet_id);

                if ($wallet) {
                    if (empty($model->receiver_name)) {
                        $model->receiver_name = $wallet->name;
                    }
                    if (empty($model->receiver_iban)) {
                        $model->receiver_iban = $wallet->iban;
                    }
                }
            }
        });
    }

    public function getStatusHtmlAttribute(): string
    {
        return Blade::render('<x-badge :color="$color" :title="$title" />', [
            'title' => $this->status->label(),
            'color' => $this->status->color() ?? 'bg-dark',
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    public function getSenderAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function getReceiverAttribute(): string
    {
        return $this->wallet
            ? ($this->wallet?->name . ' <small class="text-muted">(' . $this->wallet?->iban . ')</small>')
            : ($this->receiver_name . ' <small class="text-muted">(' . $this->receiver_iban . ')</small>');


    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function getSiteNameAttribute()
    {
        return $this->site?->name ?? $this->site_name;
    }

    public function getBankNameAttribute()
    {
        return $this->bank?->name ?? 'Unknown';
    }
}
