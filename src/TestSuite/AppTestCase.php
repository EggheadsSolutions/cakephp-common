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
    protected function setupFixtures(): void
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
            $time->setTime($time->hour, $time->minute, $time->second, 0);
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
     * @param float $delta
     * @param int $maxDepth
     * @param bool $canonicalize
     * @param bool $ignoreCase
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function assertArraySubsetEquals(
        array  $expected,
        array  $actual,
        string $message = '',
        float  $delta = 0.0,
        int    $maxDepth = 10,
        bool   $canonicalize = false,
        bool   $ignoreCase = false
    ): void {
        $actual = array_intersect_key($actual, $expected);
        self::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    /**
     * Проверка части полей сущности
     *
     * @param array $expectedSubset
     * @param Entity $entity
     * @param string $message
     * @param float $delta
     * @param int $maxDepth
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function assertEntitySubset(
        array  $expectedSubset,
        Entity $entity,
        string $message = '',
        float  $delta = 0.0,
        int    $maxDepth = 10
    ): void {
        $this->assertArraySubsetEquals($expectedSubset, $entity->toArray(), $message, $delta, $maxDepth);
    }

    /**
     * Сравнение двух сущностей
     *
     * @param Entity $expectedEntity
     * @param Entity $actualEntity
     * @param string $message
     * @param float $delta
     * @param int $maxDepth
     */
    public function assertEntityEqualsEntity(
        Entity $expectedEntity,
        Entity $actualEntity,
        string $message = '',
        float  $delta = 0.0,
        int    $maxDepth = 10
    ): void {
        self::assertEquals($expectedEntity->toArray(), $actualEntity->toArray(), $message, $delta, $maxDepth);
    }

    /**
     * Сравнение двух сущностей
     *
     * @param array $expectedArray
     * @param Entity $actualEntity
     * @param string $message
     * @param float $delta
     * @param int $maxDepth
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public function assertEntityEqualsArray(
        array  $expectedArray,
        Entity $actualEntity,
        string $message = '',
        float  $delta = 0.0,
        int    $maxDepth = 10
    ): void {
        self::assertEquals($expectedArray, $actualEntity->toArray(), $message, $delta, $maxDepth);
    }

    /**
     * Содержимое файла соответствует ожидаемой строке
     *
     * @param string $expectedString
     * @param string $actualFile
     * @param string $message
     * @param bool $canonicalize
     * @param bool $ignoreCase
     */
    public function assertFileEqualsString(
        string $expectedString,
        string $actualFile,
        string $message = '',
        bool   $canonicalize = false,
        bool   $ignoreCase = false
    ): void {
        self::assertFileExists($actualFile, $message);
        self::assertEquals(
            $expectedString,
            file_get_contents($actualFile),
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
    }
}
