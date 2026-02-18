<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\SiteInterface;
use App\Models\Site as Model;

class SiteRepository extends BaseRepository implements SiteInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }
}
