<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\SiteRepository as Repository;

class SiteService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }

    public function optionsForSelect()
    {
        return $this->repository->getModel()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }
}
