<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Controller\Request;

use Eggheads\CakephpCommon\Traits\ConverterTrait;

/**
 * @SuppressWarnings(PHPMD.MethodMix)
 */
abstract class AbstractRequest implements ValidationInterface
{
    use ConverterTrait;
}
