<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\Lib;

use Cake\Cache\Cache;
use Eggheads\CakephpCommon\Lib\AppCache;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;

class AppCacheTest extends AppTestCase
{
    /**
     * Чистилка кэша
     */
    public function testFlush(): void
    {
        Cache::write('qwe', 123, 'short');
        Cache::write('qwerty', 12345, '_cake_core_');
        Cache::write('qwertyuio', 123456789, 'default');
        self::assertEquals(123, Cache::read('qwe', 'short'));
        self::assertEquals(12345, Cache::read('qwerty', '_cake_core_'));
        self::assertEquals(123456789, Cache::read('qwertyuio', 'default'));
        AppCache::flushExcept(['short']);
        self::assertEquals(123, Cache::read('qwe', 'short'));
        //self::assertFalse(Cache::read('qwerty', '_cake_core_'));
        //self::assertFalse(Cache::read('qwertyuio', 'default'));
    }
}
