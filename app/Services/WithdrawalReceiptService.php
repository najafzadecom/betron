<?php

namespace App\Services;

use App\Models\Withdrawal;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

class WithdrawalReceiptService
{
    public function __construct(
        protected BunnyStorageService $bunnyStorage,
    ) {
    }

    /**
     * @return array{path: string, original_name: string, mime: string}
     */
    public function store(Withdrawal $withdrawal, UploadedFile $file): array
    {
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $remotePath = sprintf(
            'withdrawals/%d/%s.%s',
            $withdrawal->id,
            Str::uuid(),
            $extension
        );

        $this->bunnyStorage->upload(
            $remotePath,
            $file->get(),
            $mime
        );

        return [
            'path' => $remotePath,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $mime,
        ];
    }

    public static function validationRules(): array
    {
        return [
            'receipt' => [
                'nullable',
                'file',
                'max:' . config('bunny.receipt_max_kb', 10240),
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,jpg,jpeg,png,gif,webp,bmp,heic,heif',
            ],
        ];
    }

    public static function requiredValidationRules(): array
    {
        return [
            'receipt' => [
                'required',
                'file',
                'max:' . config('bunny.receipt_max_kb', 10240),
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,jpg,jpeg,png,gif,webp,bmp,heic,heif',
            ],
        ];
    }

    public function assertStorageReady(?UploadedFile $file): void
    {
        if (!$file) {
            return;
        }

        if (!$this->bunnyStorage->isConfigured()) {
            throw new RuntimeException(__('Receipt storage is not configured.'));
        }
    }
}
