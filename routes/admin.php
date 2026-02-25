<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BlacklistController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\VendorUserController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\SiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->as('auth.')
    ->controller(LoginController::class)
    ->group(function () {
        Route::get('login', 'showLoginForm')->name('login');
        Route::post('login', 'login');
        Route::get('logout', 'logout')->name('logout')->middleware('auth');
    });

Route::middleware('auth')
    ->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('withdrawals/send', [WithdrawalController::class, 'send'])->name('withdrawals.send');
        Route::post('withdrawals/send', [WithdrawalController::class, 'send'])->name('withdrawals.send');

        Route::get('users/profile', [UserController::class, 'profile'])->name('users.profile');
        Route::put('users/profile', [UserController::class, 'updateProfile'])->name('users.profile');

        // Dashboard DataTable Ajax
        Route::get('dashboard/transactions-ajax', [DashboardController::class, 'ajaxTransactions'])->name('dashboard.transactions.ajax');
        Route::get('dashboard/withdrawals-ajax', [DashboardController::class, 'ajaxWithdrawals'])->name('dashboard.withdrawals.ajax');

        //Excel Export
        Route::get('transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
        Route::get('withdrawals/export', [WithdrawalController::class, 'export'])->name('withdrawals.export');

        // Access Control
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);

        // Vendors
        Route::resource('vendors', VendorController::class);
        Route::post('vendors/{id}/add-deposit', [VendorController::class, 'addDeposit'])->name('vendors.add-deposit');
        Route::post('vendors/{id}/subtract-deposit', [VendorController::class, 'subtractDeposit'])->name('vendors.subtract-deposit');
        Route::get('vendors/{id}/deposit-transactions', [VendorController::class, 'depositTransactions'])->name('vendors.deposit-transactions');
        Route::get('vendors/deposit-transactions/all', [VendorController::class, 'allDepositTransactions'])->name('vendors.deposit-transactions.all');
        Route::post('vendors/bulk-update-status', [VendorController::class, 'bulkUpdateStatus'])->name('vendors.bulk-update-status');
        Route::post('vendors/{id}/login-as', [VendorController::class, 'loginAs'])->name('vendors.login-as');
        Route::resource('vendor-users', VendorUserController::class);
        Route::get('vendor-users/get-child-vendors/{parentId}', [VendorUserController::class, 'getChildVendors'])->name('vendor-users.get-child-vendors');

        // Activity Log

        Route::resource('activity-logs', ActivityLogController::class)->only(['index', 'show']);
        // Other modules
        Route::resource('transactions', TransactionController::class);
        Route::post('transactions/{id}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
        Route::post('transactions/{id}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::get('transactions/{id}/activity-logs', [TransactionController::class, 'activityLogs'])->name('transactions.activity-logs');
        Route::get('transactions/{id}/paypap-status', [TransactionController::class, 'paypapStatus'])->name('transactions.paypap-status');
        
        Route::resource('withdrawals', WithdrawalController::class);
        Route::post('withdrawals/{id}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('withdrawals/{id}/cancel', [WithdrawalController::class, 'cancel'])->name('withdrawals.cancel');
        Route::post('withdrawals/{id}/assign-vendor', [WithdrawalController::class, 'assignVendor'])->name('withdrawals.assign-vendor');
        Route::post('withdrawals/bulk-assign-vendor', [WithdrawalController::class, 'bulkAssignVendor'])->name('withdrawals.bulk-assign-vendor');
        Route::get('withdrawals/{id}/activity-logs', [WithdrawalController::class, 'activityLogs'])->name('withdrawals.activity-logs');
        Route::get('withdrawals/{id}/paypap-status', [WithdrawalController::class, 'paypapStatus'])->name('withdrawals.paypap-status');
        Route::resource('wallets', WalletController::class);

        // Wallet file routes
        Route::post('wallets/{wallet}/files', [WalletController::class, 'uploadFile'])->name('wallets.files.upload');
        Route::delete('wallets/{wallet}/files/{file}', [WalletController::class, 'deleteFile'])->name('wallets.files.delete');
        Route::resource('providers', ProviderController::class);
        Route::resource('banks', BankController::class);
        Route::post('banks/update-priorities', [BankController::class, 'updatePriorities'])->name('banks.update-priorities');
        Route::post('banks/bulk-update-status', [BankController::class, 'bulkUpdateStatus'])->name('banks.bulk-update-status');
        Route::resource('sites', SiteController::class);
        Route::post('sites/{site}/regenerate-token', [SiteController::class, 'regenerateToken'])->name('sites.regenerate-token');
        Route::resource('blacklists', BlacklistController::class);
        Route::resource('settings', SettingController::class)->only(['index']);

        Route::get('site/statistics', [SiteController::class, 'statistics'])->name('site.statistics');

        // Setting specific routes
        Route::post('settings/bulk-update', [SettingController::class, 'bulkUpdate'])->name('settings.bulk-update');

        // Blacklist specific routes
        Route::post('blacklists/{id}/toggle-status', [BlacklistController::class, 'toggleStatus'])->name('blacklists.toggle-status');

        Route::get('statistics/index', [StatisticsController::class, 'index'])->name('statistics.index');

    });
