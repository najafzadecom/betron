<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\SiteRepository as Repository;

class SiteService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }
}
