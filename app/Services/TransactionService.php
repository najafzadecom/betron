<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\TransactionRepository as Repository;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionService extends BaseService
{
    public function __construct(
        protected Repository     $repository,
        private BlacklistService $blacklistService
    ) {
    }

    public function paginate(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    /**
     * Get incoming transactions (where receiver is not null)
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

    public function paidTransactionsCount()
    {
        return $this->repository
            ->getModel()
            ->where('paid_status', true)
            ->count();
    }

    public function sumFeeAmount()
    {
        return $this->repository
            ->getModel()
            ->where('paid_status', true)
            ->sum('fee_amount');
    }

    /**
     * Get query builder for transactions
     */
    public function query()
    {
        return $this->repository->getModel()->query();
    }

    /**
     * Get transactions filtered by wallet IDs
     */
    public function getByWalletIds(array $walletIds)
    {
        return $this->repository
            ->getModel()
            ->whereIn('wallet_id', $walletIds);
    }

    /**
     * Add transaction user to blacklist
     */
    public function addToBlacklist(int $transactionId, string $reason = 'Transaction əsasında əlavə edildi'): ?object
    {
        $transaction = $this->repository->find($transactionId);

        if (!$transaction) {
            return null;
        }

        $result = [];

        // Add user_id to blacklist if exists and greater than 0
        if ($transaction->user_id && $transaction->user_id > 0) {
            $result['user_blacklist'] = $this->blacklistService->addUserToBlacklist(
                $transaction->user_id,
                $reason
            );
        }

        // Add client_ip to blacklist if exists
        if ($transaction->client_ip) {
            $result['ip_blacklist'] = $this->blacklistService->addIpToBlacklist(
                $transaction->client_ip,
                $reason
            );
        }

        return (object)$result;
    }

    /**
     * Auto add to blacklist based on transaction data
     */
    public function autoAddToBlacklist(array $transactionData, string $reason = 'Avtomatik əlavə edildi'): ?object
    {
        return $this->blacklistService->autoAddToBlacklist($transactionData, $reason);
    }

    public function request($uuid)
    {
        $transaction = $this->repository
            ->getModel()
            ->query()
            ->where('uuid', $uuid)
            ->where('status', 0)
            ->firstOrFail();

        $transaction->update(['status' => 1]);

        return $transaction;
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
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function changeStatus(int $id, int $status): bool
    {
        return $this->repository->update($id, ['status' => $status]);
    }
}
