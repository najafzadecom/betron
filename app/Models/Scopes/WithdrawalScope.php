<?php

namespace App\Models\Scopes;

use App\Services\VendorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WithdrawalScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply scope for GET requests on specific routes
        if (!request()->isMethod('GET') || !$this->shouldApplyScope()) {
            return;
        }

        $request = request();

        // Global search across multiple fields
        if ($request->filled('search')) {
            $search = $request->get('search');
            $builder->where(function ($query) use ($search) {
                $query->where('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%")
                    ->orWhere('iban', 'ilike', "%{$search}%")
                    ->orWhere('amount', 'ilike', "%{$search}%")
                    ->orWhere('currency', 'ilike', "%{$search}%");
            });
        }

        // Individual field filters
        if ($request->filled('receiver')) {
            $builder->where(function ($query) use ($request) {
                $query->where('first_name', 'ilike', '%' . $request->get('receiver') . '%')
                    ->orWhere('last_name', 'ilike', '%' . $request->get('receiver') . '%');
            });
        }

        if ($request->filled('iban')) {
            $builder->where('iban', 'ilike', '%' . $request->get('iban') . '%');
        }

        if ($request->filled('currency')) {
            $builder->where('currency', $request->get('currency'));
        }

        if ($request->filled('user_id')) {
            $userId = $request->get('user_id');
            // Only apply filter if user_id is numeric (bigint column)
            if (is_numeric($userId)) {
                $builder->where('user_id', (int)$userId);
            }
        }

        if ($request->filled('order_id')) {
            $orderId = $request->get('order_id');
            // If order_id is numeric, use it as integer, otherwise as string
            if (is_numeric($orderId)) {
                $builder->where('order_id', (string)(int)$orderId);
            } else {
                $builder->where('order_id', $orderId);
            }
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->get('status');
            // Only apply filter if status is numeric (unsignedTinyInteger column)
            if (is_numeric($status)) {
                $builder->where('status', (int)$status);
            }
        }

        // Paid Status filter
        if ($request->filled('paid_status') && $request->get('paid_status') != 'all') {
            $builder->where('paid_status', $request->get('paid_status'));
        }

        // Wallet filter
//        if ($request->filled('wallet_id')) {
//            $builder->where('wallet_id', $request->get('wallet_id'));
//        }

        // Amount filters
        if ($request->filled('amount_min')) {
            $amountMin = $request->get('amount_min');
            // Only apply filter if amount_min is numeric (float column)
            if (is_numeric($amountMin)) {
                $builder->where('amount', '>=', (float)$amountMin);
            }
        }

        if ($request->filled('amount_max')) {
            $amountMax = $request->get('amount_max');
            // Only apply filter if amount_max is numeric (float column)
            if (is_numeric($amountMax)) {
                $builder->where('amount', '<=', (float)$amountMax);
            }
        }

        // Date filters
        if ($request->filled('created_from') && $request->get('created_from') !== '') {
            $builder->where('created_at', '>=', $request->get('created_from'). ' 00:00:00');
        }

        if ($request->filled('created_to') && $request->get('created_to') !== '') {
            $builder->where('created_at', '<=', $request->get('created_to') . ' 23:59:59');
        }

        if ($request->filled('accepted_from')) {
            $builder->where('accepted_at', '>=', $request->get('accepted_from'). ' 00:00:00');
        }

        if ($request->filled('accepted_to')) {
            $builder->where('accepted_at', '<=', $request->get('accepted_to') . ' 23:59:59');
        }

        if ($request->filled('updated_from') && $request->get('updated_from') !== '') {
            $builder->where('updated_at', '>=', $request->get('updated_from'). ' 00:00:00');
        }

        if ($request->filled('updated_to') && $request->get('updated_to') !== '') {
            $builder->where('updated_at', '<=', $request->get('updated_to') . ' 23:59:59');
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
                // Withdrawal tablosunda direkt vendor_id var, wallet_id'ye gerek yok
                $builder->whereIn('vendor_id', $vendorIds);
            }
        }

        // Child vendor filter (for vendor panel)
        if ($request->filled('child_vendor_id') && request()->routeIs('vendor.withdrawals.index')) {
            $childVendorId = $request->get('child_vendor_id');
            // Only apply filter if child_vendor_id is numeric (unsignedBigInteger column)
            if (is_numeric($childVendorId)) {
                $builder->where('vendor_id', (int)$childVendorId);
            }
        }
    }

    /**
     * Check if scope should be applied based on current route
     */
    private function shouldApplyScope(): bool
    {
        return request()->routeIs('admin.withdrawals.index')
            || request()->routeIs('admin.withdrawals.export')
            || request()->routeIs('vendor.withdrawals.index')
            || request()->routeIs('vendor.withdrawals.export');
    }
}
