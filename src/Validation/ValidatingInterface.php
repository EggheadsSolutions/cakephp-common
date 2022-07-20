<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Validation;

use Cake\Validation\Validator;

interface ValidatingInterface
{
    /**
     * @param Validator $validator
     * @return Validator
     */
    public function addValidation(Validator $validator): Validator;
}
