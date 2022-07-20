<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Traits;

use Cake\Validation\Validator;
use Eggheads\CakephpCommon\Error\InternalException;
use Eggheads\CakephpCommon\Error\UserException;
use Eggheads\CakephpCommon\Serializer\SerializerFactory;
use Eggheads\CakephpCommon\Serializer\ValidatingCollection;
use Eggheads\CakephpCommon\Validation\Util;
use Eggheads\CakephpCommon\Validation\ValidatingInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use TypeError;

trait ConverterTrait
{
    /**
     * Создание объекта из json
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @param string $json JSON строка
     * @param array $context Контекст
     * @param bool $isConvertCamelCaseKeyToSnakeCase Флаг конвертации ключей CamelCase в SnakeCase
     * @return static
     * @throws \Eggheads\CakephpCommon\Error\InternalException|\Eggheads\CakephpCommon\Error\UserException
     * @phpstan-ignore-next-line
     */
    public static function createFromJson(
        string $json,
        array $context = [],
        bool $isConvertCamelCaseKeyToSnakeCase = false
    ): static {
        try {
            /** @var static $dto */
            $dto = SerializerFactory::create($isConvertCamelCaseKeyToSnakeCase)->deserialize(
                $json,
                static::class,
                'json',
                $context + [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
            );
            $dto->_validate();
        } catch (NotNormalizableValueException | ExceptionInterface $e) {
            throw new InternalException($e->getMessage());
        }

        return $dto;
    }

    /**
     * Создание объекта из массива
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @param array $data Массив данных
     * @param array $context Контекст
     * @param bool $isConvertCamelCaseKeyToSnakeCase Флаг конвертации ключей CamelCase в SnakeCase
     * @return static
     * @throws \Eggheads\CakephpCommon\Error\InternalException
     * @throws \Eggheads\CakephpCommon\Error\UserException
     * @phpstan-ignore-next-line
     */
    public static function createFromArray(
        array $data,
        array $context = [],
        bool $isConvertCamelCaseKeyToSnakeCase = false
    ): static {
        try {
            /** @var static $dto */
            $dto = SerializerFactory::create($isConvertCamelCaseKeyToSnakeCase)->denormalize(
                $data,
                static::class,
                'array',
                $context + [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
            );
            $dto->_validate();
        } catch (NotNormalizableValueException | ExceptionInterface | TypeError $e) {
            throw new InternalException($e->getMessage());
        }

        return $dto;
    }

    /**
     * @param array $data
     * @param array $context
     * @param bool $convertToSnakeCase
     * @return static[]
     * @throws InternalException
     * @throws UserException
     */
    public static function createManyFromArray(
        array $data,
        array $context = [],
        bool $convertToSnakeCase = false,
    ): array {
        try {
            /** @var static[] $objects */
            $objects = SerializerFactory::create($convertToSnakeCase)->denormalize(
                $data,
                static::class . '[]',
                'array',
                $context + [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
            );

            static::_validateManyIfNeeded($objects, $context, $convertToSnakeCase);
        } catch (NotNormalizableValueException | ExceptionInterface | TypeError $e) {
            throw new InternalException($e->getMessage());
        }

        return $objects;
    }

    /**
     * @param string $data
     * @param array $context
     * @param bool $convertToSnakeCase
     * @return static[]
     * @throws InternalException
     * @throws UserException
     */
    public static function createManyFromJson(
        string $data,
        array $context = [],
        bool $convertToSnakeCase = false,
    ): array {
        try {
            /** @var static[] $objects */
            $objects = SerializerFactory::create($convertToSnakeCase)->deserialize(
                $data,
                static::class . '[]',
                'json',
                $context + [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
            );

            static::_validateManyIfNeeded($objects, $context, $convertToSnakeCase);
        } catch (NotNormalizableValueException | ExceptionInterface | TypeError $e) {
            throw new InternalException($e->getMessage());
        }

        return $objects;
    }

    /**
     * Конвертация объекта в массив
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @param bool $isConvertCamelCaseKeyToSnakeCase Конвертировать CamelCase ключи в snake_case
     * @param array $context Контекст
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @phpstan-ignore-next-line
     */
    public function toArray(bool $isConvertCamelCaseKeyToSnakeCase = false, array $context = []): array
    {
        return SerializerFactory::create($isConvertCamelCaseKeyToSnakeCase)->normalize($this, 'array', $context);
    }

    /**
     * Преобразование строки в массив объектов
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @param string $json JSON строка
     * @param array $context Контекст
     * @param bool $useCameToSnakeConverter Использовать CamelCase в snake_case конвертер
     * @return static[]
     * @phpstan-ignore-next-line
     */
    public static function createArrayFromJson(
        string $json,
        array $context = [],
        bool $useCameToSnakeConverter = false
    ): array {
        return SerializerFactory::create($useCameToSnakeConverter)->deserialize(
            $json,
            static::class . '[]',
            'json',
            $context + [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
        );
    }

    /**
     * Проверим входные данные
     *
     * @return void
     * @throws \Eggheads\CakephpCommon\Error\UserException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    protected function _validate(): void
    {
        if ($this instanceof ValidatingInterface || method_exists(self::class, 'addValidation')) {
            if (!($this instanceof ValidatingInterface)) {
                deprecationWarning(sprintf(
                    'Класс `%s` реализует метод `addValidation`, но не реализует интерфейс `%s`',
                    static::class,
                    ValidatingInterface::class,
                ));
            }

            $validator = $this->addValidation(new Validator());
            $errors = $validator->validate($this->toArray()); // @phpstan-ignore-line
        }

        if (isset($errors) && $errors) {
            $messages = [];
            self::_getErrorsMessage($messages, $errors);
            throw new UserException(implode(', ', $messages));
        }
    }

    /**
     * Выполняет валидацию массива объектов текущего класса, если текущий класс реализует `ValidatingInterface`. При
     * наличии невалидных свойств - выбрасывает исключение.
     *
     * @param static[] $objects
     * @param array $context
     * @param bool $convertToSnakeCase
     * @return void
     * @throws ExceptionInterface
     * @throws UserException
     */
    protected static function _validateManyIfNeeded(
        array $objects,
        array $context = [],
        bool $convertToSnakeCase = false,
    ): void {
        if (!is_a(static::class, ValidatingInterface::class, true)) {
            return;
        }

        /** @var array<static & ValidatingInterface> $objects */

        $collection = ValidatingCollection::createWithItems($objects);
        $errors = Util::performValidation(
            $collection,
            $collection->toArray($convertToSnakeCase, $context),
        );

        if (!empty($errors['items'])) {
            $messages = [];
            self::_getErrorsMessage($messages, $errors['items']);
            throw new UserException(implode(', ', $messages));
        }
    }

    /**
     * Преобразование древовидного списка ошибок в плоский список
     *
     * @param string[] $messages Массив сообщений
     * @phpstan-ignore-next-line
     * @param array $errors Массив ошибок
     * @return void
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    private static function _getErrorsMessage(array &$messages, array $errors): void
    {
        foreach ($errors as $error) {
            if (is_array($error)) {
                self::_getErrorsMessage($messages, $error);
            } else {
                $messages[] = $error;
            }
        }
    }
}
