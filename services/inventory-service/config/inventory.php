<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Low Stock Detection
    |--------------------------------------------------------------------------
    | Multiplier applied to a product's min_stock_level to determine the
    | threshold at which a low-stock alert is triggered.
    | e.g. 1.0 = alert exactly at min_stock_level
    |      1.5 = alert when stock falls below 1.5x min_stock_level
    */
    'low_stock_threshold_multiplier' => env('LOW_STOCK_THRESHOLD_MULTIPLIER', 1.0),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    */
    'default_currency' => env('DEFAULT_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Stock Reservation TTL
    |--------------------------------------------------------------------------
    | Hours before an unreleased stock reservation is automatically expired.
    */
    'reservation_ttl_hours' => env('RESERVATION_TTL_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Report Cache TTL
    |--------------------------------------------------------------------------
    | Seconds to cache inventory report results.
    */
    'report_cache_ttl' => env('REPORT_CACHE_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Event Topics
    |--------------------------------------------------------------------------
    */
    'topics' => [
        'inventory_events' => env('INVENTORY_EVENTS_TOPIC', 'inventory.events'),
        'order_events'     => env('ORDER_EVENTS_TOPIC', 'order.events'),
    ],
];
