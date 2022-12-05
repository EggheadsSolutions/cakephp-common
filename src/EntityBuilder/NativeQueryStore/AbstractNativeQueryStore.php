<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\EntityBuilder\NativeQueryStore;

use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;
use Cake\ORM\Table;
use Eggheads\CakephpCommon\Error\InternalException;

/**
 * @SuppressWarnings(PHPMD.MethodMix)
 */
abstract class AbstractNativeQueryStore
{
    /** @var Connection */
    protected Connection $_connection;

    /** @var Table */
    protected Table $_table;

    /**
     * AbstractNativeQueryStore constructor
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->_connection = $table->getConnection();
        $this->_table = $table;
    }

    /**
     * Фабричный метод.
     * Возвращает перечень нативных запросов в зависимости от драйвера подключения.
     *
     * @param Table $table
     * @return MysqlNativeQueryStore|PostgresNativeQueryStore
     * @throws InternalException
     */
    public static function factory(Table $table): MysqlNativeQueryStore|PostgresNativeQueryStore
    {
        $connection = $table->getConnection();

        return match (true) {
            $connection->getDriver() instanceof Mysql => new MysqlNativeQueryStore($table),
            $connection->getDriver() instanceof Postgres => new PostgresNativeQueryStore($table),
            default => throw new InternalException('Unknown connection driver ' . $connection->getDriver()::class),
        };
    }

    /**
     * Возвращает комментарий к таблице из БД, если он задан
     *
     * @return ?string
     */
    abstract public function getTableComment(): ?string;

    /**
     * Определение существование таблицы в БД
     *
     * @return bool
     */
    abstract public function isTableExist(): bool;
}
