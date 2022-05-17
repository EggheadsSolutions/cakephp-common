<?php
declare(strict_types=1);

use Eggheads\CakephpCommon\Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\AdapterInterface;

class CreateProxyTable extends AbstractMigration
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
        $this->table('proxy_config', ['comment' => 'Таблица с прокси'])
            ->addColumn('proxy', AdapterInterface::PHINX_TYPE_CHAR, [
                'null' => false,
                'default' => null,
                'noDefault' => true,
                'comment' => 'Прокси',
            ])
            ->addColumn('username', AdapterInterface::PHINX_TYPE_CHAR, [
                'null' => false,
                'default' => null,
                'noDefault' => true,
                'comment' => 'Имя пользователя',
            ])
            ->addColumn('password', AdapterInterface::PHINX_TYPE_CHAR, [
                'null' => false,
                'default' => null,
                'noDefault' => true,
                'comment' => 'Пароль',
            ])
            ->addColumn('active', AdapterInterface::PHINX_TYPE_BOOLEAN, [
                'null' => false,
                'default' => true,
                'comment' => 'Активность',
            ])
            ->addColumn('created', AdapterInterface::PHINX_TYPE_TIMESTAMP, [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'Время создания',
            ])
            ->create();
    }
}
