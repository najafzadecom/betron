<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Models\Permission;
use App\Repositories\UserRepository as Repository;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }

    public function create(array $data): object
    {
        $data['password'] = Hash::make($data['password']);

        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $item = $this->repository->create($data);

        if ($item) {
            if ($roles) {
                $item->syncRoles($roles);
            }

            if (!empty($permissionIds)) {
                $permissions = Permission::query()->whereIn('id', $permissionIds)->get();
                $item->syncPermissions($permissions);
            }
        }

        return $item;
    }

    public function update(int $id, array $data): ?object
    {
        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $roles = [];

        if (isset($data['roles'])) {
            $roles = $data['roles'];
            unset($data['roles']);
        }

        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        $update = $this->repository->update($id, $data);

        if ($update) {
            if ($roles) {
                $update->syncRoles($roles);
            }

            if ($update && !empty($permissionIds)) {
                $permissions = Permission::query()->whereIn('id', $permissionIds)->get();
                $update->syncPermissions($permissions);
            } elseif ($update) {
                $update->syncPermissions([]);
            }
        }

        return $update;
    }
}
