<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Requests\Store\VendorUserRequest as StoreRequest;
use App\Http\Requests\Update\VendorUserRequest as UpdateRequest;
use App\Services\RoleService;
use App\Services\VendorService;
use App\Services\VendorUserService as Service;
use App\Services\WalletService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class VendorUserController extends BaseController
{
    private Service $service;
    private RoleService $roleService;
    private VendorService $vendorService;
    private WalletService $walletService;

    public function __construct(
        Service       $service,
        RoleService   $roleService,
        VendorService $vendorService,
        WalletService $walletService
    ) {
        $this->middleware('vendor_permission:vendor-users-index|vendor-users-create|vendor-users-edit', ['only' => ['index']]);
        $this->middleware('vendor_permission:vendor-users-create', ['only' => ['create', 'store']]);
        $this->middleware('vendor_permission:vendor-users-edit', ['only' => ['edit', 'update']]);
        $this->middleware('vendor_permission:vendor-users-delete', ['only' => ['destroy']]);

        $this->service = $service;
        $this->roleService = $roleService;
        $this->vendorService = $vendorService;
        $this->walletService = $walletService;
        $this->module = 'users';
    }

    public function index(): Renderable
    {
        $vendorId = $this->getVendorId();
        $vendor = $this->vendorService->getById($vendorId);
        
        if (!$vendor) {
            abort(404, __('Vendor not found'));
        }

        // Get users from current vendor and all its descendants
        $items = $this->service->getByVendorIdWithDescendants($vendor->id, $this->vendorService);

        $this->data = [
            'module' => __('Users'),
            'title' => __('List'),
            'items' => $items,
            'roles' => $this->roleService->getByGuardNameVendorIdWithDescendants('vendor', $vendor->id, $this->vendorService),
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $vendorId = $this->getVendorId();
        $vendor = $this->vendorService->getById($vendorId);
        
        if (!$vendor) {
            abort(404, __('Vendor not found'));
        }

        // Get wallets from current vendor and all its descendants
        $vendorIds = array_merge([$vendor->id], $this->vendorService->getDescendants($vendor->id));
        $wallets = $this->walletService->getByVendorIds($vendorIds);

        $this->data = [
            'title' => __('Create'),
            'module' => __('Users'),
            'method' => 'POST',
            'action' => route('vendor.' . $this->module . '.store'),
            'roles' => $this->roleService->getByGuardNameVendorIdWithDescendants('vendor', $vendor->id, $this->vendorService),
            'wallets' => $wallets,
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $vendorId = $this->getVendorId();
        $vendor = $this->vendorService->getById($vendorId);
        
        if (!$vendor) {
            abort(404, __('Vendor not found'));
        }

        $data = $request->validated();
        $data['vendor_id'] = $vendor->id;

        $this->service->create($data);

        return $this->redirectSuccess('vendor.users.index', __('User created successfully'));
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
        $vendorId = $this->getVendorId();
        $vendor = $this->vendorService->getById($vendorId);
        
        if (!$vendor) {
            abort(404, __('Vendor not found'));
        }
        
        $user = $this->service->getById($id);

        // Check if user belongs to current vendor or its descendants
        if (!$this->vendorService->canAccess($vendor->id, $user->vendor_id)) {
            abort(403);
        }

        // Get wallets from current vendor and all its descendants
        $vendorIds = array_merge([$vendor->id], $this->vendorService->getDescendants($vendor->id));
        $wallets = $this->walletService->getByVendorIds($vendorIds);

        $this->data = [
            'title' => __('Edit'),
            'module' => __('Users'),
            'item' => $user,
            'method' => 'PUT',
            'action' => route('vendor.' . $this->module . '.update', $id),
            'roles' => $this->roleService->getByGuardNameVendorIdWithDescendants('vendor', $vendor->id, $this->vendorService),
            'wallets' => $wallets,
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $vendorId = $this->getVendorId();
        $vendor = $this->vendorService->getById($vendorId);
        
        if (!$vendor) {
            abort(404, __('Vendor not found'));
        }
        
        $user = $this->service->getById($id);

        // Check if user belongs to current vendor or its descendants
        if (!$this->vendorService->canAccess($vendor->id, $user->vendor_id)) {
            abort(403);
        }

        $this->service->update($id, $request->validated());

        return $this->redirectSuccess('vendor.users.index', __('User updated successfully'));
    }

    public function destroy(string $id): JsonResponse
    {
        $vendorId = $this->getVendorId();
        $vendor = $this->vendorService->getById($vendorId);
        
        if (!$vendor) {
            abort(404, __('Vendor not found'));
        }
        
        $user = $this->service->getById($id);

        // Check if user belongs to current vendor or its descendants
        if (!$this->vendorService->canAccess($vendor->id, $user->vendor_id)) {
            return $this->json(['message' => __('Unauthorized')], 403);
        }

        if (!request()->has('confirmed')) {
            $this->data = [
                'message' => __('Delete confirmation required'),
                'confirmed' => false,
            ];

            return $this->json($this->data, 422);
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

        return $this->json($this->data, $code);
    }
}
