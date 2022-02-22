<?php // phpcs:ignore

namespace Eggheads\CakephpCommon\Traits;

trait LibraryTrait
{
    /**
     * Защищаем от создания через new
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
}
