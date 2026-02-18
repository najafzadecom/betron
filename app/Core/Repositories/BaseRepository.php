<?php

namespace App\Core\Repositories;

use App\Core\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 *
 */
abstract class  BaseRepository implements BaseRepositoryInterface
{
    /**
     * @param Model $model
     */
    public function __construct(protected Model $model)
    {
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param string $sort
     * @param string $direction
     * @return iterable
     */
    public function all(string $sort = 'id', string $direction = 'ASC'): iterable
    {
        return $this->model
            ->query()
            ->orderBy($sort, $direction)
            ->get();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function paginate(): LengthAwarePaginator
    {
        $perPage = (int)request('limit', config('pagination.per_page'));
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : config('pagination.per_page');

        $query = $this->model->query();

        // If sort parameter is not provided, use default sorting (created_at DESC, like latest())
        // Sortable trait will handle sorting when sort parameter is provided
        if (!request()->has('sort')) {
            $query->latest();
        }

        return $query->paginate($perPage)->appends(request()->query());
    }

    /**
     * @param int $id
     * @return object|null
     */
    public function find(int $id): ?object
    {
        return $this->model->find($id);
    }

    /**
     * @param array $data
     * @return object
     */
    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return object|null
     */
    public function update(int $id, array $data): ?object
    {
        $item = $this->find($id);
        if (!$item) {
            return null;
        }
        $item->update($data);

        return $item;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool
    {
        return $this->model->query()->restore($id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function forceDelete(int $id): bool
    {
        return $this->model->forceDelete($id);
    }
}
