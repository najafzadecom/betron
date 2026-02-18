<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\BankInterface;
use App\Models\Bank as Model;

class BankRepository extends BaseRepository implements BankInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function getActiveTransactionBanks($sort = 'priority', $direction = 'desc'): iterable
    {
        return $this->model
            ->query()
            ->where('status', true)
            ->where('transaction_status', true)
            ->orderBy($sort, $direction)
            ->get();
    }

    public function getActiveWithdrawalBanks($sort = 'priority', $direction = 'desc'): iterable
    {
        return $this->model
            ->query()
            ->where('status', true)
            ->where('withdrawal_status', true)
            ->orderBy($sort, $direction)
            ->get();
    }
}
