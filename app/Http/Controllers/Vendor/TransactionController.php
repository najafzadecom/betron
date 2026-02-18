<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\PaidStatus;
use App\Enums\TransactionStatus;
use App\Exports\VendorTransactionExport;
use App\Services\TransactionService;
use App\Services\VendorService;
use App\Services\WalletService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionController extends BaseController
{
    private TransactionService $transactionService;
    private VendorService $vendorService;
    private WalletService $walletService;

    public function __construct(
        TransactionService $transactionService,
        VendorService      $vendorService,
        WalletService      $walletService
    ) {
        $this->middleware('vendor_permission:vendor-transactions-index', ['only' => ['index']]);

        $this->transactionService = $transactionService;
        $this->vendorService = $vendorService;
        $this->walletService = $walletService;
        $this->module = 'transactions';
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
     * Get wallet IDs belonging to current vendor and all its descendants
     */
    private function getWalletIds(): array
    {
        $vendor = $this->vendor();
        $vendorIds = array_merge([$vendor->id], $this->vendorService->getDescendants($vendor->id));

        return $this->walletService->getWalletIdsByVendorIds($vendorIds);
    }

    /**
     * Check if transaction belongs to vendor's wallet
     */
    private function authorizeTransaction($transactionId): bool
    {
        $walletIds = $this->getWalletIds();
        $transaction = $this->transactionService->getById($transactionId);

        return $transaction && in_array($transaction->wallet_id, $walletIds);
    }

    public function index(): Renderable
    {
        $vendor = $this->vendor();
        $vendorIds = array_merge([$vendor->id], $this->vendorService->getDescendants($vendor->id));
        $walletIds = $this->getWalletIds();
        $wallets = $this->walletService->getByVendorIds($vendorIds);
        $request = request();

        // Check if current vendor is a parent vendor (has no parent)
        $isParentVendor = is_null($vendor->parent_id);

        // Get child vendors for filter
        $childVendors = $this->vendorService->getAccessibleVendorsForParent($vendor->id);

        // Get accessible vendors for filter (self + descendants)
        $accessibleVendors = $this->vendorService->getAccessibleVendors($vendor->id);

        // Validate wallet_id if provided
        if ($request->filled('wallet_id') && !in_array($request->get('wallet_id'), $walletIds)) {
            $request->merge(['wallet_id' => null]);
        }

        $query = $this->transactionService->getByWalletIds($walletIds)
            ->with(['wallet.vendor', 'site', 'bank']);

        // Sorting
        $sortBy = $request->get('sort', 'status');
        $sortDirection = $request->get('direction', 'ASC');
        $query->orderBy($sortBy, $sortDirection);
        $query->orderBy('created_at', 'DESC');

        $perPage = (int)$request->get('limit', 25);
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : 25;

        $this->data = [
            'module' => __('Transactions'),
            'title' => __('List'),
            'items' => $query->paginate($perPage),
            'wallets' => $wallets,
            'accessibleVendors' => $accessibleVendors,
            'isParentVendor' => $isParentVendor,
            'paid_statuses' => PaidStatus::cases(),
        ];

        return $this->render('list');
    }

    public function show(string $id): JsonResponse
    {
        if (!$this->authorizeTransaction($id)) {
            return response()->json(['error' => __('Unauthorized')], 403);
        }

        $this->data = [
            'item' => $this->transactionService->getById($id),
        ];

        return $this->json();
    }

    public function export(): BinaryFileResponse
    {
        $walletIds = $this->getWalletIds();

        return Excel::download(
            new VendorTransactionExport($walletIds),
            'my-transactions-' . date('Y-m-d_H:i:s') . '.xlsx'
        );
    }

    public function approve(string $id): JsonResponse
    {
        if (!$this->authorizeTransaction($id)) {
            return response()->json(['error' => __('Unauthorized')], 403);
        }

        $code = 500;
        $message = __('Unknown error');

        try {
            $transaction = $this->transactionService->getById($id);

            if (!$transaction) {
                return response()->json(['message' => __('Transaction not found')], 404);
            }

            if ($transaction->status->value !== 1) {
                return response()->json(['message' => __('Only pending transactions can be approved')], 422);
            }

            $updateData = [
                'status' => TransactionStatus::ManualConfirmed->value,
                'paid_status' => true
            ];

            $this->transactionService->update($id, $updateData);

            $code = 200;
            $message = __('Transaction approved successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return response()->json(['message' => $message], $code);
    }

    public function cancel(string $id): JsonResponse
    {
        if (!$this->authorizeTransaction($id)) {
            return response()->json(['error' => __('Unauthorized')], 403);
        }

        $code = 500;
        $message = __('Unknown error');

        try {
            $transaction = $this->transactionService->getById($id);

            if (!$transaction) {
                return response()->json(['message' => __('Transaction not found')], 404);
            }

            if ($transaction->status->value !== 1) {
                return response()->json(['message' => __('Only pending transactions can be cancelled')], 422);
            }

            $this->transactionService->update($id, [
                'status' => TransactionStatus::ManualCancelled->value
            ]);

            $code = 200;
            $message = __('Transaction cancelled successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return response()->json(['message' => $message], $code);
    }
}
