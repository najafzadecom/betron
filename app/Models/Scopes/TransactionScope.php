<?php

namespace App\Models\Scopes;

use App\Enums\TransactionStatus;
use App\Services\VendorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TransactionScope implements Scope
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
                $query->where('first_name', 'ilike', "%" . $search . "%")
                    ->orWhere('last_name', 'ilike', "%" . $search . "%")
                    ->orWhere('order_id', 'ilike', "%" . $search . "%")
                    ->orWhere('user_id', 'ilike', "%" . $search . "%")
                    ->orWhereHas('wallet', function ($query) use ($search) {
                        $query->where('name', 'ilike', '%' . $search . '%');
                        $query->orWhere('iban', 'ilike', '%' . $search . '%');
                    });
            });
        }

        // Individual field filters
        if ($request->filled('sender')) {
            $builder->where(function ($query) use ($request) {
                $query->where('first_name', 'ilike', '%' . $request->get('sender') . '%')
                    ->orWhere('last_name', 'ilike', '%' . $request->get('sender') . '%');
            });
        }

        if ($request->filled('receiver')) {
            $builder->where(function ($query) use ($request) {
                $query->where('receiver_name', 'ilike', '%' . $request->get('receiver') . '%')
                    ->orWhere('receiver_iban', 'ilike', '%' . $request->get('receiver') . '%');
            });
        }

        if ($request->filled('phone')) {
            $builder->where('phone', 'ilike', '%' . $request->get('phone') . '%');
        }

        if ($request->filled('client_ip')) {
            $builder->where('client_ip', 'ilike', '%' . $request->get('client_ip') . '%');
        }

        if ($request->filled('site_id')) {
            $siteId = $request->get('site_id');
            // Only apply filter if site_id is numeric (unsignedTinyInteger column)
            if (is_numeric($siteId)) {
                $builder->where('site_id', (int)$siteId);
            }
        }

        if ($request->filled('user_id')) {
            $userId = $request->get('user_id');
            // Only apply filter if user_id is numeric (bigint column)
            if (is_numeric($userId)) {
                $builder->where('user_id', (int)$userId);
            }
        }

        if ($request->filled('currency')) {
            $builder->where('currency', $request->get('currency'));
        }

        if ($request->filled('payment_method')) {
            $builder->where('payment_method', $request->get('payment_method'));
        }

        if ($request->filled('order_id')) {
            $builder->where('order_id', trim($request->get('order_id')));
        }

        if ($request->filled('uuid')) {
            $builder->where('uuid', $request->get('uuid'));
        }

        // Wallet filter
        if ($request->filled('wallet_id')) {
            $walletId = $request->get('wallet_id');
            // Only apply filter if wallet_id is numeric (unsignedBigInteger column)
            if (is_numeric($walletId)) {
                $builder->where('wallet_id', (int)$walletId);
            }
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->get('status');
            // Only apply filter if status is numeric (enum but stored as integer)
            if (is_numeric($status)) {
                $builder->where('status', (int)$status);
            }
        } else {
            $builder->where('status', '!=', TransactionStatus::Pending);
        }

        // Paid Status filter
        if ($request->filled('paid_status') && $request->get('paid_status') !== '' && $request->get('paid_status') != 'all') {
            $builder->where('paid_status', $request->get('paid_status'));
        }

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
        if ($request->filled('created_from')) {
            $builder->where('created_at', '>=', $request->get('created_from') . ' 00:00:00');
        }

        if ($request->filled('created_to')) {
            $builder->where('created_at', '<=', $request->get('created_to') . ' 23:59:59');
        }

        if ($request->filled('accepted_from')) {
            $builder->where('accepted_at', '>=', $request->get('accepted_from') . ' 00:00:00');
        }

        if ($request->filled('accepted_to')) {
            $builder->where('accepted_at', '<=', $request->get('accepted_to') . ' 23:59:59');
        }

        if ($request->filled('updated_from')) {
            $builder->where('updated_at', '>=', $request->get('updated_from') . ' 00:00:00');
        }

        if ($request->filled('updated_to')) {
            $builder->where('updated_at', '<=', $request->get('updated_to') . ' 23:59:59');
        }

        // Vendor filters (for admin panel) - using vendor_id directly from transactions table
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

        // Vendor filter (for vendor panel - only for parent vendors) - using vendor_id directly from transactions table
        if ($request->filled('vendor_id') && request()->routeIs('vendor.transactions.index')) {
            $vendorId = $request->get('vendor_id');
            // Only apply filter if vendor_id is numeric (unsignedBigInteger column)
            if (is_numeric($vendorId)) {
                $vendorService = app(VendorService::class);

                // Get vendor and all its descendants
                $vendorIds = array_merge([(int)$vendorId], $vendorService->getDescendants((int)$vendorId));

                $builder->whereIn('vendor_id', $vendorIds);
            }
        }
    }

    /**
     * Check if scope should be applied based on current route
     */
    private function shouldApplyScope(): bool
    {
        return request()->routeIs('admin.transactions.index')
            || request()->routeIs('admin.transactions.export')
            || request()->routeIs('vendor.transactions.index')
            || request()->routeIs('vendor.transactions.export');
    }
}
