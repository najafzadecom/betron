<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Store\RoleRequest as StoreRequest;
use App\Http\Requests\Update\RoleRequest as UpdateRequest;
use App\Services\RoleService as Service;
use App\Services\PermissionService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class RoleController extends BaseController
{
    private Service $service;
    private PermissionService $permissionService;

    public function __construct(
        Service           $service,
        PermissionService $permissionService
    ) {
        $this->middleware('permission:roles-index|roles-create|roles-edit', ['only' => ['index']]);
        $this->middleware('permission:roles-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:roles-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:roles-delete', ['only' => ['destroy']]);

        $this->service = $service;
        $this->permissionService = $permissionService;
        $this->module = 'roles';
    }

    public function index(): Renderable
    {
        $this->data = [
            'module' => __('Roles'),
            'title' => __('List'),
            'items' => $this->service->getByGuardName('web'),
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $this->data = [
            'title' => __('Create'),
            'module' => __('Roles'),
            'method' => 'POST',
            'action' => route('admin.' . $this->module . '.store'),
            'permissions' => $this->permissionService->getByGuardName('web'),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $item = $this->service->create($request->validated());

        return $this->redirectSuccess('admin.roles.index');
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

        $this->data = [
            'title' => __('Edit'),
            'module' => __('Roles'),
            'item' => $item,
            'method' => 'PUT',
            'action' => route('admin.' . $this->module . '.update', $id),
            'permissions' => $this->permissionService->getByGuardName('web'),
            'rolePermissions' => $item ? $item->permissions->pluck('id')->toArray() : [],
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $this->service->update($id, $request->validated());

        return $this->redirectSuccess('admin.roles.index');
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
}
