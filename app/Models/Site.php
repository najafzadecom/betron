<?php

namespace App\Models;

use App\Models\Scopes\SiteScope;
use App\Observers\SiteObserver;
use App\Policies\SitePolicy;
use App\Traits\HasStatusHtml;
use App\Traits\Sortable;
use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([SiteObserver::class])]
#[UsePolicy(SitePolicy::class)]
#[ScopedBy([SiteScope::class])]
class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory;
    use SoftDeletes;
    use Sortable;
    use HasStatusHtml;
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    /**
     * Get the image URL attribute
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }

        return null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }
}
