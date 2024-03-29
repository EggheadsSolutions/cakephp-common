<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\EntityBuilder\NativeQueryStore;

/**
 * Нативные запросы для PostgreSQL
 */
class PostgresNativeQueryStore extends AbstractNativeQueryStore
{
    /** @inheritdoc */
    public function getTableComment(): ?string
    {
        $tableComment = $this->_connection->query("
            SELECT obj_description('" . $this->_table->getTable() . "'::regclass);
        ")->fetchColumn(0);

        return !empty($tableComment)? $tableComment : null;
    }

    /** @inheritdoc  */
    public function isTableExist(): bool
    {
        $existingTables = $this->_connection->query("
            SELECT count(*)
            FROM INFORMATION_SCHEMA.TABLES
            WHERE table_schema='" . $this->_connection->getDriver()->schema() . "'
            AND TABLE_NAME='" . $this->_table->getTable() . "';
        ")->fetchColumn(0);

        return (bool)$existingTables;
    }
}
