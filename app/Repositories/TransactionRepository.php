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

        $query = $this->model->query();

        // Eager load vendor and parent vendor
        $query->with(['vendor.parent']);

        if (!request()->has('sort')) {
            $query->orderByRaw("
  CASE status
    WHEN 0 THEN 1
    WHEN 1 THEN 2
    WHEN 30 THEN 3
    WHEN 3 THEN 40
    WHEN 40 THEN 4
    ELSE 99
  END
")->orderByDesc('created_at');
        }

        return $query->paginate($perPage)->appends(request()->query());
    }
}
