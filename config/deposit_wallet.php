<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site-exclusive vendor wallet routing
    |--------------------------------------------------------------------------
    |
    | For listed site IDs, deposits may only be routed to the given vendor and
    | its child vendors. Those vendor trees are excluded from all other sites.
    |
    | Example: site 3 → vendor 34 means site 3 uses only vendor 34 + descendants,
    | and every other site never receives wallets from that tree.
    |
    */
    'site_exclusive_vendors' => [
        (int) env('DEPOSIT_EXCLUSIVE_SITE_ID', 3) => (int) env('DEPOSIT_EXCLUSIVE_VENDOR_ID', 34),
    ],

];
