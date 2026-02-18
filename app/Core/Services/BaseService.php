<?php

namespace App\Core\Services;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 *
 */
abstract class BaseService
{
    /**
     * @return LengthAwarePaginator
     */
    public function paginate(): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }

    /**
     * @param string $sort
     * @param string $direction
     * @return iterable
     */
    public function getAll(string $sort = 'id', string $direction = 'ASC'): iterable
    {
        return $this->repository->all($sort, $direction);
    }

    /**
     * @param int $id
     * @return object|null
     */
    public function getById(int $id): ?object
    {
        return $this->repository->find($id);
    }

    /**
     * @param array $data
     * @return object
     */
    public function create(array $data): object
    {
        return $this->repository->create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return object|null
     */
    public function update(int $id, array $data): ?object
    {
        return $this->repository->update($id, $data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool
    {
        return $this->repository->restore($id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function forceDelete(int $id): bool
    {
        return $this->repository->forceDelete($id);
    }
}
