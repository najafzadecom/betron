<?php

namespace App\Interfaces;

use App\Core\Contracts\BaseRepositoryInterface;

interface WalletFileInterface extends BaseRepositoryInterface
{
    public function findByWalletAndFileId(int $walletId, int $fileId);
}
