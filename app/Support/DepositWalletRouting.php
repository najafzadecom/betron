<?php

namespace App\Support;

use App\Services\VendorService;
use Illuminate\Database\Eloquent\Builder;

class DepositWalletRouting
{
    /**
     * Whether a vendor may be assigned to a withdrawal for the given site.
     */
    public static function isVendorAllowedForSite(int $siteId, int $vendorId): bool
    {
        $exclusive = config('deposit_wallet.site_exclusive_vendors', []);

        if ($exclusive === []) {
            return true;
        }

        $exclusiveVendorId = $exclusive[$siteId] ?? null;

        if ($exclusiveVendorId !== null) {
            return in_array($vendorId, self::vendorTreeIds((int) $exclusiveVendorId), true);
        }

        return !in_array($vendorId, self::allExclusiveVendorTreeIds($exclusive), true);
    }

    /**
     * @return array<int, list<int>> site_id => allowed vendor ids (root + descendants)
     */
    public static function exclusiveSiteVendorTrees(): array
    {
        $trees = [];

        foreach (config('deposit_wallet.site_exclusive_vendors', []) as $siteId => $rootVendorId) {
            $trees[(int) $siteId] = self::vendorTreeIds((int) $rootVendorId);
        }

        return $trees;
    }

    /**
     * @return list<int>
     */
    public static function excludedVendorIds(): array
    {
        return self::allExclusiveVendorTreeIds(config('deposit_wallet.site_exclusive_vendors', []));
    }

    /**
     * Restrict wallet selection query by site ↔ vendor tree rules.
     */
    public static function constrainWalletQuery(Builder $query, int $siteId): void
    {
        $exclusive = config('deposit_wallet.site_exclusive_vendors', []);

        if ($exclusive === []) {
            return;
        }

        $exclusiveVendorId = $exclusive[$siteId] ?? null;

        if ($exclusiveVendorId !== null) {
            $allowedVendorIds = self::vendorTreeIds((int) $exclusiveVendorId);
            if ($allowedVendorIds !== []) {
                $query->whereIn('wallets.vendor_id', $allowedVendorIds);
            }

            return;
        }

        $excludedVendorIds = self::allExclusiveVendorTreeIds($exclusive);
        if ($excludedVendorIds !== []) {
            $query->whereNotIn('wallets.vendor_id', $excludedVendorIds);
        }
    }

    /**
     * @param  array<int, int>  $exclusive site_id => root_vendor_id
     * @return list<int>
     */
    public static function allExclusiveVendorTreeIds(array $exclusive): array
    {
        $ids = [];

        foreach ($exclusive as $rootVendorId) {
            $ids = array_merge($ids, self::vendorTreeIds((int) $rootVendorId));
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<int>
     */
    public static function vendorTreeIds(int $vendorId): array
    {
        $descendants = app(VendorService::class)->getDescendants($vendorId);

        return array_values(array_unique(array_merge([$vendorId], $descendants)));
    }
}
