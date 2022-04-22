<?php
declare(strict_types=1);
declare(ticks=1);

namespace Eggheads\CakephpCommon\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Eggheads\CakephpCommon\I18n\FrozenTime;
use Eggheads\CakephpCommon\Lib\Env;

/**
 * Абстрактный класс обработчика очереди. Выполняет задание определённое кол-во времени config['maxQueueRunSeconds'], либо
 * завершает выполнение при превышении 90% лимита отведённой памяти.
 * Также корректно отрабатывает сигналы остановки процесса.
 */
abstract class AbstractQueueCommand extends Command
{
    /** @var int Сколько секунд ждать между запросами */
    protected const SLEEP_SECONDS = 1;

    private const MAX_MEMORY_LIMIT_BYTES = 4294967296;

    /** @var int Время запуска по-умолчанию */
    protected const DEFAULT_QUEUE_RUN_SECONDS = 300;

    /**
     * @var bool Сигнал остановки
     */
    private bool $_suspend = false;

    /**
     * @var int UnixTime времени завершения процесса
     */
    private int $_endMeTime;

    /**
     * @var int Лимит памяти на процесс
     */
    private int $_memoryLimit;

    /**
     * inheritdoc
     * @SuppressWarnings(PHPMD)
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->_memoryLimit = (int)ini_get('memory_limit');
        if (!$this->_memoryLimit || $this->_memoryLimit < 0) {
            $this->_memoryLimit = self::MAX_MEMORY_LIMIT_BYTES;
        } else {
            $this->_memoryLimit = (int)ceil($this->_memoryLimit * 0.9); // оставляем запас в 10%
        }

        $runSeconds = Env::getMaxQueueRunSeconds();
        if (!$runSeconds) {
            $runSeconds = static::DEFAULT_QUEUE_RUN_SECONDS;
        }

        $this->_endMeTime = (int)FrozenTime::now()->toUnixString() + $runSeconds;

        pcntl_signal(SIGTERM, [$this, "sigHandler"]);
        pcntl_signal(SIGINT, [$this, "sigHandler"]);

        return $this->_do($io);
    }

    /**
     * Запуск задания
     *
     * @param ConsoleIo $io
     * @return ?int Код завершения, если > 0, то выход
     */
    abstract protected function _runTask(ConsoleIo $io): ?int;

    /**
     * Обработчик событий от системы
     *
     * @return void
     */
    public function sigHandler(): void
    {
        $this->_suspend = true;
    }

    /**
     * Выполняю операцию
     *
     * @param ConsoleIo $io
     * @return ?int
     */
    private function _do(ConsoleIo $io): ?int
    {
        do {
            $taskCode = $this->_runTask($io);
            if ($taskCode > 0) {
                return $taskCode;
            }

            $usedMemory = memory_get_usage(true);
            if ($this->_suspend) {
                $io->info('Received exit command');
                return null;
            } elseif ((int)FrozenTime::now()->toUnixString() > $this->_endMeTime) {
                $io->info('Script run timeout');
                return null;
            } elseif ($usedMemory > $this->_memoryLimit) {
                $io->info('Script memory limit reached (' . $this->_memoryLimit . ')');
                return null;
            }
            sleep(static::SLEEP_SECONDS);
        } while (true);
    }
}
