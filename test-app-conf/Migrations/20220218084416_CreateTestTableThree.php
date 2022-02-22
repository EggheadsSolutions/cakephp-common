<?php
declare(strict_types=1);

use Eggheads\CakephpCommon\Phinx\Migration\AbstractMigration;

class CreateTestTableThree extends AbstractMigration
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
        $table = $this->table('test_table_three', ['comment' => 'table 3']);
        $table->create();
    }
}
