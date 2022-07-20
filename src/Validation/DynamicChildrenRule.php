<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Validation;

use Cake\Validation\ValidationRule;
use Cake\Validation\Validator;
use LogicException;

class DynamicChildrenRule extends ValidationRule
{
    /**
     * @param object $parent
     * @param string|null $childrenKey
     * @param string|null $message
     * @param callable|string|null $when
     */
    public function __construct(object $parent, ?string $childrenKey = null, ?string $message = null, mixed $when = null)
    {
        parent::__construct(array_filter(['message' => $message, 'on' => $when]) + [
            'rule' => function ($values, $context) use ($parent, $childrenKey, $message) {
                /** @var string $childrenKey */
                $childrenKey ??= $context['field'];

                $children = ($parent->{$childrenKey} ?? null);
                if (!is_array($children)) {
                    throw new LogicException(sprintf(
                        'Экземпляр класса `%s` не имеет свойства `%s` или его значение не массив.',
                        get_class($parent),
                        $childrenKey,
                    ));
                }

                if (!is_array($values)) {
                    return false;
                }

                $errors = [];

                foreach ($values as $key => $value) {
                    if (!array_key_exists($key, $children)) {
                        throw new LogicException(sprintf(
                            'Элемент массива свойства `%s` экземпляра класса `%s` по ключу `%s` отсутствует.',
                            $childrenKey,
                            get_class($parent),
                            $key,
                        ));
                    }

                    $child = $children[$key];
                    if (!($child instanceof ValidatingInterface)) {
                        throw new LogicException(sprintf(
                            'Элемент массива свойства `%s` экземпляра класса `%s` по ключу `%s` с типом `%s` не реализует интерфейс `%s`.',
                            $childrenKey,
                            get_class($parent),
                            $key,
                            get_debug_type($child),
                            ValidatingInterface::class,
                        ));
                    }

                    if (!is_array($value)) {
                        return false;
                    }

                    $check = Util::performValidation($child, $value, $context);
                    if (!empty($check)) {
                        $errors[$key] = $check;
                    }
                }

                return (empty($errors) ?: ($errors + ($message ? [Validator::NESTED => $message] : [])));
            },
        ]);
    }
}
