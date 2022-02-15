<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Controller\Request;

use Cake\Validation\ValidationSet;
use Cake\Validation\Validator;

interface ValidationInterface
{
    /**
     * Добавление правил валидации
     *
     * @param Validator|ValidationSet[] $validator
     * @return Validator|ValidationSet[]
     * @see https://book.cakephp.org/3/en/core-libraries/validation.html#nesting-validators
     */
    public function addValidation(Validator|array $validator): Validator|array;
}
