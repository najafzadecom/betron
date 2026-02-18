<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\BlacklistInterface;
use App\Models\Blacklist as Model;
use Illuminate\Database\Eloquent\Collection;

class BlacklistRepository extends BaseRepository implements BlacklistInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    /**
     * Check if user is blacklisted
     */
    public function isUserBlacklisted(int $userId): bool
    {
        return $this->model->where('user_id', $userId)
            ->where('type', 'user_id')
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if IP is blacklisted
     */
    public function isIpBlacklisted(string $ipAddress): bool
    {
        return $this->model->where('ip_address', $ipAddress)
            ->where('type', 'ip_address')
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Add user to blacklist
     */
    public function addUserToBlacklist(int $userId, ?string $reason = null): Model
    {
        // Check if already exists
        $existing = $this->model->where('user_id', $userId)
            ->where('type', 'user_id')
            ->first();

        if ($existing) {
            $existing->update([
                'is_active' => true,
                'reason' => $reason,
            ]);

            return $existing;
        }

        return $this->create([
            'user_id' => $userId,
            'type' => 'user_id',
            'reason' => $reason,
            'is_active' => true,
        ]);
    }

    /**
     * Add IP to blacklist
     */
    public function addIpToBlacklist(string $ipAddress, ?string $reason = null): Model
    {
        // Check if already exists
        $existing = $this->model->where('ip_address', $ipAddress)
            ->where('type', 'ip_address')
            ->first();

        if ($existing) {
            $existing->update([
                'is_active' => true,
                'reason' => $reason,
            ]);

            return $existing;
        }

        return $this->create([
            'ip_address' => $ipAddress,
            'type' => 'ip_address',
            'reason' => $reason,
            'is_active' => true,
        ]);
    }

    /**
     * Get active blacklists
     */
    public function getActiveBlacklists(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Activate/Deactivate blacklist entry
     */
    public function toggleStatus(int $id): bool
    {
        $blacklist = $this->find($id);
        if (!$blacklist) {
            return false;
        }

        return $blacklist->update([
            'is_active' => !$blacklist->is_active,
        ]);
    }
}
