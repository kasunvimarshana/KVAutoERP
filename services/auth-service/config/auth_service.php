<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Auth Service General Settings
    |--------------------------------------------------------------------------
    */

    'name' => env('SERVICE_ID', 'auth-service'),

    /*
    |--------------------------------------------------------------------------
    | Device Session Limits
    |--------------------------------------------------------------------------
    | Maximum number of concurrent device sessions per user per tenant.
    */

    'max_devices_per_user' => (int) env('AUTH_SERVICE_MAX_DEVICES_PER_USER', 10),

    /*
    |--------------------------------------------------------------------------
    | Redis Revocation Prefix
    |--------------------------------------------------------------------------
    | Namespace prefix for all Redis keys managed by the revocation service.
    */

    'revocation_prefix' => env('AUTH_SERVICE_REVOCATION_PREFIX', 'revoke'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'login' => [
            'max_attempts'   => (int) env('AUTH_RATE_LIMIT_LOGIN', 10),
            'decay_minutes'  => (int) env('AUTH_RATE_LIMIT_LOGIN_DECAY_MINUTES', 1),
        ],
        'refresh' => [
            'max_attempts'   => (int) env('AUTH_RATE_LIMIT_REFRESH', 30),
            'decay_minutes'  => (int) env('AUTH_RATE_LIMIT_REFRESH_DECAY_MINUTES', 1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Suspicious Activity Detection
    |--------------------------------------------------------------------------
    */

    'suspicious' => [
        'failed_attempts_threshold' => (int) env('AUTH_SERVICE_SUSPICIOUS_FAILED_ATTEMPTS', 5),
        'lockout_minutes'           => (int) env('AUTH_SERVICE_SUSPICIOUS_LOCKOUT_MINUTES', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Log Retention
    |--------------------------------------------------------------------------
    | Number of days to retain auth audit log entries before purging.
    */

    'audit_log_retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 2555),

    /*
    |--------------------------------------------------------------------------
    | Password Hashing
    |--------------------------------------------------------------------------
    | Force Argon2id for all password hashing in the auth service.
    */

    'password_algo' => PASSWORD_ARGON2ID,

    'argon2' => [
        'memory'      => 65536,
        'time'        => 4,
        'threads'     => 1,
    ],
];
