<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\BankCollection;
use App\Services\BankService;

class BankController extends BaseController
{
    private BankService $bankService;

    public function __construct(
        BankService $bankService
    ) {
        $this->bankService = $bankService;

    }

    public function transaction(): BankCollection
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
