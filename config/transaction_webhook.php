<?php

/*
 * Callback URLs: birden fazla URL tanımlanabilir.
 * - TRANSACTION_WEBHOOK_URL: tek URL (geriye dönük uyumluluk)
 * - TRANSACTION_WEBHOOK_URLS: virgülle ayrılmış URL listesi (örn: https://a.com/notify,https://b.com/notify)
 * İkisi birlikte kullanılırsa her iki kaynaktaki URL'ler birleştirilir.
 */
return [
    'secret_key' => env('TRANSACTION_WEBHOOK_SECRET_KEY', 'base64:LMwQ08wCOzE28jvLA0kSwZaTKGL7+CW7eczDYSBJfns=') ,
    'enabled' => env('TRANSACTION_WEBHOOK_ENABLED', true),
    'timeout' => env('TRANSACTION_WEBHOOK_TIMEOUT', 100),
];
