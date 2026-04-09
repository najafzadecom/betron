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
    public function transaction(Request $request)
    {
        if (setting('transaction_status') != '1') {
            return new BankCollection([]);
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
