<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\ORM;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\Database\FunctionsBuilder;

/**
 * @SuppressWarnings(PHPMD.MethodMix)
 */
class Query extends \Cake\ORM\Query
{
    /**
     * Построитель функций
     *
     * @var ?FunctionsBuilder
     */
    private static ?FunctionsBuilder $_funcBuilder = null;

    /**
     * статичная версия func()
     *
     * @return FunctionsBuilder
     */
    public static function funct(): FunctionsBuilder
    {
        if (empty(self::$_funcBuilder)) {
            self::$_funcBuilder = new FunctionsBuilder();
        }
        return self::$_funcBuilder;
    }
}
