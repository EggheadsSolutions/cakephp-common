<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Validation;

use Cake\Validation\Validator;

class Util
{
    /**
     * @param ValidatingInterface $validating
     * @param array $data Данные для валидации.
     * @param array|null $context Контекст валидатора CakePHP.
     * @return array
     */
    public static function performValidation(ValidatingInterface $validating, array $data, ?array $context = null): array
    {
        $validator = $validating->addValidation(new Validator);

        foreach (($context['providers'] ?? []) as $providerName => $provider) {
            $validator->setProvider((string)$providerName, $provider);
        }

        return $validator->validate($data, ($context['newRecord'] ?? true));
    }
}
