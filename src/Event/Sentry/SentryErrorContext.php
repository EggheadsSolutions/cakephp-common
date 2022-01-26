<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Event\Sentry;

use Cake\Error\Debugger;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Http\ServerRequestFactory;
use Cake\Log\Log;
use Cake\Log\LogTrait;
use Sentry\State\Scope;

use Sentry\UserDataBag;
use function Sentry\configureScope as sentryConfigureScope;

class SentryErrorContext implements EventListenerInterface
{
    /** Максимальная глубина рекурсии */
    private const INFO_MAX_NEST_LEVEL = 5;

    // 0, 1, 2 - стереть
    // 3 - Log::write, нужно стереть, если он был вызван из другого метода (например Log::error) или из LogTrait::log, иначе оставить
    // т.е. нужно проверить 4й уровень
    private const DELETE_TRACE_LEVEL_DEFAULT = 4;

    /**
     * @inerhitDoc
     */
    public function implementedEvents(): array
    {
        return [
            'CakeSentry.Client.beforeCapture' => 'setContext',
        ];
    }

    /**
     * Добавление данных окружения
     *
     * @param Event $event
     */
    public function setContext(Event $event): void
    {
        $trace = $event->getData('stackTrace');
        $extra = [
            '_args' => $this->_exportVar($this->_getCallArgs($trace)),
        ];
        $user = null;
        $tags = [];

        $exception = $event->getData('exception');
        if ($exception) {
            $tags['status'] = $exception->getCode();

            if ($exception instanceof ContextExceptionInterface) {
                $extra['_context'] = $exception->getContext();
            }
        }

        if (PHP_SAPI === 'cli') {
            global $argv;
            $extra['_argv'] = $this->_exportVar($argv);
        } else {
            $extra += [
                '_post' => $this->_exportVar($_POST),
                '_get' => $this->_exportVar($_GET),
                '_referrer' => empty($_SERVER['HTTP_REFERER']) ? null : $_SERVER['HTTP_REFERER'],
            ];

            $request = $event->getData('request') ?? ServerRequestFactory::fromGlobals();
            $request->trustProxy = true;

            $user = new UserDataBag(ipAddress: $request->clientIp());
        }

        sentryConfigureScope(function (Scope $scope) use ($extra, $user, $tags) {
            $scope->setExtras($extra);
            $scope->setTags($tags);
            if ($user) {
                $scope->setUser($user);
            }
        });
    }

    /**
     * Экспортировать переменную для передачи в сентри
     *
     * @param mixed $var
     * @return string
     */
    private function _exportVar($var): string
    {
        return empty($var) ? 'empty' : Debugger::exportVarAsPlainText($var, self::INFO_MAX_NEST_LEVEL);
    }

    /**
     * Получим доп.инфу по аргументам
     *
     * @param array $trace
     * @return array
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    private function _getCallArgs(array $trace): array
    {
        $toSlice = self::DELETE_TRACE_LEVEL_DEFAULT;
        $logWriteCall = $trace[$toSlice];
        $aboveLogWrite = $trace[$toSlice + 1];
        [, $logTrait] = namespaceSplit(LogTrait::class);
        if (
            // Log::error/warning/...
            ($this->_arrayGet($aboveLogWrite, 'class') === Log::class)
            // LogTrait::log
            || ($this->_stringsEndsWith($this->_arrayGet($logWriteCall, 'file'), $logTrait . '.php'))
        ) {
            $toSlice++;
        }

        $handleError = $trace[$toSlice];
        if (
            // ошибочные вызовы нативных функций тоже делают нехорошо
            empty($this->_arrayGet($handleError, 'file'))
            && ($this->_arrayGet($handleError, 'function') === 'handleError')
            && ($this->_arrayGet($handleError, 'type') === '->')
        ) {
            $toSlice++;
        }

        $result = [];
        $argsLevels = range($toSlice - 1, $toSlice);

        foreach ($argsLevels as $level) {
            if (empty($trace[$level])) {
                continue;
            }

            $callInfo = $trace[$level];
            $function = $callInfo['function'];
            $class = $callInfo['class'] ?? null;

            if (!empty($class)) {
                $function = $class . '::' . $function;
            }

            $function = $level . ' - ' . $function;
            $result[$function] = $callInfo['args'] ?? null;
        }

        return $result;
    }

    /**
     * Получить значение по ключу с проверками
     *
     * @param ?array<string|int, mixed> $array
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    private function _arrayGet(?array $array, $key, $default = null)
    {
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        } else {
            return $default;
        }
    }

    /**
     * Проверка, что строка заканчивается любым постфиксом из списка
     *
     * @param string $string
     * @param string|string[] $postfixes
     * @return bool
     */
    private function _stringsEndsWith(string $string, $postfixes): bool
    {
        $stringLength = strlen($string);
        foreach ((array)$postfixes as $postfix) {
            if (strripos($string, $postfix) === ($stringLength - strlen($postfix))) {
                return true;
            }
        }
        return false;
    }
}
