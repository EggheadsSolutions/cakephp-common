<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\TestSuite;

use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Eggheads\CakephpCommon\I18n\FrozenTime;
use Eggheads\CakephpCommon\Lib\AppCache;
use Eggheads\CakephpCommon\Lib\Strings;
//use Eggheads\CakephpCommon\Mailer\Transport\TestEmailTransport;
use Eggheads\CakephpCommon\ORM\Entity;
use Eggheads\CakephpCommon\TestSuite\HttpClientMock\HttpClientAdapter;
use Eggheads\CakephpCommon\TestSuite\HttpClientMock\HttpClientMocker;
use Eggheads\Mocks\ConstantMocker;
use Eggheads\Mocks\MethodMocker;
use Eggheads\Mocks\PropertyAccess;

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

    /**
     * Вызывать в реальном setUpBeforeClass
     *
     * @return void
     */
    protected static function _setUpBeforeClass(): void
    {
        // noop
    }

    /**
     * Инициализация тестового окружения
     *
     * @return void
     * @throws \Eggheads\CakephpCommon\Error\InternalException
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
     * @return void
     * @throws \ReflectionException
     */
    protected function _tearDown(): void
    {
        /** @var static $this */
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

    /**
     * Для локальных действий на tearDown
     *
     * @return void
     */
    protected function _tearDownLocal(): void
    {
        // noop
    }

    /**
     * Отключение постоянного мока; вызывать перед parent::setUp();
     *
     * @param string $mockClass Название класса для мока
     * @return void
     */
    protected function _disablePermanentMock(string $mockClass): void
    {
        PermanentMocksCollection::disableMock($mockClass);
    }

    /**
     * Чистка кеша
     *
     * @return void
     */
    protected function _clearCache(): void
    {
        AppCache::flushExcept(['_cake_core_', '_cake_model_']);
    }

    /**
     * loadModel на все таблицы фикстур
     *
     * @return void
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
     * @param FrozenTime|string|null $time Время для установки как Now
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
            $time = $time->copy()->setTime($time->hour, $time->minute, $time->second, 0);
        }
        FrozenTime::setTestNow($time);
        return $time;
    }

    /**
     * Проверка совпадения части массива
     * Замена нативного assertArraySubset, который не показывает красивые диффы
     *
     * @param array $expected Ожидаемые данные
     * @param array $actual Актуальные данные
     * @param string $message Сообщение
     * @return void
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
     * @param array $expectedSubset Ожидаемое подмножество
     * @param Entity $entity Сущность
     * @param string $message Сообщение
     * @return void
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
     * @param Entity $expectedEntity Ожидаемая сущность
     * @param Entity $actualEntity Актуальная сущность
     * @param string $message Сообщение
     * @return void
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
     * @param array $expectedArray Ожидаемый массив
     * @param Entity $actualEntity Актуальная сущность
     * @param string $message Сообщение
     * @return void
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
     * @param string $expectedString Ожидаемая строка
     * @param string $actualFile Актуальный файл
     * @param string $message Сообщение
     */
    public function assertFileEqualsString(
        string $expectedString,
        string $actualFile,
        string $message = ''
    ): void {
        self::assertFileExists($actualFile, $message);
        self::assertEquals(
            $expectedString,
            file_get_contents($actualFile),
            $message
        );
    }
}
