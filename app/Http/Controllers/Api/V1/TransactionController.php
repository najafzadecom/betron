<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TransactionStatus;
use App\Http\Requests\Api\Store\TransactionRequest;
use App\Models\Transaction;
use App\Payment\Paypap;
use App\Services\CashevoService;
use App\Services\TransactionService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class TransactionController extends BaseController
{
    public function __construct(
        private TransactionService $transactionService,
        private CashevoService $cashevoService,
        private WalletService $walletService,
        private Paypap $paypap,
    ) {
    }

    public function store(TransactionRequest $request)
    {
        if (!$this->isTransactionEnabled()) {
            return $this->response([], false, 403, 'Transaction not enabled');
        }

        DB::beginTransaction();

        try {
            $data = $this->prepareBaseData($request->validated());
            $data['status'] = 1;

            if ($this->cashevoService->enabled()) {
                $data['payment_method'] = 'manual';
                $transaction = $this->transactionService->create($data);
                $cashevoResult = $this->cashevoService->createDepositBank((float) $data['amount']);

                if (!$cashevoResult['success']) {
                    throw new RuntimeException($cashevoResult['message'] ?? 'Cashevo transaction failed');
                }

                $banka = $cashevoResult['data'][0];

                var_dump($banka);
                die();

                $transaction->update([
                    'receiver_iban' => $banka['iban'],
                    'receiver_name' => $banka['account_name'],
                ]);

                $deposit = $this->cashevoService->createDeposit($transaction, $banka['id']);

                if (!$deposit['success']) {
                    throw new RuntimeException($deposit['message'] ?? 'Cashevo deposit failed');
                }


                $response = $this->response([
                    'transaction_uuid' => $transaction->uuid,
                    'receiver_iban' => $recipient['iban'],
                    'receiver_name' => $recipient['name'],
                ], true, 200, 'Transaction created');
            } else {
                $response = $this->isManualTransaction($data['amount'])
                    ? $this->handleManualTransaction($data)
                    : $this->handlePaypapTransaction($data, $request);
            }

            DB::commit();

            return $response;
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Transaction create failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->response([], false, 500, $e->getMessage() ?: 'Transaction create failed');
        }
    }

    private function isTransactionEnabled(): bool
    {
        return setting('transaction_status') == 1;
    }

    private function isManualTransaction(float $amount): bool
    {
        return $amount < setting('manual_limit');
    }

    private function prepareBaseData(array $data): array
    {
        $data['fee'] = $data['transaction_fee'];
        $data['fee_amount'] = ($data['amount'] * $data['transaction_fee']) / 100;

        unset($data['transaction_fee']);

        return $data;
    }

    private function handleManualTransaction(array $data): JsonResponse
    {
        if ($data['amount'] < 2000) {
            return $this->response([], false, 400, 'Minimum amount is 2000');
        }

        $existingTransaction = Transaction::where('user_id', $data['user_id'])
            ->whereIn('status', [TransactionStatus::Pending, TransactionStatus::Processing])
            ->first();

        if ($existingTransaction) {
            return $this->response([], false, 400, 'User has a pending or processing transaction');
        }

        $wallet = $this->walletService->rand($data['bank_id'], $data['amount']);

        if (!$wallet) {
            return $this->response([], false, 404, 'Doesn\'t exist bank');
        }

        $data = array_merge($data, [
            'wallet_id' => $wallet->id,
            'receiver_iban' => $wallet->iban,
            'receiver_name' => $wallet->name,
            'payment_method' => 'manual',
            'vendor_id' => $wallet->vendor_id,
        ]);

        $transaction = $this->transactionService->create($data);

        return $this->response([
            'transaction_uuid' => $transaction->uuid,
            'receiver_iban' => $wallet->iban,
            'receiver_name' => $wallet->name,
        ], true, 200, 'Transaction created');
    }

    private function handlePaypapTransaction(array $data, Request $request): JsonResponse
    {
        $data['payment_method'] = 'paypap';

        $transaction = $this->transactionService->create($data);

        $paypapResult = $this->paypap->createBankDeposit([
            'type' => 'direct',
            'transactionId' => $transaction->uuid,
            'fullName' => $data['first_name'] . ' ' . $data['last_name'],
            'currency' => 'TRY',
            'amount' => $data['amount'],
            'user' => [
                'userId' => $data['user_id'],
                'username' => mb_strtolower($data['first_name'] . '_' . $data['last_name']),
                'fullName' => $data['first_name'] . ' ' . $data['last_name'],
            ],
        ]);

        if ($paypapResult['status']) {
            $transaction->update([
                'receiver_iban' => $paypapResult['data']['recipient']['iban'],
                'receiver_name' => $paypapResult['data']['recipient']['fullName'],
                'deposit_id' => $paypapResult['data']['depositId'],
            ]);

            return $this->response([
                'transaction_uuid' => $transaction->uuid,
                'receiver_iban' => $paypapResult['data']['recipient']['iban'],
                'receiver_name' => $paypapResult['data']['recipient']['fullName'],
            ], true, 200, 'Transaction created');
        }

        throw new RuntimeException($paypapResult['message'] ?? 'PayPap transaction failed');
    }

    public function update($uuid): JsonResponse
    {
        $transaction = $this->transactionService->request($uuid);

        $result = [
            'id' => $transaction->id,
            'uuid' => $transaction->uuid,
            'user_id' => $transaction->user_id,
            'first_name' => $transaction->first_name,
            'last_name' => $transaction->last_name,
            'sender' => $transaction->sender,
            'phone' => $transaction->phone,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'order_id' => $transaction->order_id,
            'receiver_iban' => $transaction->receiver_iban,
            'receiver_name' => $transaction->receiver_name,
            'receiver' => $transaction->receiver,
            'bank_id' => $transaction->bank_id,
            'bank_name' => $transaction->bank_name,
            'status' => $transaction->status,
            'paid_status' => $transaction->paid_status,
            'client_ip' => $transaction->client_ip,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at
        ];

        return $this->response(
            $result,
            true,
            201,
            'Transaction paid request successfully'
        );
    }

    public function status($uuid): JsonResponse
    {
        $transaction = $this->transactionService->getByUuid($uuid);

        $result = [
            'id' => $transaction->id,
            'uuid' => $transaction->uuid,
            'user_id' => $transaction->user_id,
            'first_name' => $transaction->first_name,
            'last_name' => $transaction->last_name,
            'sender' => $transaction->sender,
            'phone' => $transaction->phone,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'order_id' => $transaction->order_id,
            'receiver_iban' => $transaction->receiver_iban,
            'receiver_name' => $transaction->receiver_name,
            'receiver' => $transaction->receiver,
            'bank_id' => $transaction->bank_id,
            'bank_name' => $transaction->bank_name,
            'status' => $transaction->status,
            'paid_status' => $transaction->paid_status,
            'client_ip' => $transaction->client_ip,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at
        ];

        return $this->response(
            $result,
            true,
            201,
            'Transaction details'
        );
    }
}
