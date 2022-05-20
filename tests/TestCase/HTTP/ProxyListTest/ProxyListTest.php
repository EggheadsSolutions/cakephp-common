<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\HTTP\ProxyListTest;

use Eggheads\CakephpCommon\Http\ProxyList;
use Eggheads\CakephpCommon\Test\Factory\ProxyConfigFactory;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;

class ProxyListTest extends AppTestCase
{
    /**
     * Тестируем getConfig
     *
     * @return void
     * @see ProxyList::getConfig()
     */
    public function testGetConfig(): void
    {
        ProxyConfigFactory::make([
            'proxy' => 'proxy1',
            'username' => 'username1',
            'password' => 'password1',
        ])->persist();

        ProxyConfigFactory::make([
            'proxy' => 'proxy2',
            'username' => 'username2',
            'password' => 'password2',
        ])->persist();

        $anyConfig = ProxyList::getInstance()->getConfig();
        self::assertContains($anyConfig->proxy, ['proxy1', 'proxy2']);
    }
}
