<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\TestSuite\PermanentMocks;

use Eggheads\CakephpCommon\TestSuite\ClassMockEntity;
use Eggheads\Mocks\MethodMocker;
use Cake\Error\Debugger;
use Cake\Log\Log;
use Exception;

class MockLog extends ClassMockEntity
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public static function init()
    {
        MethodMocker::mock(Log::class, 'write', 'return ' . self::class . '::write(...func_get_args());');
    }

    /**
     * Вывод ошибка вместо файла в консоль
     *
     * @param int|string $level
     * @param string $message
     * @return bool
     */
    public static function write(int|string $level, string $message): bool
    {
        $trace = Debugger::trace();
        $trace = explode("\n", $trace);
        $test = '';
        foreach ($trace as $line) {
            // последняя строчка трейса в которой есть слово тест и нет пхпюнит - это строка теста, вызвавшего запись в лог
            if (stristr($line, 'test') && !stristr($line, 'phpunit')) {
                $test = $line;
            }
        }
        $file = $trace[4];
        self::_writeToConsole("test: $test \n Write to '$level' log from $file: $message\n\n");
        return true;
    }
}
