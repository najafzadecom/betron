<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Store\BankRequest as StoreRequest;
use App\Http\Requests\Update\BankRequest as UpdateRequest;
use App\Services\BankService as Service;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class BankController extends BaseController
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->middleware('permission:banks-index|banks-create|banks-edit', ['only' => ['index']]);
        $this->middleware('permission:banks-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:banks-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:banks-delete', ['only' => ['destroy']]);

        $this->service = $service;
        $this->module = 'banks';
    }

    public function index(): Renderable
    {
        $this->data = [
            'module' => __('Banks'),
            'title' => __('List'),
            'items' => $this->service->getAll('priority', 'asc'),
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $this->data = [
            'title' => __('Create'),
            'module' => __('Banks'),
            'method' => 'POST',
            'action' => route('admin.' . $this->module . '.store'),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $validatedData['image'] = $this->handleImageUpload($request->file('image'));
        }

        $item = $this->service->create($validatedData);

        return $this->redirectSuccess('admin.banks.index');
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
            'title' => __('Edit'),
            'module' => __('Banks'),
            'item' => $this->service->getById($id),
            'method' => 'PUT',
            'action' => route('admin.' . $this->module . '.update', $id),
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $validatedData = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $bank = $this->service->getById($id);

            // Delete old image if exists
            if ($bank->image && Storage::disk('public')->exists($bank->image)) {
                Storage::disk('public')->delete($bank->image);
            }

            $validatedData['image'] = $this->handleImageUpload($request->file('image'));
        }

        $this->service->update($id, $validatedData);

        return $this->redirectSuccess('admin.banks.index');
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

    /**
     * Update bank priorities
     */
    public function updatePriorities(): JsonResponse
    {
        $priorities = request('priorities', []);

        if (empty($priorities)) {
            return $this->json(422, ['message' => __('No priorities provided')]);
        }

        try {
            foreach ($priorities as $id => $priority) {
                $this->service->update($id, ['priority' => $priority]);
            }

            $this->data = [
                'message' => __('Priorities updated successfully'),
            ];

            return $this->json(200);
        } catch (\Exception $e) {
            $this->data = [
                'message' => __('Error updating priorities'),
            ];

            return $this->json(500);
        }
    }

    /**
     * Bulk update bank statuses
     */
    public function bulkUpdateStatus(): JsonResponse
    {
        $bankIds = request('bank_ids', []);
        $field = request('field');
        $value = request('value');

        // Validate inputs
        if (empty($bankIds)) {
            $this->data = [
                'success' => false,
                'message' => __('No banks selected'),
            ];
            return $this->json(422);
        }

        $allowedFields = ['status', 'transaction_status', 'withdrawal_status'];
        if (!in_array($field, $allowedFields)) {
            $this->data = [
                'success' => false,
                'message' => __('Invalid field'),
            ];
            return $this->json(422);
        }

        if (!in_array($value, [0, 1], true)) {
            $this->data = [
                'success' => false,
                'message' => __('Invalid value'),
            ];
            return $this->json(422);
        }

        try {
            foreach ($bankIds as $id) {
                $this->service->update($id, [$field => $value]);
            }

            $this->data = [
                'success' => true,
                'message' => __('Banks updated successfully'),
            ];

            return $this->json(200);
        } catch (\Exception $e) {
            $this->data = [
                'success' => false,
                'message' => __('Error updating banks'),
            ];

            return $this->json(500);
        }
    }

    /**
     * Handle image upload
     */
    private function handleImageUpload($file): string
    {
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $uploadPath = 'uploads/banks';

        // Ensure the directory exists
        Storage::disk('public')->makeDirectory($uploadPath, 0755, true, true);

        // Store file in storage/app/public/uploads/banks
        Storage::disk('public')->putFileAs($uploadPath, $file, $fileName);

        // Return the path for storage link (without 'public/' prefix)
        return $uploadPath . '/' . $fileName;
    }
}
