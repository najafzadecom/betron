<?php

namespace App\Interfaces;

use App\Core\Contracts\BaseRepositoryInterface;

interface RoleInterface extends BaseRepositoryInterface
{
    public function getByGuardName(string $guardName);
    public function findByGuardName(int $id, string $guardName);
}
