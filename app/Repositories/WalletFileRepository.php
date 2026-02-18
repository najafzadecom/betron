<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\WalletFileInterface;
use App\Models\WalletFile;

class WalletFileRepository extends BaseRepository implements WalletFileInterface
{
    public function __construct(WalletFile $model)
    {
        parent::__construct($model);
    }

    public function findByWalletAndFileId(int $walletId, int $fileId)
    {
        return $this->model->where('wallet_id', $walletId)
            ->where('id', $fileId)
            ->first();
    }
}
