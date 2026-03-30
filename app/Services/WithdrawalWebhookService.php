<?php

namespace App\Services;

use App\Models\Withdrawal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WithdrawalWebhookService
{
    /** @var array<int, string> */
    private array $webhookUrls;
    private string $secretKey;
    private bool $enabled;
    private int $timeout;

    public function __construct()
    {
        $this->webhookUrls = [];
        $this->secretKey = 'base64:LMwQ08wCOzE28jvLA0kSwZaTKGL7+CW7eczDYSBJfns=';
        $this->enabled = true;
        $this->timeout = 100;
    }

    /**
     * Send webhook when withdrawal paid_status becomes true.
     *
     * @param Withdrawal $withdrawal
     * @return bool
     */
    public function sendPaidStatusChange(Withdrawal $withdrawal): bool
    {
        // Determine URLs: prefer site-specific callback, otherwise global config
        $urls = $this->webhookUrls;
        $site = $withdrawal->site ?? null;
        if ($site && !empty($site->withdrawal_callback_url)) {
            $urls = [$site->withdrawal_callback_url];
        }

        if (!$this->enabled || empty($urls) || empty($this->secretKey)) {
            Log::channel('withdrawal_webhook')->warning('Withdrawal webhook skipped (disabled or missing config)', [
                'withdrawal_id' => $withdrawal->id,
                'enabled' => $this->enabled,
                'has_urls' => !empty($urls),
                'has_secret' => !empty($this->secretKey),
            ]);
            return false;
        }

        $payload = $this->buildPayload($withdrawal);
        $signature = $this->generateSignature($payload);
        $headers = [
            'Content-Type' => 'application/json',
            'X-Signature' => $signature,
            'X-Timestamp' => $payload['timestamp'],
        ];

        $allSuccess = true;

        foreach ($urls as $index => $webhookUrl) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders($headers)
                    ->post($webhookUrl, $payload);

                Log::channel('withdrawal_webhook')->info('Withdrawal webhook sent', [
                    'withdrawal_id' => $withdrawal->id,
                    'uuid' => $withdrawal->uuid,
                    'url_index' => $index,
                    'url' => $webhookUrl,
                    'status_code' => $response->status(),
                ]);

                if (!$response->successful()) {
                    $allSuccess = false;
                }
            } catch (\Exception $e) {
                Log::channel('withdrawal_webhook')->error('Withdrawal webhook error', [
                    'withdrawal_id' => $withdrawal->id,
                    'uuid' => $withdrawal->uuid,
                    'url_index' => $index,
                    'url' => $webhookUrl,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    /**
     * Payload: withdrawals tablosundaki alanlara göre (create_withdrawals_table migration).
     * site_name appends'tan; payment_method/accepted_at tabloda yoksa atlanır.
     *
     * @param Withdrawal $withdrawal
     * @return array<string, mixed>
     */
    public function buildPayload(Withdrawal $withdrawal): array
    {
        $payload = [
            'withdrawal_id' => $withdrawal->id,
            'uuid' => $withdrawal->uuid,
            'user_id' => $withdrawal->user_id !== null ? (string) $withdrawal->user_id : null,
            'first_name' => $withdrawal->first_name,
            'last_name' => $withdrawal->last_name,
            'bank_id' => $withdrawal->bank_id,
            'bank_name' => $withdrawal->bank_name,
            'iban' => $withdrawal->iban,
            'amount' => $withdrawal->amount,
            'fee' => $withdrawal->fee,
            'fee_amount' => $withdrawal->fee_amount,
            'order_id' => "$withdrawal->order_id",
            'currency' => $withdrawal->currency->value ?? $withdrawal->currency,
            'status' => $withdrawal->status->value ?? $withdrawal->status,
            'site_id' => $withdrawal->site_id,
            'paid_status' => $withdrawal->paid_status,
            'manual' => $withdrawal->manual,
            'vendor_id' => $withdrawal->vendor_id,
            'site_name' => $withdrawal->site_name ?? null,
            'created_at' => $withdrawal->created_at?->toIso8601String(),
            'updated_at' => $withdrawal->updated_at?->toIso8601String(),
            'timestamp' => now()->timestamp,
        ];

        if (isset($withdrawal->payment_method)) {
            $payload['payment_method'] = $withdrawal->payment_method->value ?? $withdrawal->payment_method;
        }
        if (isset($withdrawal->accepted_at)) {
            $payload['accepted_at'] = $withdrawal->accepted_at?->toIso8601String();
        }

        return $payload;
    }

    private function generateSignature(array $payload): string
    {
        ksort($payload);
        $signatureString = $payload['timestamp'] . json_encode($payload, JSON_UNESCAPED_SLASHES) . $this->secretKey;
        return hash_hmac('sha256', $signatureString, $this->secretKey);
    }
}

