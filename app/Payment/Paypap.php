<?php

namespace App\Payment;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Paypap
{
    private string $baseUrl;
    private string $apiToken;

    /**
     * Paypap constructor.
     */
    public function __construct()
    {
        $this->baseUrl = config('paypap.endpoint', 'https://test-api.paypap.org/v1');
        $this->apiToken = config('paypap.api_token');
    }

    /**
     * Make API request
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $endpoint API endpoint
     * @param array|null $data Request data for POST requests
     * @param string $errorMessage Error message prefix for logging
     * @return array
     */
    private function request(string $method, string $endpoint, ?array $data = null, string $errorMessage = 'API request'): array
    {
        try {
            $http = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ]);

            $response = match (strtoupper($method)) {
                'POST' => $http->post($this->baseUrl . $endpoint, $data),
                'GET' => $http->get($this->baseUrl . $endpoint, $data),
                'PUT' => $http->put($this->baseUrl . $endpoint, $data),
                'DELETE' => $http->delete($this->baseUrl . $endpoint, $data),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::channel('paypap')->error("PayPap {$errorMessage} error: " . $e->getMessage());

            return [
                'status' => false,
                'message' => $errorMessage . ' failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a bank deposit (direct or redirect)
     *
     * @param array $data
     * @return array
     */
    public function createBankDeposit(array $data): array
    {
        return $this->request('POST', '/bankDeposits', $data, 'create bank deposit');
    }

    /**
     * Get a bank deposit by ID
     *
     * @param string $depositId
     * @return array
     */
    public function getBankDeposit(string $depositId): array
    {
        return $this->request('GET', "/bankDeposits/{$depositId}", null, 'get bank deposit');
    }

    /**
     * Create a bank withdrawal
     *
     * @param array $data
     * @return array
     */
    public function createBankWithdrawal(array $data): array
    {
        return $this->request('POST', '/bankWithdrawals', $data, 'create bank withdrawal');
    }

    /**
     * Get a bank withdrawal by ID
     *
     * @param string $withdrawalId
     * @return array
     */
    public function getBankWithdrawal(string $withdrawalId): array
    {
        return $this->request('GET', "/bankWithdrawals/{$withdrawalId}", null, 'get bank withdrawal');
    }

    /**
     * Create a card deposit (direct or redirect)
     *
     * @param array $data
     * @return array
     */
    public function createCardDeposit(array $data): array
    {
        return $this->request('POST', '/cardDeposits', $data, 'create card deposit');
    }

    /**
     * Get a card deposit by ID
     *
     * @param string $depositId
     * @return array
     */
    public function getCardDeposit(string $depositId): array
    {
        return $this->request('GET', "/cardDeposits/{$depositId}", null, 'get card deposit');
    }

    /**
     * Verify checksum for webhook callback
     *
     * @param array $payload
     * @param string $apiSecret
     * @return bool
     */
    public function verifyChecksum(array $payload, string $apiSecret): bool
    {
        if ($payload['type'] == 'deposit') {
            if (!isset($payload['checksum']) || !isset($payload['timestamp']) || !isset($payload['depositId'])) {
                return false;
            }

            $expected = hash('sha1', $apiSecret . $payload['timestamp'] . $payload['depositId']);
        } elseif ($payload['type'] == 'withdrawal') {
            if (!isset($payload['checksum']) || !isset($payload['timestamp']) || !isset($payload['withdrawalId'])) {
                return false;
            }

            $expected = hash('sha1', $apiSecret . $payload['timestamp'] . $payload['withdrawalId']);
        }


        return hash_equals($expected, $payload['checksum']);
    }

    /**
     * Handle API response
     *
     * @param Response $response
     * @return array
     */
    private function handleResponse(Response $response): array
    {
        $statusCode = $response->status();

        if ($statusCode >= 200 && $statusCode < 300) {
            $data = $response->json();

            return [
                'status' => true,
                'data' => $data,
                'http_status' => $statusCode,
            ];
        }

        $errorData = $response->json();
        Log::channel('paypap')->error('PayPap API error: ', [
            'status' => $statusCode,
            'body' => $errorData,
        ]);

        $message = 'API error';
        if (isset($errorData['message'])) {
            if (is_array($errorData['message'])) {
                $message = $errorData['message']['desc'] ?? $errorData['message']['code'] ?? $message;
            } else {
                $message = $errorData['message'];
            }
        }

        return [
            'status' => false,
            'message' => $message,
            'error' => $errorData,
            'http_status' => $statusCode,
        ];
    }
}
