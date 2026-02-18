<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ProviderScope implements Scope
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
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('base_url', 'ilike', "%{$search}%")
                    ->orWhere('channel_id', 'ilike', "%{$search}%")
                    ->orWhere('username', 'ilike', "%{$search}%");
            });
        }

        // Individual field filters
        if ($request->filled('name')) {
            $builder->where('name', 'ilike', '%' . $request->get('name') . '%');
        }

        if ($request->filled('code')) {
            $builder->where('code', 'ilike', '%' . $request->get('code') . '%');
        }

        if ($request->filled('base_url')) {
            $builder->where('base_url', 'ilike', '%' . $request->get('base_url') . '%');
        }

        if ($request->filled('channel_id')) {
            $builder->where('channel_id', 'ilike', '%' . $request->get('channel_id') . '%');
        }

        if ($request->filled('username')) {
            $builder->where('username', 'ilike', '%' . $request->get('username') . '%');
        }

        // Status filter
        if ($request->filled('status')) {
            $builder->where('status', (bool)$request->get('status'));
        }

        // Code filters
        if ($request->filled('branch_code')) {
            $builder->where('branch_code', $request->get('branch_code'));
        }

        if ($request->filled('dealer_code')) {
            $builder->where('dealer_code', $request->get('dealer_code'));
        }

        // Date filters
        if ($request->filled('created_from')) {
            $builder->where('created_at', '>=', $request->get('created_from'));
        }

        if ($request->filled('created_to')) {
            $builder->where('created_at', '<=', $request->get('created_to'));
        }
    }

    /**
     * Check if scope should be applied based on current route
     */
    private function shouldApplyScope(): bool
    {
        return request()->routeIs('admin.providers.index');
    }
}
