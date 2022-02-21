<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Error\Middleware;

use Eggheads\CakephpCommon\Event\Sentry\ContextExceptionInterface;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use Throwable;

class ErrorHandlerMiddleware extends \Cake\Error\Middleware\ErrorHandlerMiddleware implements ContextExceptionInterface
{
    /**
     * @var \Throwable Exception
     */
    private Throwable $_exception;

    /**
     * Копия родительского метода.
     * Чтобы использовать SentryLog::logException().
     * По-умолчанию был плохой трейс и нельзя делать warn.
     * И исключения phpunit теперь прокидываются дальше.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Throwable $exception
     */
    // phpcs:ignore
    protected function logException(RequestInterface $request, Throwable $exception)
    {
        $this->_exception = $exception;

        if (!$this->getConfig('log')) {
            return;
        }

        $skipLog = $this->getConfig('skipLog');
        if ($skipLog) {
            foreach ((array)$skipLog as $class) {
                if ($exception instanceof $class) {
                    return;
                }
            }
        }
    }

    /**
     * @inheritDoc Поднял logException наверх, чтобы он вызывался до render.
     */
    public function handleException($exception, $request): ResponseInterface
    {
        $errorHandler = $this->getErrorHandler();
        $renderer = $errorHandler->getRenderer($exception, $request);
        try {
            $this->logException($request, $exception);

            return $renderer->render();
        } catch (Exception $e) {
            $this->_exception = $e;
            $this->logException($request, $e);

            $response = $this->handleException($exception, $request);

            $body = $response->getBody();
            $body->write('An Internal Server Error Occurred');
            $response = $response->withStatus(500)
                ->withBody($body);
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function getContext(): array
    {
        $cakeMessage = new stdClass();
        $cakeMessage->cakeMessage = $this->_exception->getMessage();

        return [$cakeMessage];
    }
}
