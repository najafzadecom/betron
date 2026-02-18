<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class VendorTransactionExport implements FromCollection, WithMapping, WithHeadings, WithStrictNullComparison
{
    private array $walletIds;

    public function __construct(array $walletIds)
    {
        $this->walletIds = $walletIds;
    }

    public function collection(): iterable
    {
        $query = Transaction::whereIn('wallet_id', $this->walletIds)
            ->with(['wallet', 'site', 'bank']);

        $request = request();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('order_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('wallet_id') && in_array($request->get('wallet_id'), $this->walletIds)) {
            $query->where('wallet_id', $request->get('wallet_id'));
        }

        if ($request->filled('created_from')) {
            $query->where('created_at', '>=', $request->get('created_from'));
        }

        if ($request->filled('created_to')) {
            $query->where('created_at', '<=', $request->get('created_to') . ' 23:59:59');
        }

        return $query->orderBy('created_at', 'DESC')->get();
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->uuid,
            $row->first_name,
            $row->last_name,
            $row->phone,
            $row->amount,
            $row->fee,
            $row->fee_amount,
            $row->order_id,
            $row->currency?->value,
            $row->wallet?->name,
            $row->wallet?->iban,
            $row->bank?->name ?? $row->bank_name ?? null,
            $row->client_ip,
            $row->status?->label(),
            $row->paid_status ? 'Paid' : 'Unpaid',
            $row->created_at,
            $row->updated_at
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'First Name',
            'Last Name',
            'Phone',
            'Amount',
            'Fee (%)',
            'Fee Amount',
            'Order ID',
            'Currency',
            'Wallet Name',
            'Wallet IBAN',
            'Bank Name',
            'Client IP',
            'Status',
            'Paid Status',
            'Created At',
            'Updated At'
        ];
    }
}
