<?php

namespace App\Http\Controllers\Admin;

use App\Services\SiteService;
use App\Services\StatisticsService;
use App\Services\VendorService;
use App\Services\WalletService;
use App\Support\Merchant;
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
        $isMerchant = Merchant::isMerchant();
        $merchantSiteId = Merchant::siteIdFor();
        $createdFrom = request('created_from', Merchant::defaultCreatedFrom());
        $createdTo = request('created_to', Merchant::defaultCreatedTo());

        if ($isMerchant) {
            $createdFrom = Merchant::ALL_TIME_FROM;
            $createdTo = Merchant::defaultCreatedTo();
            $siteID = $merchantSiteId;
            $parentVendorId = 0;
            $vendorId = 0;
            $filterBy = 'vendor';
            $vendorIds = [];
            $walletIds = [];
        } else {
            $siteID = (int) request('site_id', 0);
            $parentVendorId = (int) request('parent_vendor_id', 0);
            $vendorId = (int) request('vendor_id', 0);
            $filterBy = request('filter_by', 'vendor');

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

            $vendorIds = [];
            if ($parentVendorId) {
                $vendorIds = array_merge([$parentVendorId], $this->vendorService->getDescendants($parentVendorId));
            } elseif ($vendorId) {
                $vendorIds = array_merge([$vendorId], $this->vendorService->getDescendants($vendorId));
            }

            $walletIds = [];
            if (!empty($vendorIds)) {
                $walletIds = $this->walletService->getWalletIdsByVendorIds($vendorIds);
            }
        }

        $topLevelVendors = $isMerchant ? collect([]) : $this->vendorService->getTopLevelVendors();
        $childVendors = collect([]);

        if (!$isMerchant && $parentVendorId) {
            $childVendors = $this->vendorService->getAccessibleVendorsForParent($parentVendorId);
        }

        // Set filter type: if filter_by is 'wallet', use walletIds; otherwise use vendorIds
        if ($filterBy === 'wallet') {
            $this->statisticsService->vendorIds = [];
            $this->statisticsService->walletIds = $walletIds;
        } else {
            $this->statisticsService->vendorIds = $vendorIds;
            $this->statisticsService->walletIds = [];
        }

        $this->statisticsService->createdFrom = $createdFrom;
        $this->statisticsService->createdTo = $createdTo;
        $this->statisticsService->siteId = $siteID;

        $merchantSite = $isMerchant ? $this->siteService->getById($merchantSiteId) : null;

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

        $showAcceptedAverage = !$isMerchant && config('statistics.show_admin_accepted_average');
        $acceptedTransactionsAverage = $showAcceptedAverage && $acceptedTransactions > 0
            ? round($acceptedTransactionsAmount / $acceptedTransactions, 2)
            : null;

        $this->data = [
            'module' => __('Statistics'),
            'title' => __('List'),
            'sites' => $isMerchant ? collect([]) : $this->siteService->getAll(),
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
            'createdTo' => $createdTo,
            'showAcceptedAverage' => $showAcceptedAverage,
            'acceptedTransactionsAverage' => $acceptedTransactionsAverage,
            'isMerchant' => $isMerchant,
            'merchantSiteId' => $merchantSiteId,
            'merchantSite' => $merchantSite,
        ];

        return $this->render('list');
    }
}
