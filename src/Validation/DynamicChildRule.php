<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Validation;

use Cake\Validation\ValidationRule;
use Cake\Validation\Validator;
use LogicException;

class DynamicChildRule extends ValidationRule
{
    /**
     * @param object $parent
     * @param string|null $childKey
     * @param string|null $message
     * @param callable|string|null $when
     */
    public function __construct(object $parent, ?string $childKey = null, ?string $message = null, mixed $when = null)
    {
        parent::__construct(array_filter(['message' => $message, 'on' => $when]) + [
            'rule' => function ($value, $context) use ($parent, $childKey, $message) {
                /** @var string $childKey */
                $childKey ??= $context['field'];

                $child = ($parent->{$childKey} ?? null);
                if ($child === null) {
                    throw new LogicException(sprintf(
                        'Экземпляр класса `%s` не имеет свойства `%s` или его значение `null`.',
                        get_class($parent),
                        $childKey,
                    ));
                }
                if (!($child instanceof ValidatingInterface)) {
                    throw new LogicException(sprintf(
                        'Значение свойства `%s` с типом `%s` не реализует интерфейс `%s`.',
                        $childKey,
                        get_debug_type($child),
                        ValidatingInterface::class,
                    ));
                }

                if (!is_array($value)) {
                    return false;
                }

                $errors = Util::performValidation($child, $value, $context);

                return (empty($errors) ?: ($errors + ($message ? [Validator::NESTED => $message] : [])));
            },
        ]);
    }
}
