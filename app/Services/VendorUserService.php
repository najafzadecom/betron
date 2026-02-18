<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\VendorUserRepository as Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class VendorUserService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }

    public function create(array $data): object
    {
        $data['password'] = Hash::make($data['password']);
        
        // Extract roles before creating user
        $roles = $data['roles'] ?? [];
        unset($data['roles']);
        
        $user = $this->repository->create($data);

        // Sync roles if provided
        if (!empty($roles)) {
            $this->repository->syncRoles($user->id, $roles);
        }

        return $user->fresh(['roles']);
    }

    public function update(int $id, array $data): ?object
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Extract roles before updating user
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        $user = $this->repository->update($id, $data);

        // Sync roles if provided
        if (isset($roles)) {
            $this->repository->syncRoles($id, $roles);
        }

        return $user->fresh(['roles']);
    }

    /**
     * Get users by vendor ID
     */
    public function getByVendorId(int $vendorId)
    {
        return $this->repository->getByVendorId($vendorId);
    }

    /**
     * Get users by vendor ID and all its descendants
     */
    public function getByVendorIdWithDescendants(int $vendorId, VendorService $vendorService)
    {
        // Get all accessible vendor IDs (self + all descendants)
        $vendorIds = array_merge([$vendorId], $vendorService->getDescendants($vendorId));
        
        return $this->repository->getModel()
            ->whereIn('vendor_id', $vendorIds)
            ->get();
    }

    /**
     * Get active vendor users
     */
    public function getActive()
    {
        return $this->repository->getModel()->where('status', 1)->get();
    }
}
