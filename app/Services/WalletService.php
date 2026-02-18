<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Models\VendorUser;
use App\Repositories\WalletRepository as Repository;
use Illuminate\Database\Eloquent\Model;

class WalletService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }

    public function rand($bankId, $amount): ?object
    {
        return $this->repository->rand($bankId, $amount);
    }

    public function create(array $data): object
    {
        $transactionBanks = $data['transaction_banks'] ?? [];
        unset($data['transaction_banks']);

        $users = $data['user_ids'] ?? [];
        unset($data['user_ids']);

        $managerIds = $data['manager_ids'] ?? [];
        unset($data['manager_ids']);

        // Set created_by_vendor_user_id if authenticated vendor user exists (not vendor)
        if (auth('vendor')->check() && !isset($data['created_by_vendor_user_id'])) {
            $authenticatedUser = auth('vendor')->user();
            // Only set if the authenticated user is a VendorUser, not a Vendor
            if ($authenticatedUser instanceof VendorUser) {
                $data['created_by_vendor_user_id'] = $authenticatedUser->id;
            }
        }

        $item = $this->repository->create($data);

        if (!empty($transactionBanks)) {
            $item->transactionBanks()->syncWithoutDetaching($transactionBanks);
        }

        if (!empty($users)) {
            $item->users()->syncWithoutDetaching($users);
        }

        if (!empty($managerIds)) {
            $item->managers()->syncWithoutDetaching($managerIds);
        }

        return $item;
    }

    public function update(int $id, array $data): ?object
    {
        $transactionBanks = $data['transaction_banks'] ?? [];
        unset($data['transaction_banks']);

        $users = $data['user_ids'] ?? [];
        unset($data['user_ids']);

        $managerIds = $data['manager_ids'] ?? [];
        unset($data['manager_ids']);

        $item = $this->repository->update($id, $data);

        if ($item) {
            $item->transactionBanks()->sync($transactionBanks);
            $item->users()->sync($users);
            $item->managers()->sync($managerIds);
        }

        return $item;
    }

    public function firstOrCreate(array $where, array $data): object
    {
        return $this->repository->firstOrCreate($where, $data);
    }

    /**
     * Get active wallets
     */
    public function getActive()
    {
        return $this->repository->getModel()->where('status', true)->get();
    }

    /**
     * Get wallets by IDs
     */
    public function getByIds(array $ids)
    {
        return $this->repository->getModel()->whereIn('id', $ids)->get();
    }

    /**
     * Get wallets by vendor IDs with pagination
     */
    public function getByVendorIdsPaginated(array $vendorIds, int $perPage = 25)
    {
        return $this->repository->getModel()
            ->whereIn('vendor_id', $vendorIds)
            ->with(['bank', 'transactionBanks', 'vendor.parent'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get wallets by vendor IDs
     */
    public function getByVendorIds(array $vendorIds)
    {
        return $this->repository->getModel()
            ->whereIn('vendor_id', $vendorIds)
            ->get();
    }

    /**
     * Check if wallet belongs to vendor IDs
     */
    public function belongsToVendorIds(int $walletId, array $vendorIds): bool
    {
        return $this->repository->getModel()
            ->whereIn('vendor_id', $vendorIds)
            ->where('id', $walletId)
            ->exists();
    }

    /**
     * Get wallet IDs by vendor IDs
     */
    public function getWalletIdsByVendorIds(array $vendorIds): array
    {
        return $this->repository->getModel()
            ->whereIn('vendor_id', $vendorIds)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Bulk update status for wallets
     */
    public function bulkUpdateStatus(array $vendorIds, int $status)
    {
        return $this->repository->getModel()
            ->whereIn('vendor_id', $vendorIds)
            ->update(['status' => $status]);
    }
}
