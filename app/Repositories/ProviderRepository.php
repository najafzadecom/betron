<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\ProviderInterface;
use App\Models\Provider as Model;

class ProviderRepository extends BaseRepository implements ProviderInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }
}
