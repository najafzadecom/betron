<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Currency;
use App\Enums\PaidStatus;
use App\Enums\PaymentProvider;
use App\Enums\TransactionStatus;
use App\Exports\TransactionExport;
use App\Http\Requests\Store\TransactionRequest as StoreRequest;
use App\Http\Requests\Update\TransactionRequest as UpdateRequest;
use App\Models\Transaction;
use App\Payment\Paypap;
use App\Services\ActivityLogService;
use App\Services\BankService;
use App\Services\SiteService;
use App\Services\TransactionService as Service;
use App\Services\VendorService;
use App\Services\WalletService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionController extends BaseController
{
    private Service $service;

    private WalletService $walletService;

    private BankService $bankService;

    private SiteService $siteService;

    private VendorService $vendorService;

    private Paypap $paypap;

    private ActivityLogService $activityLogService;

    public function __construct(
        Service            $service,
        WalletService      $walletService,
        BankService        $bankService,
        SiteService        $siteService,
        VendorService      $vendorService,
        Paypap             $paypap,
        ActivityLogService $activityLogService
    ) {
        $this->middleware('permission:transactions-index|transactions-create|transactions-edit', ['only' => ['index']]);
        $this->middleware('permission:transactions-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:transactions-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:transactions-delete', ['only' => ['destroy']]);

        $this->service = $service;
        $this->walletService = $walletService;
        $this->bankService = $bankService;
        $this->siteService = $siteService;
        $this->vendorService = $vendorService;
        $this->paypap = $paypap;
        $this->activityLogService = $activityLogService;
        $this->module = 'transactions';
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

        $items = $this->service->paginate();

        $this->data = [
            'module' => __('Transactions'),
            'title' => __('List'),
            'items' => $items,
            'sites' => $this->siteService->getAll(),
            'topLevelVendors' => $topLevelVendors,
            'childVendors' => $childVendors,
            'parentVendorId' => $parentVendorId,
            'vendorId' => $vendorId,
            'payment_providers' => PaymentProvider::cases(),
            'transaction_statuses' => TransactionStatus::cases(),
            'paid_statuses' => PaidStatus::cases(),
            'currencies' => Currency::cases(),
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $this->data = [
            'title' => __('Create'),
            'module' => __('Transactions'),
            'method' => 'POST',
            'action' => route('admin.' . $this->module . '.store'),
            'currencies' => Currency::cases(),
            'statuses' => TransactionStatus::cases(),
            'wallets' => $this->walletService->getAll(),
            'banks' => $this->bankService->getAll(),
            'sites' => $this->siteService->getAll(),
            'payment_providers' => PaymentProvider::cases(),
            'transaction_statuses' => TransactionStatus::cases(),
            'paid_statuses' => PaidStatus::cases(),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return $this->redirectSuccess('admin.transactions.index');
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
            'module' => __('Transactions'),
            'item' => $this->service->getById($id),
            'method' => 'PUT',
            'action' => route('admin.' . $this->module . '.update', $id),
            'currencies' => Currency::cases(),
            'statuses' => TransactionStatus::cases(),
            'wallets' => $this->walletService->getAll(),
            'banks' => $this->bankService->getAll(),
            'sites' => $this->siteService->getAll(),
            'payment_providers' => PaymentProvider::cases(),
            'transaction_statuses' => TransactionStatus::cases(),
            'paid_statuses' => PaidStatus::cases(),
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $this->service->update($id, $request->validated());

        return $this->redirectSuccess('admin.transactions.index');
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
        return Excel::download(new TransactionExport($this->service), 'transaction-report-' . date('Y-m-d_H:i:s') . '.xlsx');
    }

    public function changeStatus(string $id, $status = 0): JsonResponse
    {
        $code = 500;
        $message = __('Unknown error');

        if ($this->service->changeStatus($id, $status)) {
            $code = 200;
            $message = __('Force delete successfully');
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($code);
    }

    public function approve(string $id): JsonResponse
    {
        $code = 500;
        $message = __('Unknown error');

        try {
            $transaction = $this->service->getById($id);

            if (!$transaction) {
                return $this->json(404, ['message' => __('Transaction not found')]);
            }

            if ($transaction->status->value !== 1) {
                return $this->json(422, ['message' => __('Only pending transactions can be approved')]);
            }

            $updateData = [
                'status' => TransactionStatus::ManualConfirmed->value,
                'paid_status' => true
            ];

            // Update amount if provided
            if (request()->has('amount') && request()->filled('amount')) {
                $amount = request()->get('amount');
                if (is_numeric($amount) && $amount > 0) {
                    $updateData['amount'] = $amount;
                    // fee_amount will be recalculated automatically in TransactionService::update()
                }
            }

            $this->service->update($id, $updateData);

            $code = 200;
            $message = __('Transaction approved successfully');
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
            $transaction = $this->service->getById($id);

            if (!$transaction) {
                return $this->json(['message' => __('Transaction not found')], 422);
            }

            if ($transaction->status->value !== 1) {
                return $this->json(['message' => __('Only pending transactions can be cancelled')], 422);
            }

            $this->service->update($id, [
                'status' => TransactionStatus::ManualCancelled->value
            ]);

            $code = 200;
            $message = __('Transaction cancelled successfully');
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
        $transaction = $this->service->getById($id);

        if (!$transaction) {
            abort(404);
        }

        $perPage = (int)request('limit', config('pagination.per_page'));
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : config('pagination.per_page');

        $this->data = [
            'module' => __('Transaction Activity Logs'),
            'title' => __('List'),
            'items' => $this->activityLogService->getBySubject(Transaction::class, (int)$id, $perPage),
            'transaction' => $transaction,
        ];

        return $this->render('activity-logs');
    }

    public function paypapStatus(string $id): Renderable
    {
        $transaction = $this->service->getById($id);

        if (!$transaction) {
            abort(404);
        }

        $paypapStatus = null;
        $error = null;

        if ($transaction->payment_method->value === 'paypap') {
            $depositId = $transaction->getAttribute('deposit_id') ?? $transaction->deposit_id ?? null;

            if ($depositId) {
                $result = $this->paypap->getBankDeposit($depositId);

                if ($result['status']) {
                    $paypapStatus = $result['data'];
                } else {
                    $error = $result['message'] ?? __('Failed to fetch Paypap status');
                }
            } else {
                $error = __('This transaction does not have a deposit ID');
            }
        } else {
            $error = __('This transaction is not a Paypap transaction');
        }

        $this->data = [
            'module' => __('Paypap Transaction Status'),
            'title' => __('Status'),
            'transaction' => $transaction,
            'paypapStatus' => $paypapStatus,
            'error' => $error,
        ];

        return $this->render('paypap-status');
    }
}
