<?php

namespace App\Http\Controllers;

use App\Payment\IqWalletPayment;
use Illuminate\Http\JsonResponse;

class IqWalletController extends Controller
{
    private IqWalletPayment $iqWalletPayment;

    public function __construct(
        IqWalletPayment $iqWalletPayment
    ) {
        $this->iqWalletPayment = $iqWalletPayment;
    }

    public function transaction(): JsonResponse
    {
        $transactions = $this->iqWalletPayment->getTransactionHistory();

        return response()->json($transactions);
    }

    public function getWithdrawalReceipt($uniqId): JsonResponse
    {
        $receipt = $this->iqWalletPayment->getWithdrawalReceipt($uniqId);

        return response()->json($receipt);
    }

    public function checkWithdrawalStatus($referenceId = null): JsonResponse
    {
        $withdrawal = $this->iqWalletPayment->checkWithdrawalStatus(10, $referenceId);

        return response()->json($withdrawal);
    }

    public function withdrawalAccounts(): JsonResponse
    {
        $accounts = $this->iqWalletPayment->getWithdrawalAccounts();

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

        $result = $this->iqWalletPayment->sendWithdrawal($data);

        return response()->json($result);
    }
}
