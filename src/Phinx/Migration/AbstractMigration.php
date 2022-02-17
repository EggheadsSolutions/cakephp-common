<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Phinx\Migration;

use Eggheads\CakephpCommon\Phinx\Db\Table;

abstract class AbstractMigration extends \Migrations\AbstractMigration
{
    /**
     * @inheritdoc
     *
     * @param string $tableName
     * @param array<string, mixed> $options
     */
    public function table($tableName, $options = [])
    {
        return new Table($tableName, $options, $this->getAdapter());
    }
}
