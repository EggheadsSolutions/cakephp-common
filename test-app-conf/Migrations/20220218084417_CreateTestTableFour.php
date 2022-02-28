<?php
declare(strict_types=1);

use Eggheads\CakephpCommon\Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateTestTableFour extends AbstractMigration
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
        $table = $this->table('test_table_four', ['comment' => 'description 4']);
        $table->addColumn('table_four_fk', MysqlAdapter::PHINX_TYPE_INTEGER, [
            'default' => null,
            'null' => false,
            'comment' => 'Колонка связи с one',
        ]);
        $table->addForeignKeyWithName(
            'fk_four_one',
            ['table_four_fk'],
            'test_table_one',
            ['id']
        );
        $table->create();
    }
}
