<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SiteScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply scope for GET requests on specific routes
        if (!request()->isMethod('GET') || !$this->shouldApplyScope()) {
            return;
        }

        $request = request();

        // Individual field filters
        if ($request->filled('name')) {
            $builder->where('name', 'ilike', '%' . $request->get('name') . '%');
        }

        // Status filter
        if ($request->filled('status')) {
            $builder->where('status', (bool)$request->get('status'));
        }
    }

    /**
     * Check if scope should be applied based on current route
     */
    private function shouldApplyScope(): bool
    {
        return request()->routeIs('admin.sites.index');
    }
}
