<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\WithdrawalRepository as Repository;
use Illuminate\Database\Eloquent\Model;

class WithdrawalService extends BaseService
{
    public function __construct(
        protected Repository     $repository,
        private BlacklistService $blacklistService
    ) {
    }

    /**
     * Get outgoing transactions (where sender is not null)
     */
    public function last(int $limit = 10)
    {
        return $this->repository
            ->getModel()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function sumAmount()
    {
        return $this->repository
            ->getModel()
            ->where('paid_status', true)
            ->sum('amount');
    }

    public function sumFeeAmount()
    {
        return $this->repository
            ->getModel()
            ->where('paid_status', true)
            ->sum('fee_amount');
    }

    public function paidWithdrawalsCount()
    {
        return $this->repository
            ->getModel()
            ->where('paid_status', true)
            ->count();
    }

    /**
     * Get query builder for withdrawals
     */
    public function query()
    {
        return $this->repository->getModel()->query();
    }

    /**
     * Get withdrawals filtered by wallet IDs
     */
    public function getByVendorId(int $vendorId)
    {
        return $this->repository
            ->getModel()
            ->where('vendor_id', $vendorId);
    }

    public function pending()
    {
        return $this->repository
            ->getModel()
            ->where('status', 0)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function manual()
    {
        return $this->repository
            ->getModel()
            ->where('manual', true)
            ->where('status', 0)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByUuid(string $uuid)
    {
        return $this->repository
            ->getModel()
            ->query()
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * Create multiple withdrawals from an amount array
     */
    public function createMultiple(array $data): array
    {
        $amounts = $data['amounts'] ?? [];
        $results = [];

        foreach ($amounts as $amount) {
            $withdrawalData = $data;
            $withdrawalData['amount'] = $amount;
            $withdrawalData['manual'] = 1;
            
            // Set default payment_method to manual if not provided
            if (!isset($withdrawalData['payment_method'])) {
                $withdrawalData['payment_method'] = \App\Enums\PaymentProvider::Manual->value;
            }
            
            unset($withdrawalData['amounts']);

            $results[] = $this->repository->create($withdrawalData);
        }

        return $results;
    }

    /**
     * Add withdrawal user to the blacklist
     */
    public function addToBlacklist(int $withdrawalId, string $reason = 'Withdrawal əsasında əlavə edildi'): ?object
    {
        $withdrawal = $this->repository->find($withdrawalId);

        if (!$withdrawal) {
            return null;
        }

        $result = [];

        // Add user_id to blacklist if exists and greater than 0
        if ($withdrawal->user_id && $withdrawal->user_id > 0) {
            $result['user_blacklist'] = $this->blacklistService->addUserToBlacklist(
                $withdrawal->user_id,
                $reason
            );
        }

        // Note: Withdrawal table doesn't have client_ip field
        // If needed in the future, we can get it from request context

        return (object)$result;
    }

    /**
     * Auto add to blacklist based on withdrawal data
     */
    public function autoAddToBlacklist(array $withdrawalData, string $reason = 'Avtomatik əlavə edildi'): ?object
    {
        return $this->blacklistService->autoAddToBlacklist($withdrawalData, $reason);
    }

    /**
     * Get withdrawals by vendor IDs with pagination
     */
    public function getByVendorIdsPaginated(array $vendorIds, int $perPage = 25)
    {
        if (empty($vendorIds)) {
            // Return empty paginated result if no vendor IDs
            return $this->repository->getModel()
                ->whereRaw('1 = 0') // Always false condition
                ->with(['site', 'vendor'])
                ->latest()
                ->paginate($perPage)
                ->appends(request()->query());
        }
        
        return $this->repository->getModel()
            ->whereIn('vendor_id', $vendorIds)
            ->with(['site', 'vendor'])
            ->latest()
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * Check if withdrawal belongs to vendor IDs
     */
    public function belongsToVendorIds(int $withdrawalId, array $vendorIds): bool
    {
        $withdrawal = $this->getById($withdrawalId);
        return $withdrawal && in_array($withdrawal->vendor_id, $vendorIds);
    }

    /**
     * Get withdrawals by IDs
     */
    public function getByIds(array $ids)
    {
        return $this->repository
            ->getModel()
            ->whereIn('id', $ids)
            ->get();
    }
}
