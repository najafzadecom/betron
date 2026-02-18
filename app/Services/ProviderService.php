<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\ProviderRepository as Repository;

class ProviderService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }
}
