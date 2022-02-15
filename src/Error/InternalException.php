<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Error;

use Eggheads\CakephpCommon\Event\Sentry\ContextExceptionInterface;

/**
 * @method static InternalException instance(string $message, int $code = 0, \Exception|null $previous = null)
 */
class InternalException extends Exception implements ContextExceptionInterface
{

}
