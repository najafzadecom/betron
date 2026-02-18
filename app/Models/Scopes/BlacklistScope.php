<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BlacklistScope implements Scope
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
                $query->where('user_id', 'ilike', "%{$search}%")
                    ->orWhere('ip_address', 'ilike', "%{$search}%")
                    ->orWhere('reason', 'ilike', "%{$search}%")
                    ->orWhere('type', 'ilike', "%{$search}%");
            });
        }

        // Individual field filters
        if ($request->filled('user_id')) {
            $userId = $request->get('user_id');
            // Only apply filter if user_id is numeric (bigint column)
            if (is_numeric($userId)) {
                $builder->where('user_id', (int)$userId);
            }
        }

        if ($request->filled('ip_address')) {
            $builder->where('ip_address', 'ilike', '%' . $request->get('ip_address') . '%');
        }

        if ($request->filled('type')) {
            $builder->where('type', $request->get('type'));
        }

        if ($request->filled('site_id')) {
            $siteId = $request->get('site_id');
            // Only apply filter if site_id is numeric (unsignedTinyInteger column)
            if (is_numeric($siteId)) {
                $builder->where('site_id', (int)$siteId);
            }
        }

        if ($request->filled('is_active')) {
            $builder->where('is_active', $request->get('is_active'));
        }

        if ($request->filled('reason')) {
            $builder->where('reason', 'ilike', '%' . $request->get('reason') . '%');
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
    }

    /**
     * Check if scope should be applied based on current route
     */
    private function shouldApplyScope(): bool
    {
        return request()->routeIs('admin.blacklists.index');
    }
}
