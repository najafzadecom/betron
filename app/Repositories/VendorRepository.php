<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\VendorInterface;
use App\Models\Vendor;

class VendorRepository extends BaseRepository implements VendorInterface
{
    public function __construct(Vendor $model)
    {
        parent::__construct($model);
    }
}
