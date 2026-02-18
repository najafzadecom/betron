<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\WithdrawalInterface;
use App\Models\Withdrawal as Model;
use Illuminate\Pagination\LengthAwarePaginator;

class WithdrawalRepository extends BaseRepository implements WithdrawalInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }
}
