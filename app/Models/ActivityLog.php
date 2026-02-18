<?php

namespace App\Models;

use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    use Sortable;

    protected $table = 'activity_log';

    protected $casts = [
        'properties' => 'collection',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function subject(): MorphTo
    {
        if (config('activitylog.subject_returns_soft_deleted_models')) {
            return $this->morphTo()->withTrashed();
        }

        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function getSubjectTypeDisplayAttribute(): string
    {
        if (!$this->subject_type) {
            return 'N/A';
        }

        $className = class_basename($this->subject_type);

        return ucfirst($className);
    }

    public function getCauserNameAttribute(): string
    {
        if (!$this->causer) {
            return 'System';
        }

        if (isset($this->causer->name)) {
            return $this->causer->name;
        }

        if (isset($this->causer->email)) {
            return $this->causer->email;
        }

        return 'Unknown User';
    }

    public function getDescriptionDisplayAttribute(): string
    {
        $description = ucfirst($this->description);

        switch ($this->description) {
            case 'created':
                return __('Created');
            case 'updated':
                return __('Updated');
            case 'deleted':
                return __('Deleted');
            case 'restored':
                return __('Restored');
            default:
                return $description;
        }
    }

    public function getPropertiesDisplayAttribute(): string
    {
        if (!$this->properties || $this->properties->isEmpty()) {
            return 'N/A';
        }

        $output = '';

        if ($this->properties->has('old') && $this->properties->has('attributes')) {
            $old = $this->properties->get('old', []);
            $new = $this->properties->get('attributes', []);

            foreach ($new as $key => $value) {
                if (isset($old[$key]) && $old[$key] != $value) {
                    $output .= "<strong>{$key}:</strong> {$old[$key]} → {$value}<br>";
                }
            }
        } elseif ($this->properties->has('attributes')) {
            $attributes = $this->properties->get('attributes', []);
            foreach ($attributes as $key => $value) {
                $output .= "<strong>{$key}:</strong> {$value}<br>";
            }
        }

        return $output ?: 'N/A';
    }
}
