<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Currency;
use App\Enums\WalletStatus;
use App\Http\Requests\Store\WalletRequest as StoreRequest;
use App\Http\Requests\Update\WalletRequest as UpdateRequest;
use App\Services\BankService as BankService;
use App\Services\UserService as UserService;
use App\Services\VendorService;
use App\Services\VendorUserService;
use App\Services\WalletFileService;
use App\Services\WalletService as Service;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class WalletController extends BaseController
{
    private Service $service;
    private BankService $bankService;
    private UserService $userService;
    private VendorService $vendorService;
    private VendorUserService $vendorUserService;
    private WalletFileService $walletFileService;

    public function __construct(
        Service $service,
        BankService $bankService,
        UserService $userService,
        VendorService $vendorService,
        VendorUserService $vendorUserService,
        WalletFileService $walletFileService
    ) {
        $this->middleware('permission:wallets-index|wallets-create|wallets-edit', ['only' => ['index']]);
        $this->middleware('permission:wallets-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:wallets-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:wallets-delete', ['only' => ['destroy']]);

        $this->service = $service;
        $this->bankService = $bankService;
        $this->userService = $userService;
        $this->vendorService = $vendorService;
        $this->vendorUserService = $vendorUserService;
        $this->walletFileService = $walletFileService;
        $this->module = 'wallets';
    }

    public function index(): Renderable
    {
        $parentVendorId = request('parent_vendor_id', 0);
        $vendorId = request('vendor_id', 0);

        $topLevelVendors = $this->vendorService->getTopLevelVendors();
        $childVendors = collect([]);

        if ($parentVendorId) {
            $childVendors = $this->vendorService->getAccessibleVendorsForParent($parentVendorId);
        }

        $this->data = [
            'module' => __('Wallets'),
            'title' => __('List'),
            'items' => $this->service->paginate(),
            'banks' => $this->bankService->getAll(),
            'wallet_statuses' => WalletStatus::cases(),
            'currencies' => Currency::cases(),
            'topLevelVendors' => $topLevelVendors,
            'childVendors' => $childVendors,
            'parentVendorId' => $parentVendorId,
            'vendorId' => $vendorId,
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $vendorUsers = $this->vendorUserService->getActive();

        $this->data = [
            'title' => __('Create'),
            'module' => __('Wallets'),
            'method' => 'POST',
            'action' => route('admin.' . $this->module . '.store'),
            'banks' => $this->bankService->getAll(),
            'users' => $this->userService->getAll(),
            'vendors' => $this->vendorService->getAll(),
            'vendorUsers' => $vendorUsers,
            'currencies' => Currency::cases(),
            'wallet_statuses' => WalletStatus::cases(),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Remove files from validated data as it's not a wallet column
        unset($validated['files']);

        $item = $this->service->create($validated);

        // Handle file uploads if any
        if ($request->hasFile('files')) {
            $this->walletFileService->uploadMultipleFiles($item->id, $request->file('files'));
        }

        return $this->redirectSuccess('admin.wallets.index');
    }

    public function show(string $id): JsonResponse
    {
        $this->data = [
            'item' => $this->service->getById($id),
        ];

        return $this->json();
    }

    public function edit(string $id): Renderable
    {
        $item = $this->service->getById($id);
        $vendorUsers = $this->vendorUserService->getActive();

        $this->data = [
            'title' => __('Edit'),
            'module' => __('Wallets'),
            'item' => $item,
            'method' => 'PUT',
            'action' => route('admin.' . $this->module . '.update', $id),
            'banks' => $this->bankService->getAll(),
            'users' => $this->userService->getAll(),
            'vendors' => $this->vendorService->getAll(),
            'files' => $item->files ?? [],
            'vendorUsers' => $vendorUsers,
            'currencies' => Currency::cases(),
            'wallet_statuses' => WalletStatus::cases(),
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();

        // Remove files from validated data as it's not a wallet column
        unset($validated['files']);

        $item = $this->service->update($id, $validated);

        // Handle file uploads if any
        if ($request->hasFile('files')) {
            $this->walletFileService->uploadMultipleFiles($item->id, $request->file('files'));
        }

        return $this->redirectSuccess('admin.wallets.index');
    }

    public function destroy(string $id): JsonResponse
    {
        // Check if delete confirmation was received
        if (!request()->has('confirmed')) {
            $this->data = [
                'message' => __('Delete confirmation required'),
                'confirmed' => false,
            ];

            return $this->json(422);
        }

        $message = __('Unknown error');
        $code = 500;

        if ($this->service->delete($id)) {
            $message = __('Delete successfully');
            $code = 200;
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($code);
    }

    public function restore(string $id): JsonResponse
    {
        $message = __('Unknown error');
        $code = 500;

        if ($this->service->restore($id)) {
            $message = __('Restore successfully');
            $code = 200;
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($code);
    }

    public function delete(string $id): JsonResponse
    {
        $code = 500;
        $message = __('Unknown error');

        if ($this->service->forceDelete($id)) {
            $code = 200;
            $message = __('Force delete successfully');
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($code);
    }

    public function uploadFile(string $id): JsonResponse
    {
        $wallet = $this->service->getById($id);

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
        $wallet = $this->service->getById($id);

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
}
