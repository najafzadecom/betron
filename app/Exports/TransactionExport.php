<?php

namespace App\Exports;

use App\Services\TransactionService as Service;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class TransactionExport implements FromCollection, WithMapping, WithHeadings, WithStrictNullComparison
{
    private Service $service;

    public function __construct(
        Service $service
    ) {
        $this->service = $service;
    }

    /**
     * @return iterable
     */
    public function collection(): iterable
    {
        return $this->service->getAll('created_at', 'DESC');
    }

    /**
     * Rows
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->uuid,
            $row->user_id,
            $row->first_name,
            $row->last_name,
            $row->phone,
            $row->amount,
            $row->fee,
            $row->fee_amount,
            $row->order_id,
            $row->currency?->value,
            $row->wallet_id,
            $row->receiver_name,
            $row->receiver_iban,
            $row->bank_id,
            $row->bank_name ?? null,
            $row->client_ip,
            $row->status?->label(),
            $row->paid_status ? 'Paid' : 'Unpaid',
            $row->created_at,
            $row->updated_at
        ];
    }

    /**
     * Headings
     */
    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'User ID',
            'First Name',
            'Last Name',
            'Phone',
            'Amount',
            'Fee (%)',
            'Fee Amount',
            'Order ID',
            'Currency',
            'Wallet ID',
            'Receiver Name',
            'Receiver IBAN',
            'Bank ID',
            'Bank Name',
            'Client IP',
            'Status',
            'Paid Status',
            'Created At',
            'Updated At'
        ];
    }
}
