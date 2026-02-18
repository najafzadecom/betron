<?php

namespace App\Payment;

use Illuminate\Support\Facades\Http;

class ParaQrPayment
{
    private string $baseUrl;

    private string $apiToken;

    private string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('paraqr.base_url');
        $this->apiToken = config('paraqr.api_token');
        $this->secretKey = config('paraqr.secret_key');
    }

    private function request(string $url, array $data)
    {
        $response = Http::withToken($this->apiToken)
            ->acceptJson()
            ->post($this->baseUrl . $url, $data);

        return $response->object();
    }

    public function payinRequest(array $data)
    {
        return $this->request('/v2/payin-request', $data);
    }

    public function payoutRequest(array $data)
    {
        return $this->request('/v2/payout-request', $data);
    }

    public function checkHash(array $data)
    {
        $hash = $data['hash'];

        $myHash = sha1($this->secretKey . '-' . $data['system_order_no'] . '-' . $data['direction'] . '-' . $data['amount'] . '-' . $data['status']);

        return $hash === $myHash;
    }
}
