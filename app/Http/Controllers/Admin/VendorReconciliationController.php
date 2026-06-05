<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Update\VendorReconciliationRequest;
use App\Models\VendorDailyReconciliation;
use App\Services\VendorReconciliationService;
use App\Services\VendorService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorReconciliationController extends BaseController
{
    public function __construct(
        protected VendorReconciliationService $reconciliationService,
        protected VendorService $vendorService,
    ) {
        $this->middleware('permission:vendor-reconciliations-index', ['only' => ['index', 'summary']]);
        $this->middleware('permission:vendor-reconciliations-edit', [
            'only' => ['update', 'loadDay', 'refresh', 'approve', 'archive', 'reopen'],
        ]);
        $this->module = 'vendor-reconciliations';
    }

    public function index(): Renderable
    {
        $vendorId = (int) request('vendor_id', 0);
        $parentVendorId = (int) request('parent_vendor_id', 0);
        $year = (int) request('year', (int) date('Y'));
        $month = (int) request('month', (int) date('m'));
        $date = request('date', date('Y-m-d'));

        if ($vendorId && !$parentVendorId) {
            $vendor = $this->vendorService->getById($vendorId);
            if ($vendor) {
                $parentVendorId = $vendor->parent_id ?: $vendorId;
            }
        }

        $topLevelVendors = $this->vendorService->getTopLevelVendors();
        $childVendors = $parentVendorId
            ? $this->vendorService->getAccessibleVendorsForParent($parentVendorId)
            : collect([]);

        $days = collect([]);
        $reconciliation = null;
        $vendor = null;

        if ($vendorId) {
            $vendor = $this->vendorService->getById($vendorId);
            $days = $this->reconciliationService->listForVendorMonth($vendorId, $year, $month);

            if ($date) {
                $reconciliation = VendorDailyReconciliation::query()
                    ->where('vendor_id', $vendorId)
                    ->whereDate('reconciliation_date', $date)
                    ->with(['vendor', 'approver', 'archiver'])
                    ->first();
            }
        }

        $this->data = [
            'module' => __('Vendor Reconciliation'),
            'title' => __('Daily Reconciliation'),
            'topLevelVendors' => $topLevelVendors,
            'childVendors' => $childVendors,
            'parentVendorId' => $parentVendorId,
            'vendorId' => $vendorId,
            'vendor' => $vendor,
            'year' => $year,
            'month' => $month,
            'date' => $date,
            'days' => $days,
            'reconciliation' => $reconciliation,
        ];

        return $this->render('index');
    }

    public function summary(): Renderable
    {
        $date = request('date', date('Y-m-d'));
        $summary = $this->reconciliationService->getGeneralSummaryForDate($date);

        $this->data = [
            'module' => __('General Reconciliation'),
            'title' => __('General Reconciliation'),
            'date' => $date,
            'rows' => $summary['rows'],
            'totals' => $summary['totals'],
            'missingCount' => $summary['missing_count'],
        ];

        return $this->render('summary');
    }

    public function loadDay(Request $request): RedirectResponse
    {
        $request->validate([
            'vendor_id' => 'required|integer|exists:vendors,id',
            'date' => 'required|date_format:Y-m-d',
        ]);

        $this->reconciliationService->findOrCreateDraft(
            (int) $request->input('vendor_id'),
            $request->input('date')
        );

        return redirect()->route('admin.vendor-reconciliations.index', [
            'vendor_id' => $request->input('vendor_id'),
            'parent_vendor_id' => $request->input('parent_vendor_id'),
            'year' => $request->input('year', date('Y')),
            'month' => $request->input('month', date('m')),
            'date' => $request->input('date'),
        ]);
    }

    public function update(VendorReconciliationRequest $request, int $id): RedirectResponse
    {
        try {
            $this->reconciliationService->updateDraft($id, $request->validated());
        } catch (\RuntimeException $e) {
            return $this->redirectError($e->getMessage());
        }

        $record = VendorDailyReconciliation::query()->findOrFail($id);

        return $this->redirectToReconciliation($record, __('Reconciliation saved.'));
    }

    public function refresh(int $id): RedirectResponse
    {
        try {
            $record = $this->reconciliationService->refreshFromSystem($id);
        } catch (\RuntimeException $e) {
            return $this->redirectError($e->getMessage());
        }

        return $this->redirectToReconciliation($record, __('Values refreshed from system.'));
    }

    public function approve(int $id): RedirectResponse
    {
        try {
            $record = $this->reconciliationService->approve($id);
        } catch (\RuntimeException $e) {
            return $this->redirectError($e->getMessage());
        }

        return $this->redirectToReconciliation($record, __('Reconciliation approved.'));
    }

    public function archive(int $id): RedirectResponse
    {
        try {
            $record = $this->reconciliationService->archive($id);
        } catch (\RuntimeException $e) {
            return $this->redirectError($e->getMessage());
        }

        return $this->redirectToReconciliation($record, __('Reconciliation archived.'));
    }

    public function reopen(int $id): RedirectResponse
    {
        try {
            $record = $this->reconciliationService->reopenToDraft($id);
        } catch (\RuntimeException $e) {
            return $this->redirectError($e->getMessage());
        }

        return $this->redirectToReconciliation($record, __('Reconciliation returned to draft.'));
    }

    private function redirectToReconciliation(VendorDailyReconciliation $record, string $message): RedirectResponse
    {
        $vendor = $record->vendor;
        $parentId = $vendor?->parent_id ?: $record->vendor_id;

        return redirect()
            ->route('admin.vendor-reconciliations.index', array_filter([
                'vendor_id' => $record->vendor_id,
                'parent_vendor_id' => request('parent_vendor_id', $parentId),
                'date' => $record->reconciliation_date->format('Y-m-d'),
                'year' => $record->reconciliation_date->year,
                'month' => $record->reconciliation_date->month,
            ]))
            ->with(['success' => true, 'message' => $message]);
    }

    public function calculate(Request $request): JsonResponse
    {
        $yOran = (float) $request->input('y_komisyon_oran', VendorReconciliationService::DEFAULT_COMMISSION_RATE);
        $tOran = (float) $request->input('t_komisyon_oran', VendorReconciliationService::DEFAULT_COMMISSION_RATE);

        $fields = VendorReconciliationService::applyCommissionAmounts(
            $request->only(['devir', 'yatirim', 'man_yatirim', 'yatirim_iptal', 'cekim', 'man_cekim', 'cekim_iptal', 'teslimat']),
            $yOran,
            $tOran
        );

        $this->data = [
            'y_komisyon' => $fields['y_komisyon'],
            't_komisyon' => $fields['t_komisyon'],
            'kalan' => $fields['kalan'],
        ];

        return $this->json();
    }
}
