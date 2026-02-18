<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\PermissionRepository as Repository;

class PermissionService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }

    public function getByGuardName(string $guardName)
    {
        return $this->repository->getByGuardName($guardName);
    }

    public function getByGuardNameGrouped(string $guardName)
    {
        return $this->repository->getByGuardNameGrouped($guardName);
    }
}
