<?php

namespace App\Models;

use App\Observers\SettingObserver;
use App\Policies\SettingPolicy;
use App\Traits\Sortable;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([SettingObserver::class])]
#[UsePolicy(SettingPolicy::class)]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;
    use LogsActivity;
    use Sortable;

    protected $guarded = [];

    protected $casts = [
        'value' => 'json',
    ];

    public $timestamps = false;

    /**
     * Get formatted value attribute
     */
    public function getFormattedValueAttribute(): string
    {
        if (is_array($this->value)) {
            return json_encode($this->value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return (string)$this->value;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }
}
