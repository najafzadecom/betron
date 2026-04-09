<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\WithdrawalStatus;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CashevoController extends Controller
{
    /**
     * Cashevo POST callback — sadece yatırım (POST /deposit kayıtları).
     */
    public function callbackDeposit(Request $request): JsonResponse
    {
        return $this->handleCallback(
            $request,
            'deposit',
            fn (string $id, string $status, array $payload) => $this->syncTransactionStatus($id, $status, $payload)
        );
    }

    /**
     * Cashevo POST callback — sadece çekim (POST /withdraw kayıtları).
     */
    public function callbackWithdraw(Request $request): JsonResponse
    {
        return $this->handleCallback(
            $request,
            'withdraw',
            fn (string $id, string $status, array $payload) => $this->syncWithdrawalStatus($id, $status, $payload)
        );
    }

    /**
     * @param  callable(string, string, array): void  $sync
     */
    private function handleCallback(Request $request, string $kind, callable $sync): JsonResponse
    {
        $payload = $request->all();

        Log::channel('cashevo')->info('Cashevo callback', [
            'kind' => $kind,
            'payload' => $payload,
        ]);

        $remoteTransactionId = $payload['transaction_id'] ?? $payload['transactionId'] ?? null;
        $remoteStatus = strtoupper((string) ($payload['status'] ?? ''));

        if ($remoteTransactionId) {
            $sync((string) $remoteTransactionId, $remoteStatus, $payload);
        }

        return response()->json(['received' => true]);
    }

    private function syncTransactionStatus(string $uuid, string $status, array $payload): void
    {
        $transaction = Transaction::query()->where('uuid', $uuid)->first();

        if (!$transaction) {
            return;
        }

        $updates = [];

        $amount = $this->extractCallbackAmount($payload);
        if ($amount !== null) {
            $updates['amount'] = $amount;
            $fee = (int) ($transaction->fee ?? 0);
            $updates['fee_amount'] = ($amount * $fee) / 100;
        }

        if (in_array($status, ['SUCCESSFUL', 'SUCCESS', 'COMPLETED'], true)) {
            $updates['status'] = TransactionStatus::AutoConfirmed->value;
            $updates['paid_status'] = true;
        } elseif (in_array($status, ['FAILED', 'REJECTED', 'CANCELLED', 'CANCELED'], true)) {
            $updates['status'] = TransactionStatus::AutoCancelled->value;
            $updates['paid_status'] = false;
        }

        if ($updates !== []) {
            $transaction->update($updates);
        }
    }

    private function syncWithdrawalStatus(string $uuid, string $status, array $payload): void
    {
        $withdrawal = Withdrawal::withoutGlobalScopes()->where('uuid', $uuid)->first();

        if (!$withdrawal) {
            return;
        }

        $updates = [];

        $amount = $this->extractCallbackAmount($payload);
        if ($amount !== null) {
            $updates['amount'] = $amount;
            $fee = (int) ($withdrawal->fee ?? 0);
            $updates['fee_amount'] = ($amount * $fee) / 100;
        }

        if (in_array($status, ['SUCCESSFUL', 'SUCCESS', 'COMPLETED'], true)) {
            $updates['status'] = WithdrawalStatus::AutoConfirmed->value;
            $updates['paid_status'] = true;
        } elseif (in_array($status, ['FAILED', 'REJECTED', 'CANCELLED', 'CANCELED'], true)) {
            $updates['status'] = WithdrawalStatus::AutoCancelled->value;
            $updates['paid_status'] = false;
        }

        if ($updates !== []) {
            $withdrawal->update($updates);
        }
    }

    private function extractCallbackAmount(array $payload): ?float
    {
        $raw = $payload['amount'] ?? null;
        if ($raw === null || $raw === '') {
            return null;
        }
        if (!is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }
}
