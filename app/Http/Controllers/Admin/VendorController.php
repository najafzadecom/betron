<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Store\AddDepositRequest;
use App\Http\Requests\Store\SubtractDepositRequest;
use App\Http\Requests\Store\VendorRequest as StoreRequest;
use App\Http\Requests\Update\VendorRequest as UpdateRequest;
use App\Services\VendorDepositTransactionService;
use App\Services\VendorService as Service;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VendorController extends BaseController
{
    private Service $service;
    private VendorDepositTransactionService $depositTransactionService;

    public function __construct(Service $service, VendorDepositTransactionService $depositTransactionService)
    {
        $this->middleware('permission:vendors-index|vendors-create|vendors-edit', ['only' => ['index', 'depositTransactions', 'allDepositTransactions']]);
        $this->middleware('permission:vendors-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:vendors-edit', ['only' => ['edit', 'update', 'addDeposit', 'subtractDeposit', 'bulkUpdateStatus', 'loginAs']]);
        $this->middleware('permission:vendors-delete', ['only' => ['destroy']]);

        $this->service = $service;
        $this->depositTransactionService = $depositTransactionService;
        $this->module = 'vendors';
    }

    public function index(): Renderable
    {
        $parentId = request('parent_id', 0);

        try {
            // Get top level vendors without applying VendorScope filter
            // This is for the filter dropdown, not for the list itself
            $topLevelVendors = \App\Models\Vendor::withoutGlobalScopes()
                ->whereNull('parent_id')
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            $topLevelVendors = collect([]);
        }

        $this->data = [
            'module' => __('Vendors'),
            'title' => __('List'),
            'items' => $this->service->paginate(),
            'topLevelVendors' => $topLevelVendors ?? collect([]),
            'parentId' => $parentId,
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $this->data = [
            'title' => __('Create'),
            'module' => __('Vendors'),
            'method' => 'POST',
            'action' => route('admin.' . $this->module . '.store'),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return $this->redirectSuccess('admin.vendors.index');
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
            'module' => __('Vendors'),
            'item' => $this->service->getById($id),
            'method' => 'PUT',
            'action' => route('admin.' . $this->module . '.update', $id),
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $this->service->update($id, $request->validated());

        return $this->redirectSuccess('admin.vendors.index');
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
     * Add deposit to vendor
     */
    public function addDeposit(AddDepositRequest $request, string $id): RedirectResponse
    {
        $amount = $request->validated()['amount'];
        $note = $request->validated()['note'] ?? null;

        if ($this->service->addDeposit($id, $amount, $note)) {
            return $this->redirectSuccessBack(__('Deposit added successfully'));
        }

        return $this->redirectError(__('Failed to add deposit'));
    }

    /**
     * Subtract deposit from vendor
     */
    public function subtractDeposit(SubtractDepositRequest $request, string $id): RedirectResponse
    {
        $amount = $request->validated()['amount'];
        $note = $request->validated()['note'] ?? null;

        if ($this->service->subtractDeposit($id, $amount, $note)) {
            return $this->redirectSuccessBack(__('Deposit subtracted successfully'));
        }

        return $this->redirectError(__('Failed to subtract deposit. Insufficient deposit amount.'));
    }

    /**
     * Show deposit transactions for a vendor
     */
    public function depositTransactions(string $id): Renderable
    {
        $vendor = $this->service->getById($id);
        if (!$vendor) {
            abort(404);
        }

        $filters = request()->only(['type', 'created_from', 'created_to', 'limit']);
        $items = $this->depositTransactionService->getByVendorId($id, $filters);

        $this->data = [
            'module' => __('Vendors'),
            'title' => __('Deposit Transactions'),
            'vendor' => $vendor,
            'items' => $items,
        ];

        return $this->render('deposit-transactions');
    }

    /**
     * Show all deposit transactions (not vendor-specific)
     */
    public function allDepositTransactions(): Renderable
    {
        $filters = request()->only(['parent_vendor_id', 'vendor_id', 'type', 'created_from', 'created_to', 'search', 'limit']);
        
        // If vendor_id is set, use it directly (more specific)
        // If only parent_vendor_id is set, get all descendants + parent
        if (!empty($filters['vendor_id'])) {
            // Use vendor_id directly, ignore parent_vendor_id
            unset($filters['parent_vendor_id']);
        } elseif (!empty($filters['parent_vendor_id'])) {
            // Get all descendants + parent itself
            $vendorIds = $this->service->getDescendants($filters['parent_vendor_id']);
            $vendorIds[] = (int)$filters['parent_vendor_id']; // Include parent itself
            $filters['vendor_ids'] = $vendorIds;
            unset($filters['parent_vendor_id']);
        }
        
        $items = $this->depositTransactionService->getAllTransactions($filters);
        $topLevelVendors = $this->service->getTopLevelVendors();
        $childVendors = collect([]);
        
        if (request('parent_vendor_id')) {
            $childVendors = $this->service->getAccessibleVendorsForParent(request('parent_vendor_id'));
        }

        $this->data = [
            'module' => __('Vendors'),
            'title' => __('All Deposit Transactions'),
            'items' => $items,
            'topLevelVendors' => $topLevelVendors,
            'childVendors' => $childVendors,
        ];

        return $this->render('all-deposit-transactions');
    }

    /**
     * Bulk update vendor statuses
     */
    public function bulkUpdateStatus(): JsonResponse
    {
        $vendorIds = request('vendor_ids', []);
        $field = request('field');
        $value = request('value');

        // Validate inputs
        if (empty($vendorIds)) {
            $this->data = [
                'success' => false,
                'message' => __('No vendors selected'),
            ];
            return $this->json(422);
        }

        $allowedFields = ['deposit_enabled', 'withdrawal_enabled'];
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
            foreach ($vendorIds as $id) {
                $this->service->update($id, [$field => $value]);
            }

            $this->data = [
                'success' => true,
                'message' => __('Vendors updated successfully'),
            ];

            return $this->json(200);
        } catch (\Exception $e) {
            $this->data = [
                'success' => false,
                'message' => __('Error updating vendors'),
            ];

            return $this->json(500);
        }
    }

    /**
     * Login as vendor (impersonation)
     */
    public function loginAs(string $id): RedirectResponse
    {
        $vendor = $this->service->getById($id);
        
        if (!$vendor) {
            return $this->redirectError(__('Vendor not found'));
        }

        if (!$vendor->status) {
            return $this->redirectError(__('Cannot login as inactive vendor'));
        }

        // Logout current admin session
        Auth::guard('web')->logout();
        
        // Login as vendor
        Auth::guard('vendor')->login($vendor);
        request()->session()->regenerate();

        return redirect()->route('vendor.dashboard')->with('success', __('Logged in as vendor: :name', ['name' => $vendor->name]));
    }
}
