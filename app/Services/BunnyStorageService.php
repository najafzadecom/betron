<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BunnyStorageService
{
    public function isConfigured(): bool
    {
        return (bool) config('bunny.access_key') && (bool) config('bunny.storage_zone');
    }

    public function upload(string $remotePath, string $contents, string $contentType): void
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException(__('Receipt storage is not configured.'));
        }

        $remotePath = ltrim($remotePath, '/');
        $url = $this->storageEndpoint() . '/' . $remotePath;

        $response = Http::withHeaders([
            'AccessKey' => config('bunny.access_key'),
            'Content-Type' => $contentType,
        ])->withBody($contents, $contentType)->put($url);

        if (!$response->successful()) {
            throw new RuntimeException(
                __('Failed to upload receipt to storage.') . ' (' . $response->status() . ')'
            );
        }
    }

    public function publicUrl(string $remotePath): ?string
    {
        $cdnUrl = config('bunny.cdn_url');
        if (!$cdnUrl || !$remotePath) {
            return null;
        }

        return $cdnUrl . '/' . ltrim($remotePath, '/');
    }

    private function storageEndpoint(): string
    {
        $zone = config('bunny.storage_zone');
        $region = config('bunny.region');

        if ($region) {
            return 'https://' . $region . '.storage.bunnycdn.com/' . $zone;
        }

        return 'https://storage.bunnycdn.com/' . $zone;
    }
}
