<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\TransactionInterface;
use App\Models\Transaction as Model;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository extends BaseRepository implements TransactionInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    /**
     * @return LengthAwarePaginator
     */
    public function paginate(): LengthAwarePaginator
    {
        $perPage = (int)request('limit', config('pagination.per_page'));
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : config('pagination.per_page');

        $query = $this->model->newQuery()->with(Model::listRelations());

        if (!request()->has('sort')) {
            $query->latest('created_at');
        }

        return $query->paginate($perPage)->appends(request()->query());
    }
}
