<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\BankRepository as Repository;

class BankService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }

    public function getActiveTransactionBanks($sort = 'priority', $direction = 'desc'): iterable
    {
        return $this->repository->getActiveTransactionBanks($sort, $direction);
    }

    public function getActiveWithdrawalBanks($sort = 'priority', $direction = 'desc'): iterable
    {
        return $this->repository->getActiveWithdrawalBanks($sort, $direction);
    }
}
