<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\TestSuite;

use Eggheads\CakephpCommon\Error\InternalException;
use Eggheads\CakephpCommon\Lib\AppCache;
use Eggheads\CakephpCommon\Lib\Strings;
//use Eggheads\CakephpCommon\Mailer\Transport\TestEmailTransport;
use Eggheads\CakephpCommon\ORM\Entity;
use Eggheads\CakephpCommon\TestSuite\HttpClientMock\HttpClientAdapter;
use Eggheads\CakephpCommon\TestSuite\HttpClientMock\HttpClientMocker;
use Eggheads\Mocks\ConstantMocker;
use Eggheads\Mocks\MethodMocker;
use Eggheads\Mocks\PropertyAccess;
use Eggheads\CakephpCommon\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Inflector;
use Exception;
use PHPUnit\Framework\AssertionFailedError;
use ReflectionException;

/**
 * Тестовое окружение
 *
 * @package App\Test
 */
trait TestCaseTrait
{
    /**
     * Список правильно проинициализированных таблиц
     *
     * @var string[]
     */
    private static array $_tableRegistry = [];

    /** Вызывать в реальном setUpBeforeClass */
    protected static function _setUpBeforeClass(): void
    {
        // noop
    }

    /**
     * Инициализация тестового окружения
     *
     * @throws InternalException
     */
    protected function _setUp(): void
    {
        $this->_clearCache();
        PermanentMocksCollection::init();
        $this->_loadFixtureModels();

        HttpClientAdapter::enableDebug();
        $this->_setUpLocal();
    }

    /**
     * Чистка тестового окружения
     *
     * @throws ReflectionException|AssertionFailedError|Exception
     */
    protected function _tearDown(): void
    {
        /** @var TestCase $this */
        ConstantMocker::restore();
        PropertyAccess::restoreStaticAll();
        FrozenTime::setTestNow(null); // сбрасываем тестовое время
        // TestEmailTransport::clearMessages(); раскоммитить после переноса TestEmailTransport
        $this->_tearDownLocal(); // @phpstan-ignore-line
        SingletonCollection::clearCollection();

        try {
            MethodMocker::restore($this->hasFailed());
        } finally {
            PermanentMocksCollection::destroy();
            HttpClientMocker::clean($this->hasFailed());
        }
    }

    /**
     * Для локальных действий на setUp
     *
     * @return void
     */
    protected function _setUpLocal()
    {
        // noop
    }

    /** Для локальных действий на tearDown */
    protected function _tearDownLocal(): void
    {
        // noop
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
     * Чистка кеша
     */
    protected function _clearCache(): void
    {
        AppCache::flushExcept(['_cake_core_', '_cake_model_']);
    }

    /**
     * loadModel на все таблицы фикстур
     */
    protected function _loadFixtureModels(): void
    {
        if (empty($this->fixtures)) {
            return;
        }
        foreach ($this->fixtures as $fixtureName) {
            $modelAlias = Inflector::camelize(Strings::lastPart('.', $fixtureName));
            if (TableRegistry::getTableLocator()->exists($modelAlias)) {
                TableRegistry::getTableLocator()->remove($modelAlias);
            }
            $this->{$modelAlias} = TableRegistry::getTableLocator()->get($modelAlias, [
                'className' => $modelAlias,
                'testInit' => true,
            ]);
        }
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
