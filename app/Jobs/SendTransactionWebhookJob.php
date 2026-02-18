<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\TransactionWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTransactionWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = [300, 900, 1800]; // 5 minutes, 15 minutes, 30 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $transactionId
    ) {
        // Set queue name if needed
        $this->onQueue('webhooks');
    }

    /**
     * Execute the job.
     */
    public function handle(TransactionWebhookService $webhookService): void
    {
        try {
            // Reload transaction from database to ensure we have latest data
            $transaction = Transaction::find($this->transactionId);

            if (!$transaction) {
                Log::channel('transaction_webhook')->warning('Transaction not found for webhook', [
                    'transaction_id' => $this->transactionId,
                ]);
                return;
            }

            // Send webhook (only called when paid_status is true)
            $success = $webhookService->sendPaidStatusChange($transaction);

            if (!$success) {
                // If webhook fails, throw exception to trigger retry
                throw new \Exception('Webhook request failed');
            }
        } catch (\Exception $e) {
            Log::channel('transaction_webhook')->error('Webhook job failed', [
                'transaction_id' => $this->transactionId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('transaction_webhook')->error('Webhook job permanently failed', [
            'transaction_id' => $this->transactionId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
