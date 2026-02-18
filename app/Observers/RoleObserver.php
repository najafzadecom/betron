<?php

namespace App\Observers;

use App\Models\Role as Model;
use Illuminate\Support\Facades\Cache;

class RoleObserver
{
    protected string $prefix = 'role_';

    public function created(Model $data): void
    {
        Cache::forget('roles');
        Cache::rememberForever($this->prefix . $data->id, fn () => $data);
    }

    public function updated(Model $data): void
    {
        Cache::forget('roles');
        Cache::rememberForever($this->prefix . $data->id, fn () => $data);
    }

    public function deleted(Model $data): void
    {
        Cache::forget($this->prefix . $data->id);
    }

    public function restored(Model $data): void
    {
        Cache::rememberForever($this->prefix . $data->id, fn () => $data);
    }

    public function forceDeleted(Model $data): void
    {
        Cache::forget($this->prefix . $data->id);
    }
}
