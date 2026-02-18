<?php

use App\Http\Controllers\Vendor\Auth\LoginController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\RoleController;
use App\Http\Controllers\Vendor\StatisticsController;
use App\Http\Controllers\Vendor\TransactionController;
use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Vendor\VendorUserController;
use App\Http\Controllers\Vendor\WalletController;
use App\Http\Controllers\Vendor\WithdrawalController;
use App\Http\Controllers\Vendor\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->as('auth.')
    ->controller(LoginController::class)
    ->group(function () {
        Route::get('login', 'showLoginForm')->name('login');
        Route::post('login', 'login');
        Route::get('logout', 'logout')->name('logout')->middleware('auth:vendor');
    });

Route::middleware('auth:vendor')
    ->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('deposit-transactions', [DashboardController::class, 'depositTransactions'])->name('deposit-transactions');

        // Profile
        Route::get('profile/password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.password');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

        // Vendor Management (Sub-vendors)
        Route::resource('vendors', VendorController::class);
        Route::post('vendors/{id}/add-deposit', [VendorController::class, 'addDeposit'])->name('vendors.add-deposit');
        Route::post('vendors/{id}/subtract-deposit', [VendorController::class, 'subtractDeposit'])->name('vendors.subtract-deposit');
        Route::get('vendors/{id}/deposit-transactions', [VendorController::class, 'depositTransactions'])->name('vendors.deposit-transactions');
        Route::get('vendors/deposit-transactions/all', [VendorController::class, 'allDepositTransactions'])->name('vendors.deposit-transactions.all');
        Route::post('vendors/bulk-update-status', [VendorController::class, 'bulkUpdateStatus'])->name('vendors.bulk-update-status');
        Route::post('vendors/{id}/login-as', [VendorController::class, 'loginAs'])->name('vendors.login-as');

        // Vendor Users Management
        Route::resource('users', VendorUserController::class);

        // Roles Management
        Route::resource('roles', RoleController::class);

        // Wallets
        Route::post('wallets/bulk-update-status', [WalletController::class, 'bulkUpdateStatus'])->name('wallets.bulk-update-status');
        Route::resource('wallets', WalletController::class);
        Route::post('wallets/{wallet}/files', [WalletController::class, 'uploadFile'])->name('wallets.files.upload');
        Route::delete('wallets/{wallet}/files/{file}', [WalletController::class, 'deleteFile'])->name('wallets.files.delete');

        // Transactions (read-only)
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
        Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::post('transactions/{id}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
        Route::post('transactions/{id}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

        // Withdrawals (read-only)
        Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::get('withdrawals/export', [WithdrawalController::class, 'export'])->name('withdrawals.export');
        Route::get('withdrawals/{transaction}', [WithdrawalController::class, 'show'])->name('withdrawals.show');
        Route::post('withdrawals/{id}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('withdrawals/{id}/cancel', [WithdrawalController::class, 'cancel'])->name('withdrawals.cancel');
        Route::post('withdrawals/{id}/assign-vendor', [WithdrawalController::class, 'assignVendor'])->name('withdrawals.assign-vendor');
        Route::post('withdrawals/bulk-assign-vendor', [WithdrawalController::class, 'bulkAssignVendor'])->name('withdrawals.bulk-assign-vendor');

        // Statistics
        Route::get('statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    });
