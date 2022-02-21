<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Error;

/**
 * @method static \Eggheads\CakephpCommon\Error\UserException instance(string $message, int $code = 0, \Eggheads\CakephpCommon\Error\Exception|null $previous = null)
 */
class UserException extends Exception
{
    /**
     * Сообщение, которое будет выведено юзеру.
     * По умолчанию это то же самое, что и message, но можно задать что-то другое.
     * message используется для записи в лог
     *
     * @var string
     */
    protected string $_userMessage = '';

    /**
     * @inheritDoc По умолчанию выключено
     */
    protected bool $_writeToLog = false;

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setUserMessage($this->message);
    }

    /**
     * Получить сообщение для юзера
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return $this->_userMessage;
    }

    /**
     * Задать специальное сообщение для юзера
     *
     * @param string $message Сообщение
     * @return $this
     */
    public function setUserMessage(string $message): static
    {
        $this->_userMessage = (string)$message;

        return $this;
    }
}
