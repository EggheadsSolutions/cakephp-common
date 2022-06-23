<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\HTTP\ProxyListTest;

use Eggheads\CakephpCommon\Http\Items\ProxyItem;
use Eggheads\CakephpCommon\Http\ProxyList;
use Eggheads\CakephpCommon\Test\Factory\ProxyConfigFactory;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;

class ProxyListTest extends AppTestCase
{
    /**
     * Тестируем getConfig
     *
     * @return void
     */
    public function testGetProxy(): void
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

        $list = ProxyList::getInstance()->getProxyList();
        self::assertCount(2, $list);
        self::assertEquals(
            array_map(
                [ProxyItem::class, 'create'],
                [
                    [
                        'proxy' => 'proxy1',
                        'username' => 'username1',
                        'password' => 'password1',
                    ],
                    [
                        'proxy' => 'proxy2',
                        'username' => 'username2',
                        'password' => 'password2',
                    ],
                ]
            ),
            $list
        );
    }
}
