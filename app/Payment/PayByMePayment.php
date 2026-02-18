<?php

namespace App\Payment;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayByMePayment
{
    private string $baseUrl;

    private string $email;

    private string $key;

    /**
     *
     */
    public function __construct()
    {
        $this->baseUrl = config('paybyme.endpoint');
        $this->email = config('paybyme.email');
        $this->key = config('paybyme.key');
    }

    /**
     * @param int $minuteRange
     * @param string|null $iban
     * @return array
     */
    public function getTransactionHistory(int $minuteRange = 60, ?string $iban = null): array
    {
        $params = [
            'email' => $this->email,
            'key' => $this->key,
            'minute_range' => $minuteRange,
        ];

        if ($iban) {
            $params['iban'] = $iban;
        }

        try {
            $response = Http::get($this->baseUrl . '/transaction/last/list', $params);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::channel('paybyme')->error('PayByMe transaction history error: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Əlaqə xətası',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function sendWithdrawal(array $data): array
    {
        $params = [
            'email' => $this->email,
            'key' => $this->key,
            'name' => $data['name'],
            'description' => $data['description'],
            'from_iban' => $data['from_iban'],
            'iban' => $data['iban'],
            'balance' => $data['balance'],
            'identity_id' => $data['identity_id'] ?? '12312312312',
            'reference_id' => $data['reference_id'],
        ];

        try {
            $response = Http::post($this->baseUrl . '/cashout/send', $params);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::channel('paybyme')->error('PayByMe withdrawal error: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Çəkim əməliyyatında xəta',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param string|null $referenceId
     * @param int $minuteRange
     * @return array
     */
    public function checkWithdrawalStatus(int $minuteRange = 10, ?string $referenceId = null): array
    {
        $params = [
            'email' => $this->email,
            'key' => $this->key,
            'minute_range' => $minuteRange,
        ];

        if ($referenceId) {
            $params['reference_id'] = $referenceId;
        }

        try {
            $response = Http::get($this->baseUrl . '/cashout/transactions', $params);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::channel('paybyme')->error('PayByMe withdrawal status error: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Status yoxlanmasında xəta',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array
     */
    public function getWithdrawalAccounts(): array
    {
        $params = [
            'email' => $this->email,
            'key' => $this->key,
        ];

        try {
            $response = Http::get($this->baseUrl . '/cashout/accounts', $params);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::channel('paybyme')->error('PayByMe accounts error: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Hesablar əldə edilərkən xəta',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param string $uniqueId
     * @return array
     */
    public function getWithdrawalReceipt(string $uniqueId): array
    {
        $params = [
            'email' => $this->email,
            'key' => $this->key,
            'unique_id' => $uniqueId,
        ];

        try {
            $response = Http::get($this->baseUrl . '/cashout/transactions/dekont', $params);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::channel('paybyme')->error('PayByMe receipt error: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Dekont əldə edilərkən xəta',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Response $response
     * @return array
     */
    private function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            $data = $response->json();

            if ($data['status'] ?? false) {
                return $data;
            } else {
                Log::channel('paybyme')->warning('PayByMe API error: ', $data);

                return [
                    'status' => false,
                    'message' => 'API xətası',
                    'data' => $data,
                ];
            }
        }

        Log::channel('paybyme')->error('PayByMe HTTP error: ' . $response->status() . ' - ' . $response->body());

        return [
            'status' => false,
            'message' => 'HTTP xətası: ' . $response->status(),
            'error' => $response->body(),
        ];
    }
}
