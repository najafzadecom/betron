<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class VendorUserScope implements Scope
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
                $query->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhereHas('roles', function ($query) use ($search) {
                        $query->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        // Individual field filters
        if ($request->filled('name')) {
            $builder->where('name', 'ilike', '%' . $request->get('name') . '%');
        }

        if ($request->filled('email')) {
            $builder->where('email', 'ilike', '%' . $request->get('email') . '%');
        }

        // Vendor filter (for admin panel)
        if ($request->filled('vendor_id')) {
            $vendorId = $request->get('vendor_id');
            // Only apply filter if vendor_id is numeric (unsignedBigInteger column)
            if (is_numeric($vendorId)) {
                $builder->where('vendor_id', (int)$vendorId);
            }
        }

        // Parent Vendor filter (for admin panel) - shows users of all vendors under selected parent vendor
        if ($request->filled('parent_vendor_id')) {
            $parentVendorId = $request->get('parent_vendor_id');
            // Only apply filter if parent_vendor_id is numeric
            if (is_numeric($parentVendorId)) {
                // Get all vendor IDs that are descendants of the parent vendor (including the parent itself)
                $vendorIds = $this->getVendorIdsUnderParent((int)$parentVendorId);
                
                $builder->whereIn('vendor_id', $vendorIds);
            }
        }

        // Role filter by ID
        if ($request->filled('role_id')) {
            $roleId = $request->get('role_id');
            // Only apply filter if role_id is numeric (unsignedBigInteger column)
            if (is_numeric($roleId)) {
                $builder->whereHas('roles', function ($query) use ($roleId) {
                    $query->where('id', (int)$roleId);
                });
            }
        }

        // Role filter
        if ($request->filled('role')) {
            $builder->whereHas('roles', function ($query) use ($request) {
                $query->where('name', $request->get('role'));
            });
        }

        // Role name filter (for admin panel)
        if ($request->filled('role_name')) {
            $builder->whereHas('roles', function ($query) use ($request) {
                $query->where('name', 'ilike', '%' . $request->get('role_name') . '%');
            });
        }

        // Status filter
        if ($request->filled('status') && $request->get('status') !== '') {
            $builder->where('status', $request->get('status'));
        }

        // Date filters
        if ($request->filled('created_from')) {
            $builder->where('created_at', '>=', $request->get('created_from'));
        }

        if ($request->filled('created_to')) {
            $builder->where('created_at', '<=', $request->get('created_to') . ' 23:59:59');
        }

        if ($request->filled('updated_from')) {
            $builder->where('updated_at', '>=', $request->get('updated_from'));
        }

        if ($request->filled('updated_to')) {
            $builder->where('updated_at', '<=', $request->get('updated_to') . ' 23:59:59');
        }
    }

    /**
     * Check if scope should be applied based on current route
     */
    private function shouldApplyScope(): bool
    {
        return request()->routeIs('vendor.users.index')
            || request()->routeIs('admin.vendor-users.index');
    }

    /**
     * Get all vendor IDs under a parent vendor (including the parent itself and all descendants)
     */
    private function getVendorIdsUnderParent(int $parentVendorId): array
    {
        $vendorIds = [$parentVendorId];
        $processedIds = [$parentVendorId];
        
        // Get direct children
        $children = \App\Models\Vendor::where('parent_id', $parentVendorId)->pluck('id')->toArray();
        $vendorIds = array_merge($vendorIds, $children);
        
        // Recursively get all descendants (with cycle protection)
        foreach ($children as $childId) {
            if (!in_array($childId, $processedIds)) {
                $processedIds[] = $childId;
                $vendorIds = array_merge($vendorIds, $this->getVendorIdsUnderParent($childId));
            }
        }
        
        return array_unique($vendorIds);
    }
}
