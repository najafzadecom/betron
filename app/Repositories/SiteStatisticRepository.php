<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\SiteStatisticInterface;
use App\Models\SiteStatistic;

class SiteStatisticRepository extends BaseRepository implements SiteStatisticInterface
{
    public function __construct(SiteStatistic $model)
    {
        parent::__construct($model);
    }

    public function getBySiteId(int $siteId)
    {
        return $this->model->where('site_id', $siteId)->first();
    }
}
