<?php
declare(strict_types=1);

use Eggheads\CakephpCommon\Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateTestTableTwo extends AbstractMigration
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
        $table = $this->table('test_table_two', ['comment' => 'description qweqwe']);
        $table->addColumn('table_one_fk', MysqlAdapter::PHINX_TYPE_INTEGER, [
            'default' => null,
            'null' => false,
            'comment' => 'Колонка связи с one',
        ]);
        $table->addColumn('col_text', MysqlAdapter::PHINX_TYPE_TEXT, [
            'default' => null,
            'null' => false,
            'comment' => 'Описание',
        ]);
        $table->addForeignKeyWithName(
            'fk_two_one',
            ['table_one_fk'],
            'test_table_one',
            ['id']
        );
        $table->create();
    }
}
