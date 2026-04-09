<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CashevoService
{
    public function enabled(): bool
    {
        return filled(config('cashevo.api_key')) && filled(config('cashevo.client_name'));
    }

    public function callbackUrl(): string
    {
        return url('/app/cashevo/callback');
    }

    public function createDepositBank(float $amount): array
    {
        if (!$this->enabled()) {
            return [
                'success' => false,
                'message' => 'Cashevo integration is not configured',
                'data' => null,
            ];
        }

        $payload = [
            'client_name' => (string) config('cashevo.client_name'),
            'amount' => (float) $amount,
        ];

        return $this->request('/deposit-bank', $payload, 'deposit-bank', useXApiKey: true);
    }

    /**
     * Para yatırma kaydı (callback için) — POST /deposit
     */
    public function createDeposit(Transaction $transaction, ?string $bankaFromBankQuery = null)
    {
        if (!$this->enabled()) {
            return [
                'success' => false,
                'message' => 'Cashevo integration is not configured',
                'data' => null,
            ];
        }

        dd($bankaFromBankQuery);

        $currency = "TRY";

        $banka = $bankaFromBankQuery ?? (string) (int) $transaction->bank_id;

        $payload = [
            'callback_url' => $this->callbackUrl(),
            'name' => $transaction->first_name,
            'surname' => $transaction->last_name,
            'type' => 'DEPOSIT',
            'amount' => $this->formatAmount((float) $transaction->amount),
            'banka' => $banka,
            'userId' => (string) $transaction->user_id,
            'currency' => $currency,
            'username' => $this->usernameForUser($transaction->user_id, $transaction->order_id),
            'paymentSource' => (string) config('cashevo.client_name'),
            'transactionId' => (string) $transaction->uuid,
            'paymentMethod' => (string) config('cashevo.payment_method'),
        ];

        return $this->request('/deposit', $payload, 'deposit', useXApiKey: false);
    }

    public function createWithdraw(Withdrawal $withdrawal): array
    {
        if (!$this->enabled()) {
            return [
                'success' => false,
                'message' => 'Cashevo integration is not configured',
                'data' => null,
            ];
        }

        $currency = $withdrawal->currency instanceof \BackedEnum
            ? $withdrawal->currency->value
            : (string) $withdrawal->currency;

        $payload = [
            'callback_url' => $this->callbackUrl(),
            'name' => $withdrawal->first_name,
            'surname' => $withdrawal->last_name,
            'type' => 'WITHDRAW',
            'amount' => $this->formatAmount((float) $withdrawal->amount),
            'userId' => (string) $withdrawal->user_id,
            'iban' => $this->normalizeIban($withdrawal->iban),
            'currency' => $currency,
            'username' => $this->usernameForUser($withdrawal->user_id, $withdrawal->order_id),
            'paymentSource' => (string) config('cashevo.client_name'),
            'transactionId' => (string) $withdrawal->uuid,
            'paymentMethod' => (string) config('cashevo.payment_method'),
        ];

        return $this->request('/withdraw', $payload, 'withdraw', useXApiKey: false);
    }

    public function extractRecipient(array $responseData): array
    {
        $recipient = $responseData['recipient'] ?? $responseData['data']['recipient'] ?? $responseData;

        $iban = $recipient['iban']
            ?? $recipient['IBAN']
            ?? $responseData['iban']
            ?? $responseData['IBAN']
            ?? null;

        $name = $recipient['fullName']
            ?? $recipient['name']
            ?? $recipient['accountName']
            ?? $responseData['fullName']
            ?? $responseData['name']
            ?? null;

        return [
            'iban' => $iban,
            'name' => $name,
        ];
    }

    /**
     * deposit-bank cevabından banka kodu (POST /deposit body banka alanı).
     */
    public function extractBanka(array $responseData): ?string
    {
        $nested = $responseData['data'] ?? null;
        if (is_array($nested)) {
            foreach (['banka', 'bank_id', 'bankCode', 'bank', 'id'] as $key) {
                if (isset($nested[$key]) && $nested[$key] !== '' && $nested[$key] !== null) {
                    return (string) $nested[$key];
                }
            }
        }

        foreach (['banka', 'bank_id', 'bankCode', 'bank'] as $key) {
            if (isset($responseData[$key]) && $responseData[$key] !== '' && $responseData[$key] !== null) {
                return (string) $responseData[$key];
            }
        }

        return null;
    }

    private function request(string $path, array $payload, string $kind, bool $useXApiKey = false): array
    {
        $url = config('cashevo.base_url') . $path;
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        if ($useXApiKey) {
            $headers['x-api-key'] = (string) config('cashevo.api_key');
        } else {
            $headers['api-key'] = (string) config('cashevo.api_key');
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($url, $payload);
            $decoded = $response->json();

            if ($response->successful()) {
                Log::channel('cashevo')->info('Cashevo request ok', [
                    'kind' => $kind,
                    'status' => $response->status(),
                    'response' => $decoded,
                ]);

                return [
                    'success' => true,
                    'message' => 'OK',
                    'data' => is_array($decoded) ? $decoded : [],
                ];
            }

            Log::channel('cashevo')->warning('Cashevo request failed', [
                'kind' => $kind,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => (string) ($decoded['message'] ?? $response->body() ?? 'Cashevo request failed'),
                'data' => is_array($decoded) ? $decoded : [],
            ];
        } catch (\Throwable $e) {
            Log::channel('cashevo')->error('Cashevo request exception', [
                'kind' => $kind,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    private function normalizeIban(?string $iban): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $iban));
    }

    private function usernameForUser(mixed $userId, ?string $orderId): string
    {
        $raw = (string) $userId;
        if ($raw !== '' && $raw !== '0') {
            return 'user_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $raw);
        }

        return 'order_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $orderId);
    }
}
