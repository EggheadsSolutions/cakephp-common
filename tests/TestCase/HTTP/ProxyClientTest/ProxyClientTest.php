<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\HTTP\ProxyClientTest;

use Cake\Datasource\ConnectionManager;
use Eggheads\CakephpCommon\Http\ProxyClient;
use Eggheads\CakephpCommon\Http\ProxyList;
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
        // Чистим тестовую таблицу и добавляем 1 запись
        $tableName = ProxyList::DEFAULT_TABLE_NAME;
        $connection = ConnectionManager::get('default');
        $connection->execute("DELETE FROM $tableName WHERE id > 0");
        $connection->execute("INSERT INTO $tableName (proxy, username, password, active) VALUES ('proxy1', 'username1', 'password1', 1)");

        $proxyClient = new ProxyClient();
        self::assertEquals([
            'proxy' => 'proxy1',
            'username' => 'username1',
            'password' => 'password1',
        ], $proxyClient->getConfig('proxy'));
    }
}
