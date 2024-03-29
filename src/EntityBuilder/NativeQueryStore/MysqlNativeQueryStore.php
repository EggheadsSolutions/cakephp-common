<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\EntityBuilder\NativeQueryStore;

/**
 * Нативные запросы для Mysql
 */
class MysqlNativeQueryStore extends AbstractNativeQueryStore
{
    /** @inheritdoc */
    public function getTableComment(): ?string
    {
        $tableComment = $this->_connection->query("
            SELECT table_comment
            FROM INFORMATION_SCHEMA.TABLES
            WHERE table_schema='" . $this->_connection->config()['database'] . "'
            AND TABLE_NAME='" . $this->_table->getTable() . "';
        ")->fetchColumn(0);

        return !empty($tableComment)? $tableComment : null;
    }

    /** @inheritdoc */
    public function isTableExist(): bool
    {
        $existingTables = $this->_connection->query("
            SELECT count(*)
            FROM INFORMATION_SCHEMA.TABLES
            WHERE table_schema='" . $this->_connection->config()['database'] . "'
            AND TABLE_NAME='" . $this->_table->getTable() . "';
        ")->fetchColumn(0);

        return (bool)$existingTables;
    }
}
