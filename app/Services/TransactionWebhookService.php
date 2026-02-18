<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionWebhookService
{
    /** @var array<int, string> */
    private array $webhookUrls;
    private string $secretKey;
    private bool $enabled;
    private int $timeout;

    public function __construct()
    {
        $this->webhookUrls = config('transaction_webhook.urls', []) ?: [];
        $this->secretKey = config('transaction_webhook.secret_key', '');
        $this->enabled = config('transaction_webhook.enabled', true);
        $this->timeout = config('transaction_webhook.timeout', 100);
    }

    /**
     * Send webhook notification when transaction paid_status becomes true.
     * Tüm tanımlı callback URL'lerine sırayla istek atar; hepsi 2xx dönerse true.
     *
     * @param Transaction $transaction
     * @return bool
     */
    public function sendPaidStatusChange(Transaction $transaction): bool
    {
        if (!$this->enabled || empty($this->webhookUrls) || empty($this->secretKey)) {
            return false;
        }

        $payload = [
            'transaction_id' => $transaction->id,
            'uuid' => $transaction->uuid,
            'user_id' => $transaction->user_id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency->value ?? $transaction->currency,
            'status' => $transaction->status->value ?? $transaction->status,
            'paid_status' => true,
            'first_name' => $transaction->first_name,
            'last_name' => $transaction->last_name,
            'phone' => $transaction->phone,
            'receiver_iban' => $transaction->receiver_iban,
            'receiver_name' => $transaction->receiver_name,
            'bank_id' => $transaction->bank_id,
            'bank_name' => $transaction->bank_name,
            'wallet_id' => $transaction->wallet_id,
            'site_id' => $transaction->site_id,
            'site_name' => $transaction->site_name,
            'order_id' => $transaction->order_id,
            'payment_method' => $transaction->payment_method->value ?? $transaction->payment_method,
            'created_at' => $transaction->created_at?->toIso8601String(),
            'updated_at' => $transaction->updated_at?->toIso8601String(),
            'accepted_at' => $transaction->accepted_at?->toIso8601String(),
            'timestamp' => now()->timestamp,
        ];

        $signature = $this->generateSignature($payload);
        $headers = [
            'Content-Type' => 'application/json',
            'X-Signature' => $signature,
            'X-Timestamp' => $payload['timestamp'],
        ];

        $allSuccess = true;

        foreach ($this->webhookUrls as $index => $webhookUrl) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders($headers)
                    ->post($webhookUrl, $payload);

                Log::channel('transaction_webhook')->info('Transaction webhook sent', [
                    'transaction_id' => $transaction->id,
                    'uuid' => $transaction->uuid,
                    'url_index' => $index,
                    'url' => $webhookUrl,
                    'paid_status' => true,
                    'status_code' => $response->status(),
                    'response' => $response->json(),
                ]);

                if (!$response->successful()) {
                    $allSuccess = false;
                }
            } catch (\Exception $e) {
                Log::channel('transaction_webhook')->error('Transaction webhook error', [
                    'transaction_id' => $transaction->id,
                    'uuid' => $transaction->uuid,
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
     * Generate HMAC SHA256 signature for the payload
     *
     * @param array $payload
     * @return string
     */
    private function generateSignature(array $payload): string
    {
        // Sort payload by keys to ensure consistent signature
        ksort($payload);

        // Create signature string: timestamp + sorted JSON payload + secret key
        $signatureString = $payload['timestamp'] . json_encode($payload, JSON_UNESCAPED_SLASHES) . $this->secretKey;

        // Generate HMAC SHA256 signature
        return hash_hmac('sha256', $signatureString, $this->secretKey);
    }
}
