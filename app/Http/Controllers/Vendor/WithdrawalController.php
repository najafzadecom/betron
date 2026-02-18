<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\Currency;
use App\Enums\PaidStatus;
use App\Enums\WithdrawalStatus;
use App\Exports\WithdrawalExport;
use App\Services\SiteService;
use App\Services\VendorService;
use App\Services\WithdrawalService as Service;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WithdrawalController extends BaseController
{
    private Service $service;
    private SiteService $siteService;
    private VendorService $vendorService;

    public function __construct(
        Service       $service,
        SiteService   $siteService,
        VendorService $vendorService
    ) {
        $this->middleware('vendor_permission:vendor-withdrawals-index', ['only' => ['index']]);

        $this->service = $service;
        $this->siteService = $siteService;
        $this->vendorService = $vendorService;
        $this->module = 'withdrawals';
    }

    /**
     * Get current vendor
     */
    private function vendor()
    {
        $vendorId = $this->getVendorId();
        $vendor = $this->vendorService->getById($vendorId);

        if (!$vendor) {
            abort(404, __('Vendor not found'));
        }

        return $vendor;
    }

    /**
     * Check if withdrawal belongs to current vendor or its child vendors
     */
    private function authorizeWithdrawal($id): bool
    {
        $vendor = $this->vendor();
        // Include current vendor and all child vendors
        $vendorIds = array_merge([$vendor->id], $this->vendorService->getDescendants($vendor->id));

        return $this->service->belongsToVendorIds($id, $vendorIds);
    }

    public function index(): Renderable
    {
        $vendor = $this->vendor();
        
        // Get child vendors for filter
        $childVendors = $this->vendorService->getAccessibleVendorsForParent($vendor->id);
        
        // Check if specific child vendor is selected
        $childVendorId = request('child_vendor_id');
        
        // Determine view type: 'own' (default) or 'child_vendors'
        // If child_vendor_id is selected, automatically switch to child_vendors view
        $viewType = request('view_type', 'own');
        if ($childVendorId && $childVendors->contains('id', $childVendorId)) {
            $viewType = 'child_vendors';
        }
        
        // Get vendor IDs based on view type and child vendor filter
        if ($childVendorId && $childVendors->contains('id', $childVendorId)) {
            // Specific child vendor selected - show only that vendor's withdrawals
            $vendorIds = [(int)$childVendorId];
        } elseif ($viewType === 'child_vendors' && $childVendors->isNotEmpty()) {
            // Get child vendor IDs (exclude current vendor's own withdrawals)
            // getAccessibleVendorsForParent already excludes parent, but ensure it's excluded
            $childVendorIds = $childVendors->pluck('id')->toArray();
            $vendorIds = array_values(array_diff($childVendorIds, [$vendor->id]));
        } else {
            // Get withdrawals only for current vendor (not descendants)
            $vendorIds = [$vendor->id];
        }

        $perPage = (int)request('limit', 25);
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : 25;
        $items = $this->service->getByVendorIdsPaginated($vendorIds, $perPage);

        $this->data = [
            'module' => __('Withdrawals'),
            'title' => __('List'),
            'items' => $items,
            'sites' => $this->siteService->getAll(),
            'childVendors' => $childVendors,
            'viewType' => $viewType,
            'currencies' => Currency::cases(),
            'paid_statuses' => PaidStatus::cases(),
        ];

        return $this->render('list');
    }

    public function show(string $id): JsonResponse
    {
        if (!$this->authorizeWithdrawal($id)) {
            return response()->json(['error' => __('Unauthorized')], 403);
        }

        $this->data = [
            'item' => $this->service->getById($id),
        ];

        return $this->json();
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(
            new WithdrawalExport($this->service, $this->vendor()->id),
            'withdrawal-report-' . date('Y-m-d_H:i:s') . '.xlsx'
        );
    }

    public function approve(string $id): JsonResponse
    {
        if (!$this->authorizeWithdrawal($id)) {
            return response()->json(['error' => __('Unauthorized')], 403);
        }

        $code = 500;
        $message = __('Unknown error');

        try {
            $withdrawal = $this->service->getById($id);

            if (!$withdrawal) {
                return response()->json(['message' => __('Withdrawal not found')], 404);
            }

            if ($withdrawal->status->value !== 1) {
                return response()->json(['message' => __('Only pending withdrawals can be approved')], 422);
            }

            $this->service->update($id, [
                'status' => WithdrawalStatus::ManualConfirmed->value,
                'paid_status' => true
            ]);

            $code = 200;
            $message = __('Withdrawal approved successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return response()->json(['message' => $message], $code);
    }

    public function cancel(string $id): JsonResponse
    {
        if (!$this->authorizeWithdrawal($id)) {
            return response()->json(['error' => __('Unauthorized')], 403);
        }

        $code = 500;
        $message = __('Unknown error');

        try {
            $withdrawal = $this->service->getById($id);

            if (!$withdrawal) {
                return response()->json(['message' => __('Withdrawal not found')], 404);
            }

            if ($withdrawal->status->value !== 1) {
                return response()->json(['message' => __('Only pending withdrawals can be cancelled')], 422);
            }

            $this->service->update($id, [
                'status' => WithdrawalStatus::ManualCancelled->value
            ]);

            $code = 200;
            $message = __('Withdrawal cancelled successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return response()->json(['message' => $message], $code);
    }

    public function assignVendor(string $id): JsonResponse
    {
        $code = 500;
        $message = __('Unknown error');

        try {
            $vendor = $this->vendor();
            
            // Check if withdrawal belongs to current vendor (not descendants)
            // Only withdrawals belonging to current vendor can be assigned to sub-vendors
            if (!$this->authorizeWithdrawal($id)) {
                return response()->json(['message' => __('Unauthorized')], 403);
            }

            $withdrawal = $this->service->getById($id);

            if (!$withdrawal) {
                return response()->json(['message' => __('Withdrawal not found')], 404);
            }

            $vendorId = request('vendor_id');

            if (!$vendorId) {
                return response()->json(['message' => __('Vendor is required')], 422);
            }

            // Validate vendor exists and is a child vendor
            $targetVendor = $this->vendorService->getById($vendorId);
            if (!$targetVendor) {
                return response()->json(['message' => __('Vendor not found')], 422);
            }

            // Check if the selected vendor is a child vendor (descendant) of current vendor
            $childVendorIds = $this->vendorService->getDescendants($vendor->id);
            if (!in_array($vendorId, $childVendorIds)) {
                return response()->json(['message' => __('You can only assign to your sub-vendors')], 422);
            }

            $updateData = [
                'vendor_id' => $vendorId
            ];

            // If set_processing is checked, update status to Processing
            if (request('set_processing')) {
                $updateData['status'] = WithdrawalStatus::Processing->value;
            }

            $this->service->update($id, $updateData);

            $code = 200;
            $message = __('Vendor assigned successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return response()->json(['message' => $message], $code);
    }

    public function bulkAssignVendor(): JsonResponse
    {
        $code = 500;
        $message = __('Unknown error');

        try {
            $vendor = $this->vendor();
            $withdrawalIds = request('withdrawal_ids', []);
            $vendorId = request('vendor_id');

            if (empty($withdrawalIds) || !is_array($withdrawalIds)) {
                return response()->json(['message' => __('Please select at least one withdrawal')], 422);
            }

            if (!$vendorId) {
                return response()->json(['message' => __('Vendor is required')], 422);
            }

            // Validate vendor exists and is a child vendor
            $targetVendor = $this->vendorService->getById($vendorId);
            if (!$targetVendor) {
                return response()->json(['message' => __('Vendor not found')], 422);
            }

            // Check if the selected vendor is a child vendor (descendant) of current vendor
            $childVendorIds = $this->vendorService->getDescendants($vendor->id);
            if (!in_array($vendorId, $childVendorIds)) {
                return response()->json(['message' => __('You can only assign to your sub-vendors')], 422);
            }

            // Get withdrawals and validate they belong to current vendor or child vendors
            $withdrawals = $this->service->getByIds($withdrawalIds);

            if ($withdrawals->isEmpty()) {
                return response()->json(['message' => __('No valid withdrawals found')], 422);
            }

            // Get accessible vendor IDs (current vendor + child vendors)
            $accessibleVendorIds = array_merge([$vendor->id], $childVendorIds);

            // Filter withdrawals that belong to current vendor or child vendors
            $validWithdrawals = $withdrawals->filter(function ($withdrawal) use ($accessibleVendorIds) {
                return in_array($withdrawal->vendor_id, $accessibleVendorIds);
            });

            if ($validWithdrawals->isEmpty()) {
                return response()->json(['message' => __('No valid withdrawals to assign. You can only assign withdrawals that belong to you or your sub-vendors.')], 422);
            }

            $updateData = [
                'vendor_id' => $vendorId
            ];

            // If set_processing is checked, update status to Processing
            if (request('set_processing')) {
                $updateData['status'] = WithdrawalStatus::Processing->value;
            }

            $assignedCount = 0;
            foreach ($validWithdrawals as $withdrawal) {
                $this->service->update($withdrawal->id, $updateData);
                $assignedCount++;
            }

            $code = 200;
            $message = __('Bulk vendor assignment completed') . ' (' . $assignedCount . ' ' . __('withdrawals') . ')';
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return response()->json(['message' => $message], $code);
    }
}
