<?php

namespace App\Observers;

use App\Enums\WithdrawalStatus;
use App\Models\Withdrawal as Model;
use App\Services\VendorService;
use Illuminate\Support\Facades\Cache;

class WithdrawalObserver
{
    protected string $prefix = 'withdrawal_';

    public function __construct(
        protected VendorService $vendorService
    ) {
    }

    public function created(Model $data): void
    {
        Cache::rememberForever($this->prefix . $data->id, fn () => $data);
    }

    public function updated(Model $data): void
    {
        Cache::rememberForever($this->prefix . $data->id, fn () => $data);

        // Check if withdrawal status changed to confirmed
        if ($data->wasChanged('status')) {
            $oldStatus = $data->getOriginal('status');
            $newStatus = $data->status;

            // If status changed to confirmed (AutoConfirmed or ManualConfirmed)
            if (in_array($newStatus, [WithdrawalStatus::AutoConfirmed, WithdrawalStatus::ManualConfirmed])) {
                // Check if this withdrawal was not already processed
                if (!in_array($oldStatus, [WithdrawalStatus::AutoConfirmed, WithdrawalStatus::ManualConfirmed])) {
                    // Set accepted_at timestamp
                    if (empty($data->accepted_at)) {
                        $data->accepted_at = now();
                        $data->saveQuietly();
                    }

                    // Get vendor from withdrawal
                    if ($data->vendor_id) {
                        // Withdrawal amount (vendor fee will be calculated in VendorService)
                        $amount = $data->amount ?? 0;

                        // Process deposit (increases deposit)
                        // VendorService will calculate: amount + (amount * vendor.withdrawal_fee / 100)
                        $this->vendorService->processWithdrawalDeposit(
                            $data->id,
                            $amount,
                            $data->vendor_id
                        );

                        // Also process deposit for parent vendor if exists
                        $vendor = $data->vendor;
                        if ($vendor && $vendor->parent_id) {
                            $parentVendor = $vendor->parent;
                            if ($parentVendor) {
                                // Process deposit for parent vendor (increases deposit)
                                // Parent vendor fee will be calculated in VendorService
                                $this->vendorService->processWithdrawalDeposit(
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
