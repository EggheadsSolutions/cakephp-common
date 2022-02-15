<?php

use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;

return [
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),
    'Security' => [
        'salt' => env('SECURITY_SALT', '8b95ba1312772f4b405afe652d2847f8f89ec462ad790a730b10c158cd44703f'),
    ],
    'Datasources' => [
        'default' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'host' => 'mysql',
            'port' => '3306',
            'username' => 'root',
            'password' => 'secret',
            'database' => 'app',
            'encoding' => 'utf8',
            'timezone' => 'SYSTEM',
            'cacheMetadata' => true,
            'quoteIdentifiers' => true,
        ],
        'test' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'host' => 'mysql',
            'port' => '3306',
            'username' => 'root',
            'password' => 'secret',
            'database' => 'app_test',
            'encoding' => 'utf8',
            'timezone' => 'SYSTEM',
            'cacheMetadata' => true,
            'quoteIdentifiers' => true,
            'init' => ['SET FOREIGN_KEY_CHECKS=0'],
        ],
    ],
    'Sentry' => [
        'dsn' => '',
        'attach_stacktrace' => true,
        'send_default_pii' => true,
    ],
];
