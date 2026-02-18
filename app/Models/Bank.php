<?php

namespace App\Models;

use App\Models\Scopes\BankScope;
use App\Traits\HasStatusHtml;
use App\Traits\Sortable;
use Database\Factories\BankFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Blade;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([BankScope::class])]
class Bank extends Model
{
    /** @use HasFactory<BankFactory> */
    use HasFactory;
    use HasStatusHtml;
    use LogsActivity;
    use SoftDeletes;
    use Sortable;

    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
        'priority' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['image_url', 'transaction_status_html', 'withdrawal_status_html'];

    /**
     * Get the image URL attribute
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        return null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function getTransactionStatusHtmlAttribute(): string
    {
        return Blade::render('<x-status :status="$status" />', [
            'status' => $this->transaction_status,
        ]);
    }

    public function getWithdrawalStatusHtmlAttribute(): string
    {
        return Blade::render('<x-status :status="$status" />', [
            'status' => $this->withdrawal_status,
        ]);
    }
}
