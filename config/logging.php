<?php

use Monolog\Handler\NullHandler;

return [
    'default' => env('LOG_CHANNEL', 'daily'),
    'deprecations' => ['channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'), 'trace' => false],

    'channels' => [
        'stack' => ['driver' => 'stack', 'channels' => explode(',', env('LOG_STACK', 'daily')), 'ignore_exceptions' => false],
        'single' => ['driver' => 'single', 'path' => storage_path('logs/laravel.log'), 'level' => env('LOG_LEVEL', 'debug'), 'replace_placeholders' => true],
        'daily' => ['driver' => 'daily', 'path' => storage_path('logs/laravel.log'), 'level' => env('LOG_LEVEL', 'debug'), 'days' => env('LOG_DAYS', 14), 'replace_placeholders' => true],
        'stderr' => ['driver' => 'monolog', 'level' => env('LOG_LEVEL', 'debug'), 'handler' => Monolog\Handler\StreamHandler::class, 'with' => ['stream' => 'php://stderr'], 'processors' => [Monolog\Processor\PsrLogMessageProcessor::class]],
        'syslog' => ['driver' => 'syslog', 'level' => env('LOG_LEVEL', 'debug')],
        'errorlog' => ['driver' => 'errorlog', 'level' => env('LOG_LEVEL', 'debug')],
        'null' => ['driver' => 'monolog', 'handler' => NullHandler::class],
        'emergency' => ['path' => storage_path('logs/laravel.log')],
    ],
];
