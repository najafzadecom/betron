<?php

namespace App\Policies;

use App\Models\User;
use App\Models\User as Model;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Model $data): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Model $data): bool
    {
        return false;
    }

    public function delete(User $user, Model $data): bool
    {
        return false;
    }

    public function restore(User $user, Model $data): bool
    {
        return false;
    }

    public function forceDelete(User $user, Model $data): bool
    {
        return false;
    }
}
