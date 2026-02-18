<?php

namespace App\Services;

use App\Repositories\DashboardRepository;

class DashboardService
{
    public function __construct(
        protected DashboardRepository $repository
    ) {
    }

    public function getVendorStatistics(array $walletIds): array
    {
        return $this->repository->getVendorStatistics($walletIds);
    }

    public function getRecentTransactions(array $walletIds, int $limit = 10)
    {
        return $this->repository->getRecentTransactions($walletIds, $limit);
    }

    /**
     * Get wallet IDs for a vendor
     */
    public function getVendorWalletIds(int $vendorId, VendorService $vendorService): array
    {
        $vendor = $vendorService->getById($vendorId);
        if (!$vendor) {
            return [];
        }
        
        return $vendor->wallets()->pluck('id')->toArray();
    }
}
