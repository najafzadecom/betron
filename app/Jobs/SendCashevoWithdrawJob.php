<?php

namespace App\Jobs;

use App\Models\Withdrawal;
use App\Services\CashevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCashevoWithdrawJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public int $withdrawalId
    ) {
        $this->onQueue('integrations');
    }

    public function handle(CashevoService $cashevo): void
    {
        if (!$cashevo->enabled()) {
            return;
        }

        $withdrawal = Withdrawal::withoutGlobalScopes()->find($this->withdrawalId);

        if (!$withdrawal) {
            Log::channel('cashevo')->warning('Withdrawal not found for Cashevo withdraw', [
                'withdrawal_id' => $this->withdrawalId,
            ]);

            return;
        }

        if (!$cashevo->notifyWithdraw($withdrawal)) {
            throw new \RuntimeException('Cashevo withdraw notification failed');
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('cashevo')->error('Cashevo withdraw job permanently failed', [
            'withdrawal_id' => $this->withdrawalId,
            'error' => $exception->getMessage(),
        ]);
    }
}
