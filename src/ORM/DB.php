<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\ORM;

use Eggheads\CakephpCommon\Traits\Library;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;

/**
 * @SuppressWarnings(PHPMD.ShortClassName)
 */
class DB
{
    use Library;

    const CONNECTION_DEFAULT = 'default';
    const CONNECTION_TEST = 'test';

    /**
     * Дефолтный коннекшн
     *
     * @param string $name
     * @param bool $useAliases
     * @return Connection
     */
    public static function getConnection(string $name = self::CONNECTION_DEFAULT, bool $useAliases = true): Connection
    {
        return ConnectionManager::get($name, $useAliases); // @phpstan-ignore-line
    }

    /**
     * переподсоединиться, если отвалился
     *
     * @param string $connectionName
     * @return void
     */
    public static function restoreConnection(string $connectionName = self::CONNECTION_DEFAULT)
    {
        $connection = self::getConnection($connectionName);
        if (!$connection->isConnected()) {
            $connection->connect();
        }
    }
}
