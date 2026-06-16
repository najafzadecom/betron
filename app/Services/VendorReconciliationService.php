<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\VendorReconciliationStatus;
use App\Enums\WithdrawalStatus;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\VendorDailyReconciliation;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VendorReconciliationService
{
    public const DEFAULT_COMMISSION_RATE = 4.0;

    public function __construct(
        protected VendorService $vendorService,
    ) {
    }

    public static function calculateYKomisyon(float $yatirim, float $manYatirim, float $oran): float
    {
        return round(($yatirim + $manYatirim) * $oran / 100, 2);
    }

    public static function calculateTKomisyon(float $teslimat, float $oran): float
    {
        return round($teslimat * $oran / 100, 2);
    }

    public static function defaultDepositCommissionRate(?Vendor $vendor): float
    {
        $fee = (float) ($vendor?->transaction_fee ?? 0);

        return $fee > 0 ? $fee : self::DEFAULT_COMMISSION_RATE;
    }

    public static function defaultSettlementCommissionRate(?Vendor $vendor): float
    {
        $fee = (float) ($vendor?->settlement_fee ?? 0);

        return $fee > 0 ? $fee : self::DEFAULT_COMMISSION_RATE;
    }

    /**
     * @param  array<string, float>  $amounts
     * @return array<string, float>
     */
    public static function applyCommissionAmounts(array $amounts, float $yKomisyonOran, float $tKomisyonOran): array
    {
        $amounts['y_komisyon'] = self::calculateYKomisyon(
            (float) ($amounts['yatirim'] ?? 0),
            (float) ($amounts['man_yatirim'] ?? 0),
            $yKomisyonOran
        );
        $amounts['t_komisyon'] = self::calculateTKomisyon(
            (float) ($amounts['teslimat'] ?? 0),
            $tKomisyonOran
        );
        $amounts['kalan'] = self::calculateKalan($amounts);

        return $amounts;
    }

    /**
     * kalan = devir + yatirim + man_yatirim - yatirim_iptal + cekim_iptal
     *         - cekim - man_cekim - y_komisyon - teslimat - t_komisyon
     */
    public static function calculateKalan(array $fields): float
    {
        return round(
            (float) ($fields['devir'] ?? 0)
            + (float) ($fields['yatirim'] ?? 0)
            + (float) ($fields['man_yatirim'] ?? 0)
            - (float) ($fields['yatirim_iptal'] ?? 0)
            - (float) ($fields['cekim'] ?? 0)
            - (float) ($fields['man_cekim'] ?? 0)
            + (float) ($fields['cekim_iptal'] ?? 0)
            - (float) ($fields['y_komisyon'] ?? 0)
            - (float) ($fields['teslimat'] ?? 0)
            - (float) ($fields['t_komisyon'] ?? 0),
            2
        );
    }

    /**
     * @param  array<int>  $vendorIds
     */
    public function getDevirForDate(array $vendorIds, string $date): float
    {
        $previous = VendorDailyReconciliation::query()
            ->whereIn('vendor_id', $vendorIds)
            ->where('reconciliation_date', '<', $date)
            ->where('status', VendorReconciliationStatus::Approved)
            ->orderByDesc('reconciliation_date')
            ->get(['vendor_id', 'kalan']);

        $sum = 0.0;
        $picked = [];

        foreach ($previous as $record) {
            if (isset($picked[$record->vendor_id])) {
                continue;
            }

            $picked[$record->vendor_id] = true;
            $sum += (float) $record->kalan;
        }

        return round($sum, 2);
    }

    public function computeSuggestedValues(int $vendorId, string $date): array
    {
        $vendorIds = array_merge([$vendorId], $this->vendorService->getDescendants($vendorId));
        $vendor = Vendor::query()->find($vendorId);
        $from = $date . ' 00:00:00';
        $to = $date . ' 23:59:59';

        $confirmedTransactionStatuses = [
            TransactionStatus::ManualConfirmed->value,
            TransactionStatus::AutoConfirmed->value,
        ];

        $confirmedWithdrawalStatuses = [
            WithdrawalStatus::ManualConfirmed->value,
            WithdrawalStatus::AutoConfirmed->value,
        ];

        // Tüm onaylı yatırımlar (manuel/otomatik onay fark etmez) → yatirim. man_* alanları elle girilir.
        $yatirim = (float) Transaction::query()
            ->whereIn('vendor_id', $vendorIds)
            ->whereNull('deleted_at')
            ->whereBetween('accepted_at', [$from, $to])
            ->where('paid_status', true)
            ->whereIn('status', $confirmedTransactionStatuses)
            ->sum('amount');

        $cekim = (float) Withdrawal::query()
            ->whereIn('vendor_id', $vendorIds)
            ->whereNull('deleted_at')
            ->whereBetween('accepted_at', [$from, $to])
            ->where('paid_status', true)
            ->whereIn('status', $confirmedWithdrawalStatuses)
            ->sum('amount');

        $teslimat = 0.0;
        $devir = $this->getDevirForDate($vendorIds, $date);
        $yKomisyonOran = self::defaultDepositCommissionRate($vendor);
        $tKomisyonOran = self::defaultSettlementCommissionRate($vendor);

        $fields = [
            'devir' => $devir,
            'yatirim' => round($yatirim, 2),
            'man_yatirim' => 0.0,
            'yatirim_iptal' => 0.0,
            'cekim' => round($cekim, 2),
            'man_cekim' => 0.0,
            'cekim_iptal' => 0.0,
            'y_komisyon_oran' => $yKomisyonOran,
            'teslimat' => $teslimat,
            't_komisyon_oran' => $tKomisyonOran,
        ];

        return self::applyCommissionAmounts($fields, $yKomisyonOran, $tKomisyonOran);
    }

    public function listForVendorMonth(int $vendorId, int $year, int $month): Collection
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $records = VendorDailyReconciliation::query()
            ->where('vendor_id', $vendorId)
            ->whereBetween('reconciliation_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($r) => $r->reconciliation_date->format('Y-m-d'));

        $days = collect();
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $key = $day->format('Y-m-d');
            $record = $records->get($key);
            $days->push([
                'date' => $key,
                'label' => $day->format('d.m.Y'),
                'weekday' => $day->translatedFormat('D'),
                'status' => $record?->status?->value,
                'kalan' => $record?->kalan,
                'id' => $record?->id,
            ]);
        }

        return $days;
    }

    public function findOrCreateDraft(int $vendorId, string $date): VendorDailyReconciliation
    {
        $existing = VendorDailyReconciliation::query()
            ->where('vendor_id', $vendorId)
            ->whereDate('reconciliation_date', $date)
            ->first();

        if ($existing) {
            return $existing;
        }

        $suggested = $this->computeSuggestedValues($vendorId, $date);

        return VendorDailyReconciliation::query()->create([
            'vendor_id' => $vendorId,
            'reconciliation_date' => $date,
            'devir' => $suggested['devir'],
            'yatirim' => $suggested['yatirim'],
            'man_yatirim' => $suggested['man_yatirim'],
            'yatirim_iptal' => $suggested['yatirim_iptal'],
            'cekim' => $suggested['cekim'],
            'man_cekim' => $suggested['man_cekim'],
            'cekim_iptal' => $suggested['cekim_iptal'],
            'y_komisyon_oran' => $suggested['y_komisyon_oran'],
            'y_komisyon' => $suggested['y_komisyon'],
            'teslimat' => $suggested['teslimat'],
            't_komisyon_oran' => $suggested['t_komisyon_oran'],
            't_komisyon' => $suggested['t_komisyon'],
            'kalan' => $suggested['kalan'],
            'status' => VendorReconciliationStatus::Draft,
        ]);
    }

    public function updateDraft(int $id, array $data): VendorDailyReconciliation
    {
        $record = VendorDailyReconciliation::query()->findOrFail($id);

        if ($record->status !== VendorReconciliationStatus::Draft) {
            throw new \RuntimeException(__('Only draft reconciliations can be edited.'));
        }

        $yKomisyonOran = (float) ($data['y_komisyon_oran'] ?? $record->y_komisyon_oran ?? self::DEFAULT_COMMISSION_RATE);
        $tKomisyonOran = (float) ($data['t_komisyon_oran'] ?? $record->t_komisyon_oran ?? self::DEFAULT_COMMISSION_RATE);

        $fields = [
            'devir' => (float) ($data['devir'] ?? $record->devir),
            'yatirim' => (float) ($data['yatirim'] ?? $record->yatirim),
            'man_yatirim' => (float) ($data['man_yatirim'] ?? $record->man_yatirim),
            'yatirim_iptal' => (float) ($data['yatirim_iptal'] ?? $record->yatirim_iptal),
            'cekim' => (float) ($data['cekim'] ?? $record->cekim),
            'man_cekim' => (float) ($data['man_cekim'] ?? $record->man_cekim),
            'cekim_iptal' => (float) ($data['cekim_iptal'] ?? $record->cekim_iptal),
            'teslimat' => (float) ($data['teslimat'] ?? $record->teslimat),
        ];

        $fields = self::applyCommissionAmounts($fields, $yKomisyonOran, $tKomisyonOran);

        $record->fill($fields);
        $record->y_komisyon_oran = $yKomisyonOran;
        $record->t_komisyon_oran = $tKomisyonOran;
        $record->kalan = $fields['kalan'];
        $record->notes = $data['notes'] ?? $record->notes;
        $record->save();

        return $record->fresh(['vendor', 'approver', 'archiver']);
    }

    public function refreshFromSystem(int $id): VendorDailyReconciliation
    {
        $record = VendorDailyReconciliation::query()->findOrFail($id);

        if ($record->status !== VendorReconciliationStatus::Draft) {
            throw new \RuntimeException(__('Only draft reconciliations can be refreshed.'));
        }

        $suggested = $this->computeSuggestedValues(
            $record->vendor_id,
            $record->reconciliation_date->format('Y-m-d')
        );

        $devir = (float) $suggested['devir'];
        $yKomisyonOran = (float) ($record->y_komisyon_oran ?: $suggested['y_komisyon_oran']);
        $tKomisyonOran = (float) ($record->t_komisyon_oran ?: $suggested['t_komisyon_oran']);

        $fields = [
            'devir' => $devir,
            'yatirim' => $suggested['yatirim'],
            'man_yatirim' => (float) $record->man_yatirim,
            'yatirim_iptal' => (float) $record->yatirim_iptal,
            'cekim' => $suggested['cekim'],
            'man_cekim' => (float) $record->man_cekim,
            'cekim_iptal' => (float) $record->cekim_iptal,
            'teslimat' => (float) $record->teslimat,
        ];

        $fields = self::applyCommissionAmounts($fields, $yKomisyonOran, $tKomisyonOran);

        $record->fill($fields);
        $record->y_komisyon_oran = $yKomisyonOran;
        $record->t_komisyon_oran = $tKomisyonOran;
        $record->kalan = $fields['kalan'];
        $record->save();

        return $record->fresh(['vendor', 'approver', 'archiver']);
    }

    public function approve(int $id): VendorDailyReconciliation
    {
        $record = VendorDailyReconciliation::query()->findOrFail($id);

        if ($record->status !== VendorReconciliationStatus::Draft) {
            throw new \RuntimeException(__('Only draft reconciliations can be approved.'));
        }

        $fields = self::applyCommissionAmounts(
            $record->only(['devir', 'yatirim', 'man_yatirim', 'yatirim_iptal', 'cekim', 'man_cekim', 'cekim_iptal', 'teslimat']),
            (float) ($record->y_komisyon_oran ?? self::DEFAULT_COMMISSION_RATE),
            (float) ($record->t_komisyon_oran ?? self::DEFAULT_COMMISSION_RATE)
        );
        $record->fill($fields);
        $record->kalan = $fields['kalan'];
        $record->status = VendorReconciliationStatus::Approved;
        $record->approved_at = now();
        $record->approved_by = auth('web')->id();
        $record->save();

        return $record->fresh(['vendor', 'approver', 'archiver']);
    }

    public function archive(int $id): VendorDailyReconciliation
    {
        $record = VendorDailyReconciliation::query()->findOrFail($id);

        if ($record->status !== VendorReconciliationStatus::Approved) {
            throw new \RuntimeException(__('Only approved reconciliations can be archived.'));
        }

        $record->status = VendorReconciliationStatus::Archived;
        $record->archived_at = now();
        $record->archived_by = auth('web')->id();
        $record->save();

        return $record->fresh(['vendor', 'approver', 'archiver']);
    }

    public function reopenToDraft(int $id): VendorDailyReconciliation
    {
        $record = VendorDailyReconciliation::query()->findOrFail($id);

        if ($record->status === VendorReconciliationStatus::Archived) {
            throw new \RuntimeException(__('Archived reconciliations cannot be reopened.'));
        }

        if ($record->status !== VendorReconciliationStatus::Approved) {
            throw new \RuntimeException(__('Only approved reconciliations can be returned to draft.'));
        }

        $record->status = VendorReconciliationStatus::Draft;
        $record->approved_at = null;
        $record->approved_by = null;
        $record->save();

        return $record->fresh(['vendor', 'approver', 'archiver']);
    }

    /**
     * @return array{
     *     year: int,
     *     month: int,
     *     date: string,
     *     days: Collection,
     *     exists: bool,
     *     reconciliation: VendorDailyReconciliation|null,
     *     values: array<string, float>
     * }
     */
    public function getVendorPanelView(int $vendorId, ?string $date = null): array
    {
        $date = $date ?: date('Y-m-d');
        $carbon = Carbon::parse($date);
        $year = (int) $carbon->year;
        $month = (int) $carbon->month;

        $reconciliation = VendorDailyReconciliation::query()
            ->where('vendor_id', $vendorId)
            ->whereDate('reconciliation_date', $date)
            ->first();

        $numericFields = [
            'devir', 'yatirim', 'man_yatirim', 'yatirim_iptal', 'cekim', 'man_cekim', 'cekim_iptal',
            'y_komisyon_oran', 'y_komisyon', 'teslimat', 't_komisyon_oran', 't_komisyon', 'kalan',
        ];

        $values = array_fill_keys($numericFields, 0.0);
        if ($reconciliation) {
            foreach ($numericFields as $field) {
                $values[$field] = (float) $reconciliation->{$field};
            }
        }

        return [
            'year' => $year,
            'month' => $month,
            'date' => $date,
            'days' => $this->listForVendorMonth($vendorId, $year, $month),
            'exists' => $reconciliation !== null,
            'reconciliation' => $reconciliation,
            'values' => $values,
        ];
    }

    public static function resolvePanelDate(int $year, int $month, ?string $requestedDate = null): string
    {
        if ($requestedDate) {
            $parsed = Carbon::parse($requestedDate);
            if ((int) $parsed->year === $year && (int) $parsed->month === $month) {
                return $parsed->format('Y-m-d');
            }
        }

        $today = Carbon::today();
        if ((int) $today->year === $year && (int) $today->month === $month) {
            return $today->format('Y-m-d');
        }

        return Carbon::create($year, $month, 1)->format('Y-m-d');
    }

    /**
     * @return array{date: string, rows: array<int, array<string, mixed>>, totals: array<string, float>, missing_count: int}
     */
    public function getGeneralSummaryForDate(string $date): array
    {
        $vendors = Vendor::query()
            ->withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->with('parent:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $reconciliations = VendorDailyReconciliation::query()
            ->whereDate('reconciliation_date', $date)
            ->get()
            ->keyBy('vendor_id');

        $numericFields = [
            'devir', 'yatirim', 'man_yatirim', 'yatirim_iptal', 'cekim', 'man_cekim', 'cekim_iptal',
            'y_komisyon', 'teslimat', 't_komisyon', 'kalan',
        ];

        $totals = array_fill_keys($numericFields, 0.0);
        $rows = [];
        $missingCount = 0;

        foreach ($vendors as $vendor) {
            $record = $reconciliations->get($vendor->id);
            $exists = $record !== null;

            if (!$exists) {
                $missingCount++;
            }

            $values = [];
            foreach ($numericFields as $field) {
                $values[$field] = $exists ? (float) $record->{$field} : 0.0;
                $totals[$field] += $values[$field];
            }

            $rows[] = [
                'vendor_id' => $vendor->id,
                'vendor_name' => $vendor->name,
                'parent_name' => $vendor->parent?->name,
                'exists' => $exists,
                'status' => $record?->status,
                'values' => $values,
            ];
        }

        foreach ($totals as $key => $value) {
            $totals[$key] = round($value, 2);
        }

        return [
            'date' => $date,
            'rows' => $rows,
            'totals' => $totals,
            'missing_count' => $missingCount,
        ];
    }
}
