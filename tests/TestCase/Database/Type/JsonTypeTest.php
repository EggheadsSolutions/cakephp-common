<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\Database\Type;

use Eggheads\CakephpCommon\TestSuite\AppTestCase;
use Cake\ORM\Table;

/**
 * @property Table $TestTableFive
 */
class JsonTypeTest extends AppTestCase
{
    /**
     * Сохранение значения Null в поле типа JSON
     */
    public function testSaveNull(): void
    {
        $newEntity = $this->TestTableFive->newEntity([
            'col_json' => null,
        ]);
        $res = $this->TestTableFive->save($newEntity);
        self::assertNotEmpty($res);
        self::assertNull($res->col_json); // @phpstan-ignore-line
        $dbValueIsNull = $this->TestTableFive->exists(['col_json IS NULL']);
        self::assertTrue($dbValueIsNull, 'Неправильно работает переопределение JsonType');
    }
}
