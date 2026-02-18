<?php

namespace App\Interfaces;

use App\Core\Contracts\BaseRepositoryInterface;
use App\Models\Blacklist;
use Illuminate\Database\Eloquent\Collection;

interface BlacklistInterface extends BaseRepositoryInterface
{
    /**
     * Check if user is blacklisted
     */
    public function isUserBlacklisted(int $userId): bool;

    /**
     * Check if IP is blacklisted
     */
    public function isIpBlacklisted(string $ipAddress): bool;

    /**
     * Add user to blacklist
     */
    public function addUserToBlacklist(int $userId, ?string $reason = null): Blacklist;

    /**
     * Add IP to blacklist
     */
    public function addIpToBlacklist(string $ipAddress, ?string $reason = null): Blacklist;

    /**
     * Get active blacklists
     */
    public function getActiveBlacklists(): Collection;

    /**
     * Activate/Deactivate blacklist entry
     */
    public function toggleStatus(int $id): bool;
}
