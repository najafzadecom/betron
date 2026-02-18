<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\RoleInterface;
use App\Models\Role as Model;

class RoleRepository extends BaseRepository implements RoleInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function getByGuardName(string $guardName)
    {
        return $this->model->where('guard_name', $guardName)->with('permissions')->get();
    }

    public function getByGuardNameVendorId(string $guardName, int $vendorId)
    {
        return $this->model->where('guard_name', $guardName)->where('vendor_id', $vendorId)->with('permissions')->get();
    }

    public function findByGuardName(int $id, string $guardName)
    {
        return $this->model->where('guard_name', $guardName)->with('permissions')->findOrFail($id);
    }

    public function getByGuardNameAndVendorId(string $guardName, int $vendorId)
    {
        return $this->model
            ->where('guard_name', $guardName)
            ->where('vendor_id', $vendorId)
            ->with('permissions')
            ->get();
    }

    public function findByGuardNameAndVendorId(int $id, string $guardName, int $vendorId)
    {
        return $this->model
            ->where('guard_name', $guardName)
            ->where('vendor_id', $vendorId)
            ->with('permissions')
            ->findOrFail($id);
    }
}
