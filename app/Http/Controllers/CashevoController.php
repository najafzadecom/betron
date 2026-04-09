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
     * Cashevo POST callback (application/json).
     */
    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::channel('cashevo')->info('Cashevo callback', [
            'payload' => $payload,
        ]);

        $remoteTransactionId = $payload['transaction_id'] ?? $payload['transactionId'] ?? null;
        $remoteStatus = strtoupper((string) ($payload['status'] ?? ''));

        if ($remoteTransactionId) {
            $this->syncTransactionStatus((string) $remoteTransactionId, $remoteStatus);
            $this->syncWithdrawalStatus((string) $remoteTransactionId, $remoteStatus);
        }

        return response()->json(['received' => true]);
    }

    private function syncTransactionStatus(string $uuid, string $status): void
    {
        $transaction = Transaction::query()->where('uuid', $uuid)->first();

        if (!$transaction) {
            return;
        }

        if (in_array($status, ['SUCCESSFUL', 'SUCCESS', 'COMPLETED'], true)) {
            $transaction->update([
                'status' => TransactionStatus::AutoConfirmed->value,
                'paid_status' => true,
            ]);

            return;
        }

        if (in_array($status, ['FAILED', 'REJECTED', 'CANCELLED', 'CANCELED'], true)) {
            $transaction->update([
                'status' => TransactionStatus::AutoCancelled->value,
                'paid_status' => false,
            ]);
        }
    }

    private function syncWithdrawalStatus(string $uuid, string $status): void
    {
        $withdrawal = Withdrawal::withoutGlobalScopes()->where('uuid', $uuid)->first();

        if (!$withdrawal) {
            return;
        }

        if (in_array($status, ['SUCCESSFUL', 'SUCCESS', 'COMPLETED'], true)) {
            $withdrawal->update([
                'status' => WithdrawalStatus::AutoConfirmed->value,
                'paid_status' => true,
            ]);

            return;
        }

        if (in_array($status, ['FAILED', 'REJECTED', 'CANCELLED', 'CANCELED'], true)) {
            $withdrawal->update([
                'status' => WithdrawalStatus::AutoCancelled->value,
                'paid_status' => false,
            ]);
        }
    }
}
