<?php

namespace App\Http\Controllers;

use App\Payment\PayByMePayment;
use Illuminate\Http\JsonResponse;

class PayByMeController extends Controller
{
    private PayByMePayment $payByMePayment;

    public function __construct(
        PayByMePayment $payByMePayment
    ) {
        $this->payByMePayment = $payByMePayment;
    }

    public function transaction(): JsonResponse
    {
        $transactions = $this->payByMePayment->getTransactionHistory();

        return response()->json($transactions);
    }

    public function getWithdrawalReceipt($uniqId): JsonResponse
    {
        $receipt = $this->payByMePayment->getWithdrawalReceipt($uniqId);

        return response()->json($receipt);
    }

    public function checkWithdrawalStatus($referenceId = null): JsonResponse
    {
        $withdrawal = $this->payByMePayment->checkWithdrawalStatus(10, $referenceId);

        return response()->json($withdrawal);
    }

    public function withdrawalAccounts(): JsonResponse
    {
        $accounts = $this->payByMePayment->getWithdrawalAccounts();

        return response()->json($accounts);
    }

    public function sendWithdrawal(): JsonResponse
    {
        $data = [
            'name' => 'Yilmaz Orkun',
            'description' => 'Test amaçlı',
            'from_iban' => 'TR950020600348044552470056',
            'iban' => 'TR770011100000000081291524',
            'balance' => "500",
            'identity_id' => '12312312312',
            'reference_id' => uniqid(),
        ];

        $result = $this->payByMePayment->sendWithdrawal($data);

        return response()->json($result);
    }
}
