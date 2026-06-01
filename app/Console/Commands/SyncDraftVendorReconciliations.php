<?php

namespace App\Console\Commands;

use App\Enums\VendorReconciliationStatus;
use App\Models\VendorDailyReconciliation;
use App\Services\VendorReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncDraftVendorReconciliations extends Command
{
    protected $signature = 'vendor-reconciliations:sync-drafts';

    protected $description = 'Taslak bayi mutabakatlarını sistemden günceller (yatırım/çekim)';

    public function handle(VendorReconciliationService $reconciliationService): int
    {
        $drafts = VendorDailyReconciliation::query()
            ->where('status', VendorReconciliationStatus::Draft)
            ->orderBy('reconciliation_date')
            ->get();

        if ($drafts->isEmpty()) {
            $this->info('Güncellenecek taslak mutabakat yok.');

            return Command::SUCCESS;
        }

        $synced = 0;
        $failed = 0;

        foreach ($drafts as $draft) {
            try {
                $reconciliationService->refreshFromSystem($draft->id);
                $synced++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('vendor-reconciliations:sync-drafts failed', [
                    'reconciliation_id' => $draft->id,
                    'vendor_id' => $draft->vendor_id,
                    'date' => $draft->reconciliation_date?->format('Y-m-d'),
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $this->info(sprintf('%d taslak mutabakat işlendi: %d güncellendi, %d hata.', $drafts->count(), $synced, $failed));

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
