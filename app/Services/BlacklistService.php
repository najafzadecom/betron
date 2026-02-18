<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Models\Blacklist;
use App\Repositories\BlacklistRepository as Repository;
use Illuminate\Support\Collection;

class BlacklistService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }

    /**
     * Check if user is blacklisted
     */
    public function isUserBlacklisted(int $userId): bool
    {
        return $this->repository->isUserBlacklisted($userId);
    }

    /**
     * Check if IP is blacklisted
     */
    public function isIpBlacklisted(string $ipAddress): bool
    {
        return $this->repository->isIpBlacklisted($ipAddress);
    }

    /**
     * Add user to blacklist
     */
    public function addUserToBlacklist(int $userId, ?string $reason = null): Blacklist
    {
        return $this->repository->addUserToBlacklist($userId, $reason);
    }

    /**
     * Add IP to blacklist
     */
    public function addIpToBlacklist(string $ipAddress, ?string $reason = null): Blacklist
    {
        return $this->repository->addIpToBlacklist($ipAddress, $reason);
    }

    /**
     * Get active blacklists
     */
    public function getActiveBlacklists(): Collection
    {
        return $this->repository->getActiveBlacklists();
    }

    /**
     * Activate/Deactivate blacklist entry
     */
    public function toggleStatus(int $id): bool
    {
        return $this->repository->toggleStatus($id);
    }

    /**
     * Check if request should be blocked based on user ID or IP
     */
    public function shouldBlockRequest(?int $userId, ?string $ipAddress): bool
    {
        // Check user ID blacklist
        if ($userId && $this->isUserBlacklisted($userId)) {
            return true;
        }

        // Check IP blacklist
        if ($ipAddress && $this->isIpBlacklisted($ipAddress)) {
            return true;
        }

        return false;
    }

    /**
     * Auto-add to blacklist based on transaction/withdrawal data
     */
    public function autoAddToBlacklist(array $data, string $reason = 'Avtomatik əlavə edildi'): ?Blacklist
    {
        // Check if we have user_id
        if (isset($data['user_id']) && $data['user_id'] > 0) {
            return $this->addUserToBlacklist($data['user_id'], $reason);
        }

        // Check if we have client_ip
        if (isset($data['client_ip']) && !empty($data['client_ip'])) {
            return $this->addIpToBlacklist($data['client_ip'], $reason);
        }

        return null;
    }
}
