<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\ORM;

use Cake\Database\Driver;
use Eggheads\CakephpCommon\I18n\FrozenTime;
use Eggheads\CakephpCommon\Test\Factory\TestTableOneFactory;
use Eggheads\CakephpCommon\Test\Factory\TestTableTwoFactory;
use Eggheads\Mocks\MethodMocker;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Query;
use Cake\ORM\Table;
use PDOException;
use TestApp\Model\Entity\TestTableOne;
use TestApp\Model\Entity\TestTableTwo;
use TestApp\Model\Table\TestTableOneTable;
use TestApp\Model\Table\TestTableTwoTable;

/**
 * @property TestTableOneTable $TestTableOne
 * @property TestTableTwoTable $TestTableTwo
 */
class TableTest extends AppTestCase
{
    /**
     * Получение сущности разными способами
     */
    public function testGetEntity(): void
    {
        self::assertInstanceOf(TestTableOneTable::class, TestTableOneTable::instance(), 'Не сработало получение инстанса');
        self::assertFalse($this->TestTableOne->getEntity(-1)); // не выкинулся ексепшн

        /** @var TestTableTwo $test2 */
        $test2 = TestTableTwoFactory::make()->persist();
        $test1 = $test2->TestTableOne;

        /** @var TestTableOne $testEntity */
        $testEntity = $this->TestTableOne->getEntity($test1->id, ['contain' => 'TestTableTwo']);
        self::assertInstanceOf(TestTableOne::class, $testEntity, 'Не вернулась сущность');
        self::assertEquals($test1->id, $testEntity->id, 'Вернулась не та сущность');
        self::assertNotEmpty($testEntity->TestTableTwo, 'Не применились опции');
        self::assertSame($testEntity, $this->TestTableOne->getEntity($testEntity), 'Не вернулась сущность');
    }

    /**
     * Сохранение в одно действие
     */
    public function testSaveArr(): void
    {
        // сохранение новой записи
        $saveData = [
            'col_enum' => 'val2',
            'col_text' => 'textextext',
            'col_time' => '2017-03-17 16:34:44',
        ];

        $saveResult = $this->TestTableOne->saveArr($saveData);
        self::assertInstanceOf(TestTableOne::class, $saveResult, 'Неправильный результат сохранения');

        $expectedData = array_replace($saveData, [
            'col_time' => new FrozenTime($saveData['col_time']),
            'id' => $saveResult->id,
        ]);
        /** @var TestTableOne $newRecord */
        $newRecord = $this->TestTableOne->get($saveResult->id);
        $this->assertEntityEqualsArray($expectedData, $newRecord, 'Неправильно создалось');

        // редактирование
        $newText = '2222222222';
        $saveResult = $this->TestTableOne->saveArr([
            'col_text' => $newText,
            'col_time' => $saveData['col_time'],
        ], $newRecord, ['dirtyFields' => 'col_time']);
        self::assertInstanceOf(TestTableOne::class, $saveResult, 'Неправильный результат сохранения при редактировании');

        $expectedData['col_text'] = $newText;
        /** @var TestTableOne $newRecord */
        $newRecord = $this->TestTableOne->get($newRecord->id);
        $this->assertEntityEqualsArray($expectedData, $newRecord, 'Неправильно отредактировалось');

        // редактирование по id
        $newText = 'zzzzzzzz';
        $saveResult = $this->TestTableOne->saveArr([
            'col_text' => $newText,
            'col_time' => $saveData['col_time'],
        ], $newRecord->id, ['dirtyFields' => 'col_time']);
        self::assertInstanceOf(TestTableOne::class, $saveResult, 'Неправильный результат сохранения при редактировании по id');

        $expectedData['col_text'] = $newText;
        /** @var TestTableOne $newRecord */
        $newRecord = $this->TestTableOne->get($newRecord->id);
        $this->assertEntityEqualsArray($expectedData, $newRecord, 'Неправильно отредактировалось по id');
    }

