<?php

namespace App\Models\Scopes;

use App\Services\VendorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WalletScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply scope for GET requests on specific routes
        $isBulkUpdate = request()->routeIs('vendor.wallets.bulk-update-status');

        if ((!request()->isMethod('GET') && !$isBulkUpdate) || !$this->shouldApplyScope()) {
            return;
        }

        $request = request();

        // Global search across multiple fields
        if ($request->filled('search')) {
            $search = $request->get('search');
            $builder->where(function ($query) use ($search) {
                $query->where('name', 'ilike', "%{$search}%")
                    ->orWhere('iban', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhere('currency', 'ilike', "%{$search}%")
                    ->orWhereHas('bank', function ($query) use ($search) {
                        $query->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        // Individual field filters

        if ($request->filled('name')) {
            $builder->where('name', 'ilike', '%' . $request->get('name') . '%');
        }

        if ($request->filled('iban')) {
            $builder->where('iban', 'ilike', '%' . $request->get('iban') . '%');
        }

        if ($request->filled('bank_id')) {
            $bankId = $request->get('bank_id');
            // Only apply filter if bank_id is numeric (unsignedBigInteger column)
            if (is_numeric($bankId)) {
                $builder->where('bank_id', (int)$bankId);
            }
        }

        // Vendor filters (for admin panel)
        if ($request->filled('parent_vendor_id') || $request->filled('vendor_id')) {
            $vendorService = app(VendorService::class);

            $vendorIds = [];

            if ($request->filled('vendor_id')) {
                // Specific vendor selected
                $vendorId = $request->get('vendor_id');
                // Only apply filter if vendor_id is numeric (unsignedBigInteger column)
                if (is_numeric($vendorId)) {
                    $vendorIds = [(int)$vendorId];
                }
            } elseif ($request->filled('parent_vendor_id')) {
                // Parent vendor selected - get all descendants
                $parentId = $request->get('parent_vendor_id');
                // Only apply filter if parent_vendor_id is numeric
                if (is_numeric($parentId)) {
                    $vendorIds = array_merge([(int)$parentId], $vendorService->getDescendants((int)$parentId));
                }
            }

            if (!empty($vendorIds)) {
                $builder->whereIn('vendor_id', $vendorIds);
            }
        }

        // Vendor filter (for vendor panel)
        if ($request->filled('vendor_id') && request()->routeIs('vendor.wallets.index')) {
            $vendorId = $request->get('vendor_id');
            // Only apply filter if vendor_id is numeric (unsignedBigInteger column)
            if (is_numeric($vendorId)) {
                $builder->where('vendor_id', (int)$vendorId);
            }
        }

        if ($request->filled('description')) {
            $builder->where('description', 'ilike', '%' . $request->get('description') . '%');
        }

        if ($request->filled('currency')) {
            $builder->where('currency', $request->get('currency'));
        }

        // Status filter
        if ($request->filled('status')) {
            $builder->where('status', (bool)$request->get('status'));
        }

        // Amount filters
        if ($request->filled('total_amount_min')) {
            $totalAmountMin = $request->get('total_amount_min');
            // Only apply filter if total_amount_min is numeric (float column)
            if (is_numeric($totalAmountMin)) {
                $builder->where('total_amount', '>=', (float)$totalAmountMin);
            }
        }

        if ($request->filled('total_amount_max')) {
            $totalAmountMax = $request->get('total_amount_max');
            // Only apply filter if total_amount_max is numeric (float column)
            if (is_numeric($totalAmountMax)) {
                $builder->where('total_amount', '<=', (float)$totalAmountMax);
            }
        }

        if ($request->filled('blocked_amount_min')) {
            $blockedAmountMin = $request->get('blocked_amount_min');
            // Only apply filter if blocked_amount_min is numeric (float column)
            if (is_numeric($blockedAmountMin)) {
                $builder->where('blocked_amount', '>=', (float)$blockedAmountMin);
            }
        }

        if ($request->filled('blocked_amount_max')) {
            $blockedAmountMax = $request->get('blocked_amount_max');
            // Only apply filter if blocked_amount_max is numeric (float column)
            if (is_numeric($blockedAmountMax)) {
                $builder->where('blocked_amount', '<=', (float)$blockedAmountMax);
            }
        }

        // Date filters
        if ($request->filled('created_from')) {
            $builder->where('created_at', '>=', $request->get('created_from'));
        }

        if ($request->filled('created_to')) {
            $builder->where('created_at', '<=', $request->get('created_to'));
        }

        if ($request->filled('updated_from')) {
            $builder->where('updated_at', '>=', $request->get('updated_from'));
        }

        if ($request->filled('updated_to')) {
            $builder->where('updated_at', '<=', $request->get('updated_to'));
        }

        if ($request->filled('last_sync_date_from')) {
            $builder->where('last_sync_date', '>=', $request->get('last_sync_date_from'));
        }

        if ($request->filled('last_sync_date_to')) {
            $builder->where('last_sync_date', '<=', $request->get('last_sync_date_to'));
        }
    }

    /**
     * Check if scope should be applied based on current route
     */
    private function shouldApplyScope(): bool
    {
        return request()->routeIs('admin.wallets.index')
            || request()->routeIs('vendor.wallets.index')
            || request()->routeIs('vendor.wallets.bulk-update-status');
    }
}
