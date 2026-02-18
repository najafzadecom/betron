<?php

namespace App\Models;

use App\Models\Scopes\PermissionScope;
use App\Observers\PermissionObserver;
use App\Policies\PermissionPolicy;
use App\Traits\Sortable;
use Database\Factories\PermissionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([PermissionObserver::class])]
#[UsePolicy(PermissionPolicy::class)]
#[ScopedBy([PermissionScope::class])]
class Permission extends \Spatie\Permission\Models\Permission
{
    /** @use HasFactory<PermissionFactory> */
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;
    use Sortable;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }
}
