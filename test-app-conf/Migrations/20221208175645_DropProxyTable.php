<?php
declare(strict_types=1);

use Eggheads\CakephpCommon\Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\AdapterInterface;

class DropProxyTable extends AbstractMigration
{
    /**
     * @return void
     */
    public function up(): void
    {
        $this->table('proxy_config')->drop()->save();
    }

    /**
     * @return void
     */
    public function down(): void
    {
        $this->table('proxy_config', ['comment' => 'Таблица с прокси'])
            ->addColumn('proxy', AdapterInterface::PHINX_TYPE_CHAR, [
                'null' => false,
                'length' => 255,
                'noDefault' => true,
                'comment' => 'Прокси',
            ])
            ->addColumn('username', AdapterInterface::PHINX_TYPE_CHAR, [
                'null' => false,
                'length' => 255,
                'noDefault' => true,
                'comment' => 'Имя пользователя',
            ])
            ->addColumn('password', AdapterInterface::PHINX_TYPE_CHAR, [
                'null' => false,
                'length' => 255,
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
