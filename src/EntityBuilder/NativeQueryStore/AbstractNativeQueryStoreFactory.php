<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\EntityBuilder\NativeQueryStore;

use Cake\Database\Connection;
use Cake\ORM\Table;
use Eggheads\CakephpCommon\Error\InternalException;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD.MethodMix)
 */
abstract class AbstractNativeQueryStoreFactory
{
    /** @var Connection */
    protected Connection $_connection;

    /** @var Table */
    protected Table $_table;

    /**
     * AbstractNativeQueryStoreFactory constructor
     *
     * @param Connection $connection
     * @param Table $table
     */
    public function __construct(Connection $connection, Table $table)
    {
        $this->_connection = $connection;
        $this->_table = $table;
    }

    /**
     * Фабричный метод.
     * Возвращает перечень нативных запросов в зависимости от драйвера подключения.
     *
     * @param Table $table
     * @return static
     * @throws InternalException
     */
    public static function getQueryStore(Table $table): static
    {
        $connection = $table->getConnection();
        $refClass = new ReflectionClass($connection->getDriver());
        $class = __NAMESPACE__ . '\\' . $refClass->getShortName() . 'NativeQueryStore';

        if (!class_exists($class)) {
            throw new InternalException("Native query store class $class not found");
        }

        /**
         * @uses PostgresNativeQueryStore
         * @uses MysqlNativeQueryStore
         */
        return new $class($connection, $table);
    }

    /**
     * Возвращает комментарий к таблице из БД, если он задан
     *
     * @return string|null
     */
    public function getTableComment(): ?string
    {
        $tableComment = $this->_getTableComment();
        if ($tableComment === null) {
            return null;
        }

        return ' * @tableComment ' . $tableComment;
    }

    /**
     * Определение существование таблицы в БД
     *
     * @return bool
     */
    public function isTableExist(): bool
    {
        return $this->_isTableExist();
    }

    /**
     * Возвращает комментарий к таблице из БД, если он задан
     *
     * @return ?string
     */
    abstract protected function _getTableComment(): ?string;

    /**
     * Определение существование таблицы в БД
     *
     * @return bool
     */
    abstract protected function _isTableExist(): bool;
}
