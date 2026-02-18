<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pratik Ödeme API Ayarları
    |--------------------------------------------------------------------------
    |
    | Bu faylda Pratik Ödeme API-si üçün lazım olan ayarlar var.
    |
    */
    'username' => env('PRATIK_PAYMENT_USERNAME', '13105203634'),
    'password' => env('PRATIK_PAYMENT_PASSWORD', ''),
    'dealer_code' => env('PRATIK_PAYMENT_DEALER_CODE', 'PRTK112252'),
    'branch_code' => env('PRATIK_PAYMENT_BRANCH_CODE', ''),
    'channel_id' => env('PRATIK_PAYMENT_CHANNEL_ID', '20'),

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */

    'base_url' => env('PRATIK_PAYMENT_BASE_URL', 'https://api.pratik.co.id/'),
];
