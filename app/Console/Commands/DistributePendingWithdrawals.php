<?php

namespace App\Console\Commands;

use App\Enums\WithdrawalStatus;
use App\Models\Vendor;
use App\Models\Withdrawal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DistributePendingWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'withdrawals:distribute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pending statusündeki withdrawal\'ları sırayla aktif üst bayilere dağıtır';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Pending withdrawal\'lar dağıtılıyor...');

        $withdrawals = Withdrawal::query()
            ->where('status', WithdrawalStatus::Pending)
            ->where('vendor_id', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($withdrawals->isEmpty()) {
            $this->info('Dağıtılacak pending withdrawal bulunamadı.');
            return Command::SUCCESS;
        }

        $this->info(sprintf('%d adet pending withdrawal bulundu.', $withdrawals->count()));

        $isPostgres = DB::getDriverName() === 'pgsql';

        $query = Vendor::query()
            ->whereNull('parent_id')
            ->orderBy('id');

        if ($isPostgres) {
            $query->whereRaw('status IS TRUE')
                ->whereRaw('withdrawal_enabled IS TRUE');
        } else {
            $query->where('status', true)
                ->where('withdrawal_enabled', true);
        }

        $parentVendors = $query->get();

        if ($parentVendors->isEmpty()) {
            $this->warn('Aktif üst bayi bulunamadı.');
            return Command::SUCCESS;
        }

        $this->info(sprintf('%d adet aktif üst bayi bulundu.', $parentVendors->count()));

        $cacheKey = 'withdrawal_last_vendor_id';
        $lastVendorId = Cache::get($cacheKey);
        $currentVendorIndex = 0;

        if ($lastVendorId) {
            $index = $parentVendors->search(function ($vendor) use ($lastVendorId) {
                return $vendor->id == $lastVendorId;
            });
            if ($index !== false) {
                $currentVendorIndex = ($index + 1) % $parentVendors->count();
            }
        }

        $distributed = 0;
        $failed = 0;

        foreach ($withdrawals as $withdrawal) {
            try {
                $attempts = 0;
                $maxAttempts = $parentVendors->count();
                $vendorAssigned = false;

                // Try to find a vendor that meets the minimum withdrawal amount requirement
                while ($attempts < $maxAttempts && !$vendorAssigned) {
                    $vendor = $parentVendors[$currentVendorIndex];

                    // Check if vendor has minimum withdrawal amount set and if withdrawal amount meets it
                    $minimumAmount = $vendor->minimum_withdrawal_amount ?? 0;
                    if ($minimumAmount > 0 && $withdrawal->amount < $minimumAmount) {
                        $this->warn(sprintf(
                            'Withdrawal #%d (Amount: %s) minimum miktarı karşılamıyor. Vendor #%d minimum: %s. Sonraki vendor deneniyor...',
                            $withdrawal->id,
                            $withdrawal->amount,
                            $vendor->id,
                            $minimumAmount
                        ));
                        $currentVendorIndex = ($currentVendorIndex + 1) % $parentVendors->count();
                        $attempts++;
                        continue;
                    }

                    // Check if vendor has maximum withdrawal amount set and if withdrawal amount exceeds it
                    $maximumAmount = $vendor->maximum_withdrawal_amount ?? 0;
                    if ($maximumAmount > 0 && $withdrawal->amount > $maximumAmount) {
                        $this->warn(sprintf(
                            'Withdrawal #%d (Amount: %s) maximum miktarı aşıyor. Vendor #%d maximum: %s. Sonraki vendor deneniyor...',
                            $withdrawal->id,
                            $withdrawal->amount,
                            $vendor->id,
                            $maximumAmount
                        ));
                        $currentVendorIndex = ($currentVendorIndex + 1) % $parentVendors->count();
                        $attempts++;
                        continue;
                    }

                    // Vendor meets requirements, assign withdrawal
                    $withdrawal->vendor_id = $vendor->id;
                    $withdrawal->status = WithdrawalStatus::Processing;
                    $withdrawal->save();

                    Cache::put($cacheKey, $vendor->id, now()->addHours(6));

                    $this->info(sprintf(
                        'Withdrawal #%d (Amount: %s) -> Vendor #%d (%s) atandı.',
                        $withdrawal->id,
                        $withdrawal->amount,
                        $vendor->id,
                        $vendor->name ?? 'N/A'
                    ));

                    $distributed++;
                    $vendorAssigned = true;
                    $currentVendorIndex = ($currentVendorIndex + 1) % $parentVendors->count();
                }

                // If no vendor could be assigned due to minimum/maximum amount restrictions
                if (!$vendorAssigned) {
                    $this->error(sprintf(
                        'Withdrawal #%d (Amount: %s) hiçbir vendor\'ın minimum/maksimum miktar şartını karşılamıyor. Atama yapılamadı.',
                        $withdrawal->id,
                        $withdrawal->amount
                    ));
                    $failed++;
                }
            } catch (\Exception $e) {
                Log::error('Withdrawal dağıtım hatası', [
                    'withdrawal_id' => $withdrawal->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $this->error(sprintf(
                    'Withdrawal #%d dağıtılırken hata oluştu: %s',
                    $withdrawal->id,
                    $e->getMessage()
                ));
                $failed++;
            }
        }

        $this->info(sprintf(
            'İşlem tamamlandı. %d başarılı, %d başarısız.',
            $distributed,
            $failed
        ));

        return Command::SUCCESS;
    }
}
