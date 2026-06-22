<?php

namespace App\Support;

use App\Models\User;

class Merchant
{
    public const DEFAULT_SITE_ID = 4;

    public static function isMerchant(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->hasRole('Merchant') ?? false;
    }

    public static function siteIdFor(?User $user = null): ?int
    {
        return self::isMerchant($user) ? self::DEFAULT_SITE_ID : null;
    }
}
