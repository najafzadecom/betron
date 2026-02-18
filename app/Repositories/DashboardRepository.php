<?php

namespace App\Repositories;

use App\Enums\TransactionStatus;
use App\Interfaces\DashboardInterface;
use App\Models\Transaction;

class DashboardRepository implements DashboardInterface
{
    public function __construct(
        protected Transaction $transaction
    ) {
    }

    public function getVendorStatistics(array $walletIds): array
    {
        $totalWallets = count($walletIds);
        
        $totalTransactions = $this->transaction
            ->whereIn('wallet_id', $walletIds)
            ->count();

        $totalAmount = $this->transaction
            ->whereIn('wallet_id', $walletIds)
            ->where('status', TransactionStatus::ManualConfirmed)
            ->sum('amount');

        $pendingTransactions = $this->transaction
            ->whereIn('wallet_id', $walletIds)
            ->where('status', TransactionStatus::Pending)
            ->count();

        return [
            'totalWallets' => $totalWallets,
            'totalTransactions' => $totalTransactions,
            'totalAmount' => $totalAmount,
            'pendingTransactions' => $pendingTransactions,
        ];
    }

    public function getRecentTransactions(array $walletIds, int $limit = 10)
    {
        return $this->transaction
            ->whereIn('wallet_id', $walletIds)
            ->with(['wallet', 'bank'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
