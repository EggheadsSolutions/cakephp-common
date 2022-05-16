<?php

use Cake\Cache\Engine\FileEngine;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Log\Engine\FileLog;

return [
    'serverProtocol' => 'http',
    'serverName' => 'common.artskills.ru',
    'debug' => true,
    'threadsLimit' => 6,

    'App' => [
        'namespace' => 'TestApp',
        'encoding' => env('APP_ENCODING', 'UTF-8'),
        'defaultLocale' => env('APP_DEFAULT_LOCALE', 'en_US'),
        'base' => false,
        'dir' => 'test-app',
        'webroot' => 'webroot',
        'wwwRoot' => WWW_ROOT,
        'fullBaseUrl' => false,
        'imageBaseUrl' => 'img/',
        'cssBaseUrl' => 'css/',
        'jsBaseUrl' => 'js/',
        'paths' => [
            'plugins' => [ROOT . DS . 'plugins' . DS],
            'templates' => [APP . 'Template' . DS],
            'locales' => [APP . 'Locale' . DS],
        ],
    ],

    'Security' => [
        'salt' => 'test_security_salt',
    ],

    'Datasources' => [
        'default' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'host' => 'mysql',
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
            'host' => 'mysql',
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
    ],

    'Cache' => [
        'default' => [
            'className' => FileEngine::class,
            'path' => CACHE,
            'url' => env('CACHE_DEFAULT_URL', null),
        ],

        '_cake_core_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_core_',
            'path' => CACHE . 'persistent/',
            'serialize' => true,
            'duration' => '+30 seconds',
            'url' => env('CACHE_CAKECORE_URL', null),
        ],

        '_cake_model_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_model_',
            'path' => CACHE . 'models/',
            'serialize' => true,
            'duration' => '+30 seconds',
            'url' => env('CACHE_CAKEMODEL_URL', null),
        ],

        'short' => [
            'className' => FileEngine::class,
            'prefix' => 'short_',
            'path' => CACHE . 'models/',
            'serialize' => true,
            'duration' => '+30 seconds',
            'url' => env('CACHE_CAKEMODEL_URL', null),
        ],
    ],

    'Error' => [
        'errorLevel' => E_ALL,
        'exceptionRenderer' => 'Cake\Error\ExceptionRenderer',
        'skipLog' => [],
        'log' => true,
        'trace' => true,
    ],

    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => ['nobody@artskills.ru' => 'ArtSkills.ru'],
            'returnPath' => ['gift@artskills.ru' => 'ArtSkills.ru'],
            'replyTo' => ['gift@artskills.ru' => 'ArtSkills.ru'],
            'headers' => ['Precedence' => 'bulk'],
        ],
    ],

    'EmailTransport' => [
        'default' => [
            'className' => 'Mail',
            'host' => 'localhost',
            'port' => 25,
            'timeout' => 30,
            'username' => 'user',
            'password' => 'secret',
            'client' => null,
            'tls' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
        'test' => [
            'className' => 'ArtSkills.TestEmail',
        ],
    ],

    'Log' => [
        'debug' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'debug',
            'levels' => ['notice', 'info', 'debug'],
            'url' => env('LOG_DEBUG_URL', null),
        ],
        'error' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'error',
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
            'url' => env('LOG_ERROR_URL', null),
        ],
        'sentry' => [
            'className' => FileLog::class,
            'levels' => [],
        ],
    ],
];
