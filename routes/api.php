<?php

use App\Http\Controllers\Api\V1\BankController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->as('v1.')
    ->middleware(['check_api_token', 'check_blacklist'])
    ->group(function () {

        Route::prefix('bank')->as('bank.')->group(function () {

            // Get Active Banks
            Route::get('/', [BankController::class, 'transaction'])->name('transaction');
            Route::get('/transaction', [BankController::class, 'transaction'])->name('transaction');
            Route::get('/withdrawal', [BankController::class, 'withdrawal'])->name('withdrawal');
        });

        Route::prefix('transaction')->as('transaction.')->group(function () {

            // Create Transaction
            Route::post('/', [TransactionController::class, 'store'])->name('store');
            Route::put('/{uuid}', [TransactionController::class, 'update'])->name('update');
            Route::get('/{uuid}/status', [TransactionController::class, 'status'])->name('status');
        });

        Route::prefix('withdrawal')->as('withdrawal.')->group(function () {

            // Create Withdrawal Request
            Route::post('/', [WithdrawalController::class, 'store'])->name('store');
            Route::get('/{uuid}/status', [WithdrawalController::class, 'status'])->name('status');
        });

        Route::prefix('wallet')->as('wallet.')->group(function () {

            // Get All Wallets
            Route::get('/', [WalletController::class, 'index'])->name('index');
        });
    });
