<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\HTTP\ProxyClientTest;

use Cake\Core\Configure;
use Cake\Http\Client\Exception\RequestException;
use Eggheads\CakephpCommon\Http\ProxyClient;
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
        self::assertEquals(Configure::read(ProxyClient::CONFIG_FIELD_NAME), (new ProxyClient())->getConfig('proxy'));
    }

    /**
     * Тестируем, что прокси используется
     * Тут должно выпасть исключение, т.к. прокси, что в конфиге тестов быть не должно
     *
     * @return void
     */
    public function testProxyUsed(): void
    {
        $proxyName = Configure::read(ProxyClient::CONFIG_FIELD_NAME)['proxy'];

        $this->expectException(RequestException::class);
        $this->expectExceptionMessageMatches("/$proxyName/");
        (new ProxyClient())->get('https://eggheads.solutions');
    }
}
