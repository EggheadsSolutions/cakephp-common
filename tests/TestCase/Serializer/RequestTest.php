<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\Serializer;

use Cake\Validation\Validator;
use Eggheads\CakephpCommon\Traits\ConverterTrait;

class RequestTest
{
    use ConverterTrait;

    /**
     * @var int Числовое поле
     */
    public int $fieldInt;

    /**
     * @var string|null Строковое поле
     */
    public ?string $fieldString = null;

    /**
     * @var RequestTest|null Объект
     */
    public ?RequestTest $fieldObject = null;

    /**
     * @var RequestTest[] Объекты
     */
    public array $objects = [];

    /**
     * @param Validator|array $validator Валидатор
     * @return Validator
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function addValidation(Validator|array $validator): Validator
    {
        $validator->requirePresence('fieldInt', true, 'Не указан fieldInt');

        return $validator;
    }
}
