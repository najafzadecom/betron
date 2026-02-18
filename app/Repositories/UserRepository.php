<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\UserInterface;
use App\Models\User as Model;

class UserRepository extends BaseRepository implements UserInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }
}
