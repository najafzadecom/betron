<?php

namespace App\Models;

use App\Models\Scopes\RoleScope;
use App\Observers\RoleObserver;
use App\Policies\RolePolicy;
use App\Traits\HasStatusHtml;
use App\Traits\Sortable;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Blade;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([RoleObserver::class])]
#[UsePolicy(RolePolicy::class)]
#[ScopedBy([RoleScope::class])]
class Role extends \Spatie\Permission\Models\Role
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;
    use HasStatusHtml;
    use LogsActivity;
    use SoftDeletes;
    use Sortable;

    protected $guarded = [];

    protected $appends = ['coloredName'];

    public function getColoredNameAttribute(): string
    {
        return Blade::render('<x-badge :color="$color" :title="$title" />', [
            'title' => $this->name,
            'color' => $this->color ?? 'bg-dark',
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    /**
     * Get the vendor that owns the role
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
