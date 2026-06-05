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
        'yatirim_iptal',
        'cekim',
        'man_cekim',
        'cekim_iptal',
        'y_komisyon_oran',
        'y_komisyon',
        'teslimat',
        't_komisyon_oran',
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
            'yatirim_iptal' => 'float',
            'cekim' => 'float',
            'man_cekim' => 'float',
            'cekim_iptal' => 'float',
            'y_komisyon_oran' => 'float',
            'y_komisyon' => 'float',
            'teslimat' => 'float',
            't_komisyon_oran' => 'float',
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
