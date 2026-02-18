<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\VendorDepositTransactionRepository as Repository;

class VendorDepositTransactionService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }

    /**
     * Get transactions by vendor ID
     */
    public function getByVendorId(int $vendorId, array $filters = [])
    {
        return $this->repository->getByVendorId($vendorId, $filters);
    }

    /**
     * Create a deposit transaction record
     */
    public function create(array $data): object
    {
        return $this->repository->create($data);
    }

    /**
     * Get all transactions with filters
     */
    public function getAllTransactions(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }
}

