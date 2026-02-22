<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    */
    'version' => env('API_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    | Default and maximum items per page. Enforced in BaseRepository.
    */
    'pagination' => [
        'per_page'     => (int) env('API_PER_PAGE', 15),
        'max_per_page' => (int) env('API_MAX_PER_PAGE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache (File Driver — not Redis, to preserve Redis free-tier memory)
    |--------------------------------------------------------------------------
    | ttl      = short-lived cache (frequently mutated lists / single models)
    | long_ttl = long-lived cache (reference data: roles, permissions, config)
    */
    'cache' => [
        'enabled'  => env('API_CACHE_ENABLED', true),
        'driver'   => env('API_CACHE_DRIVER', 'file'),   // NEVER change to 'redis' — see tiered-cache docs
        'ttl'      => (int) env('API_CACHE_TTL', 300),   // 5 minutes
        'long_ttl' => (int) env('API_CACHE_LONG_TTL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'api'  => (int) env('API_RATE_LIMIT', 60),         // per minute, per user/IP
        'auth' => (int) env('API_AUTH_RATE_LIMIT', 5),     // per minute per IP for auth routes
    ],

    /*
    |--------------------------------------------------------------------------
    | Sanctum Token
    |--------------------------------------------------------------------------
    */
    'token_expiry' => (int) env('SANCTUM_TOKEN_EXPIRY', 1440), // minutes (default 24h)

];
