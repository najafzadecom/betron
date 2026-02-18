<?php

namespace App\Http\Controllers\Admin;

use App\Services\SiteService;
use App\Services\StatisticsService;
use App\Services\VendorService;
use App\Services\WalletService;
use Illuminate\Contracts\Support\Renderable;

class StatisticsController extends BaseController
{
    private SiteService $siteService;
    private StatisticsService $statisticsService;
    private VendorService $vendorService;
    private WalletService $walletService;

    public function __construct(
        SiteService       $siteService,
        StatisticsService $statisticsService,
        VendorService     $vendorService,
        WalletService     $walletService
    ) {
        $this->middleware('permission:statistics-index', ['only' => ['index']]);

        $this->siteService = $siteService;
        $this->statisticsService = $statisticsService;
        $this->vendorService = $vendorService;
        $this->walletService = $walletService;
        $this->module = 'statistics';
    }

    public function index(): Renderable
    {
        $createdFrom = request('created_from', date('Y-m-d'));
        $createdTo = request('created_to', date('Y-m-d'));
        $siteID = request('site_id', 0);
        $parentVendorId = request('parent_vendor_id', 0);
        $vendorId = request('vendor_id', 0);
        $filterBy = request('filter_by', 'vendor'); // 'vendor' or 'wallet'

        // If vendor_id is set but parent_vendor_id is not, find the parent vendor
        if ($vendorId && !$parentVendorId) {
            $vendor = $this->vendorService->getById($vendorId);
            if ($vendor && $vendor->parent_id) {
                // Find the top-level parent (parent with no parent)
                $currentVendor = $vendor;
                while ($currentVendor && $currentVendor->parent_id) {
                    $currentVendor = $this->vendorService->getById($currentVendor->parent_id);
                }
                if ($currentVendor) {
                    $parentVendorId = $currentVendor->id;
                }
            } else {
                // If vendor has no parent, it is the parent vendor itself
                $parentVendorId = $vendorId;
            }
        }

        $topLevelVendors = $this->vendorService->getTopLevelVendors();
        $childVendors = collect([]);

        if ($parentVendorId) {
            $childVendors = $this->vendorService->getAccessibleVendorsForParent($parentVendorId);
        }

        // Get vendor IDs for filtering (parent vendor and all child vendors)
        $vendorIds = [];
        if ($parentVendorId) {
            $vendorIds = array_merge([$parentVendorId], $this->vendorService->getDescendants($parentVendorId));
        } elseif ($vendorId) {
            // If only vendor_id is set and no parent, use that vendor and its descendants
            $vendorIds = array_merge([$vendorId], $this->vendorService->getDescendants($vendorId));
        }


        // Get wallet IDs for selected vendors (parent vendor and all child vendors)
        $walletIds = [];
        if (!empty($vendorIds)) {
            $walletIds = $this->walletService->getWalletIdsByVendorIds($vendorIds);
        }

        // Set filter type: if filter_by is 'wallet', use walletIds; otherwise use vendorIds
        if ($filterBy === 'wallet') {
            // Filter by wallet: use walletIds, clear vendorIds
            $this->statisticsService->vendorIds = [];
            $this->statisticsService->walletIds = $walletIds;
        } else {
            // Filter by vendor: use vendorIds, clear walletIds
            $this->statisticsService->vendorIds = $vendorIds;
            $this->statisticsService->walletIds = [];
        }

        $this->statisticsService->createdFrom = $createdFrom;
        $this->statisticsService->createdTo = $createdTo;
        $this->statisticsService->siteId = $siteID;

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

        $totalWithdrawalsAmount = $this->statisticsService->getTotalWithdrawals();
        $acceptedWithdrawalsAmount = $this->statisticsService->getAcceptedWithdrawalsAmount();
        $rejectedWithdrawalsAmount = $this->statisticsService->getRejectedWithdrawalsAmount();
        $pendingWithdrawalsAmount = $this->statisticsService->getPendingWithdrawalsAmount();

        $this->data = [
            'module' => __('Statistics'),
            'title' => __('List'),
            'sites' => $this->siteService->getAll(),
            'topLevelVendors' => $topLevelVendors,
            'childVendors' => $childVendors,
            'parentVendorId' => $parentVendorId,
            'vendorId' => $vendorId,
            'filterBy' => $filterBy,
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
            'createdTo' => $createdTo
        ];

        return $this->render('list');
    }
}
