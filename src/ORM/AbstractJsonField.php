<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\ORM;

use Eggheads\CakephpCommon\Serializer\ArraySerializerInterface;
use Eggheads\CakephpCommon\Serializer\SerializerFactory;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * Для описания JSON в БД в виде объекта
 */
abstract class AbstractJsonField implements ArraySerializerInterface
{
    /**
     * @inheritDoc
     * @deprecated Проверить использование, удалить при внедрении
     * @throws ExceptionInterface
     * @phpstan-ignore-next-line
     */
    public static function createFromArray(array $data, array $context = []): static|array
    {
        $type = static::class . (!empty($data[0]) ? '[]' : '');

        return SerializerFactory::create()->denormalize($data, $type, null, $context);
    }
}
