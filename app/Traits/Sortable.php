<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    public static function bootSortable(): void
    {
        static::addGlobalScope('sortable', function (Builder $builder) {
            $model = new static();
            $sort = getCurrentSort($model->getKeyName());
            $direction = getSortDirection();

            if ($sort && in_array($direction, ['asc', 'desc'])) {
                $sortableFields = getSortableFields($model->getTable());
                if (in_array($sort, $sortableFields)) {
                    $builder->orderBy($sort, $direction);
                }
            }
        });
    }
}
