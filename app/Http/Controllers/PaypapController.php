<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\WithdrawalStatus;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Payment\Paypap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaypapController extends Controller
{
    private Paypap $paypap;

    public function __construct(Paypap $paypap)
    {
        $this->paypap = $paypap;
    }

    /**
     * Create a bank deposit (direct)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createBankDepositDirect(Request $request): JsonResponse
    {
        $data = [
            'type' => 'direct',
            'transactionId' => $request->input('transaction_id', uniqid()),
            'fullName' => $request->input('full_name'),
            'currency' => $request->input('currency', 'TRY'),
            'amount' => $request->input('amount'),
            'user' => $request->input('user'),
        ];

        $result = $this->paypap->createBankDeposit($data);

        return response()->json($result, $result['http_status'] ?? 200);
    }

    /**
     * Get bank deposit details
     *
     * @param string $depositId
     * @return JsonResponse
     */
    public function getBankDeposit(string $depositId): JsonResponse
    {
        $result = $this->paypap->getBankDeposit($depositId);

        return response()->json($result, $result['http_status'] ?? 200);
    }

    /**
     * Create a bank withdrawal
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createBankWithdrawal(Request $request): JsonResponse
    {
        $data = [
            'transactionId' => $request->input('transaction_id', uniqid()),
            'fullName' => $request->input('full_name'),
            'iban' => $request->input('iban'),
            'currency' => $request->input('currency', 'TRY'),
            'amount' => $request->input('amount'),
            'user' => $request->input('user'),
        ];

        $result = $this->paypap->createBankWithdrawal($data);

        return response()->json($result, $result['http_status'] ?? 200);
    }

    /**
     * Get bank withdrawal details
     *
     * @param string $withdrawalId
     * @return JsonResponse
     */
    public function getBankWithdrawal(string $withdrawalId): JsonResponse
    {
        $result = $this->paypap->getBankWithdrawal($withdrawalId);

        return response()->json($result, $result['http_status'] ?? 200);
    }

    /**
     * Create a card deposit (direct)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createCardDepositDirect(Request $request): JsonResponse
    {
        $data = [
            'type' => 'direct',
            'transactionId' => $request->input('transaction_id', uniqid()),
            'currency' => $request->input('currency', 'TRY'),
            'amount' => $request->input('amount'),
            'card' => $request->input('card'),
            'billing' => $request->input('billing'),
            'user' => $request->input('user'),
            'redirectUrls' => $request->input('redirect_urls'),
        ];

        $result = $this->paypap->createCardDeposit($data);

        return response()->json($result, $result['http_status'] ?? 200);
    }

    /**
     * Create a card deposit (redirect)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createCardDepositRedirect(Request $request): JsonResponse
    {
        $data = [
            'type' => 'redirect',
            'transactionId' => $request->input('transaction_id', uniqid()),
            'currency' => $request->input('currency', 'TRY'),
            'amount' => $request->input('amount'),
            'billing' => $request->input('billing'),
            'user' => $request->input('user'),
            'redirectUrls' => $request->input('redirect_urls'),
        ];

        $result = $this->paypap->createCardDeposit($data);

        return response()->json($result, $result['http_status'] ?? 200);
    }

    /**
     * Get card deposit details
     *
     * @param string $depositId
     * @return JsonResponse
     */
    public function getCardDeposit(string $depositId): JsonResponse
    {
        $result = $this->paypap->getCardDeposit($depositId);

        return response()->json($result, $result['http_status'] ?? 200);
    }

    /**
     * Handle webhook callback
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::channel('paypap')->info(json_encode($payload, JSON_PRETTY_PRINT));
        $apiSecret = config('paypap.api_secret');

        // Verify checksum
        if (!$this->paypap->verifyChecksum($payload, $apiSecret)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid checksum',
            ], 401);
        }


        if ($payload['type'] == 'deposit') {

            if ($payload['requestedAmount'] == $payload['amount'] && $payload['status']['code'] == '4001') {
                Transaction::query()
                    ->whereRaw('paid_status IS FALSE')
                    ->whereIn('status', [TransactionStatus::Pending, TransactionStatus::Processing])
                    ->where('payment_method', 'paypap')
                    ->where('deposit_id', $payload['depositId'])
                    ->update([
                        'paid_status' => true,
                        'status' => TransactionStatus::AutoConfirmed
                    ]);
            } elseif ($payload['status']['code'] > 4001) {
                Transaction::query()
                    ->whereRaw('paid_status IS FALSE')
                    ->whereIn('status', [TransactionStatus::Pending, TransactionStatus::Processing])
                    ->where('payment_method', 'paypap')
                    ->where('deposit_id', $payload['depositId'])
                    ->update([
                        'paid_status' => false,
                        'status' => TransactionStatus::AutoCancelled
                    ]);
            }

            Log::channel('paypap')->info(DB::getRawQueryLog());
        } elseif ($payload['type'] == 'withdrawal') {
            if ($payload['status']['code'] == '5001') {
                Withdrawal::query()
                    ->whereRaw('paid_status IS FALSE')
                    ->where('status', WithdrawalStatus::Processing)
                    ->where('payment_method', 'paypap')
                    ->where('withdrawal_id', $payload['withdrawalId'])
                    ->update([
                        'paid_status' => true,
                        'status' => WithdrawalStatus::AutoConfirmed
                    ]);
            } elseif ($payload['status']['code'] > 5001) {
                Withdrawal::query()
                    ->whereRaw('paid_status IS FALSE')
                    ->where('status', WithdrawalStatus::Processing)
                    ->where('payment_method', 'paypap')
                    ->where('withdrawal_id', $payload['withdrawalId'])
                    ->update([
                        'paid_status' => false,
                        'status' => WithdrawalStatus::AutoCancelled
                    ]);
            }

        }

        return response()->json([
            'status' => true,
            'message' => 'Callback received',
            'data' => $payload,
        ]);
    }
}
