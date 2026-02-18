<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Requests\Store\AddDepositRequest;
use App\Http\Requests\Store\SubtractDepositRequest;
use App\Http\Requests\Vendor\Store\VendorRequest as StoreRequest;
use App\Http\Requests\Vendor\Update\VendorRequest as UpdateRequest;
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

    public function __construct(
        Service                         $service,
        VendorDepositTransactionService $depositTransactionService
    ) {
        $this->service = $service;
        $this->depositTransactionService = $depositTransactionService;
        $this->module = 'vendors';
    }

    public function index(): Renderable
    {
        $vendorId = $this->getVendorId();

        // Check if vendor can create sub-vendors
        $canCreateVendor = $this->service->canCreateVendor($vendorId);

        // Get child vendors with filters and pagination
        $filters = request()->only(['search', 'name', 'email', 'status', 'created_from', 'created_to', 'limit']);
        $items = $this->service->getChildVendorsPaginated($vendorId, $filters);

        $this->data = [
            'module' => __('Vendors'),
            'title' => __('List'),
            'items' => $items,
            'canCreateVendor' => $canCreateVendor,
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $vendorId = $this->getVendorId();

        // Check if vendor can create sub-vendors (only top-level and their direct children)
        if (!$this->service->canCreateVendor($vendorId)) {
            abort(403, __('You cannot create sub-vendors. Only top-level vendors and their direct children can create vendors.'));
        }

        $this->data = [
            'title' => __('Create'),
            'module' => __('Vendors'),
            'method' => 'POST',
            'action' => route('vendor.' . $this->module . '.store'),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $vendorId = $this->getVendorId();

        // Check if vendor can create sub-vendors (only top-level and their direct children)
        if (!$this->service->canCreateVendor($vendorId)) {
            return $this->redirectError(__('You cannot create sub-vendors. Only top-level vendors and their direct children can create vendors.'));
        }

        $data = $request->validated();
        $data['parent_id'] = $vendorId; // Set current vendor as parent

        $this->service->create($data);

        return $this->redirectSuccess('vendor.vendors.index', __('Vendor created successfully'));
    }

    public function show(string $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        // Check if vendor can access this vendor
        if (!$this->service->canAccess($vendorId, (int)$id)) {
            return $this->json(['message' => __('Unauthorized')], 403);
        }

        $this->data = [
            'item' => $this->service->getById($id),
        ];

        return $this->json();
    }

    public function edit(string $id): Renderable
    {
        $vendorId = $this->getVendorId();

        // Check if vendor can access this vendor
        if (!$this->service->canAccess($vendorId, (int)$id)) {
            abort(403);
        }

        $vendor = $this->service->getById($id);

        $this->data = [
            'title' => __('Edit'),
            'module' => __('Vendors'),
            'item' => $vendor,
            'method' => 'PUT',
            'action' => route('vendor.' . $this->module . '.update', $id),
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $vendorId = $this->getVendorId();

        // Check if vendor can access this vendor
        if (!$this->service->canAccess($vendorId, (int)$id)) {
            abort(403);
        }

        $data = $request->validated();
        // Don't allow changing parent_id
        unset($data['parent_id']);

        $this->service->update((int)$id, $data);

        return $this->redirectSuccess('vendor.vendors.index', __('Vendor updated successfully'));
    }

    public function destroy(string $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        // Check if vendor can access this vendor
        if (!$this->service->canAccess($vendorId, (int)$id)) {
            return $this->json(['message' => __('Unauthorized')], 403);
        }

        // Don't allow deleting self
        if ($vendorId == $id) {
            return $this->json(['message' => __('Cannot delete yourself')], 422);
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

        if ($this->service->delete((int)$id)) {
            $message = __('Delete successfully');
            $code = 200;
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($this->data, $code);
    }

    /**
     * Add deposit to child vendor
     */
    public function addDeposit(AddDepositRequest $request, string $id): RedirectResponse
    {
        $vendorId = $this->getVendorId();

        // Check if vendor can access this vendor (must be a child vendor)
        if (!$this->service->canAccess($vendorId, (int)$id)) {
            return $this->redirectError(__('Unauthorized'));
        }

        $amount = $request->validated()['amount'];
        $note = $request->validated()['note'] ?? null;

        if ($this->service->addDeposit($id, $amount, $note)) {
            return $this->redirectSuccessBack(__('Deposit added successfully'));
        }

        return $this->redirectError(__('Failed to add deposit'));
    }

    /**
     * Subtract deposit from child vendor
     */
    public function subtractDeposit(SubtractDepositRequest $request, string $id): RedirectResponse
    {
        $vendorId = $this->getVendorId();

        // Check if vendor can access this vendor (must be a child vendor)
        if (!$this->service->canAccess($vendorId, (int)$id)) {
            return $this->redirectError(__('Unauthorized'));
        }

        $amount = $request->validated()['amount'];
        $note = $request->validated()['note'] ?? null;

        if ($this->service->subtractDeposit($id, $amount, $note)) {
            return $this->redirectSuccessBack(__('Deposit subtracted successfully'));
        }

        return $this->redirectError(__('Failed to subtract deposit. Insufficient deposit amount.'));
    }

    /**
     * Show deposit transactions for a child vendor
     */
    public function depositTransactions(string $id): Renderable
    {
        $vendorId = $this->getVendorId();

        // Check if vendor can access this vendor (must be a child vendor)
        if (!$this->service->canAccess($vendorId, (int)$id)) {
            abort(403);
        }

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
     * Show all deposit transactions for child vendors
     */
    public function allDepositTransactions(): Renderable
    {
        $vendorId = $this->getVendorId();

        // Get all child vendor IDs (descendants)
        $childVendorIds = $this->service->getDescendants($vendorId);

        $filters = request()->only(['vendor_id', 'type', 'created_from', 'created_to', 'search', 'limit']);

        // Filter by child vendors only
        if (!empty($filters['vendor_id'])) {
            // Check if vendor_id is a child vendor
            if (!in_array((int)$filters['vendor_id'], $childVendorIds)) {
                abort(403);
            }
        } else {
            // Filter by all child vendors
            $filters['vendor_ids'] = $childVendorIds;
        }

        $items = $this->depositTransactionService->getAllTransactions($filters);

        // Get child vendors for filter dropdown (only direct children)
        $childVendors = $this->service->getChildren($vendorId);

        $this->data = [
            'module' => __('Vendors'),
            'title' => __('All Deposit Transactions'),
            'items' => $items,
            'childVendors' => $childVendors,
        ];

        return $this->render('all-deposit-transactions');
    }

    /**
     * Bulk update vendor statuses (for sub-vendors only)
     */
    public function bulkUpdateStatus(): JsonResponse
    {
        $vendorId = $this->getVendorId();
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
            // Get all accessible vendor IDs (descendants)
            $accessibleVendorIds = $this->service->getDescendants($vendorId);

            // Filter vendor_ids to only include accessible ones
            $validVendorIds = array_intersect(array_map('intval', $vendorIds), $accessibleVendorIds);

            if (empty($validVendorIds)) {
                $this->data = [
                    'success' => false,
                    'message' => __('Unauthorized'),
                ];
                return $this->json(403);
            }

            foreach ($validVendorIds as $id) {
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
     * Login as child vendor (impersonation)
     */
    public function loginAs(string $id): RedirectResponse
    {
        $vendorId = $this->getVendorId();

        // Check if vendor can access this vendor (must be a child vendor)
        if (!$this->service->canAccess($vendorId, (int)$id)) {
            return $this->redirectError(__('Unauthorized'));
        }

        $vendor = $this->service->getById($id);

        if (!$vendor) {
            return $this->redirectError(__('Vendor not found'));
        }

        if (!$vendor->status) {
            return $this->redirectError(__('Cannot login as inactive vendor'));
        }

        // Logout current vendor session
        Auth::guard('vendor')->logout();

        // Login as child vendor
        Auth::guard('vendor')->login($vendor);
        request()->session()->regenerate();

        return redirect()->route('vendor.dashboard')->with('success', __('Logged in as vendor: :name', ['name' => $vendor->name]));
    }
}
