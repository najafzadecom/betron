<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Models\Scopes\ProviderScope;
use App\Observers\ProviderObserver;
use App\Policies\ProviderPolicy;
use App\Traits\HasStatusHtml;
use App\Traits\Sortable;
use Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([ProviderObserver::class])]
#[UsePolicy(ProviderPolicy::class)]
#[ScopedBy([ProviderScope::class])]
class Provider extends Model
{
    /** @use HasFactory<ProviderFactory> */
    use HasFactory;
    use HasStatusHtml;
    use LogsActivity;
    use SoftDeletes;
    use Sortable;

    protected $guarded = [];

    protected $casts = [
        'credentials' => 'array',
        'type' => PaymentProvider::class
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }
}
