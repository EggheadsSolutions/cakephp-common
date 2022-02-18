<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Serializer;

interface ArraySerializerInterface
{
    /**
     * Преобразование массива в объект
     *
     * @param array $data
     * @param array $context
     * @return ArraySerializerInterface|ArraySerializerInterface[]
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public static function createFromArray(array $data, array $context = []): static|array;
}
