<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Event\Sentry;

interface ContextExceptionInterface
{
    /**
     * Доп информация при логировании Exception
     *
     * @return array
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function getContext(): array;
}
