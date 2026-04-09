<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\BankCollection;
use App\Services\BankService;
use App\Services\CashevoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankController extends BaseController
{
    private BankService $bankService;

    private CashevoService $cashevoService;

    public function __construct(
        BankService $bankService,
        CashevoService $cashevoService
    ) {
        $this->bankService = $bankService;
        $this->cashevoService = $cashevoService;
    }

    /**
     * Cashevo açıkken: Cashevo POST /deposit-bank (tutara göre havale hesabı).
     * Kapalıyken: yerel aktif banka listesi (eski davranış).
     */
    public function transaction(Request $request): JsonResponse|BankCollection
    {
        if (setting('transaction_status') != '1') {
            return new BankCollection([]);
        }

        if ($this->cashevoService->enabled()) {
            $request->validate([
                'amount' => 'required|numeric|min:0.01',
            ]);

            $result = $this->cashevoService->createDepositBank((float) $request->query('amount'));

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Cashevo deposit-bank failed',
                    'code' => 502,
                    'total' => 0,
                    'data' => is_array($result['data'] ?? null) ? $result['data'] : [],
                ], 502);
            }

            return $result;

            $recipient = $this->cashevoService->extractRecipient($result['data'] ?? []);

            return response()->json([
                'success' => true,
                'message' => 'Deposit bank retrieved successfully',
                'code' => 200,
                'total' => 1,
                'data' => [
                    [
                        'receiver_iban' => $recipient['iban'],
                        'receiver_name' => $recipient['name'],
                        'cashevo' => $result['data'] ?? [],
                    ],
                ],
            ]);
        }

        $banks = $this->bankService->getActiveTransactionBanks('priority', 'asc');

        return new BankCollection($banks);
    }

    public function withdrawal(): BankCollection
    {
        $banks = $this->bankService->getActiveWithdrawalBanks('priority', 'asc');

        return new BankCollection($banks);
    }
}
