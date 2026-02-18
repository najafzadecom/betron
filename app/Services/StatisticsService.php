<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\WithdrawalStatus;
use App\Repositories\TransactionRepository;
use App\Repositories\WithdrawalRepository;

class StatisticsService
{
    public string $createdFrom;
    public string $createdTo;

    public int $siteId = 0;
    public array $vendorIds = [];
    public array $walletIds = [];
    
    public function __construct(
        protected TransactionRepository $transactionRepository,
        protected WithdrawalRepository  $withdrawalRepository
    ) {
    }

    public function getTotalTransactions(): int
    {
        $query = $this->transactionRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('accepted_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('accepted_at', '<=', $this->createdTo . ' 23:59:59');

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        } elseif (!empty($this->walletIds)) {
            $query->whereIn('wallet_id', $this->walletIds);
        }

        return $query->count();
    }

    public function getTotalWithdrawals(): int
    {
        $query = $this->withdrawalRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('accepted_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('accepted_at', '<=', $this->createdTo . ' 23:59:59');


        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        }

        return $query->count();
    }

    public function getTotalTransactionsAmount(): int
    {
        $query = $this->transactionRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('accepted_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('accepted_at', '<=', $this->createdTo . ' 23:59:59');

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        } elseif (!empty($this->walletIds)) {
            $query->whereIn('wallet_id', $this->walletIds);
        }

        return $query->sum('amount');
    }

    public function getTotalWithdrawalsAmount(): int
    {
        $query = $this->withdrawalRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('accepted_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('accepted_at', '<=', $this->createdTo . ' 23:59:59');

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        } elseif (!empty($this->walletIds)) {
            $query->whereIn('wallet_id', $this->walletIds);
        }

        return $query->sum('amount');
    }

    public function getAcceptedTransactions(): int
    {
        $query = $this->transactionRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('accepted_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('accepted_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereRaw('paid_status IS TRUE');

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        } elseif (!empty($this->walletIds)) {
            $query->whereIn('wallet_id', $this->walletIds);
        }

        return $query->count();
    }

    public function getRejectedTransactions(): int
    {
        $query = $this->transactionRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('accepted_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('accepted_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereRaw('paid_status IS FALSE');

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        } elseif (!empty($this->walletIds)) {
            $query->whereIn('wallet_id', $this->walletIds);
        }

        return $query->count();
    }

    public function getPendingTransactions(): int
    {
        $query = $this->transactionRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('created_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereRaw('paid_status IS FALSE')
            ->whereIn('status', [
                TransactionStatus::Pending->value,
                TransactionStatus::Processing->value,
            ]);

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        } elseif (!empty($this->walletIds)) {
            $query->whereIn('wallet_id', $this->walletIds);
        }

        return $query->count();
    }

    public function getAcceptedTransactionsAmount(): int
    {
        $query = $this->transactionRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('accepted_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('accepted_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereRaw('paid_status IS TRUE')
            ->whereIn('status', [
                TransactionStatus::ManualConfirmed->value,
                TransactionStatus::AutoConfirmed->value,
            ]);

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        }
        elseif (!empty($this->walletIds)) {
            $query->whereIn('wallet_id', $this->walletIds);
        }

        return $query->sum('amount');
    }

    public function getRejectedTransactionsAmount(): int
    {
        $query = $this->transactionRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('created_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereRaw('paid_status IS FALSE')
            ->whereIn('status', [
                TransactionStatus::ManualCancelled->value,
                TransactionStatus::AutoCancelled->value,
            ]);

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        } elseif (!empty($this->walletIds)) {
            $query->whereIn('wallet_id', $this->walletIds);
        }

        return $query->sum('amount');
    }

    public function getPendingTransactionsAmount(): int
    {
        $query = $this->transactionRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('created_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereRaw('paid_status IS FALSE')
            ->whereIn('status', [
                TransactionStatus::Pending->value,
                TransactionStatus::Processing->value,
            ]);

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        }
        elseif (!empty($this->walletIds)) {
            $query->whereIn('wallet_id', $this->walletIds);
        }

        return $query->sum('amount');
    }

    public function getAcceptedWithdrawals(): int
    {
        // Paid = ManualConfirmed + AutoConfirmed
        $query = $this->withdrawalRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('accepted_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('accepted_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereIn('status', [
                WithdrawalStatus::ManualConfirmed->value,
                WithdrawalStatus::AutoConfirmed->value,
            ]);

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        }

        return $query->count();
    }

    public function getRejectedWithdrawals(): int
    {
        // Unpaid = ManualCancelled + AutoCancelled + Processing + Pending
        $query = $this->withdrawalRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('created_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereIn('status', [
                WithdrawalStatus::ManualCancelled->value,
                WithdrawalStatus::AutoCancelled->value
            ]);

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        }

        return $query->count();
    }

    public function getAcceptedWithdrawalsAmount(): int
    {
        // Paid = ManualConfirmed + AutoConfirmed
        $query = $this->withdrawalRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('accepted_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('accepted_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereRaw('paid_status IS TRUE')
            ->whereIn('status', [
                WithdrawalStatus::ManualConfirmed->value,
                WithdrawalStatus::AutoConfirmed->value,
            ]);

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        }

        return $query->sum('amount');
    }

    public function getRejectedWithdrawalsAmount(): int
    {
        // Unpaid = ManualCancelled + AutoCancelled + Processing + Pending
        $query = $this->withdrawalRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('created_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereIn('status', [
                WithdrawalStatus::ManualCancelled->value,
                WithdrawalStatus::AutoCancelled->value
            ]);

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        }

        return $query->sum('amount');
    }

    public function getPendingWithdrawals(): int
    {
        $query = $this->withdrawalRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('created_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereRaw('paid_status IS FALSE')
            ->whereIn('status', [
                WithdrawalStatus::Pending->value,
                WithdrawalStatus::Processing->value,
            ]);

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        }

        return $query->count();
    }

    public function getPendingWithdrawalsAmount(): int
    {
        $query = $this->withdrawalRepository
            ->getModel()
            ->query()
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $this->createdFrom.' 00:00:00')
            ->where('created_at', '<=', $this->createdTo . ' 23:59:59')
            ->whereRaw('paid_status IS FALSE')
            ->whereIn('status', [
                WithdrawalStatus::Pending->value,
                WithdrawalStatus::Processing->value,
            ]);

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        if (!empty($this->vendorIds)) {
            $query->whereIn('vendor_id', $this->vendorIds);
        }

        return $query->sum('amount');
    }
}
