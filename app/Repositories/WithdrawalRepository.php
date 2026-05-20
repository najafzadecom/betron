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

    public function paginate(): LengthAwarePaginator
    {
        $perPage = (int) request('limit', config('pagination.per_page'));
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : config('pagination.per_page');

        $query = $this->model->newQuery()->with(Model::LIST_RELATIONS);

        if (!request()->has('sort')) {
            $query->latest('created_at');
        }

        return $query->paginate($perPage)->appends(request()->query());
    }
}
