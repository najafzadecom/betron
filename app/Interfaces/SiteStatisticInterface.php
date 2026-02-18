<?php

namespace App\Interfaces;

use App\Core\Contracts\BaseRepositoryInterface;

interface SiteStatisticInterface extends BaseRepositoryInterface
{
    public function getBySiteId(int $siteId);
}
