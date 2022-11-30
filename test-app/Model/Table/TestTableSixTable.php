<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Eggheads\CakephpCommon\ORM\Table;

class TestTableSixTable extends Table
{
    /** @inerhitDoc */
    public static function defaultConnectionName(): string
    {
        return 'postgres';
    }
}
