<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\Store\WithdrawalRequest;
use App\Models\Bank;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;

class WithdrawalController extends BaseController
{
    private WithdrawalService $withdrawalService;

    public function __construct(
        WithdrawalService $withdrawalService
    ) {
        $this->withdrawalService = $withdrawalService;
    }

    public function store(WithdrawalRequest $request): JsonResponse
    {
        if (setting('minimum_limit') > $request->get('amount')) {
            return $this->response(
                [],
                false,
                401,
                'Withdrawal amount cannot be lower than the minimum limit.'
            );
        }

        if (!setting('withdrawal_status')) {
            return $this->response(
                [],
                false,
                401,
                'Withdrawal service is disabled.'
            );
        }


        $data = $request->validated();

        $bank = Bank::query()->findOrFail($data['bank_id']);

        if (!$bank->withdrawal_status) {
            return $this->response(
                [],
                false,
                400,
                'This bank is closed for withdrawals.'
            );
        }

        $data['bank_name'] = $bank->name;
        $data['fee'] = $data['withdrawal_fee'];
        $data['fee_amount'] = ($data['amount'] * $data['withdrawal_fee']) / 100;

        unset($data['withdrawal_fee']);

        // Set default payment_method to manual if not provided
        if (!isset($data['payment_method'])) {
            $data['payment_method'] = \App\Enums\PaymentProvider::Manual->value;
        }

        $withdrawal = $this->withdrawalService->create($data);

        $result = [
            'id' => $withdrawal->id,
            'uuid' => $withdrawal->uuid,
            'first_name' => $withdrawal->first_name,
            'last_name' => $withdrawal->last_name,
            'receiver' => $withdrawal->receiver,
            'iban' => $withdrawal->iban,
            'bank_id' => $withdrawal->bank_id,
            'bank_name' => $withdrawal->bank_name,
            'amount' => $withdrawal->amount,
            'order_id' => $withdrawal->order_id,
            'site_id' => $withdrawal->site_id,
            'site_name' => $withdrawal->site_name,
            'sender_name' => $withdrawal->sender_name ?? null,
            'sender_iban' => $withdrawal->sender_iban ?? null,
            'status' => (int)$withdrawal->status,
            'paid_status' => (bool)$withdrawal->paid_status,
            'created_at' => $withdrawal->created_at,
            'updated_at' => $withdrawal->updated_at
        ];

        return $this->response(
            $result,
            true,
            201,
            'Withdrawal created successfully'
        );
    }

    public function status($uuid): JsonResponse
    {
        $withdrawal = $this->withdrawalService->getByUuid($uuid);

        $result = [
            'id' => $withdrawal->id,
            'uuid' => $withdrawal->uuid,
            'first_name' => $withdrawal->first_name,
            'last_name' => $withdrawal->last_name,
            'receiver' => $withdrawal->receiver,
            'iban' => $withdrawal->iban,
            'bank_id' => $withdrawal->bank_id,
            'bank_name' => $withdrawal->bank_name,
            'amount' => $withdrawal->amount,
            'order_id' => $withdrawal->order_id,
            'site_id' => $withdrawal->site_id,
            'site_name' => $withdrawal->site_name,
            'sender_name' => $withdrawal->sender_name,
            'sender_iban' => $withdrawal->sender_iban,
            'status' => $withdrawal->status,
            'paid_status' => $withdrawal->paid_status,
            'created_at' => $withdrawal->created_at,
            'updated_at' => $withdrawal->updated_at
        ];

        return $this->response(
            $result,
            true,
            201,
            'Withdrawal created successfully'
        );
    }
}
