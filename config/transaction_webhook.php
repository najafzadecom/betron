<?php

/*
 * Callback URLs: birden fazla URL tanımlanabilir.
 * - TRANSACTION_WEBHOOK_URL: tek URL (geriye dönük uyumluluk)
 * - TRANSACTION_WEBHOOK_URLS: virgülle ayrılmış URL listesi (örn: https://a.com/notify,https://b.com/notify)
 * İkisi birlikte kullanılırsa her iki kaynaktaki URL'ler birleştirilir.
 */
$singleUrl = env('TRANSACTION_WEBHOOK_URL');
$multipleUrls = env('TRANSACTION_WEBHOOK_URLS');
$urls = array_values(array_filter(array_unique(array_merge(
    $singleUrl ? [trim($singleUrl)] : [],
    $multipleUrls ? array_map('trim', explode(',', $multipleUrls)) : []
))));

return [
    'url' => $urls[0] ?? env('TRANSACTION_WEBHOOK_URL'), // geriye dönük uyumluluk
    'urls' => $urls,
    'secret_key' => env('TRANSACTION_WEBHOOK_SECRET_KEY'),
    'enabled' => env('TRANSACTION_WEBHOOK_ENABLED', true),
    'timeout' => env('TRANSACTION_WEBHOOK_TIMEOUT', 100),
];
