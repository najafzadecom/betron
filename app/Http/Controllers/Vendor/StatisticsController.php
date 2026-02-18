<?php

namespace App\Http\Controllers\Vendor;

use App\Services\StatisticsService;
use App\Services\VendorService;
use Illuminate\Contracts\Support\Renderable;

class StatisticsController extends BaseController
{
    private StatisticsService $statisticsService;
    private VendorService $vendorService;

    public function __construct(
        StatisticsService $statisticsService,
        VendorService     $vendorService,
    ) {
        $this->statisticsService = $statisticsService;
        $this->vendorService = $vendorService;
        $this->module = 'statistics';
    }

    public function index(): Renderable
    {
        $vendorId = $this->getVendorId();
        $vendor = $this->vendorService->getById($vendorId);

        if (!$vendor) {
            abort(404, __('Vendor not found'));
        }

        // Get all vendor IDs (self + descendants)
        $vendorIds = array_merge([$vendorId], $this->vendorService->getDescendants($vendorId));


        // Get child vendors for filter
        $childVendors = $this->vendorService->getAccessibleVendorsForParent($vendorId);

        $createdFrom = request('created_from', date('Y-m-d'));
        $createdTo = request('created_to', date('Y-m-d'));
        $childVendorId = request('child_vendor_id', 0);

        $filteredVendorIds = $vendorIds;
        if ($childVendorId && in_array($childVendorId, $vendorIds)) {
            $filteredVendorIds = [$childVendorId];
        }

        $this->statisticsService->createdFrom = $createdFrom;
        $this->statisticsService->createdTo = $createdTo;
        $this->statisticsService->vendorIds = $filteredVendorIds;

        $totalTransactions = $this->statisticsService->getTotalTransactions();
        $acceptedTransactions = $this->statisticsService->getAcceptedTransactions();
        $rejectedTransactions = $this->statisticsService->getRejectedTransactions();
        $pendingTransactions = $this->statisticsService->getPendingTransactions();

        $totalWithdrawals = $this->statisticsService->getTotalWithdrawals();
        $acceptedWithdrawals = $this->statisticsService->getAcceptedWithdrawals();
        $rejectedWithdrawals = $this->statisticsService->getRejectedWithdrawals();
        $pendingWithdrawals = $this->statisticsService->getPendingWithdrawals();

        $totalTransactionsAmount = $this->statisticsService->getTotalTransactionsAmount();
        $acceptedTransactionsAmount = $this->statisticsService->getAcceptedTransactionsAmount();
        $rejectedTransactionsAmount = $this->statisticsService->getRejectedTransactionsAmount();
        $pendingTransactionsAmount = $this->statisticsService->getPendingTransactionsAmount();

        $totalWithdrawalsAmount = $this->statisticsService->getTotalWithdrawalsAmount();
        $acceptedWithdrawalsAmount = $this->statisticsService->getAcceptedWithdrawalsAmount();
        $rejectedWithdrawalsAmount = $this->statisticsService->getRejectedWithdrawalsAmount();
        $pendingWithdrawalsAmount = $this->statisticsService->getPendingWithdrawalsAmount();

        $this->data = [
            'module' => __('Statistics'),
            'title' => __('List'),
            'childVendors' => $childVendors,
            'totalTransactions' => $totalTransactions,
            'acceptedTransactions' => $acceptedTransactions,
            'rejectedTransactions' => $rejectedTransactions,
            'pendingTransactions' => $pendingTransactions,
            'totalWithdrawals' => $totalWithdrawals,
            'acceptedWithdrawals' => $acceptedWithdrawals,
            'rejectedWithdrawals' => $rejectedWithdrawals,
            'pendingWithdrawals' => $pendingWithdrawals,
            'totalTransactionsAmount' => $totalTransactionsAmount,
            'acceptedTransactionsAmount' => $acceptedTransactionsAmount,
            'rejectedTransactionsAmount' => $rejectedTransactionsAmount,
            'pendingTransactionsAmount' => $pendingTransactionsAmount,
            'totalWithdrawalsAmount' => $totalWithdrawalsAmount,
            'acceptedWithdrawalsAmount' => $acceptedWithdrawalsAmount,
            'rejectedWithdrawalsAmount' => $rejectedWithdrawalsAmount,
            'pendingWithdrawalsAmount' => $pendingWithdrawalsAmount,
            'createdFrom' => $createdFrom,
            'createdTo' => $createdTo,
            'childVendorId' => $childVendorId,
        ];

        return $this->render('list');
    }
}
