<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\ORM;

use Eggheads\CakephpCommon\Test\Factory\TestTableOneFactory;
use Eggheads\CakephpCommon\Test\Factory\TestTableTwoFactory;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;
use TestApp\Model\Entity\TestTableOne;
use TestApp\Model\Entity\TestTableTwo;
use TestApp\Model\Table\TestTableOneTable;
use TestApp\Model\Table\TestTableTwoTable;

/**
 * @property TestTableOneTable $TestTableOne
 * @property TestTableTwoTable $TestTableTwo
 */
class EntityTest extends AppTestCase
{
    /** @inheritdoc */
    public $fixtures = [
        'app.TestTableOne',
        'app.TestTableTwo',
    ];

    /** проверка на изменение значения поля */
    public function testChanged(): void
    {
        /** @var TestTableTwo $entity */
        $entity = TestTableTwoFactory::make()->persist();
        $entity->table_one_fk = $entity->table_one_fk;
        self::assertTrue($entity->isDirty('table_one_fk'));
        self::assertFalse($entity->changed('table_one_fk'));
    }

    /** удаление дочерней сущности */
    public function testDeleteChild(): void
    {
        $assocName = 'TestTableTwo';
        /** @var TestTableTwo $entity1 */
        $entity1 = TestTableTwoFactory::make()->persist();
        /** @var TestTableTwo $entity2 */
        $entity2 = TestTableTwoFactory::make()->persist();
        $this->TestTableTwo->saveArr(['table_one_fk' => $entity1->TestTableOne->id], $entity2);
        $childIds = [$entity2->id, $entity1->id];
        sort($childIds);

        $entity = $this->TestTableOne->get($entity1->TestTableOne->id, ['contain' => $assocName]);
        $childIds2 = array_column($entity->toArray()[$assocName], 'id');
        sort($childIds2);

        self::assertEquals($childIds, $childIds2);

        $childIndex = 0;
        $entity->deleteChild($assocName, $childIndex); // @phpstan-ignore-line
        unset($childIds[$childIndex]);
        self::assertEquals(array_values($childIds), array_column($entity->toArray()[$assocName], 'id'));
        self::assertTrue($entity->isDirty($assocName));
    }

    /**
     * удаление несуществующей дочерней сущности
     */
    public function testDeleteChildNotExists(): void
    {
        $this->expectExceptionMessage("Unknown property TestTableTwo");
        $this->expectException(\Exception::class);

        /** @var TestTableOne $entity */
        $entity = TestTableOneFactory::make()->persist();
        $entity->deleteChild('TestTableTwo', 0); // @phpstan-ignore-line
    }
}
