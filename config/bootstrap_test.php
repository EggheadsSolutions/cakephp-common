<?php
ini_set('soap.wsdl_cache_ttl', '1');

if (!defined('TEST_MODE')) {
    define('TEST_MODE', 1);
}

require_once __DIR__ . '/bootstrap.php';

use Eggheads\CakephpCommon\Lib\AppCache;
use Eggheads\CakephpCommon\Lib\DB;
use Eggheads\CakephpCommon\Lib\Env;
use Eggheads\CakephpCommon\TestSuite\HttpClientMock\HttpClientAdapter;

Env::setHttpClientAdapter(HttpClientAdapter::class);

AppCache::flushAll();

Env::setMockFolder(TESTS . 'Suite' . DS . 'Mock' . DS);
Env::setMockNamespace('App\Test\Suite\Mock');
