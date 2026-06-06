<?php

namespace App\Observers;

use App\Enums\TransactionStatus;
use App\Jobs\SendTransactionWebhookJob;
use App\Models\Site;
use App\Models\Transaction as Model;
use App\Models\Wallet;
use App\Services\VendorService;
use Illuminate\Support\Facades\Cache;

class TransactionObserver
{
    protected string $prefix = 'transaction_';

    /** @var array<int, TransactionStatus> */
    private const CANCELLED_STATUSES = [
        TransactionStatus::Cancelled,
        TransactionStatus::AutoCancelled,
        TransactionStatus::ManualCancelled,
    ];

    /** @var array<int, TransactionStatus> */
    private const CONFIRMED_STATUSES = [
        TransactionStatus::AutoConfirmed,
        TransactionStatus::ManualConfirmed,
    ];

    public function __construct(
        protected VendorService $vendorService
    ) {
    }

    public function creating(Model $data): void
    {
        if (!empty($data->wallet_id) && empty($data->vendor_id)) {
            $wallet = Wallet::withoutGlobalScopes()
                ->select('id', 'vendor_id')
                ->find($data->wallet_id);
            if ($wallet && $wallet->vendor_id) {
                $data->vendor_id = $wallet->vendor_id;
            }
        }

        if (!isset($data->fee_amount) && isset($data->amount)) {
            $fee = null;

            if (isset($data->fee)) {
                $fee = $data->fee;
            } elseif (isset($data->site_id)) {
                $site = Site::find($data->site_id);
                if ($site) {
                    $fee = $site->transaction_fee ?? 0;
                    $data->fee = $fee;
                }
            }

            if ($fee !== null) {
                $data->fee_amount = ($data->amount * $fee) / 100;
            } else {
                $data->fee_amount = 0;
            }
        }
    }

    public function created(Model $data): void
    {
        Cache::rememberForever($this->prefix . $data->id, fn () => $data);

        $this->decreaseCapacityForTransaction($data);
    }

    public function updating(Model $data): void
    {
        if ($data->isDirty('amount')) {
            $fee = $data->fee ?? $data->getOriginal('fee') ?? 0;
            $data->fee_amount = ($data->amount * $fee) / 100;
        }
    }

    public function updated(Model $data): void
    {
        Cache::rememberForever($this->prefix . $data->id, fn () => $data);

        SendTransactionWebhookJob::dispatch($data->id);

        if ($data->wasChanged('wallet_id') && $data->wallet_id && !$data->getOriginal('wallet_id')) {
            $this->decreaseCapacityForTransaction($data);
        }

        if ($data->wasChanged('status')) {
            $oldStatus = $data->getOriginal('status');
            $newStatus = $data->status;

            if ($this->isConfirmedStatus($newStatus) && !$this->isConfirmedStatus($oldStatus)) {
                if (empty($data->accepted_at)) {
                    $data->accepted_at = now();
                    $data->saveQuietly();
                }

                $this->processConfirmedDeposit($data);
            }

            if ($this->isCancelledStatus($newStatus) && !$this->isConfirmedStatus($oldStatus)) {
                $this->releaseCapacityForTransaction($data);
            }
        }
    }

    public function deleted(Model $data): void
    {
        Cache::forget($this->prefix . $data->id);

        if (!$this->isConfirmedStatus($data->status)) {
            $this->releaseCapacityForTransaction($data);
        }
    }

    public function restored(Model $data): void
    {
        Cache::rememberForever($this->prefix . $data->id, fn () => $data);
    }

    public function forceDeleted(Model $data): void
    {
        Cache::forget($this->prefix . $data->id);
    }

    private function decreaseCapacityForTransaction(Model $transaction): void
    {
        if ($this->isCancelledStatus($transaction->status) || $transaction->status === TransactionStatus::Draft) {
            return;
        }

        if (!$this->resolveVendorId($transaction)) {
            return;
        }

        $amount = (float) ($transaction->amount ?? 0);
        if ($amount <= 0) {
            return;
        }

        $this->eachTransactionVendor(
            $transaction,
            fn (int $vendorId) => $this->vendorService->blockTransactionCapacity($vendorId, $amount)
        );
    }

    private function releaseCapacityForTransaction(Model $transaction): void
    {
        if (!$this->resolveVendorId($transaction)) {
            return;
        }

        $amount = (float) ($transaction->amount ?? 0);
        if ($amount <= 0) {
            return;
        }

        $this->eachTransactionVendor(
            $transaction,
            fn (int $vendorId) => $this->vendorService->releaseTransactionCapacity($vendorId, $amount)
        );
    }

    private function processConfirmedDeposit(Model $transaction): void
    {
        if (!$this->resolveVendorId($transaction)) {
            return;
        }

        $amount = (float) ($transaction->amount ?? 0);

        $this->eachTransactionVendor(
            $transaction,
            function (int $vendorId) use ($transaction, $amount) {
                $this->vendorService->processTransactionDeposit(
                    $transaction->id,
                    $amount,
                    $vendorId
                );
            }
        );
    }

    /**
     * @param  callable(int): void  $callback
     */
    private function eachTransactionVendor(Model $transaction, callable $callback): void
    {
        foreach ($this->transactionVendorIds($transaction) as $vendorId) {
            $callback($vendorId);
        }
    }

    /**
     * @return list<int>
     */
    private function transactionVendorIds(Model $transaction): array
    {
        $vendorId = $this->resolveVendorId($transaction);
        if (!$vendorId) {
            return [];
        }

        $ids = [$vendorId];

        $transaction->loadMissing('wallet.vendor.parent', 'vendor.parent');
        $vendor = $transaction->wallet?->vendor ?? $transaction->vendor;
        if ($vendor?->parent_id && $vendor->parent) {
            $ids[] = (int) $vendor->parent->id;
        }

        return $ids;
    }

    private function resolveVendorId(Model $transaction): ?int
    {
        if ($transaction->wallet_id) {
            $transaction->loadMissing('wallet:id,vendor_id');
            if ($transaction->wallet?->vendor_id) {
                return (int) $transaction->wallet->vendor_id;
            }
        }

        return $transaction->vendor_id ? (int) $transaction->vendor_id : null;
    }

    private function isConfirmedStatus(mixed $status): bool
    {
        return in_array($status, self::CONFIRMED_STATUSES, true);
    }

    private function isCancelledStatus(mixed $status): bool
    {
        return in_array($status, self::CANCELLED_STATUSES, true);
    }
}
