<?php

namespace App\Http\Controllers\Vendor;

use App\Services\VendorReconciliationService;
use App\Services\VendorService;
use Illuminate\Contracts\Support\Renderable;

class VendorReconciliationController extends BaseController
{
    public function __construct(
        protected VendorReconciliationService $reconciliationService,
        protected VendorService $vendorService,
    ) {
        $this->module = 'reconciliations';
    }

    public function index(): Renderable
    {
        $vendorId = $this->getVendorId();
        if (!$vendorId) {
            abort(404, __('Vendor not found'));
        }

        $vendor = $this->vendorService->getById($vendorId);
        if (!$vendor) {
            abort(404, __('Vendor not found'));
        }

        $year = (int) request('year', (int) date('Y'));
        $month = (int) request('month', (int) date('m'));
        $date = VendorReconciliationService::resolvePanelDate($year, $month, request('date'));

        $view = $this->reconciliationService->getVendorPanelView($vendorId, $date);

        $this->data = [
            'module' => __('Vendor Reconciliation'),
            'title' => __('Daily Reconciliation'),
            'vendor' => $vendor,
            'year' => $view['year'],
            'month' => $view['month'],
            'date' => $view['date'],
            'days' => $view['days'],
            'exists' => $view['exists'],
            'reconciliation' => $view['reconciliation'],
            'values' => $view['values'],
        ];

        return $this->render('index');
    }
}
