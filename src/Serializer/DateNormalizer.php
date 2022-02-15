<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Serializer;

use Eggheads\CakephpCommon\I18n\FrozenDate;
use Eggheads\CakephpCommon\I18n\FrozenTime;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class DateNormalizer extends DateTimeNormalizer
{
    /**
     * @var array<string,bool> Поддерживаемые типы объектов
     */
    private const SUPPORTED_TYPES = [
        FrozenTime::class => true,
        FrozenDate::class => true,
    ];

    /**
     * {@inheritdoc}
     *
     * @param mixed $data
     * @param string $type
     * @param null|string $format
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return isset(self::SUPPORTED_TYPES[$type]);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $data
     * @param string $type
     * @param null|string $format
     * @param array $context
     * @return FrozenDate|FrozenTime
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public function denormalize($data, $type, $format = null, array $context = []): FrozenTime|FrozenDate
    {
        if ('' === $data || null === $data) {
            throw new NotNormalizableValueException('The data is either an empty string or null, you should pass a string that can be parsed with the passed format or a valid DateTime string.');
        }

        /** @phpstan-ignore-next-line */
        switch ($type) {
            case FrozenTime::class:
                return FrozenTime::parse($data);
            case FrozenDate::class:
                return FrozenDate::parse($data);
        }
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function normalize($object, $format = null, array $context = []): string
    {
        return $object->toDateString();
    }
}
