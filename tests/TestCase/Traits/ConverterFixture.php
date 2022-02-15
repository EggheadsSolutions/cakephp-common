<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\Traits;

use Eggheads\CakephpCommon\Traits\ConverterTrait;

class ConverterFixture
{
    use ConverterTrait;

    /** @var int Числовое поле */
    public int $intField;

    /** @var string Строковое поле */
    public string $stringField;

    /** @var bool Булевое поле */
    public bool $boolField;
}
