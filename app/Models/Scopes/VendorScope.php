<?php

namespace App\Models\Scopes;

use App\Services\VendorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class VendorScope implements Scope
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
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Individual field filters
        if ($request->filled('name')) {
            $builder->where('name', 'ilike', '%' . $request->get('name') . '%');
        }

        if ($request->filled('email')) {
            $builder->where('email', 'ilike', '%' . $request->get('email') . '%');
        }

        // Status filter
        if ($request->filled('status') && $request->get('status') !== '') {
            $builder->where('status', $request->get('status'));
        }

        // Parent Vendor filter (for admin panel)
        // Show parent vendor and its direct children only (1 level)
        // No recursive calls to prevent memory issues
        if ($request->filled('parent_id')) {
            $parentId = $request->get('parent_id');
            
            // Only apply filter if parent_id is numeric
            if (is_numeric($parentId)) {
                $parentId = (int)$parentId;
                
                // Show parent vendor and its direct children only
                $builder->where(function ($query) use ($parentId) {
                    $query->where('id', $parentId) // Include parent itself
                        ->orWhere('parent_id', $parentId); // Include direct children
                });
            }
        }

        // Date filters
        if ($request->filled('created_from')) {
            $builder->where('created_at', '>=', $request->get('created_from'));
        }

        if ($request->filled('created_to')) {
            $builder->where('created_at', '<=', $request->get('created_to') . ' 23:59:59');
        }
    }

    /**
     * Check if scope should be applied based on current route
     */
    private function shouldApplyScope(): bool
    {
        return request()->routeIs('admin.vendors.index');
    }

    /**
     * Get vendor IDs with depth limit to prevent infinite loops
     * Uses iterative approach instead of recursive to avoid memory issues
     */
    private function getVendorIdsWithDepthLimit(int $parentId, int $maxDepth = 10): array
    {
        $vendorIds = [$parentId];
        $currentLevel = [$parentId];
        $visited = [$parentId => true];
        $depth = 0;

        while (!empty($currentLevel) && $depth < $maxDepth) {
            $nextLevel = [];
            
            foreach ($currentLevel as $vendorId) {
                $children = \App\Models\Vendor::where('parent_id', $vendorId)
                    ->pluck('id')
                    ->toArray();
                
                foreach ($children as $childId) {
                    // Prevent circular references
                    if (!isset($visited[$childId])) {
                        $visited[$childId] = true;
                        $vendorIds[] = $childId;
                        $nextLevel[] = $childId;
                    }
                }
            }
            
            $currentLevel = $nextLevel;
            $depth++;
        }

        return array_unique($vendorIds);
    }
}
