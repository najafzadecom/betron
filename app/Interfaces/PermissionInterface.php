<?php

namespace App\Interfaces;

use App\Core\Contracts\BaseRepositoryInterface;

interface PermissionInterface extends BaseRepositoryInterface
{
    public function getByGuardName(string $guardName);
    public function getByGuardNameGrouped(string $guardName);
}
