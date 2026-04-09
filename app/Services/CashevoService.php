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

    public function notifyDeposit(Transaction $transaction): bool
    {
        if (!$this->enabled()) {
            return true;
        }

        $currency = $transaction->currency instanceof \BackedEnum
            ? $transaction->currency->value
            : (string) $transaction->currency;

        $payload = [
            'callback_url' => $this->callbackUrl(),
            'name' => $transaction->first_name,
            'surname' => $transaction->last_name,
            'type' => 'DEPOSIT',
            'amount' => $this->formatAmount((float) $transaction->amount),
            'banka' => (string) (int) $transaction->bank_id,
            'userId' => (string) $transaction->user_id,
            'currency' => $currency,
            'username' => $this->usernameForUser($transaction->user_id, $transaction->order_id),
            'paymentSource' => (string) config('cashevo.client_name'),
            'transactionId' => (string) $transaction->uuid,
            'paymentMethod' => (string) config('cashevo.payment_method'),
        ];

        return $this->post('/deposit', $payload, $transaction->id, 'deposit');
    }

    public function notifyWithdraw(Withdrawal $withdrawal): bool
    {
        if (!$this->enabled()) {
            return true;
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

        return $this->post('/withdraw', $payload, $withdrawal->id, 'withdraw');
    }

    private function post(string $path, array $payload, int $localId, string $kind): bool
    {
        $url = config('cashevo.base_url') . $path;
        $headers = [
            'api-key' => (string) config('cashevo.api_key'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if (filled(config('cashevo.bearer_token'))) {
            $headers['Authorization'] = 'Bearer ' . config('cashevo.bearer_token');
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::channel('cashevo')->info('Cashevo request ok', [
                    'kind' => $kind,
                    'local_id' => $localId,
                    'status' => $response->status(),
                ]);

                return true;
            }

            Log::channel('cashevo')->warning('Cashevo request failed', [
                'kind' => $kind,
                'local_id' => $localId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::channel('cashevo')->error('Cashevo request exception', [
                'kind' => $kind,
                'local_id' => $localId,
                'error' => $e->getMessage(),
            ]);

            return false;
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

    private function usernameForUser(mixed $userId, string $orderId): string
    {
        $raw = (string) $userId;
        if ($raw !== '' && $raw !== '0') {
            return 'user_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $raw);
        }

        return 'order_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $orderId);
    }
}
