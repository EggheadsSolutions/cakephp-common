<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\HTTP\ProxyClientTest;

use Cake\Http\Exception\HttpException;
use Eggheads\CakephpCommon\Http\Client;
use Eggheads\CakephpCommon\Http\Items\ProxyItem;
use Eggheads\CakephpCommon\Http\ProxyClient;
use Eggheads\CakephpCommon\Http\ProxyList;
use Eggheads\CakephpCommon\Test\Factory\ProxyConfigFactory;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;
use Eggheads\Mocks\ConstantMocker;
use Eggheads\Mocks\MethodMocker;

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

    public function testChangeProxy(): void
    {
        // Убираем паузу между запросами
        ConstantMocker::mock(Client::class, 'DEFAULT_REPEAT_REQUEST_TIMEOUT', 1);

        // Конфиги прокси на запросы
        MethodMocker::mock(ProxyList::class, 'getConfig')
            ->expectCall(2)
            ->willReturnValueList([
                ProxyItem::create([
                    'proxy' => 'proxy1',// Прокси на 1 запрос
                    'username' => 'username1',
                    'password' => 'password1',
                ]),
                ProxyItem::create([
                    'proxy' => 'proxy2', // Прокси на 2 запрос
                    'username' => 'username2',
                    'password' => 'password2',
                ]),
            ]);

        // Бросаем "правильное" исключение, чтобы был заход на второй запрос
        MethodMocker::mock(\Cake\Http\Client::class, '_sendRequest')
            ->willThrowException('cURl Error (6) other', HttpException::class);

        $count = 0;
        // Проверяем, что changeProxy вызывается 2 раза
        MethodMocker::sniff(ProxyClient::class, 'changeProxy', static function ($args, $originalResult) use (&$count) {
            if ($count === 1) {
                self::assertEquals('proxy2' ,$originalResult->getConfig('proxy')['proxy']); // Проверяем, что при втором вызове, прокси сменился
            }
            ++$count;
        })
            ->expectCall(2);

        $proxyClient = new ProxyClient();
        $this->expectException(HttpException::class);
        $proxyClient->get('https://eggheads.solutions');
    }
}
