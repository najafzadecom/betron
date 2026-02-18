<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\SiteStatisticRepository as Repository;

class SiteStatisticService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }

    public function getBySiteId(int $siteId)
    {
        return $this->repository->getBySiteId($siteId);
    }
}
