<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\VendorDepositTransactionInterface;
use App\Models\VendorDepositTransaction as Model;

class VendorDepositTransactionRepository extends BaseRepository implements VendorDepositTransactionInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    /**
     * Get transactions by vendor ID with pagination
     */
    public function getByVendorId(int $vendorId, array $filters = [])
    {
        $query = $this->model->where('vendor_id', $vendorId);

        // Apply filters
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to'] . ' 23:59:59');
        }

        $perPage = (int)($filters['limit'] ?? 25);
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : 25;

        return $query->with(['vendor.parent', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($filters);
    }

    /**
     * Get all transactions with filters and pagination
     */
    public function getAll(array $filters = [])
    {
        $query = $this->model->newQuery();

        // Apply filters
        // vendor_id is more specific, so use it if set
        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        } elseif (!empty($filters['vendor_ids'])) {
            // If vendor_id is not set, use vendor_ids (for parent vendor filtering)
            $query->whereIn('vendor_id', $filters['vendor_ids']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to'] . ' 23:59:59');
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('vendor', function ($vendorQuery) use ($search) {
                    $vendorQuery->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                })
                ->orWhere('note', 'ilike', "%{$search}%");
            });
        }

        $perPage = (int)($filters['limit'] ?? 25);
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : 25;

        return $query->with(['vendor.parent', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($filters);
    }
}

