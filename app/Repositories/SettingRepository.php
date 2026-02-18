<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\SettingInterface;
use App\Models\Setting as Model;
use Illuminate\Database\Eloquent\Collection;

class SettingRepository extends BaseRepository implements SettingInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    /**
     * Get setting by key
     */
    public function getByKey(string $key): ?Model
    {
        return $this->model->where('key', $key)->first();
    }

    /**
     * Get settings by group
     */
    public function getByGroup(string $group): Collection
    {
        return $this->model->where('group', $group)->get();
    }

    /**
     * Get all settings
     */
    public function getAll($sort = 'id', $direction = 'desc'): Collection
    {
        return $this->model->orderBy('group')->orderBy('name')->get();
    }

    /**
     * Update setting by key
     */
    public function updateByKey(string $key, $value): bool
    {
        $setting = $this->getByKey($key);
        if ($setting) {
            return $setting->update(['value' => $value]);
        }

        return false;
    }
}
