<?php

namespace App\Interfaces;

use App\Core\Contracts\BaseRepositoryInterface;

interface VendorUserInterface extends BaseRepositoryInterface
{
    public function getByVendorId(int $vendorId);
}
