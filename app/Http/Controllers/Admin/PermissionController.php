<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Store\PermissionRequest as StoreRequest;
use App\Http\Requests\Update\PermissionRequest as UpdateRequest;
use App\Services\PermissionService as Service;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PermissionController extends BaseController
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->middleware('permission:permissions-index|permissions-create|permissions-edit', ['only' => ['index']]);
        $this->middleware('permission:permissions-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:permissions-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:permissions-delete', ['only' => ['destroy']]);

        $this->service = $service;
        $this->module = 'permissions';
    }

    public function index(): Renderable
    {
        $this->data = [
            'module' => __('Permissions'),
            'title' => __('List'),
            'items' => $this->service->paginate(),
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $this->data = [
            'module' => __('Permissions'),
            'title' => __('Create'),
            'method' => 'POST',
            'action' => route('admin.' . $this->module . '.store'),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $item = $this->service->create($request->validated());

        return $this->redirectSuccess('admin.permissions.index');
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
        $this->data = [
            'module' => __('Permissions'),
            'title' => __('Edit'),
            'item' => $this->service->getById($id),
            'method' => 'PUT',
            'action' => route('admin.' . $this->module . '.update', $id),
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $this->service->update($id, $request->validated());

        return $this->redirectSuccess('admin.permissions.index');
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
