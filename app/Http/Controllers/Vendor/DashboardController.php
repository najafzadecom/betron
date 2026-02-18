<?php

namespace App\Http\Controllers\Vendor;

use App\Services\DashboardService;
use App\Services\VendorDepositTransactionService;
use App\Services\VendorService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseController
{
    private DashboardService $dashboardService;
    private VendorService $vendorService;
    private VendorDepositTransactionService $depositTransactionService;

    public function __construct(
        DashboardService                $dashboardService,
        VendorService                   $vendorService,
        VendorDepositTransactionService $depositTransactionService
    ) {
        $this->dashboardService = $dashboardService;
        $this->vendorService = $vendorService;
        $this->depositTransactionService = $depositTransactionService;
        $this->module = 'dashboard';
    }

    public function index(): Renderable
    {
        $vendorId = $this->getVendorId();
        $vendor = $this->vendorService->getById($vendorId);

        if (!$vendor) {
            abort(404, __('Vendor not found'));
        }

        $walletIds = $this->dashboardService->getVendorWalletIds($vendorId, $this->vendorService);

        // Get statistics
        $statistics = $this->dashboardService->getVendorStatistics($walletIds);

        // Recent transactions
        $recentTransactions = $this->dashboardService->getRecentTransactions($walletIds, 10);

        $this->data = [
            'title' => __('Dashboard'),
            'vendor' => $vendor,
            'depositAmount' => $vendor->deposit_amount ?? 0,
            'totalWallets' => $statistics['totalWallets'],
            'totalTransactions' => $statistics['totalTransactions'],
            'totalAmount' => $statistics['totalAmount'],
            'pendingTransactions' => $statistics['pendingTransactions'],
            'recentTransactions' => $recentTransactions,
        ];

        return $this->render('index');
    }

    /**
     * Show deposit transactions for current vendor
     */
    public function depositTransactions(): Renderable
    {
        $vendorId = $this->getVendorId();
        $vendor = $this->vendorService->getById($vendorId);

        if (!$vendor) {
            abort(404, __('Vendor not found'));
        }

        $filters = request()->only(['type', 'created_from', 'created_to', 'limit']);
        $items = $this->depositTransactionService->getByVendorId($vendorId, $filters);

        $this->data = [
            'module' => __('Dashboard'),
            'title' => __('Deposit Transactions'),
            'vendor' => $vendor,
            'items' => $items,
        ];

        return $this->render('deposit-transactions');
    }
}
