<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\TestSuite;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Eggheads\CakephpCommon\Error\InternalException;
use Eggheads\CakephpCommon\I18n\FrozenTime;
use Eggheads\CakephpCommon\Lib\AppCache;
use Eggheads\CakephpCommon\Lib\DB;
use Eggheads\CakephpCommon\ORM\Entity;
use Eggheads\CakephpCommon\Plugins\CakephpFixtureFactories\DiscoverFactories;
use Eggheads\CakephpCommon\TestSuite\HttpClientMock\HttpClientAdapter;
use Eggheads\CakephpCommon\TestSuite\HttpClientMock\HttpClientMocker;
use Eggheads\CakephpCommon\ValueObject\ValueObject;
use Eggheads\Mocks\ConstantMocker;
use Eggheads\Mocks\MethodMocker;
use Eggheads\Mocks\PropertyAccess;
use InvalidArgumentException;
use ReflectionException;

/**
 * @SuppressWarnings(PHPMD.MethodMix)
 */
abstract class AppTestCase extends TestCase
{
    /**
     * @inheritdoc
     * @throws InternalException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_clearCache();
        PermanentMocksCollection::init();

        HttpClientAdapter::enableDebug();
    }

    /**
     * @inheritdoc
     * @throws ReflectionException
     */
    public function tearDown(): void
    {
        parent::tearDown();

        ConstantMocker::restore();
        PropertyAccess::restoreStaticAll();
        FrozenTime::setTestNow(null); // сбрасываем тестовое время
        // TestEmailTransport::clearMessages(); раскоммитить после переноса TestEmailTransport

        SingletonCollection::clearCollection();

        try {
            MethodMocker::restore($this->hasFailed());
        } finally {
            PermanentMocksCollection::destroy();
            HttpClientMocker::clean($this->hasFailed());
        }

        $this->_truncateTables();
    }

    /**
     * Отключение постоянного мока; вызывать перед parent::setUp();
     *
     * @param string $mockClass
     */
    protected function _disablePermanentMock(string $mockClass): void
    {
        PermanentMocksCollection::disableMock($mockClass);
    }

    /**
     * loadModel на все таблицы фикстур
     */
    public function setupFixtures(): void
    {
        if (!empty($this->fixtures)) {
            throw new InvalidArgumentException('Fixtures в этом проекте не используются, удалите "$this->fixtures"');
        }

        $tables = DiscoverFactories::getTableList();

        foreach ($tables as $modelAlias) {
            if (!TableRegistry::getTableLocator()->exists($modelAlias)) {
                $this->{$modelAlias} = TableRegistry::getTableLocator()->get($modelAlias, [
                    'className' => $modelAlias,
                    'testInit' => true,
                ]);
            }
        }
    }

    /**
     * Очистка таблиц после каждого теста
     *
     * @return void
     */
    protected function _truncateTables(): void
    {
        $tables = DiscoverFactories::getTableList();
        $connection = DB::getConnection(DB::CONNECTION_TEST);

        $connection->disableForeignKeys();

        foreach ($tables as $modelAlias) {
            if (isset($this->{$modelAlias})) {
                $this->{$modelAlias}->truncate();
            }
        }

        $connection->enableForeignKeys();
    }

    /**
     * Чистка кеша
     */
    protected function _clearCache(): void
    {
        AppCache::flushExcept(['_cake_core_', '_cake_model_']);
    }

    /**
     * Задать тестовое время
     * Чтоб можно было передавать строку
     *
     * @param FrozenTime|string|null $time
     * @param bool $clearMicroseconds убрать из времени микросекунды (PHP7).
     *                                Полезно тем, что в базу микросекунды всё равно не сохранятся
     * @return FrozenTime
     */
    protected function _setTestNow(FrozenTime|string $time = null, bool $clearMicroseconds = true): FrozenTime
    {
        if (!($time instanceof FrozenTime)) {
            $time = new FrozenTime($time);
        }
        if ($clearMicroseconds) {
            $time = $time->setTime($time->hour, $time->minute, $time->second);
        }
        FrozenTime::setTestNow($time);
        return $time;
    }

    /**
     * Проверка совпадения части массива
     * Замена нативного assertArraySubset, который не показывает красивые диффы
     *
     * @param array $expected
     * @param array $actual
     * @param string $message
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function assertArraySubsetEquals(
        array  $expected,
        array  $actual,
        string $message = ''
    ): void {
        $actual = array_intersect_key($actual, $expected);
        self::assertEquals($expected, $actual, $message);
    }

    /**
     * Проверка части полей сущности
     *
     * @param array $expectedSubset
     * @param Entity $entity
     * @param string $message
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function assertEntitySubset(
        array  $expectedSubset,
        Entity $entity,
        string $message = ''
    ): void {
        $this->assertArraySubsetEquals($expectedSubset, $entity->toArray(), $message);
    }

    /**
     * Сравнение двух сущностей
     *
     * @param Entity $expectedEntity
     * @param Entity $actualEntity
     * @param string $message
     */
    public function assertEntityEqualsEntity(
        Entity $expectedEntity,
        Entity $actualEntity,
        string $message = ''
    ): void {
        self::assertEquals($expectedEntity->toArray(), $actualEntity->toArray(), $message);
    }

    /**
     * Сравнение двух сущностей
     *
     * @param array $expectedArray
     * @param Entity $actualEntity
     * @param string $message
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public function assertEntityEqualsArray(
        array  $expectedArray,
        Entity $actualEntity,
        string $message = ''
    ): void {
        self::assertEquals($expectedArray, $actualEntity->toArray(), $message);
    }

    /**
     * Содержимое файла соответствует ожидаемой строке
     *
     * @param string $expectedString
     * @param string $actualFile
     * @param string $message
     */
    public function assertFileEqualsString(
        string $expectedString,
        string $actualFile,
        string $message = ''
    ): void {
        self::assertFileExists($actualFile, $message);
        self::assertEquals($expectedString, file_get_contents($actualFile), $message);
    }

    /**
     * Метод для сравнения двух ValueObject с указанием допустимой погрешности в процентах
     *
     * @param ValueObject $expected
     * @param ValueObject $actual
     * @param float $errorRatePercent допустимая погрешность в процентах
     * @return void
     */
    public static function assertEqualsValueObjects(ValueObject $expected, ValueObject $actual, float $errorRatePercent = 0): void
    {
        self::assertEquals(get_class($expected), get_class($actual));
        foreach ($actual->toArray() as $property => $value) {
            $delta = is_numeric($value) ? ($value * $errorRatePercent * 0.01) : 0.0;
            self::assertEquals($expected->{$property}, $value, '', $delta);
        }
    }
}
