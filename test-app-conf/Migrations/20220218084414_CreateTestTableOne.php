<?php
declare(strict_types=1);

use Eggheads\CakephpCommon\Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateTestTableOne extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('test_table_one', ['comment' => 'description blabla']);
        $table->addColumn('col_enum', MysqlAdapter::PHINX_TYPE_ENUM, [
            'values' => ['val1', 'val2', 'val3'],
            'default' => 'val1',
            'limit' => 255,
            'null' => false,
            'comment' => 'Колонка с enum',
        ]);
        $table->addColumn('col_text', MysqlAdapter::PHINX_TYPE_TEXT, [
            'default' => null,
            'null' => false,
            'comment' => 'Описание',
        ]);
        $table->addColumn('col_time', MysqlAdapter::PHINX_TYPE_TIMESTAMP, [
            'default' => null,
            'null' => false,
            'comment' => 'Дата создания',
        ]);
        $table->create();
    }
}
