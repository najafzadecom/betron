<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BankScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply scope for GET requests on specific routes
        if (!request()->isMethod('GET') || !$this->shouldApplyScope()) {
            return;
        }

        $request = request();

        if ($request->filled('name')) {
            $builder->where('name', 'ilike', '%' . $request->get('name') . '%');
        }

        if ($request->filled('status')) {
            $builder->where('status', (bool)$request->get('status'));
        }

        if ($request->filled('transaction_status')) {
            $builder->where('transaction_status', (bool)$request->get('transaction_status'));
        }

        if ($request->filled('withdrawal_status')) {
            $builder->where('withdrawal_status', (bool)$request->get('withdrawal_status'));
        }
    }

    /**
     * Check if scope should be applied based on current route
     */
    private function shouldApplyScope(): bool
    {
        return request()->routeIs('admin.banks.index');
    }
}