    /**
     * Редактирование связанных сущностей
     */
    public function testChildEdit(): void
    {
        /** @var TestTableTwo $entity2 */
        $entity2 = TestTableTwoFactory::make()->persist();
        $testId = $entity2->TestTableOne->id;

        /** @var TestTableTwo $entity2 */
        $entity2 = TestTableTwoFactory::make()->persist();
        $this->TestTableTwo->saveArr(['table_one_fk' => $testId], $entity2);

        $assoc = 'TestTableTwo';
        $table = TestTableOneTable::instance();

        // если дочерняя сущность dirty, а родительская - нет, то дочерняя сохранится
        $newText = 'test text ololo';
        /** @var TestTableOne $testEntity */
        $testEntity = $table->getEntity($testId, ['contain' => $assoc]);
        self::assertNotEquals($newText, $testEntity->TestTableTwo[0]->col_text);
        $testEntity->TestTableTwo[0]->col_text = $newText;
        $table->save($testEntity);// @phpstan-ignore-line
        $testEntity = $table->getEntity($testId, ['contain' => $assoc]);
        self::assertEquals($newText, $testEntity->TestTableTwo[0]->col_text);// @phpstan-ignore-line

        // смена способа сохранения дочерних сущностей
        /** @var TestTableOne $testEntity */
        $testEntity = $table->getEntity($testId, ['contain' => $assoc]);
        self::assertEquals(HasMany::SAVE_APPEND, $table->$assoc->getSaveStrategy());// @phpstan-ignore-line
        self::assertCount(2, $testEntity->TestTableTwo);
        $testEntity->deleteChild($assoc, 1);
        $table->save($testEntity);// @phpstan-ignore-line
        // на самом деле не удалилась
        $testEntity = $table->getEntity($testId, ['contain' => $assoc]);
        self::assertCount(2, $testEntity->TestTableTwo);// @phpstan-ignore-line

        // а теперь удалится
        $testEntity->deleteChild($assoc, 1);
        $table->save($testEntity, ['assocStrategies' => [$assoc => HasMany::SAVE_REPLACE]]);// @phpstan-ignore-line
        // стратегия изменилась ровно на одно сохранение
        self::assertEquals(HasMany::SAVE_APPEND, $table->$assoc->getSaveStrategy());// @phpstan-ignore-line
        $testEntity = $table->getEntity($testId, ['contain' => $assoc]);
        self::assertCount(1, $testEntity->TestTableTwo);// @phpstan-ignore-line
    }

    /**
     * exists с contain
     */
    public function testExistsContain(): void
    {
        /** @var TestTableTwo $entity2 */
        $entity2 = TestTableTwoFactory::make()->persist();
        $this->TestTableOne->getConnection()->disableForeignKeys();
        $this->TestTableOne->delete($entity2->TestTableOne);
        $this->TestTableOne->getConnection()->enableForeignKeys();

        //существование записи
        $exists = $this->TestTableTwo->exists(['id' => $entity2->id]);
        self::assertTrue($exists, 'Не найдена запись');

        $notExists = $this->TestTableTwo->exists(['TestTableTwo.id' => $entity2->id], ['TestTableOne' => ['joinType' => 'INNER']]);
        self::assertFalse($notExists, 'Найдена запись, хотя не должна');
    }

    /**
     * Попытка вставить запись с плохим внешним ключом
     */
    public function testBadFK(): void
    {
        $this->expectExceptionMessage("a foreign key constraint fails");
        $this->expectException(PDOException::class);
        $this->TestTableTwo->saveArr(['table_one_fk' => 88, 'col_text' => 'textextext']);
    }

    /**
     * Получаем запись с блокировкой
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testFindAndLock(): void
    {
        MethodMocker::sniff(Table::class, 'save')->expectCall(4);
        MethodMocker::sniff(Query::class, 'sql', function ($args, $result) {
            static $firstQuery = 1;
            if ($firstQuery === 4) {
                self::assertTextContains('LIMIT 1 FOR UPDATE', $result, 'Не добавилась блокировка запроса');
            }
            $firstQuery++;
        });

        MethodMocker::sniff(Driver::class, 'beginTransaction', function () {
            self::assertTrue(true, 'Транзакция не началась');
        });

        MethodMocker::sniff(Driver::class, 'commitTransaction', function () {
            self::assertTrue(true, 'Транзакция не закончилась');
        });

        /** @var TestTableTwo $entity1 */
        $entity1 = TestTableOneFactory::make()->persist();

        /** @var TestTableTwo $entity2 */
        $entity2 = TestTableTwoFactory::make()->persist();
        $query = $this->TestTableTwo->find()->where(['id' => $entity2->id]);

        $result = $this->TestTableTwo->updateWithLock($query, ['table_one_fk' => $entity1->id]);

