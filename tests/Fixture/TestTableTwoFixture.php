<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\Fixture;

use Eggheads\CakephpCommon\TestSuite\Fixture\TestFixture;

class TestTableTwoFixture extends TestFixture
{
    /**
     * @inheritdoc
     * @phpstan-ignore-next-line
     */
    public $records = [
        ['id' => '11', 'table_one_fk' => '1000'],
    ];
}
