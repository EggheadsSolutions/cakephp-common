<?php
declare(strict_types=1);

namespace TestApp\Controller;

use Eggheads\CakephpCommon\ValueObject\ValueObject;

class TestValueObject extends ValueObject
{
    /** @var string Тестовое свойство */
    public string $testProperty = 'testData';
}
