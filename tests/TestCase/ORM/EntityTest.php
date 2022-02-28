<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\ORM;

use Eggheads\CakephpCommon\Test\Factory\TestTableTwoFactory;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;
use TestApp\Model\Entity\TestTableTwo;
use TestApp\Model\Table\TestTableOneTable;
use TestApp\Model\Table\TestTableTwoTable;

/**
 * @property TestTableOneTable $TestTableOne
 * @property TestTableTwoTable $TestTableTwo
 */
class EntityTest extends AppTestCase
{
    /** проверка на изменение значения поля */
    public function testChanged(): void
    {
        /** @var TestTableTwo $entity */
        $entity = TestTableTwoFactory::make()->persist();
        $entity->table_one_fk = $entity->table_one_fk;
        self::assertTrue($entity->isDirty('table_one_fk'));
        self::assertFalse($entity->changed('table_one_fk'));
    }
}
