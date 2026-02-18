<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Store\VendorUserRequest as StoreRequest;
use App\Http\Requests\Update\VendorUserRequest as UpdateRequest;
use App\Services\RoleService;
use App\Services\VendorService;
use App\Services\VendorUserService as Service;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class VendorUserController extends BaseController
{
    private Service $service;
    private RoleService $roleService;
    private VendorService $vendorService;

    public function __construct(
        Service       $service,
        RoleService   $roleService,
        VendorService $vendorService
    ) {
        $this->middleware('permission:vendor-users-index|vendor-users-create|vendor-users-edit', ['only' => ['index']]);
        $this->middleware('permission:vendor-users-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:vendor-users-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:vendor-users-delete', ['only' => ['destroy']]);

        $this->service = $service;
        $this->roleService = $roleService;
        $this->vendorService = $vendorService;
        $this->module = 'vendor-users';
    }

    public function index(): Renderable
    {
        $this->data = [
            'module' => __('Vendor Users'),
            'title' => __('List'),
            'items' => $this->service->paginate(),
            'vendors' => $this->vendorService->getActives(),
            'topLevelVendors' => $this->vendorService->getTopLevelVendors(),
            'roles' => $this->roleService->getByGuardName('vendor'),
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $this->data = [
            'title' => __('Create'),
            'module' => __('Vendor Users'),
            'method' => 'POST',
            'action' => route('admin.' . $this->module . '.store'),
            'roles' => $this->roleService->getByGuardName('vendor'),
            'vendors' => $this->vendorService->getActives(),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return $this->redirectSuccess('admin.vendor-users.index');
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
        $user = $this->service->getById($id);

        $this->data = [
            'title' => __('Edit'),
            'module' => __('Vendor Users'),
            'item' => $user,
            'method' => 'PUT',
            'action' => route('admin.' . $this->module . '.update', $id),
            'roles' => $this->roleService->getByGuardName('vendor'),
            'vendors' => $this->vendorService->getActives(),
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $this->service->update($id, $request->validated());

        return $this->redirectSuccess('admin.vendor-users.index');
    }

    public function destroy(string $id): JsonResponse
    {
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

    /**
     * Get child vendors for a parent vendor (AJAX)
     */
    public function getChildVendors(int $parentId): JsonResponse
    {
        $vendor = $this->vendorService->getById($parentId);
        
        if (!$vendor) {
            return response()->json(['vendors' => []], 404);
        }

        // Get all accessible vendors (parent + all descendants)
        $vendors = $this->vendorService->getAccessibleVendorsForParent($parentId);

        return response()->json([
            'vendors' => $vendors->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                ];
            })->toArray(),
        ]);
    }
}
