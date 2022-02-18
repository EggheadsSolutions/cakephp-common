<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Error;

use stdClass;

/**
 * @internal
 * @SuppressWarnings(PHPMD.MethodMix)
 * @SuppressWarnings(PHPMD.MethodProps)
 */
class Exception extends \Exception
{
    /** @var string Ключ доп. информации для логирования */
    private const KEY_ADD_INFO = 'addInfo';

    /** @var string Ключ для Scope */
    private const KEY_SCOPE = 'scope';

    /**
     * Логировать ли это исключение
     *
     * @var bool
     */
    protected bool $_writeToLog = true;

    /**
     * Инфа для логов
     *
     * @var array
     */
    protected array $_logContext = []; // @phpstan-ignore-line

    /**
     * Слать ли оповещения при ошибке
     *
     * @var bool
     */
    protected bool $_alert = true;

    /**
     * Создание эксепшна в статическом стиле
     *
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     * @return static
     */
    public static function instance(string $message = '', int $code = 0, ?\Exception $previous = null): Exception
    {
        /** @phpstan-ignore-next-line */
        return new static($message, $code, $previous);
    }

    /**
     * Писать ли об ошибке в лог
     *
     * @param bool $writeToLog
     * @return $this
     */
    public function setWriteToLog(bool $writeToLog): Exception
    {
        $this->_writeToLog = $writeToLog;
        return $this;
    }

    /**
     * Задать scope для логирования ошибок
     *
     * @param string|string[]|null $scope
     * @return $this
     */
    public function setLogScope(array|string|null $scope): Exception
    {
        if ($scope === null) {
            unset($this->_logContext[self::KEY_SCOPE]);
            return $this;
        }
        $this->_logContext[self::KEY_SCOPE] = (array)$scope;
        return $this->setWriteToLog(true);
    }

    /**
     * Задать доп. инфу для логирования
     *
     * @param mixed $info
     * @return $this
     */
    public function setLogAddInfo(mixed $info): Exception
    {
        if ($info === null) {
            unset($this->_logContext[self::KEY_ADD_INFO]);
            return $this;
        }
        $this->_logContext[self::KEY_ADD_INFO] = $info;
        return $this->setWriteToLog(true);
    }

    /**
     * Задать контекст для логов.
     *
     * @param array $context
     * @param bool $fullOverwrite
     * @return $this
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function setLogContext(array $context, bool $fullOverwrite = false): Exception
    {
        if ($fullOverwrite) {
            $this->_logContext = $context;
        } else {
            $this->_logContext = $context + $this->_logContext;
        }
        return $this->setWriteToLog(true);
    }

    /**
     * Рекурсивно подготавливает данные для метода getContext
     *
     * @param mixed $val
     * @return mixed
     */
    private function _getContextObj(mixed $val): mixed
    {
        if (is_array($val)) {
            $obj = new stdClass();
            foreach ($val as $key => $value) {
                $stringKey = (string)$key;
                $obj->$stringKey = $this->_getContextObj($value);
            }
            return $obj;
        }
        return $val;
    }

    /**
     * Получение контекста для логирования
     *
     * @return array<int, mixed>
     */
    public function getContext(): array
    {
        $out = [];

        if ($this->_writeToLog === true && !empty($this->_logContext)) {
            $out[] = $this->_getContextObj($this->_logContext);
        }

        return $out;
    }
}
