<?php

namespace App\Payment;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PratikPayment
{
    private string $baseUrl;
    private string $userName;
    private string $password;
    private string $dealerCode;
    private string $branchCode;
    private string $channelID;
    private ?string $accessToken;

    public function __construct()
    {
        $this->baseUrl = config('pratik.base_url');
        $this->userName = config('pratik.username');
        $this->password = config('pratik.password');
        $this->dealerCode = config('pratik.dealer_code');
        $this->branchCode = config('pratik.branch_code');
        $this->channelID = config('pratik.channel_id');

        $this->accessToken = Cache::get('pratik-token');
    }

    /**
     * Authenticate and store token in a cache.
     */
    private function authenticate(): void
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'channelID' => $this->channelID,
            ])->post("{$this->baseUrl}/merchantapi/login", [
                'UserName' => $this->userName,
                'Password' => $this->password,
                'DealerCode' => $this->dealerCode,
                'BranchCode' => $this->branchCode,
            ]);

            $data = $response->json();

            if (isset($data['Success']) && $data['Success'] && $data['ResponseCode'] === '0000') {
                $this->accessToken = $data['Token'];
                $expiresIn = (int)$data['expires_in'] ?? 3600;

                Cache::put('pratik-token', $this->accessToken, $expiresIn);
            } else {
                Log::warning('PratikPayment auth failed', [
                    'response' => $data,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('PratikPayment authentication error: ' . $e->getMessage());
        }
    }

    public function myAccountBasic(): ?object
    {
        return $this->post('/merchantapi/my_account_basic', ['thisStep' => 'My_Account_Basic']);
    }

    public function myBalance(): ?object
    {
        return $this->post('/merchantapi/my_balance', ['thisStep' => 'My_Balance']);
    }

    public function mySecretKey(): ?object
    {
        return $this->post('/merchantapi/my_secret_key', ['thisStep' => 'Secret_Key_Send']);
    }

    public function myConfirmKey()
    {
        $confirmKey = rand(1000000000, 9999999999);

        $this->post('/merchantapi/my_confirm_key', ['thisStep' => 'Confirm_Key_Send', 'confirmKey' => $confirmKey]);

        return $confirmKey;
    }

    public function myIbanSave(): ?object
    {
        return $this->post(
            '/merchantapi/my_iban_save',
            [
                'iban' => 'TR280013400001493472700178',
                'accountHolderName' => 'Alıcı Hesap Sahibi Ad Soyad'
            ]
        );
    }

    public function checkBusinessWallet(): ?object
    {
        return $this->post(
            '/merchantapi/check_business_wallet',
            [
                'dataInfo' => '10DD3C28-0BBE-4B33-8F1A-A7EDB25C1319',
                'currencyCode' => 'TRY'
            ]
        );
    }

    public function moneyTransactionHistory($sDate, $lDate, $transactionTypeId = 6): ?object
    {
        return $this->post(
            '/merchantapi/money-transaction-history',
            [
                'thisStep' => 'Money_Transaction_History',
                'sDate' => $sDate,
                'lDate' => $lDate,
                'transactionTypeId' => $transactionTypeId,
                'transactionStatus' => 1,
                'qSize' => 'top100'
            ]
        );
    }

    public function sendMoneyToBank($walletId, $receiver, $iban, $amount, $extTransactionId): ?object
    {
        $secretKey = '1C8C550A-8E36-408D-9935-74E0F8FCEBC9';

        return $this->post(
            '/merchantapi/send_money_to_bank',
            [
                'thisStep' => 'send_money_to_bank',
                'senderWalletId' => $walletId,
                'receiverAccountHolderName' => $receiver,
                'receiverIban' => $iban,
                'receiverNationalIdOrTaxNo' => '',
                'senderDescription' => '',
                'paymentType' => 99,
                'amount' => $amount,
                'currencyCode' => 'TRY',
                'extTransactionId' => $extTransactionId,
                'confirmType' => 'confirmKey',
                'hashKey' => $this->generateHash($walletId, $amount, $iban, $extTransactionId, $secretKey),
            ]
        );
    }

    public function sendMoneyConfirm($walletId, $amount, $transactionId, $passCode): ?object
    {
        return $this->post(
            '/merchantapi/send_money_confirm',
            [
                'thisStep' => 'send_money_confirm',
                'senderWalletId' => $walletId,
                'amount' => $amount,
                'currencyCode' => 'TRY',
                'transactionId' => $transactionId,
                'passCode' => $passCode
            ]
        );
    }

    /**
     * Make an authenticated POST request.
     */
    public function post(string $endpoint, array $payload = []): ?object
    {
        return $this->request('post', $endpoint, $payload);
    }

    /**
     * Make an authenticated GET request.
     */
    public function get(string $endpoint, array $params = []): ?object
    {
        return $this->request('get', $endpoint, $params);
    }

    /**
     * Base HTTP request with token handling.
     */
    private function request(string $method, string $endpoint, array $data = []): ?object
    {
        if (!$this->accessToken) {
            $this->authenticate();
        }

        try {
            $url = $this->baseUrl . "{$endpoint}";

            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'accessToken' => $this->accessToken,
                'channelID' => $this->channelID,
            ];

            $response = Http::withHeaders($headers)->$method($url, $data);

            if ($response->status() === 401) {
                $this->authenticate();

                $headers['accessToken'] = $this->accessToken;

                $response = Http::withHeaders($headers)->$method($url, $data);
            }

            return $response->object();
        } catch (\Throwable $e) {
            Log::error('PratikPayment request error', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function generateHash($walletId, $amount, $iban, $extTransactionId, $secretKey): string
    {
        $dataToHash = $walletId . ":" . $amount . ":" . $iban . ":" . $extTransactionId . ":" . $secretKey;

        return hash('sha256', $dataToHash);
    }
}
