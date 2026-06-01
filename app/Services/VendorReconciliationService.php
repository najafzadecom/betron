<?php

namespace App\Services;

use App\Enums\PaymentProvider;
use App\Enums\TransactionStatus;
use App\Enums\VendorDepositTransactionType;
use App\Enums\VendorReconciliationStatus;
use App\Enums\WithdrawalStatus;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\VendorDailyReconciliation;
use App\Models\VendorDepositTransaction;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VendorReconciliationService
{
    public static function calculateKalan(array $fields): float
    {
        return round(
            (float) ($fields['devir'] ?? 0)
            + (float) ($fields['yatirim'] ?? 0)
            + (float) ($fields['man_yatirim'] ?? 0)
            - (float) ($fields['cekim'] ?? 0)
            - (float) ($fields['man_cekim'] ?? 0)
            - (float) ($fields['y_komisyon'] ?? 0)
            - (float) ($fields['teslimat'] ?? 0)
            - (float) ($fields['t_komisyon'] ?? 0),
            2
        );
    }

    public function getDevirForDate(int $vendorId, string $date): float
    {
        $previous = VendorDailyReconciliation::query()
            ->where('vendor_id', $vendorId)
            ->where('reconciliation_date', '<', $date)
            ->where('status', VendorReconciliationStatus::Approved)
            ->orderByDesc('reconciliation_date')
            ->first();

        return $previous ? (float) $previous->kalan : 0.0;
    }

    public function computeSuggestedValues(int $vendorId, string $date): array
    {
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

        $yatirim = (float) Transaction::query()
            ->where('vendor_id', $vendorId)
            ->whereNull('deleted_at')
            ->whereBetween('accepted_at', [$from, $to])
            ->where('paid_status', true)
            ->whereIn('status', $confirmedTransactionStatuses)
            ->where('payment_method', '!=', PaymentProvider::Manual->value)
            ->sum('amount');

        $manYatirimTransactions = (float) Transaction::query()
            ->where('vendor_id', $vendorId)
            ->whereNull('deleted_at')
            ->whereBetween('accepted_at', [$from, $to])
            ->where('paid_status', true)
            ->whereIn('status', $confirmedTransactionStatuses)
            ->where('payment_method', PaymentProvider::Manual->value)
            ->sum('amount');

        $manYatirimDeposit = (float) VendorDepositTransaction::query()
            ->where('vendor_id', $vendorId)
            ->where('type', VendorDepositTransactionType::ADD->value)
            ->whereNull('transaction_id')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $cekim = (float) Withdrawal::query()
            ->where('vendor_id', $vendorId)
            ->whereNull('deleted_at')
            ->whereBetween('accepted_at', [$from, $to])
            ->where('paid_status', true)
            ->whereIn('status', $confirmedWithdrawalStatuses)
            ->where(function ($q) {
                $q->whereNull('payment_method')
                    ->orWhere('payment_method', '!=', PaymentProvider::Manual->value);
            })
            ->sum('amount');

        $manCekimWithdrawals = (float) Withdrawal::query()
            ->where('vendor_id', $vendorId)
            ->whereNull('deleted_at')
            ->whereBetween('accepted_at', [$from, $to])
            ->where('paid_status', true)
            ->whereIn('status', $confirmedWithdrawalStatuses)
            ->where('payment_method', PaymentProvider::Manual->value)
            ->sum('amount');

        $manCekimDeposit = (float) VendorDepositTransaction::query()
            ->where('vendor_id', $vendorId)
            ->where('type', VendorDepositTransactionType::SUBTRACT->value)
            ->whereNull('withdrawal_id')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $transactionFee = (float) ($vendor?->transaction_fee ?? 0);
        $totalTransactionAmount = (float) Transaction::query()
            ->where('vendor_id', $vendorId)
            ->whereNull('deleted_at')
            ->whereBetween('accepted_at', [$from, $to])
            ->where('paid_status', true)
            ->whereIn('status', $confirmedTransactionStatuses)
            ->sum('amount');

        $yKomisyon = round($totalTransactionAmount * $transactionFee / 100, 2);

        $teslimat = 0.0;
        $settlementFee = (float) ($vendor?->settlement_fee ?? 0);
        $tKomisyon = round($teslimat * $settlementFee / 100, 2);

        $devir = $this->getDevirForDate($vendorId, $date);

        $fields = [
            'devir' => $devir,
            'yatirim' => round($yatirim, 2),
            'man_yatirim' => round($manYatirimTransactions + $manYatirimDeposit, 2),
            'cekim' => round($cekim, 2),
            'man_cekim' => round($manCekimWithdrawals + $manCekimDeposit, 2),
            'y_komisyon' => $yKomisyon,
            'teslimat' => $teslimat,
            't_komisyon' => $tKomisyon,
        ];

        $fields['kalan'] = self::calculateKalan($fields);

        return $fields;
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
            'cekim' => $suggested['cekim'],
            'man_cekim' => $suggested['man_cekim'],
            'y_komisyon' => $suggested['y_komisyon'],
            'teslimat' => $suggested['teslimat'],
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

        $fields = [
            'devir' => (float) ($data['devir'] ?? $record->devir),
            'yatirim' => (float) ($data['yatirim'] ?? $record->yatirim),
            'man_yatirim' => (float) ($data['man_yatirim'] ?? $record->man_yatirim),
            'cekim' => (float) ($data['cekim'] ?? $record->cekim),
            'man_cekim' => (float) ($data['man_cekim'] ?? $record->man_cekim),
            'y_komisyon' => (float) ($data['y_komisyon'] ?? $record->y_komisyon),
            'teslimat' => (float) ($data['teslimat'] ?? $record->teslimat),
            't_komisyon' => (float) ($data['t_komisyon'] ?? $record->t_komisyon),
        ];

        $record->fill($fields);
        $record->kalan = self::calculateKalan($fields);
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

        $devir = (float) $record->devir;
        $fields = array_merge($suggested, ['devir' => $devir]);
        $record->fill([
            'yatirim' => $fields['yatirim'],
            'man_yatirim' => $fields['man_yatirim'],
            'cekim' => $fields['cekim'],
            'man_cekim' => $fields['man_cekim'],
            'y_komisyon' => $fields['y_komisyon'],
            'teslimat' => $record->teslimat,
            't_komisyon' => $record->t_komisyon,
        ]);
        $record->kalan = self::calculateKalan($record->only([
            'devir', 'yatirim', 'man_yatirim', 'cekim', 'man_cekim', 'y_komisyon', 'teslimat', 't_komisyon',
        ]));
        $record->save();

        return $record->fresh(['vendor', 'approver', 'archiver']);
    }

    public function approve(int $id): VendorDailyReconciliation
    {
        $record = VendorDailyReconciliation::query()->findOrFail($id);

        if ($record->status !== VendorReconciliationStatus::Draft) {
            throw new \RuntimeException(__('Only draft reconciliations can be approved.'));
        }

        $record->kalan = self::calculateKalan($record->only([
            'devir', 'yatirim', 'man_yatirim', 'cekim', 'man_cekim', 'y_komisyon', 'teslimat', 't_komisyon',
        ]));
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
}
