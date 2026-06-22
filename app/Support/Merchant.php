<?php

namespace App\Support;

use App\Models\User;

class Merchant
{
    public const DEFAULT_SITE_ID = 4;
    public const ALL_TIME_FROM = '2000-01-01';

    public static function isMerchant(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->hasRole('Merchant') ?? false;
    }

    public static function siteIdFor(?User $user = null): ?int
    {
        return self::isMerchant($user) ? self::DEFAULT_SITE_ID : null;
    }

    public static function defaultCreatedFrom(?User $user = null): string
    {
        return self::isMerchant($user) ? self::ALL_TIME_FROM : date('Y-m-d');
    }

    public static function defaultCreatedTo(): string
    {
        return date('Y-m-d');
    }
}
