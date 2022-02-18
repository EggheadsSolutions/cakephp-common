<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\TestSuite;

/**
 * Мок методов касса
 */
abstract class ClassMockEntity
{
    /**
     * Базовый метод, который и иницализирует все подмены
     *
     * @return void
     */
    public static function init()
    {
    }

    /**
     * Вызов после каждого теста
     *
     * @return void
     */
    public static function destroy()
    {
    }

    /**
     * Записать сообщение
     *
     * @param string $str Строка сообщения
     * @return void
     */
    protected static function _writeToConsole(string $str): void
    {
        PermanentMocksCollection::setHasWarning(true);
        file_put_contents('php://stderr', $str);
    }
}
