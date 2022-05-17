<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\HTTP\ProxyClientTest;

use Eggheads\CakephpCommon\Http\ProxyClient;
use Eggheads\CakephpCommon\Test\Factory\ProxyConfigFactory;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;

class ProxyClientTest extends AppTestCase
{
    /**
     * Тестируем, что подставляются прокси
     *
     * @return void
     */
    public function testProxyClient(): void
    {
        ProxyConfigFactory::make([
            'proxy' => 'proxy1',
            'username' => 'username1',
            'password' => 'password1',
        ])->persist();

        $proxyClient = new ProxyClient();
        self::assertEquals([
            'proxy' => 'proxy1',
            'username' => 'username1',
            'password' => 'password1',
        ], $proxyClient->getConfig('proxy'));
    }
}
