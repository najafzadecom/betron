<?php

namespace App\Jobs;

use App\Models\Withdrawal;
use App\Services\WithdrawalWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWithdrawalWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [300, 900, 1800];

    public function __construct(
        public int $withdrawalId
    ) {
        $this->onQueue('webhooks');
    }

    public function handle(WithdrawalWebhookService $webhookService): void
    {
        try {
            $withdrawal = Withdrawal::withoutGlobalScopes()->find($this->withdrawalId);

            if (!$withdrawal) {
                Log::channel('withdrawal_webhook')->warning('Withdrawal not found for webhook', [
                    'withdrawal_id' => $this->withdrawalId,
                ]);
                return;
            }

            $success = $webhookService->sendPaidStatusChange($withdrawal);

            if (!$success) {
                throw new \Exception('Withdrawal webhook request failed');
            }
        } catch (\Exception $e) {
            Log::channel('withdrawal_webhook')->error('Withdrawal webhook job failed', [
                'withdrawal_id' => $this->withdrawalId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('withdrawal_webhook')->error('Withdrawal webhook job permanently failed', [
            'withdrawal_id' => $this->withdrawalId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

