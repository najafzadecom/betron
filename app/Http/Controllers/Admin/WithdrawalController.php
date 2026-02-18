<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Currency;
use App\Enums\PaidStatus;
use App\Enums\PaymentProvider;
use App\Enums\WithdrawalStatus;
use App\Exports\WithdrawalExport;
use App\Http\Requests\Store\WithdrawalRequest as StoreRequest;
use App\Http\Requests\Update\WithdrawalRequest as UpdateRequest;
use App\Models\Withdrawal;
use App\Payment\Paypap;
use App\Services\ActivityLogService;
use App\Services\BankService;
use App\Services\SiteService;
use App\Services\VendorService;
use App\Services\WalletService;
use App\Services\WithdrawalService as Service;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WithdrawalController extends BaseController
{
    private Service $service;

    private WalletService $walletService;

    private SiteService $siteService;
    private VendorService $vendorService;
    private BankService $bankService;
    private Paypap $paypap;
    private ActivityLogService $activityLogService;

    public function __construct(
        Service            $service,
        WalletService      $walletService,
        SiteService        $siteService,
        VendorService      $vendorService,
        BankService        $bankService,
        Paypap             $paypap,
        ActivityLogService $activityLogService
    ) {
        $this->middleware('permission:withdrawals-index|withdrawals-create|withdrawals-edit', ['only' => ['index']]);
        $this->middleware('permission:withdrawals-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:withdrawals-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:withdrawals-delete', ['only' => ['destroy']]);

        $this->service = $service;
        $this->walletService = $walletService;
        $this->siteService = $siteService;
        $this->vendorService = $vendorService;
        $this->bankService = $bankService;
        $this->paypap = $paypap;
        $this->activityLogService = $activityLogService;
        $this->module = 'withdrawals';
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
            'module' => __('Withdrawals'),
            'title' => __('List'),
            'items' => $this->service->paginate(),
            'sites' => $this->siteService->getAll(),
            'topLevelVendors' => $topLevelVendors,
            'childVendors' => $childVendors,
            'parentVendorId' => $parentVendorId,
            'vendorId' => $vendorId,
            'vendors' => $this->vendorService->getAll(),
            'currencies' => Currency::cases(),
            'paid_statuses' => PaidStatus::cases(),
        ];

        return $this->render('list');
    }

    public function send(): Renderable
    {
        $activeWallets = $this->walletService->getActive();

        $this->data = [
            'module' => __('Withdrawals'),
            'title' => __('List'),
            'items' => $this->service->manual(),
            'wallets' => $activeWallets,
        ];

        return $this->render('send');
    }

    public function create(): Renderable
    {
        $this->data = [
            'title' => __('Create'),
            'module' => __('Withdrawals'),
            'method' => 'POST',
            'action' => route('admin.' . $this->module . '.store'),
            'wallets' => $this->walletService->getAll(),
            'sites' => $this->siteService->getAll(),
            'vendors' => $this->vendorService->getAll(),
            'statuses' => WithdrawalStatus::cases(),
            'paid_statuses' => PaidStatus::cases(),
            'currencies' => Currency::cases(),
            'banks' => $this->bankService->getAll(),
            'payment_providers' => PaymentProvider::cases(),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['amounts']) && is_array($data['amounts'])) {
            $this->service->createMultiple($data);
        } else {
            $this->service->create($data);
        }

        return $this->redirectSuccess('admin.withdrawals.index');
    }

    public function show(string $id)
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
            'module' => __('Withdrawals'),
            'item' => $this->service->getById($id),
            'method' => 'PUT',
            'action' => route('admin.' . $this->module . '.update', $id),
            'wallets' => $this->walletService->getAll(),
            'sites' => $this->siteService->getAll(),
            'vendors' => $this->vendorService->getAll(),
            'statuses' => WithdrawalStatus::cases(),
            'paid_statuses' => PaidStatus::cases(),
            'currencies' => Currency::cases(),
            'banks' => $this->bankService->getAll(),
            'payment_providers' => PaymentProvider::cases(),
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $this->service->update($id, $request->validated());

        return $this->redirectSuccess('admin.withdrawals.index');
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

    public function export(): BinaryFileResponse
    {
        return Excel::download(new WithdrawalExport($this->service), 'withdrawal-report-' . date('Y-m-d_H:i:s') . '.xlsx');
    }

    public function approve(string $id): JsonResponse
    {
        $code = 500;
        $message = __('Unknown error');

        try {
            $withdrawal = $this->service->getById($id);

            if (!$withdrawal) {
                return $this->json(['message' => __('Withdrawal not found')], 404);
            }

            if ($withdrawal->status->value !== 0) {
                return $this->json(['message' => __('Only pending withdrawals can be approved')], 422);
            }

            $this->service->update($id, [
                'status' => WithdrawalStatus::ManualConfirmed->value,
                'paid_status' => true
            ]);

            $code = 200;
            $message = __('Withdrawal approved successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($this->data, $code);
    }

    public function cancel(string $id): JsonResponse
    {
        $code = 500;
        $message = __('Unknown error');

        try {
            $withdrawal = $this->service->getById($id);

            if (!$withdrawal) {
                return $this->json(404, ['message' => __('Withdrawal not found')], 422);
            }

            if ($withdrawal->status->value !== 0) {
                return $this->json(['message' => __('Only pending withdrawals can be cancelled')], 422);
            }

            $this->service->update($id, [
                'status' => WithdrawalStatus::ManualCancelled->value
            ]);

            $code = 200;
            $message = __('Withdrawal cancelled successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($this->data, $code);
    }

    public function activityLogs(string $id): Renderable
    {
        $withdrawal = $this->service->getById($id);

        if (!$withdrawal) {
            abort(404);
        }

        $perPage = (int)request('limit', config('pagination.per_page'));
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : config('pagination.per_page');

        $this->data = [
            'module' => __('Withdrawal Activity Logs'),
            'title' => __('List'),
            'items' => $this->activityLogService->getBySubject(Withdrawal::class, (int)$id, $perPage),
            'withdrawal' => $withdrawal,
        ];

        return $this->render('activity-logs');
    }

    public function paypapStatus(string $id): Renderable
    {
        $withdrawal = $this->service->getById($id);

        if (!$withdrawal) {
            abort(404);
        }

        $paypapStatus = null;
        $error = null;

        $withdrawalId = $withdrawal->getAttribute('withdrawal_id') ?? $withdrawal->withdrawal_id ?? null;

        if ($withdrawalId) {
            $result = $this->paypap->getBankWithdrawal($withdrawalId);

            if ($result['status']) {
                $paypapStatus = $result['data'];
            } else {
                $error = $result['message'] ?? __('Failed to fetch Paypap status');
            }
        } else {
            $error = __('This withdrawal does not have an operation ID');
        }

        $this->data = [
            'module' => __('Paypap Withdrawal Status'),
            'title' => __('Status'),
            'withdrawal' => $withdrawal,
            'paypapStatus' => $paypapStatus,
            'error' => $error,
        ];

        return $this->render('paypap-status');
    }

    public function assignVendor(string $id): JsonResponse
    {
        $code = 500;
        $message = __('Unknown error');

        try {
            $withdrawal = $this->service->getById($id);

            if (!$withdrawal) {
                return $this->json(['message' => __('Withdrawal not found')], 404);
            }

            $vendorId = request('vendor_id');

            if (!$vendorId) {
                return $this->json(['message' => __('Vendor is required')], 422);
            }

            // Validate vendor exists
            $vendor = $this->vendorService->getById($vendorId);
            if (!$vendor) {
                return $this->json(['message' => __('Vendor not found')], 422);
            }

            $updateData = [
                'vendor_id' => $vendorId
            ];

            // If set_processing is checked, update status to Processing
            if (request('set_processing')) {
                $updateData['status'] = WithdrawalStatus::Processing->value;
            }

            $this->service->update($id, $updateData);

            $code = 200;
            $message = __('Vendor assigned successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($this->data, $code);
    }

    public function bulkAssignVendor(): JsonResponse
    {
        $code = 500;
        $message = __('Unknown error');

        try {
            $withdrawalIds = request('withdrawal_ids', []);
            $vendorId = request('vendor_id');

            if (empty($withdrawalIds) || !is_array($withdrawalIds)) {
                return $this->json(['error' => __('Please select at least one withdrawal')], 422);
            }

            if (!$vendorId) {
                return $this->json(['error' => __('Vendor is required')], 422);
            }

            // Validate vendor exists and is a top-level vendor (parent_id must be null)
            $vendor = $this->vendorService->getById($vendorId);
            if (!$vendor) {
                return $this->json(['error' => __('Vendor not found')], 422);
            }

            // Check if vendor is a top-level vendor (parent_id must be null)
            if ($vendor->parent_id !== null) {
                return $this->json(['error' => __('Only top-level vendors can be assigned')], 422);
            }

            // Get withdrawals and validate they exist and are in correct status
            $withdrawals = $this->service->getByIds($withdrawalIds);

            if ($withdrawals->isEmpty()) {
                return $this->json(['error' => __('No valid withdrawals found')], 422);
            }

            // Filter withdrawals that have vendor assigned and are in Processing status only
            $validWithdrawals = $withdrawals->filter(function ($withdrawal) {
                return $withdrawal->vendor_id && 
                       $withdrawal->status->value === WithdrawalStatus::Processing->value;
            });

            if ($validWithdrawals->isEmpty()) {
                return $this->json(['error' => __('No valid withdrawals to assign. Only withdrawals with assigned vendor and Processing status can be reassigned.')], 422);
            }

            // Only update vendor_id, status remains Processing (already filtered)
            $updateData = [
                'vendor_id' => $vendorId
            ];

            $assignedCount = 0;
            foreach ($validWithdrawals as $withdrawal) {
                $this->service->update($withdrawal->id, $updateData);
                $assignedCount++;
            }

            $code = 200;
            $message = __('Bulk vendor assignment completed') . ' (' . $assignedCount . ' ' . __('withdrawals') . ')';
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($this->data, $code);
    }
}
