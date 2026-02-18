<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Requests\Store\VendorRoleRequest as StoreRequest;
use App\Http\Requests\Update\VendorRoleRequest as UpdateRequest;
use App\Services\PermissionService;
use App\Services\RoleService;
use App\Services\VendorService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class RoleController extends BaseController
{
    private RoleService $roleService;
    private PermissionService $permissionService;
    private VendorService $vendorService;

    public function __construct(
        RoleService $roleService,
        PermissionService $permissionService,
        VendorService $vendorService
    ) {
        $this->middleware('vendor_permission:vendor-roles-index|vendor-roles-create|vendor-roles-edit', ['only' => ['index']]);
        $this->middleware('vendor_permission:vendor-roles-create', ['only' => ['create', 'store']]);
        $this->middleware('vendor_permission:vendor-roles-edit', ['only' => ['edit', 'update']]);
        $this->middleware('vendor_permission:vendor-roles-delete', ['only' => ['destroy']]);

        $this->roleService = $roleService;
        $this->permissionService = $permissionService;
        $this->vendorService = $vendorService;
        $this->module = 'roles';
    }

    public function index(): Renderable
    {
        $vendorId = $this->getVendorId();

        // Get roles from current vendor and all its descendants
        $items = $this->roleService->getByGuardNameVendorIdWithDescendants('vendor', $vendorId, $this->vendorService);

        $this->data = [
            'module' => __('Roles'),
            'title' => __('List'),
            'items' => $items,
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $this->data = [
            'title' => __('Create'),
            'module' => __('Roles'),
            'method' => 'POST',
            'action' => route('vendor.' . $this->module . '.store'),
            'permissions' => $this->permissionService->getByGuardName('vendor'),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $vendorId = $this->getVendorId();
        $this->roleService->createWithPermissions($request->validated(), 'vendor', $vendorId);

        return $this->redirectSuccess('vendor.roles.index', __('Role created successfully'));
    }

    public function show(string $id): JsonResponse
    {
        $vendorId = $this->getVendorId();
        $role = $this->roleService->findByGuardName($id, 'vendor');
        
        // Check if role belongs to current vendor or its descendants
        if (!$role || !$this->vendorService->canAccess($vendorId, $role->vendor_id)) {
            return response()->json(['error' => __('Unauthorized')], 403);
        }

        // Add translated names to permissions
        $role->permissions->each(function ($permission) {
            $permission->translated_name = __($permission->name);
        });

        $this->data = [
            'item' => $role,
        ];

        return $this->json();
    }

    public function edit(string $id): Renderable
    {
        $vendorId = $this->getVendorId();
        $item = $this->roleService->findByGuardName($id, 'vendor');
        
        // Check if role belongs to current vendor or its descendants
        if (!$item || !$this->vendorService->canAccess($vendorId, $item->vendor_id)) {
            abort(403);
        }

        $this->data = [
            'title' => __('Edit'),
            'module' => __('Roles'),
            'item' => $item,
            'method' => 'PUT',
            'action' => route('vendor.' . $this->module . '.update', $id),
            'permissions' => $this->permissionService->getByGuardName('vendor'),
            'rolePermissions' => $item ? $item->permissions->pluck('id')->toArray() : [],
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $vendorId = $this->getVendorId();
        $role = $this->roleService->findByGuardName($id, 'vendor');
        
        // Check if role belongs to current vendor or its descendants
        if (!$role || !$this->vendorService->canAccess($vendorId, $role->vendor_id)) {
            abort(403);
        }
        
        // Don't allow changing vendor_id
        $data = $request->validated();
        unset($data['vendor_id']);
        
        $this->roleService->updateWithPermissions($id, $data, 'vendor', $role->vendor_id);

        return $this->redirectSuccess('vendor.roles.index', __('Role updated successfully'));
    }

    public function destroy(string $id): JsonResponse
    {
        if (!request()->has('confirmed')) {
            $this->data = [
                'message' => __('Delete confirmation required'),
                'confirmed' => false,
            ];

            return $this->json($this->data, 422);
        }

        $message = __('Unknown error');
        $code = 500;

        if ($this->roleService->delete($id)) {
            $message = __('Delete successfully');
            $code = 200;
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($this->data, $code);
    }
}
