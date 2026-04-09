<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\CashevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCashevoDepositJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public int $transactionId
    ) {
        $this->onQueue('integrations');
    }

    public function handle(CashevoService $cashevo): void
    {
        if (!$cashevo->enabled()) {
            return;
        }

        $transaction = Transaction::query()->find($this->transactionId);

        if (!$transaction) {
            Log::channel('cashevo')->warning('Transaction not found for Cashevo deposit', [
                'transaction_id' => $this->transactionId,
            ]);

            return;
        }

        if (!$cashevo->notifyDeposit($transaction)) {
            throw new \RuntimeException('Cashevo deposit notification failed');
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('cashevo')->error('Cashevo deposit job permanently failed', [
            'transaction_id' => $this->transactionId,
            'error' => $exception->getMessage(),
        ]);
    }
}
