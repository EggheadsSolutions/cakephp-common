<?php

use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;

return [
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),
    'Security' => [
        'salt' => env('SECURITY_SALT', '__SALT__'),
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
];
