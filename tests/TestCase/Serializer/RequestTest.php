<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\Serializer;

use Cake\Validation\Validator;
use Eggheads\CakephpCommon\Traits\ConverterTrait;
use Eggheads\CakephpCommon\Validation\DynamicChildrenRule;
use Eggheads\CakephpCommon\Validation\DynamicChildRule;
use Eggheads\CakephpCommon\Validation\ValidatingInterface;

class RequestTest implements ValidatingInterface
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
     * @param Validator $validator Валидатор
     * @return Validator
     */
    public function addValidation(Validator $validator): Validator
    {
        $validator->requirePresence('fieldInt', true, 'Не указан fieldInt');
        $validator->nonNegativeInteger('fieldInt', 'Отрицательный fieldInt');

        $validator->allowEmptyFor('fieldString');
        $validator->minLength('fieldString', 3, 'Короткий fieldString');
        $validator->maxLength('fieldString', 20, 'Длинный fieldString');

        $validator->allowEmptyFor('fieldObject');
        $validator->add('fieldObject', Validator::NESTED, new DynamicChildRule($this));

        $validator->add('objects', Validator::NESTED, new DynamicChildrenRule($this));

        return $validator;
    }
}
