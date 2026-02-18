<?php

namespace App\Traits;

use Illuminate\Support\Facades\Blade;

trait HasStatusHtml
{
    public function getStatusHtmlAttribute(): string
    {
        return Blade::render('<x-status :status="$status" />', [
            'status' => (bool) $this->status,
        ]);
    }

    public static function bootHasStatusHtml(): void
    {
        static::retrieved(function ($model) {
            if (!in_array('status_html', $model->appends)) {
                $model->appends[] = 'status_html';
            }
        });
    }
}
