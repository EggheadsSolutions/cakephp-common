<?php
declare(strict_types=1);

use Eggheads\CakephpCommon\Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CreateTestTableFive extends AbstractMigration
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
        $table = $this->table('test_table_five', ['comment' => 'description 5']);
        $table->addColumn('col_json', MysqlAdapter::PHINX_TYPE_JSON, [
            'default' => null,
            'null' => true,
            'comment' => 'Описание JSON',
        ]);
        $table->create();
    }
}
