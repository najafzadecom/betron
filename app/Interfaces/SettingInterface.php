<?php

namespace App\Interfaces;

use App\Core\Contracts\BaseRepositoryInterface;

interface SettingInterface extends BaseRepositoryInterface
{
    /**
     * Get setting by key
     */
    public function getByKey(string $key);

    /**
     * Get settings by group
     */
    public function getByGroup(string $group);

    /**
     * Get all settings
     */
    public function getAll();

    /**
     * Update setting by key
     */
    public function updateByKey(string $key, $value): bool;
}
