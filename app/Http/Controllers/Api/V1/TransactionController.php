<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\Store\TransactionRequest;
use App\Services\CashevoService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class TransactionController extends BaseController
{
    private TransactionService $transactionService;
    private CashevoService $cashevoService;

    public function __construct(
        TransactionService $transactionService,
        CashevoService     $cashevoService
    ) {
        $this->transactionService = $transactionService;
        $this->cashevoService = $cashevoService;
    }

    public function store(TransactionRequest $request): JsonResponse
    {
        if (!$this->isTransactionEnabled()) {
            return $this->response([], false, 403, 'Transaction not enabled');
        }

        DB::beginTransaction();

        try {
            $data = $this->prepareBaseData($request->validated());
            $data['status'] = 1;
            $data['payment_method'] = 'manual';

            $transaction = $this->transactionService->create($data);
            $cashevoResult = $this->cashevoService->createDepositBank((float) $data['amount']);

            if (!$cashevoResult['success']) {
                throw new RuntimeException($cashevoResult['message'] ?? 'Cashevo transaction failed');
            }

            $recipient = $this->cashevoService->extractRecipient($cashevoResult['data'] ?? []);

            $transaction->update([
                'receiver_iban' => $recipient['iban'],
                'receiver_name' => $recipient['name'],
            ]);

            $response = $this->response([
                'transaction_uuid' => $transaction->uuid,
                'receiver_iban' => $recipient['iban'],
                'receiver_name' => $recipient['name'],
            ], true, 200, 'Transaction created');

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

    private function prepareBaseData(array $data): array
    {
        $data['fee'] = $data['transaction_fee'];
        $data['fee_amount'] = ($data['amount'] * $data['transaction_fee']) / 100;

        unset($data['transaction_fee']);

        return $data;
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
