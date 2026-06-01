<?php

namespace App\Models;

use App\Enums\VendorReconciliationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorDailyReconciliation extends Model
{
    protected $fillable = [
        'vendor_id',
        'reconciliation_date',
        'devir',
        'yatirim',
        'man_yatirim',
        'cekim',
        'man_cekim',
        'y_komisyon',
        'teslimat',
        't_komisyon',
        'kalan',
        'status',
        'notes',
        'approved_at',
        'approved_by',
        'archived_at',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'reconciliation_date' => 'date',
            'devir' => 'float',
            'yatirim' => 'float',
            'man_yatirim' => 'float',
            'cekim' => 'float',
            'man_cekim' => 'float',
            'y_komisyon' => 'float',
            'teslimat' => 'float',
            't_komisyon' => 'float',
            'kalan' => 'float',
            'status' => VendorReconciliationStatus::class,
            'approved_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function archiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}
