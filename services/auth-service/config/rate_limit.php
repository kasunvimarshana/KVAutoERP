<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */

    'login' => [
        'max_attempts' => (int) env('RATE_LIMIT_LOGIN', 5),
        'decay_minutes' => (int) env('RATE_LIMIT_DECAY_MINUTES', 1),
    ],

    'refresh' => [
        'max_attempts' => (int) env('RATE_LIMIT_REFRESH', 10),
        'decay_minutes' => (int) env('RATE_LIMIT_DECAY_MINUTES', 1),
    ],

    'register' => [
        'max_attempts' => (int) env('RATE_LIMIT_REGISTER', 3),
        'decay_minutes' => (int) env('RATE_LIMIT_DECAY_MINUTES', 1),
    ],

    'password_reset' => [
        'max_attempts' => 3,
        'decay_minutes' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Suspicious Activity Detection
    |--------------------------------------------------------------------------
    */

    'suspicious_activity' => [
        'max_failed_logins'      => (int) env('MAX_FAILED_LOGINS', 5),
        'window_minutes'         => (int) env('SUSPICIOUS_ACTIVITY_WINDOW_MINUTES', 15),
        'ip_change_detection'    => (bool) env('IP_CHANGE_DETECTION', true),
        'geo_change_detection'   => false,
        'lock_on_threshold'      => true,
        'lock_duration_minutes'  => 30,
    ],

];
