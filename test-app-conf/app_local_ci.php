<?php
declare(strict_types=1);

use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;

return [
    'Datasources' => [
        'default' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => 'root',
            'database' => 'cakephp',
            'encoding' => 'utf8',
            'timezone' => 'SYSTEM',
            'cacheMetadata' => true,
            'quoteIdentifiers' => true,
        ],
        'test' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => 'root',
            'database' => 'cakephp_test',
            'encoding' => 'utf8',
            'timezone' => 'SYSTEM',
            'cacheMetadata' => true,
            'quoteIdentifiers' => true,
            'init' => ['SET FOREIGN_KEY_CHECKS=0'],
        ],
        'postgres' => [
            'className' => Connection::class,
            'driver' => Postgres::class,
            'persistent' => false,
            'host' => '127.0.0.1',
            'port' => '5432',
            'username' => 'postgres',
            'password' => 'postgres',
            'database' => 'postgres_cakephp',
            'encoding' => 'utf8',
            'cacheMetadata' => true,
        ],
        'test_postgres' => [
            'className' => Connection::class,
            'driver' => Postgres::class,
            'persistent' => false,
            'host' => '127.0.0.1',
            'port' => '5432',
            'username' => 'postgres',
            'password' => 'postgres',
            'database' => 'postgres_cakephp_test',
            'encoding' => 'utf8',
            'cacheMetadata' => true,
        ],
    ],
];
