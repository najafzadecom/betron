<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\WalletResource;
use App\Services\WalletService;

class WalletController extends BaseController
{
    private WalletService $walletService;

    public function __construct(
        WalletService $walletService
    ) {
        $this->walletService = $walletService;
    }

    public function index(): WalletResource
    {
        $wallet = $this->walletService->getById(1);

        return new WalletResource($wallet);

    }
}
