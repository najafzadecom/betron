<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository as Repository;
use Illuminate\Database\Eloquent\Collection;

class RoleService extends BaseService
{
    public function __construct(
        protected Repository $repository,
        protected PermissionRepository $permissionRepository
    ) {
    }

    public function create(array $data): object
    {
        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        $role = $this->repository->create($data);

        if (!empty($permissionIds)) {
            $permissions = $this->permissionRepository->getModel()->whereIn('id', $permissionIds)->get();
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    public function update(int $id, array $data): ?object
    {
        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        $role = $this->repository->update($id, $data);

        if ($role && !empty($permissionIds)) {
            $permissions = $this->permissionRepository->getModel()->whereIn('id', $permissionIds)->get();
            $role->syncPermissions($permissions);
        } elseif ($role) {
            $role->syncPermissions([]);
        }

        return $role;
    }

    public function getAllPermissions(): iterable
    {
        return $this->permissionRepository->all();
    }

    public function getActives(): Collection
    {
        return $this->repository->getModel()
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    public function getByGuardName(string $guardName)
    {
        return $this->repository->getByGuardName($guardName);
    }

    public function getByGuardNameVendorId(string $guardName, int $vendorId)
    {
        return $this->repository->getByGuardNameVendorId($guardName, $vendorId);
    }

    public function findByGuardName(int $id, string $guardName)
    {
        return $this->repository->findByGuardName($id, $guardName);
    }

    public function createWithPermissions(array $data, string $guardName, ?int $vendorId = null): object
    {
        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        // Set vendor_id if provided
        if ($vendorId !== null) {
            $data['vendor_id'] = $vendorId;
        }

        $role = $this->repository->create($data);

        if (!empty($permissionIds)) {
            // Get permission names for specific guard
            $permissionNames = $this->permissionRepository->getModel()
                ->where('guard_name', $guardName)
                ->whereIn('id', $permissionIds)
                ->pluck('name')
                ->toArray();

            $role->syncPermissions($permissionNames);
        }

        return $role;
    }

    public function updateWithPermissions(int $id, array $data, string $guardName, ?int $vendorId = null): ?object
    {
        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        // Set vendor_id if provided
        if ($vendorId !== null) {
            $data['vendor_id'] = $vendorId;
        }

        $role = $this->repository->update($id, $data);

        if ($role && isset($permissionIds)) {
            // Get permission names for specific guard
            $permissionNames = $this->permissionRepository->getModel()
                ->where('guard_name', $guardName)
                ->whereIn('id', $permissionIds)
                ->pluck('name')
                ->toArray();

            $role->syncPermissions($permissionNames);
        }

        return $role;
    }

    public function getByGuardNameAndVendorId(string $guardName, int $vendorId)
    {
        return $this->repository->getByGuardNameAndVendorId($guardName, $vendorId);
    }

    public function findByGuardNameAndVendorId(int $id, string $guardName, int $vendorId)
    {
        return $this->repository->findByGuardNameAndVendorId($id, $guardName, $vendorId);
    }

    /**
     * Get roles by guard name and vendor ID with all descendants
     */
    public function getByGuardNameVendorIdWithDescendants(string $guardName, int $vendorId, VendorService $vendorService)
    {
        // Get all accessible vendor IDs (self + all descendants)
        $vendorIds = array_merge([$vendorId], $vendorService->getDescendants($vendorId));
        
        return $this->repository->getModel()
            ->where('guard_name', $guardName)
            ->whereIn('vendor_id', $vendorIds)
            ->get();
    }
}
