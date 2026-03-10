<?php
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
return [
    'default'      => env('LOG_CHANNEL', 'stderr'),
    'deprecations' => ['channel' => 'null', 'trace' => false],
    'channels' => [
        'stack'  => ['driver' => 'stack', 'channels' => ['stderr'], 'ignore_exceptions' => false],
        'single' => ['driver' => 'single', 'path' => storage_path('logs/laravel.log'), 'level' => env('LOG_LEVEL', 'debug')],
        'stderr' => ['driver' => 'monolog', 'level' => env('LOG_LEVEL', 'debug'), 'handler' => StreamHandler::class, 'with' => ['stream' => 'php://stderr'], 'processors' => [PsrLogMessageProcessor::class]],
        'null'   => ['driver' => 'monolog', 'handler' => \Monolog\Handler\NullHandler::class],
    ],
];
