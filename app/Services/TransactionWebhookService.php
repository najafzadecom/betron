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
        // Determine URLs: prefer site-specific callback, otherwise global config
        $urls = $this->webhookUrls;
        $site = $transaction->site ?? null;
        if ($site && !empty($site->transaction_callback_url)) {
            $urls = [$site->transaction_callback_url];
        }

        if (!$this->enabled || empty($urls) || empty($this->secretKey)) {
            Log::channel('transaction_webhook')->warning('Webhook skipped (disabled or missing config)', [
                'transaction_id' => $transaction->id,
                'enabled' => $this->enabled,
                'has_urls' => !empty($urls),
                'has_secret' => !empty($this->secretKey),
            ]);
            return false;
        }

        $payload = $this->buildPayload($transaction);
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

                // File log (mevcut)
                Log::channel('transaction_webhook')->info('Transaction webhook sent', [
                    'payload' => $payload,
                ]);

                // Telegram log: gönderilen istek + alınan response'u olduğu gibi
                Log::channel('telegram')->info('Transaction webhook request/response', [
                    'logged_at' => now()->toIso8601String(),
                    'transaction_id' => $transaction->id,
                    'uuid' => $transaction->uuid,
                    'url_index' => $index,
                    'url' => $webhookUrl,
                    'request' => [
                        'headers' => $headers,
                        'payload' => $payload,
                    ],
                    'response' => [
                        'status_code' => $response->status(),
                        'headers' => $response->headers(),
                        'body' => $response->body(),
                    ],
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

                // Telegram log: hata durumunda da isteği ve hatayı gönder
                Log::channel('telegram')->error('Transaction webhook error', [
                    'logged_at' => now()->toIso8601String(),
                    'transaction_id' => $transaction->id,
                    'uuid' => $transaction->uuid,
                    'url_index' => $index,
                    'url' => $webhookUrl,
                    'request' => [
                        'headers' => $headers,
                        'payload' => $payload,
                    ],
                    'error' => $e->getMessage(),
                ]);

                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    /**
     * Build webhook payload for a transaction (same structure as sent to callback URLs).
     *
     * @param Transaction $transaction
     * @return array<string, mixed>
     */
    public function buildPayload(Transaction $transaction): array
    {
        return [
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
            'order_id' => "$transaction->order_id",
            'payment_method' => $transaction->payment_method->value ?? $transaction->payment_method,
            'created_at' => $transaction->created_at?->toIso8601String(),
            'updated_at' => $transaction->updated_at?->toIso8601String(),
            'accepted_at' => $transaction->accepted_at?->toIso8601String(),
            'timestamp' => now()->timestamp,
        ];
    }

    /**
     * Get reference data for signature verification: json (sorted), json_string, timestamp, signature.
     * Returns null if secret_key is not configured.
     *
     * @param Transaction $transaction
     * @return array{json: array, json_string: string, timestamp: int, signature: string}|null
     */
    public function getReferenceData(Transaction $transaction): ?array
    {
        return $this->getReferenceDataFromPayload($this->buildPayload($transaction));
    }

    /**
     * Get reference data from a given payload (e.g. to use static amount "1.50").
     * Returns null if secret_key is not configured.
     *
     * @param array<string, mixed> $payload
     * @return array{json: array, json_string: string, timestamp: int, signature: string}|null
     */
    public function getReferenceDataFromPayload(array $payload): ?array
    {
        if (empty($this->secretKey)) {
            return null;
        }
        ksort($payload);
        $jsonString = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac(
            'sha256',
            $payload['timestamp'] . $jsonString . $this->secretKey,
            $this->secretKey
        );
        return [
            'json' => $payload,
            'json_string' => $jsonString,
            'timestamp' => $payload['timestamp'],
            'signature' => $signature,
        ];
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

