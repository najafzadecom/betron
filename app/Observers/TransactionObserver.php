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

    public function __construct(
        protected VendorService $vendorService
    ) {
    }

    public function creating(Model $data): void
    {
        // Set vendor_id from wallet if wallet_id is provided but vendor_id is not
        if (!empty($data->wallet_id) && empty($data->vendor_id)) {
            $wallet = Wallet::withoutGlobalScopes()
                ->select('id', 'vendor_id')
                ->find($data->wallet_id);
            if ($wallet && $wallet->vendor_id) {
                $data->vendor_id = $wallet->vendor_id;
            }
        }

        // Calculate fee_amount based on fee or site's transaction_fee
        if (!isset($data->fee_amount) && isset($data->amount)) {
            $fee = null;

            // If fee is already set, use it
            if (isset($data->fee)) {
                $fee = $data->fee;
            } // If site_id is provided but fee is not, get transaction_fee from site
            elseif (isset($data->site_id)) {
                $site = Site::find($data->site_id);
                if ($site) {
                    $fee = $site->transaction_fee ?? 0;
                    $data->fee = $fee;
                }
            }

            // Calculate fee_amount if we have fee and amount
            if ($fee !== null) {
                $data->fee_amount = ($data->amount * $fee) / 100;
            } else {
                // If neither fee nor site_id is provided, set fee_amount to 0
                $data->fee_amount = 0;
            }
        }
    }

    public function created(Model $data): void
    {
        Cache::rememberForever($this->prefix . $data->id, fn () => $data);
    }

    public function updating(Model $data): void
    {
        // If amount is being updated, recalculate fee_amount based on fee
        if ($data->isDirty('amount')) {
            $fee = $data->fee ?? $data->getOriginal('fee') ?? 0;
            $newAmount = $data->amount;
            $data->fee_amount = ($newAmount * $fee) / 100;
        }
    }

    public function updated(Model $data): void
    {
        Cache::rememberForever($this->prefix . $data->id, fn () => $data);

        // Check if paid_status changed to true and send webhook via queue
        if ($data->wasChanged('paid_status') && $data->paid_status) {
            // Dispatch webhook job to queue (only when paid_status becomes true)
            SendTransactionWebhookJob::dispatch($data->id);
        }

        // Check if transaction status changed to confirmed
        if ($data->wasChanged('status')) {
            $oldStatus = $data->getOriginal('status');
            $newStatus = $data->status;

            // If status changed to confirmed (AutoConfirmed or ManualConfirmed)
            if (in_array($newStatus, [TransactionStatus::AutoConfirmed, TransactionStatus::ManualConfirmed])) {
                // Check if this transaction was not already processed
                if (!in_array($oldStatus, [TransactionStatus::AutoConfirmed, TransactionStatus::ManualConfirmed])) {
                    // Set accepted_at timestamp
                    if (empty($data->accepted_at)) {
                        $data->accepted_at = now();
                        $data->saveQuietly();
                    }

                    // Get vendor from wallet
                    if ($data->wallet && $data->wallet->vendor_id) {
                        // Transaction amount (vendor fee will be calculated in VendorService)
                        $amount = $data->amount ?? 0;

                        // Process deposit (decreases deposit)
                        // VendorService will calculate: amount - (amount * vendor.transaction_fee / 100)
                        $this->vendorService->processTransactionDeposit(
                            $data->id,
                            $amount,
                            $data->wallet->vendor_id
                        );

                        // Also process deposit for parent vendor if exists
                        $vendor = $data->wallet->vendor;
                        if ($vendor && $vendor->parent_id) {
                            $parentVendor = $vendor->parent;
                            if ($parentVendor) {
                                // Process deposit for parent vendor (decreases deposit)
                                // Parent vendor fee will be calculated in VendorService
                                $this->vendorService->processTransactionDeposit(
                                    $data->id,
                                    $amount,
                                    $parentVendor->id
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    public function deleted(Model $data): void
    {
        Cache::forget($this->prefix . $data->id);
    }

    public function restored(Model $data): void
    {
        Cache::rememberForever($this->prefix . $data->id, fn () => $data);
    }

    public function forceDeleted(Model $data): void
    {
        Cache::forget($this->prefix . $data->id);
    }
}