        self::assertInstanceOf(TestTableTwo::class, $result);
        self::assertEquals($entity1->id, $result->table_one_fk, 'Не сработало обновление');
    }

    /**
     * Поиск записи с блокировкой при пустом результате
     */
    public function testFindAndLockEmpty(): void
    {
        MethodMocker::sniff(Table::class, 'save')->expectCall(0);
        $query = $this->TestTableTwo->find()->where(['id' => 26]);
        $result = $this->TestTableTwo->updateWithLock($query, ['table_one_fk' => 45]);
        self::assertNull($result, 'Не пустой результат при некорректном запросе');
    }

    /**
     * короткое описание опций для findList
     */
    public function testShortFindList(): void
    {
        /** @var TestTableTwo $entity1 */
        $entity1 = TestTableTwoFactory::make()->persist();
        /** @var TestTableTwo $entity2 */
        $entity2 = TestTableTwoFactory::make()->persist();
        /** @var TestTableTwo $entity3 */
        $entity3 = TestTableTwoFactory::make()->persist();
        $this->TestTableTwo->saveArr(['table_one_fk' => $entity1->TestTableOne->id], $entity3);

        // одно поле - и ключ и значение
        $classicList = $this->TestTableTwo->find('list', [
            'keyField' => 'id',
            'valueField' => 'id',
        ])->toArray();
        $shortList = $this->TestTableTwo->find('list', ['id'])->toArray();
        $expectedList = [
            $entity1->id => $entity1->id,
            $entity2->id => $entity2->id,
            $entity3->id => $entity3->id,
        ];
        self::assertEquals($expectedList, $classicList);
        self::assertEquals($expectedList, $shortList);

        // ключ => значение
        $classicList = $this->TestTableTwo->find('list', [
            'keyField' => 'id',
            'valueField' => 'table_one_fk',
        ])->toArray();
        $shortList = $this->TestTableTwo->find('list', ['id' => 'table_one_fk'])->toArray();
        $expectedList = [
            $entity1->id => $entity1->TestTableOne->id,
            $entity2->id => $entity2->TestTableOne->id,
            $entity3->id => $entity1->TestTableOne->id,
        ];
        self::assertEquals($expectedList, $classicList);
        self::assertEquals($expectedList, $shortList);

        // выражения и алиасы
        $query = $this->TestTableTwo->find();
        $classicList = $this->TestTableTwo
            ->find('list', [
                'keyField' => 'table_one_fk',
                'valueField' => 'cnt',
            ])
            ->select([
                'table_one_fk',
                'cnt' => $query->func()->count('*'),
            ])
            ->group(['table_one_fk'])
            ->toArray();
        $shortList = $this->TestTableTwo->find('list', ['table_one_fk' => 'cnt'])
            ->select([
                'table_one_fk',
                'cnt' => $query->func()->count('*'),
            ], true)
            ->group(['table_one_fk'])
            ->toArray();
        $expectedList = [
            $entity1->TestTableOne->id => 2,
            $entity2->TestTableOne->id => 1,
        ];
        self::assertEquals($expectedList, $classicList);
        self::assertEquals($expectedList, $shortList);

        // сортировка
        $shortList = $this->TestTableTwo->find('list', ['table_one_fk'])
            ->select([
                'cnt' => $query->func()->count('*'),
            ])
            ->group(['table_one_fk'])
            ->orderAsc('cnt')
            ->toArray();
        $expectedList = [
            $entity2->TestTableOne->id => $entity2->TestTableOne->id,
            $entity1->TestTableOne->id => $entity1->TestTableOne->id,
        ];
        self::assertEquals($expectedList, $shortList);
        // проверка сортировки. просто assertEquals не учитывает порядок ключей
        self::assertEquals(array_keys($expectedList), array_keys($shortList));

        // джоины
        $shortList = $this->TestTableTwo
            ->find('list', [
                'id' => 'TestTableOne.col_text',
            ])
            ->contain('TestTableOne')
            ->where([
                'table_one_fk' => $entity1->TestTableOne->id,
            ])
            ->toArray();
        $expectedList = [
            $entity1->id => $entity1->TestTableOne->col_text,
            $entity3->id => $entity1->TestTableOne->col_text,
        ];
        self::assertEquals($expectedList, $shortList);
    }
}
