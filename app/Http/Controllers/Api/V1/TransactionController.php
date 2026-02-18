<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TransactionStatus;
use App\Http\Requests\Api\Store\TransactionRequest;
use App\Models\Transaction;
use App\Payment\Paypap;
use App\Services\TransactionService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends BaseController
{
    private TransactionService $transactionService;
    private WalletService $walletService;

    private Paypap $paypap;

    public function __construct(
        TransactionService $transactionService,
        WalletService      $walletService,
        Paypap             $paypap
    ) {
        $this->transactionService = $transactionService;
        $this->walletService = $walletService;
        $this->paypap = $paypap;
    }

    public function store(TransactionRequest $request): JsonResponse
    {
        if (!$this->isTransactionEnabled()) {
            return $this->response([], false, 403, 'Transaction not enabled');
        }

        $data = $this->prepareBaseData($request->validated());

        return $this->isManualTransaction($data['amount'])
            ? $this->handleManualTransaction($data)
            : $this->handlePaypapTransaction($data, $request);
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

        // Check if user has any pending or processing transactions
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

        $result = [
            'transaction_uuid' => $transaction->uuid,
            'receiver_iban' => $wallet->iban,
            'receiver_name' => $wallet->name,
        ];

        return $this->response($result, true, 200, 'Transaction created');
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

            $result = [
                'transaction_uuid' => $transaction->uuid,
                'receiver_iban' => $paypapResult['data']['recipient']['iban'],
                'receiver_name' => $paypapResult['data']['recipient']['fullName'],
            ];

            return $this->response($result, true, 200, 'Transaction created');
        }

        return $this->response([], false, 500, $paypapResult['message']);
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
