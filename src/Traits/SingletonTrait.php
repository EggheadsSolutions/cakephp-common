<?php // phpcs:ignore

namespace Eggheads\CakephpCommon\Traits;

use Eggheads\CakephpCommon\Lib\Env;
use Eggheads\CakephpCommon\TestSuite\SingletonCollection;

/**
 * Трейт-одиночка.
 * strict_types специально не объявлено, ибо не работает с ним
 */
trait SingletonTrait
{
    /**
     * Объект-одиночка
     *
     * @var ?static
     */
    private static $_instance;

    /**
     * Защищаем от создания через new Singleton
     */
    private function __construct()
    {
    }

    /**
     * Защищаем от создания через клонирование
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Защищаем от создания через unserialize
     *
     * @return void
     */
    public function __wakeup()
    {
    }

    /**
     * Возвращает объект-одиночку
     *
     * @return static
     */
    public static function getInstance(): static
    {
        if (empty(static::$_instance)) {
            static::$_instance = new static(); // @phpstan-ignore-line
        }

        if (Env::isUnitTest()) {
            SingletonCollection::append(static::class);
        }

        return static::$_instance;
    }

    /**
     * Подчищаем инстанс, если объект уничтожили
     */
    public function __destruct()
    {
        static::$_instance = null;
    }
}
