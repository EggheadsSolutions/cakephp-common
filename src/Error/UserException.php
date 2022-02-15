<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Error;

/**
 * @method static UserException instance(string $message, int $code = 0, \Exception|null $previous = null)
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
     * @inheritdoc
     * по умолчанию выключено
     */
    protected bool $_writeToLog = false;


    /**
     * @inheritdoc
     * @param string $message
     * @param int $code
     * @param ?\Exception $previous
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
     * @param string $message
     * @return $this
     */
    public function setUserMessage(string $message): self
    {
        $this->_userMessage = (string)$message;
        return $this;
    }
}
