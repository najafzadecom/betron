<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserScope implements Scope
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
                    ->orWhere('username', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('telegram', 'ilike', "%{$search}%")
                    ->orWhereHas('roles', function ($query) use ($search) {
                        $query->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        // Individual field filters
        if ($request->filled('name')) {
            $builder->where('name', 'ilike', '%' . $request->get('name') . '%');
        }

        if ($request->filled('username')) {
            $builder->where('username', 'ilike', '%' . $request->get('username') . '%');
        }

        if ($request->filled('email')) {
            $builder->where('email', 'ilike', '%' . $request->get('email') . '%');
        }

        if ($request->filled('telegram')) {
            $builder->where('telegram', 'ilike', '%' . $request->get('telegram') . '%');
        }

        // Status filter
        if ($request->filled('status')) {
            $builder->where('status', (bool)$request->get('status'));
        }

        // Role filters
        if ($request->filled('role_id')) {
            $builder->whereHas('roles', function ($query) use ($request) {
                $query->where('id', $request->get('role_id'));
            });
        }

        if ($request->filled('role_name')) {
            $roleName = $request->get('role_name');
            $builder->whereHas('roles', function ($query) use ($roleName) {
                $query->where('name', 'ilike', "%{$roleName}%");
            });
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
        return request()->routeIs('admin.users.index');
    }
}
