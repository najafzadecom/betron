<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\Currency;
use App\Enums\WalletStatus;
use App\Http\Requests\Store\VendorWalletRequest as StoreRequest;
use App\Http\Requests\Update\VendorWalletRequest as UpdateRequest;
use App\Services\BankService;
use App\Services\VendorService;
use App\Services\VendorUserService;
use App\Services\WalletFileService;
use App\Services\WalletService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WalletController extends BaseController
{
    private WalletService $walletService;
    private BankService $bankService;
    private VendorService $vendorService;
    private VendorUserService $vendorUserService;
    private WalletFileService $walletFileService;

    public function __construct(
        WalletService     $walletService,
        BankService       $bankService,
        VendorService     $vendorService,
        VendorUserService $vendorUserService,
        WalletFileService $walletFileService
    ) {
        $this->middleware('vendor_permission:vendor-wallets-index|vendor-wallets-create|vendor-wallets-edit', ['only' => ['index']]);
        $this->middleware('vendor_permission:vendor-wallets-create', ['only' => ['create', 'store']]);
        $this->middleware('vendor_permission:vendor-wallets-edit', ['only' => ['edit', 'update']]);
        $this->middleware('vendor_permission:vendor-wallets-delete', ['only' => ['destroy']]);

        $this->walletService = $walletService;
        $this->bankService = $bankService;
        $this->vendorService = $vendorService;
        $this->vendorUserService = $vendorUserService;
        $this->walletFileService = $walletFileService;
        $this->module = 'wallets';
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
     * Check if wallet belongs to current vendor or its descendants
     */
    private function authorizeWallet($walletId): bool
    {
        $vendor = $this->vendor();
        $vendorIds = array_merge([$vendor->id], $this->vendorService->getDescendants($vendor->id));

        return $this->walletService->belongsToVendorIds($walletId, $vendorIds);
    }

    public function index(): Renderable
    {
        $vendor = $this->vendor();
        // Get wallets from current vendor and all its descendants
        $vendorIds = array_merge([$vendor->id], $this->vendorService->getDescendants($vendor->id));

        $perPage = (int)request('limit', 25);
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : 25;
        $items = $this->walletService->getByVendorIdsPaginated($vendorIds, $perPage);

        // Check if current vendor is a parent vendor (has no parent)
        $isParentVendor = is_null($vendor->parent_id);

        // Get accessible vendors for filter (self + descendants)
        $accessibleVendors = $this->vendorService->getAccessibleVendors($vendor->id);

        $this->data = [
            'module' => __('Wallets'),
            'title' => __('List'),
            'items' => $items,
            'banks' => $this->bankService->getAll(),
            'wallet_statuses' => WalletStatus::cases(),
            'isParentVendor' => $isParentVendor,
            'accessibleVendors' => $accessibleVendors,
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $vendor = $this->vendor();
        // Get users from current vendor and all its descendants
        $vendorUsers = $this->vendorUserService->getByVendorIdWithDescendants($vendor->id, $this->vendorService);

        $this->data = [
            'title' => __('Create'),
            'module' => __('Wallets'),
            'method' => 'POST',
            'action' => route('vendor.wallets.store'),
            'item' => null,
            'banks' => $this->bankService->getAll(),
            'vendorUsers' => $vendorUsers->where('status', 1),
            'currencies' => Currency::cases(),
            'wallet_statuses' => WalletStatus::cases(),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Set vendor_id to current vendor
        $validated['vendor_id'] = $this->vendor()->id;

        // Remove files from validated data
        unset($validated['files']);

        $item = $this->walletService->create($validated);

        // Handle file uploads if any
        if ($request->hasFile('files')) {
            $this->walletFileService->uploadMultipleFiles($item->id, $request->file('files'));
        }

        return $this->redirectSuccess('vendor.wallets.index');
    }

    public function show(string $id): JsonResponse
    {
        if (!$this->authorizeWallet($id)) {
            return response()->json(['error' => __('Unauthorized')], 403);
        }

        $this->data = [
            'item' => $this->walletService->getById($id),
        ];

        return $this->json();
    }

    public function edit(string $id): Renderable
    {
        if (!$this->authorizeWallet($id)) {
            abort(403, __('Unauthorized'));
        }

        $item = $this->walletService->getById($id);
        $vendor = $this->vendor();
        // Get users from current vendor and all its descendants
        $vendorUsers = $this->vendorUserService->getByVendorIdWithDescendants($vendor->id, $this->vendorService);

        $this->data = [
            'title' => __('Edit'),
            'module' => __('Wallets'),
            'item' => $item,
            'method' => 'PUT',
            'action' => route('vendor.wallets.update', $id),
            'banks' => $this->bankService->getAll(),
            'files' => $item->files ?? [],
            'vendorUsers' => $vendorUsers->where('status', 1),
            'currencies' => Currency::cases(),
            'wallet_statuses' => WalletStatus::cases(),
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        if (!$this->authorizeWallet($id)) {
            abort(403, __('Unauthorized'));
        }

        $validated = $request->validated();

        // Ensure vendor_id stays the same
        //$validated['vendor_id'] = $this->vendor()->id;

        // Remove files from validated data
        unset($validated['files']);

        $item = $this->walletService->update($id, $validated);

        // Handle file uploads if any
        if ($request->hasFile('files')) {
            $this->walletFileService->uploadMultipleFiles($item->id, $request->file('files'));
        }

        return $this->redirectSuccess('vendor.wallets.index');
    }

    public function uploadFile(string $id): JsonResponse
    {
        if (!$this->authorizeWallet($id)) {
            return response()->json(['error' => __('Unauthorized')], 403);
        }

        $wallet = $this->walletService->getById($id);

        if (!$wallet) {
            return response()->json(['error' => __('Wallet not found')], 404);
        }

        $request = request();

        if (!$request->hasFile('file')) {
            return response()->json(['error' => __('No file uploaded')], 422);
        }

        $walletFile = $this->walletFileService->uploadFile($wallet->id, $request->file('file'));

        return response()->json([
            'message' => __('File uploaded successfully'),
            'file' => [
                'id' => $walletFile->id,
                'name' => $walletFile->original_name,
                'url' => Storage::url($walletFile->file_path),
                'size' => $walletFile->file_size,
            ],
        ], 200);
    }

    public function deleteFile(string $id, string $fileId): JsonResponse
    {
        if (!$this->authorizeWallet($id)) {
            return response()->json(['error' => __('Unauthorized')], 403);
        }

        $wallet = $this->walletService->getById($id);

        if (!$wallet) {
            return response()->json(['error' => __('Wallet not found')], 404);
        }

        $walletFile = $this->walletFileService->findByWalletAndFileId($wallet->id, $fileId);

        if (!$walletFile) {
            return response()->json(['error' => __('File not found')], 404);
        }

        if ($this->walletFileService->deleteFile($fileId)) {
            return response()->json(['success' => true, 'message' => __('File deleted successfully')], 200);
        }

        return response()->json(['error' => __('Failed to delete file')], 500);
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $action = $request->get('action');

        if (!in_array($action, ['active', 'inactive'])) {
            return redirect()->back(); // Or abort
        }

        $status = $action === 'active' ? WalletStatus::Active->value : WalletStatus::Inactive->value;

        $vendor = $this->vendor();
        $vendorIds = array_merge([$vendor->id], $this->vendorService->getDescendants($vendor->id));

        $this->walletService->bulkUpdateStatus($vendorIds, $status);

        return $this->redirectSuccess('vendor.wallets.index');
    }
}
