<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\VendorUserInterface;
use App\Models\VendorUser;
use Illuminate\Support\Facades\Cache;

class VendorUserRepository extends BaseRepository implements VendorUserInterface
{
    public function __construct(VendorUser $model)
    {
        parent::__construct($model);
    }

    /**
     * Get users by vendor ID
     */
    public function getByVendorId(int $vendorId)
    {
        return $this->model->where('vendor_id', $vendorId)->get();
    }

    /**
     * Sync user roles
     */
    public function syncRoles(int $userId, array $roleIds): void
    {
        $user = $this->find($userId);
        
        // Get role names for vendor guard
        $roles = \Spatie\Permission\Models\Role::where('guard_name', 'vendor')
            ->whereIn('id', $roleIds)
            ->pluck('name')
            ->toArray();
        
        $user->syncRoles($roles);
        
        // Clear permission cache for this user
        $user->forgetCachedPermissions();
        
        // Clear global Spatie Permission cache
        $cacheStore = config('permission.cache.store') !== 'default' 
            ? config('permission.cache.store') 
            : null;
        Cache::store($cacheStore)->forget(config('permission.cache.key'));
    }
}
