<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\WalletFileRepository as Repository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WalletFileService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }

    public function findByWalletAndFileId(int $walletId, int $fileId)
    {
        return $this->repository->findByWalletAndFileId($walletId, $fileId);
    }

    public function uploadFile(int $walletId, UploadedFile $file): object
    {
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $uploadPath = 'uploads/wallets/' . $walletId;

        Storage::disk('public')->makeDirectory($uploadPath, 0755, true, true);

        $filePath = Storage::disk('public')->putFileAs($uploadPath, $file, $fileName);

        return $this->repository->create([
            'wallet_id' => $walletId,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    public function deleteFile(int $fileId): bool
    {
        $walletFile = $this->repository->find($fileId);

        if (!$walletFile) {
            return false;
        }

        if (Storage::disk('public')->exists($walletFile->file_path)) {
            Storage::disk('public')->delete($walletFile->file_path);
        }

        return $this->repository->delete($fileId);
    }

    public function uploadMultipleFiles(int $walletId, array $files): void
    {
        $uploadPath = 'uploads/wallets/' . $walletId;

        Storage::disk('public')->makeDirectory($uploadPath, 0755, true, true);

        foreach ($files as $file) {
            $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();

            $filePath = Storage::disk('public')->putFileAs($uploadPath, $file, $fileName);

            $this->repository->create([
                'wallet_id' => $walletId,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }
}
