<?php

namespace App\Repositories;

use App\Enums\WithdrawalStatus;
use App\Interfaces\DashboardInterface;
use App\Models\Transaction;
use App\Models\Withdrawal;

class DashboardRepository implements DashboardInterface
{
    public function __construct(
        protected Transaction $transaction
    ) {
    }

    public function getVendorStatistics(array $walletIds, int $vendorId): array
    {
        $totalReceivedDepositAmount = 0.0;
        $totalReceivedDepositCount = 0;
        $totalCommissionAmount = 0.0;

        if (!empty($walletIds)) {
            $paidDeposits = $this->transaction
                ->whereIn('wallet_id', $walletIds)
                ->whereIn('status', [TransactionStatus::ManualConfirmed, TransactionStatus::AutoConfirmed])
                ->where('paid_status', true);

            $totalReceivedDepositAmount = (float) (clone $paidDeposits)->sum('amount');
            $totalReceivedDepositCount = (clone $paidDeposits)->count();
            $totalCommissionAmount = (float) (clone $paidDeposits)->sum('fee_amount');
        }

        // API ve dağıtım akışı çoğunlukla Processing (1); Pending (0) da kalabilir
        $pendingWithdrawals = Withdrawal::query()
            ->where('vendor_id', $vendorId)
            ->where('paid_status', true)
            ->whereIn('status', [WithdrawalStatus::ManualConfirmed, WithdrawalStatus::ManualConfirmed]);

        $pendingWithdrawalsAmount = (float) (clone $pendingWithdrawals)->sum('amount');
        $pendingWithdrawalsCount = (clone $pendingWithdrawals)->count();

        return [
            'totalReceivedDepositAmount' => $totalReceivedDepositAmount,
            'totalReceivedDepositCount' => $totalReceivedDepositCount,
            'totalCommissionAmount' => $totalCommissionAmount,
            'pendingWithdrawalsAmount' => $pendingWithdrawalsAmount,
            'pendingWithdrawalsCount' => $pendingWithdrawalsCount,
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
