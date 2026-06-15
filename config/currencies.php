<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | ISO 4217 currency codes accepted for payment requests. The exchange
    | rate is always fetched as EUR -> {currency}.
    |
    */
    'supported' => [
        'EUR', 'USD', 'GBP', 'BRL', 'MXN', 'JPY', 'CAD', 'AUD', 'CHF', 'PLN',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exchange Rate Provider
    |--------------------------------------------------------------------------
    */
    'exchange_rate' => [
        'base_url' => env('EXCHANGE_RATE_API_URL', 'https://open.er-api.com/v6/latest'),
        'base_currency' => 'EUR',
        'cache_ttl' => env('EXCHANGE_RATE_CACHE_TTL', 300),
    ],
];
