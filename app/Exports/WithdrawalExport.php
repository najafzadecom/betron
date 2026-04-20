<?php

namespace App\Exports;

use App\Services\WithdrawalService as Service;
use App\Models\Withdrawal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class WithdrawalExport implements FromCollection, WithMapping, WithHeadings, WithStrictNullComparison
{
    private Service $service;
    private $vendorId;

    public function __construct(
        Service $service,
        $vendorId
    ) {
        $this->service = $service;
        $this->vendorId = $vendorId;
    }
    /**
     * @return iterable
     */
    public function collection(): iterable
    {
        // return $this->service->getAll('created_at', 'DESC'); //Todo: Fix By Vendor Or All
       if ($this->vendorId) {
        return Withdrawal::where('vendor_id', $this->vendorId)->orderBy('created_at', 'DESC')->get();
       } else {
        return Withdrawal::orderBy('created_at', 'DESC')->get();
       }
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
            $row->wallet_id,
            $row->sender_name,
            $row->sender_iban,
            $row->first_name,
            $row->last_name,
            $row->bank_id,
            $row->bank_name,
            $row->iban,
            $row->amount,
            $row->fee,
            $row->fee_amount,
            $row->order_id,
            $row->currency?->value,
            $row->status->label(),
            $row->site_id,
            $row->paid_status ? 'Paid' : 'Unpaid',
            $row->manual ? 'Yes' : 'No',
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
            'Wallet ID',
            'Sender Name',
            'Sender IBAN',
            'First Name',
            'Last Name',
            'Bank ID',
            'Bank Name',
            'IBAN',
            'Amount',
            'Fee (%)',
            'Fee Amount',
            'Order ID',
            'Currency',
            'Status',
            'Site ID',
            'Paid Status',
            'Manual',
            'Created At',
            'Updated At'
        ];
    }
}
