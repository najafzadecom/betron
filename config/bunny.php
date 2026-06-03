<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bunny.net Storage (withdrawal receipts)
    |--------------------------------------------------------------------------
    |
    | Storage API: PUT https://{region}.storage.bunnycdn.com/{zone}/{path}
    | Public files via pull zone URL (BUNNY_CDN_URL).
    |
    */

    'storage_zone' => env('BUNNY_STORAGE_ZONE', 'bankexpress3'),

    'access_key' => env('BUNNY_STORAGE_ACCESS_KEY'),

    /**
     * Region slug (e.g. de, uk) or empty for default storage.bunnycdn.com
     */
    'region' => env('BUNNY_STORAGE_REGION', ''),

    /**
     * Pull zone base URL without trailing slash, e.g. https://bankexpress3.b-cdn.net
     */
    'cdn_url' => rtrim((string) env('BUNNY_CDN_URL', ''), '/'),

    'receipt_max_kb' => (int) env('BUNNY_RECEIPT_MAX_KB', 10240),

];
