<?php

return [

    'base_url' => rtrim(env('CASHEVO_BASE_URL', 'https://prov.cashevo.net'), '/'),

    'api_key' => env('CASHEVO_API_KEY'),

    'client_name' => env('CASHEVO_CLIENT_NAME'),

    'bearer_token' => env('CASHEVO_BEARER_TOKEN'),

    'payment_method' => env('CASHEVO_PAYMENT_METHOD', 'BANK_TRANSFER'),

];
