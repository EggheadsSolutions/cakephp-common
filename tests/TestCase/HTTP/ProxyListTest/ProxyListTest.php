<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\HTTP\ProxyListTest;

use Cake\Datasource\ConnectionManager;
use Eggheads\CakephpCommon\Http\ProxyList;
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
        // Чистим тестовую таблицу и добавляем 2 записи
        $tableName = ProxyList::DEFAULT_TABLE_NAME;
        $connection = ConnectionManager::get('default');
        $connection->execute("DELETE FROM $tableName WHERE id > 0");
        $connection->execute("INSERT INTO $tableName (proxy, username, password, active) VALUES ('proxy1', 'username1', 'password1', 1), ('proxy2', 'username2', 'password2', 1)");

        $anyConfig = ProxyList::getInstance()->getConfig();
        self::assertContains($anyConfig->proxy, ['proxy1', 'proxy2']);
    }
}
