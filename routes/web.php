<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\IqWalletController;
use App\Http\Controllers\ParaQrController;
use App\Http\Controllers\PayByMeController;
use App\Http\Controllers\PaypapController;
use App\Http\Controllers\PratikController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('index');

// Route::domain('docs.betron.org')->group(function () {
    Route::get('/docs', function () {
        return view('docs.api');
    })->name('docs.api');
// });

Route::prefix('paraqr/')->as('paraqr.')->group(function () {
    Route::get('/', [ParaQrController::class, 'index'])->name('index');
    Route::get('payin', [ParaQrController::class, 'payIn'])->name('payin');
    Route::get('payout', [ParaQrController::class, 'payOut'])->name('payOut');
    Route::post('callback', [ParaQrController::class, 'receive'])->name('callback');
});


//IqWallet
Route::prefix('iqwallet/')->as('iqwallet.')->group(function () {
    Route::get('transaction', [IqWalletController::class, 'transaction'])->name('transaction');

    Route::get('withdrawal/send', [IqWalletController::class, 'sendWithdrawal'])->name('sendWithdrawal');
    Route::get('withdrawal/accounts', [IqWalletController::class, 'withdrawalAccounts'])->name('withdrawalAccounts');
    Route::get('withdrawal/{uniqueId}/receipt', [IqWalletController::class, 'getWithdrawalReceipt'])->name('getWithdrawalReceipt');
    Route::get('withdrawal/status/{referenceId?}', [IqWalletController::class, 'checkWithdrawalStatus'])->name('checkWithdrawalStatus');
});

//IqWallet
Route::prefix('paybyme/')->as('paybyme.')->group(function () {
    Route::get('transaction', [PayByMeController::class, 'transaction'])->name('transaction');
    Route::get('withdrawal/send', [PayByMeController::class, 'sendWithdrawal'])->name('sendWithdrawal');
    Route::get('withdrawal/accounts', [PayByMeController::class, 'withdrawalAccounts'])->name('withdrawalAccounts');
    Route::get('withdrawal/{uniqueId}/receipt', [PayByMeController::class, 'getWithdrawalReceipt'])->name('getWithdrawalReceipt');
    Route::get('withdrawal/status/{referenceId?}', [PayByMeController::class, 'checkWithdrawalStatus'])->name('checkWithdrawalStatus');
});

Route::prefix('pratik/')->as('pratik.')->group(function () {
    Route::get('/', [PratikController::class, 'index'])->name('index');
});

//PayPap
Route::prefix('paypap/')->as('paypap.')->group(function () {
    // Bank Deposits
    Route::post('bank-deposit/direct', [PaypapController::class, 'createBankDepositDirect'])->name('bankDeposit.direct');
    Route::post('bank-deposit/redirect', [PaypapController::class, 'createBankDepositRedirect'])->name('bankDeposit.redirect');
    Route::get('bank-deposit/{depositId}', [PaypapController::class, 'getBankDeposit'])->name('bankDeposit.get');
    
    // Bank Withdrawals
    Route::post('bank-withdrawal', [PaypapController::class, 'createBankWithdrawal'])->name('bankWithdrawal.create');
    Route::get('bank-withdrawal/{withdrawalId}', [PaypapController::class, 'getBankWithdrawal'])->name('bankWithdrawal.get');
    
    // Card Deposits
    Route::post('card-deposit/direct', [PaypapController::class, 'createCardDepositDirect'])->name('cardDeposit.direct');
    Route::post('card-deposit/redirect', [PaypapController::class, 'createCardDepositRedirect'])->name('cardDeposit.redirect');
    Route::get('card-deposit/{depositId}', [PaypapController::class, 'getCardDeposit'])->name('cardDeposit.get');
    
    // Webhook Callback
    Route::post('callback', [PaypapController::class, 'callback'])->name('callback');
});
