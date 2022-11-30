<?php
declare(strict_types=1);

use Eggheads\CakephpCommon\Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\AdapterInterface;

class CreateTestTableSix extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('test_table_six', ['comment' => 'Table description 6']);
        $table
            ->addColumn('created', AdapterInterface::PHINX_TYPE_TIMESTAMP, [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
                'comment' => 'Создано',
            ])
            ->create();
    }
}
