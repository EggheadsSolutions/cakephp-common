<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Serializer;

use Cake\Validation\Validator;
use Eggheads\CakephpCommon\Traits\ConverterTrait;
use Eggheads\CakephpCommon\Validation\DynamicChildrenRule;
use Eggheads\CakephpCommon\Validation\ValidatingInterface;

class ValidatingCollection implements ValidatingInterface
{
    use ConverterTrait;

    /** @var ValidatingInterface[] */
    public array $items = [];

    /** @inheritDoc */
    public function addValidation(Validator $validator): Validator
    {
        $validator->add('items', Validator::NESTED, new DynamicChildrenRule($this));

        return $validator;
    }

    /**
     * @param ValidatingInterface[] $items
     * @return static
     */
    public static function createWithItems(array $items): static
    {
        $instance = new static;
        $instance->items = $items;

        return $instance;
    }
}
