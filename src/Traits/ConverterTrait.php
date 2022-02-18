<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Traits;

use Eggheads\CakephpCommon\Error\InternalException;
use Eggheads\CakephpCommon\Error\UserException;
use Eggheads\CakephpCommon\Serializer\SerializerFactory;
use Cake\Validation\Validator;
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
     * @param string $json
     * @param array $context
     * @param bool $isConvertCamelCaseKeyToSnakeCase
     * @return static
     * @throws InternalException|UserException
     * @phpstan-ignore-next-line
     */
    public static function createFromJson(string $json, array $context = [], bool $isConvertCamelCaseKeyToSnakeCase = false): static
    {
        try {
            /** @var static $dto */
            $dto = SerializerFactory::create($isConvertCamelCaseKeyToSnakeCase)->deserialize(
                $json,
                static::class,
                'json',
                $context + [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
            );
            if (method_exists(self::class, 'addValidation')) {
                $dto->_validate();
            }
        } catch (NotNormalizableValueException | ExceptionInterface $e) {
            throw new InternalException($e->getMessage());
        }

        return $dto;
    }

    /**
     * Создание объекта из массива
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @param array $data
     * @param array $context
     * @param bool $isConvertCamelCaseKeyToSnakeCase
     * @return static
     * @throws InternalException
     * @throws UserException
     * @phpstan-ignore-next-line
     */
    public static function createFromArray(array $data, array $context = [], bool $isConvertCamelCaseKeyToSnakeCase = false): static
    {
        try {
            /** @var static $dto */
            $dto = SerializerFactory::create($isConvertCamelCaseKeyToSnakeCase)->denormalize(
                $data,
                static::class,
                'array',
                $context + [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
            );
            if (method_exists(self::class, 'addValidation')) {
                $dto->_validate();
            }
        } catch (NotNormalizableValueException | ExceptionInterface | TypeError $e) {
            throw new InternalException($e->getMessage());
        }

        return $dto;
    }

    /**
     * Конвертация объекта в массив
     *
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @param bool $isConvertCamelCaseKeyToSnakeCase Конвертировать CamelCase ключи в snake_case
     * @param array $context
     * @return array
     * @throws ExceptionInterface
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
     * @param string $json
     * @param array $context
     * @param bool $useCameToSnakeConverter
     * @return static[]
     * @phpstan-ignore-next-line
     */
    public static function createArrayFromJson(string $json, array $context = [], bool $useCameToSnakeConverter = false): array
    {
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
     * @throws UserException
     * @throws ExceptionInterface
     */
    protected function _validate(): void
    {
        //@phpstan-ignore-next-line
        if (method_exists(self::class, 'addValidation')) {
            $errors = $this->addValidation(new Validator())->validate($this->toArray());
        }

        if (isset($errors) && $errors) {
            $messages = [];
            $this->_getErrorsMessage($messages, $errors);
            throw new UserException(implode(', ', $messages));
        }
    }

    /**
     * Преобразование древовидного списка ошибок в плоский список
     *
     * @param string[] $messages
     * @phpstan-ignore-next-line
     * @param array $errors
     * @return void
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    private function _getErrorsMessage(array &$messages, array $errors): void
    {
        foreach ($errors as $error) {
            if (is_array($error)) {
                $this->_getErrorsMessage($messages, $error);
            } else {
                $messages[] = $error;
            }
        }
    }
}
