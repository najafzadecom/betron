<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\PermissionInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission as Model;

class PermissionRepository extends BaseRepository implements PermissionInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function getByGuardName(string $guardName): Collection
    {
        return $this->model
            ->newQuery()
            ->where('guard_name', $guardName)
            ->get();
    }

    public function getByGuardNameGrouped(string $guardName): Collection
    {
        return $this->model
            ->newQuery()
            ->where('guard_name', $guardName)
            ->get()->groupBy(function ($permission) {
                return explode('-', $permission->name)[1] ?? 'other';
            });
    }
}
