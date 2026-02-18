<?php

namespace App\Core\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 *
 */
interface BaseRepositoryInterface
{
    /**
     * @return Model
     */
    public function getModel(): Model;

    /**
     * @return iterable
     */
    public function all(): iterable;

    /**
     * @return LengthAwarePaginator
     */
    public function paginate(): LengthAwarePaginator;

    /**
     * @param int $id
     * @return object|null
     */
    public function find(int $id): ?object;

    /**
     * @param array $data
     * @return object
     */
    public function create(array $data): object;

    /**
     * @param int $id
     * @param array $data
     * @return object|null
     */
    public function update(int $id, array $data): ?object;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
