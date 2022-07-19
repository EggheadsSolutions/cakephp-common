<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\Serializer;

use Eggheads\CakephpCommon\Error\InternalException;
use Eggheads\CakephpCommon\Error\UserException;
use Eggheads\CakephpCommon\Lib\Arrays;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class SerializerTest extends AppTestCase
{
    /**
     * Создание объекта из json-а - ошибки
     *
     * @group simple
     * @throws InternalException
     * @throws UserException
     */
    public function testCreateFromJsonException(): void
    {
        $this->expectExceptionMessage('Не указан fieldInt');
        $data = [
            'fieldString' => 'testString',
            'fieldObject' => [
                'fieldInt' => 9,
                'fieldString' => 'exampleString',
                'fieldObject' => null,
            ],
        ];
        RequestTest::createFromJson(Arrays::encode($data));
    }

    /**
     * Создание объекта из json-а
     *
     * @group simple
     * @throws ExceptionInterface
     * @throws InternalException
     * @throws UserException
     */
    public function testCreateFromJson(): void
    {
        $data = [
            'fieldInt' => 5,
            'fieldString' => 'testString',
            'fieldObject' => [
                'fieldInt' => '9',
                'fieldString' => 'exampleString',
                'fieldObject' => null,
                'objects' => [],
            ],
            'objects' => [],
        ];
        $result = RequestTest::createFromJson(Arrays::encode($data));
        $data['fieldObject']['fieldInt'] = 9;
        self::assertEquals($data, $result->toArray());
    }

    /**
     * Создание объекта из массива - ошибки
     *
     * @group simple
     * @throws InternalException
     * @throws UserException
     */
    public function testCreateFromArrayException(): void
    {
        $this->expectExceptionMessage('Не указан fieldInt');
        $data = [
            'fieldString' => 'testString',
            'fieldObject' => [
                'fieldInt' => 9,
                'fieldString' => 'exampleString',
                'fieldObject' => null,
            ],
        ];
        RequestTest::createFromArray($data);
    }

    /**
     * Создание объекта/массива объектов самого себя из массива c рекурсивной проверкой на ошибки
     *
     * @group simple
     * @throws InternalException
     * @throws UserException
     */
    public function testCreateFromArrayWithRecursiveSameObjectException(): void
    {
        $this->expectExceptionMessage('Не указан fieldInt');
        $data = [
            'fieldInt' => 8,
            'fieldObject' => [
                'fieldInt' => 9,
                'fieldObject' => ['fieldInt' => 19],
                'objects' => [
                    ['fieldInt' => 10],
                    [
                        'fieldInt' => 11,
                        'objects' => [
                            ['fieldInt' => 12],
                            [
                                'fieldInt' => 13,
                                'fieldObject' => [
                                    'fieldInt' => 14,
                                    'objects' => [
                                        ['fieldInt' => 15],
                                        ['fieldString' => 'exampleString4'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        RequestTest::createFromArray($data);
    }

    /**
     * Создание объекта из массива
     *
     * @group simple
     * @throws ExceptionInterface
     * @throws InternalException
     * @throws UserException
     */
    public function testCreateFromArray(): void
    {
        $data = [
            'fieldInt' => 5,
            'fieldString' => 'testString',
            'fieldObject' => [
                'fieldInt' => '9',
                'fieldString' => 'exampleString',
                'fieldObject' => null,
                'objects' => [],
            ],
            'objects' => [
                [
                    'fieldInt' => 19,
                    'fieldString' => 'exampleString',
                    'testF' => 123,
                    'fieldObject' => null,
                    'objects' => [
                        [
                            'fieldInt' => 19,
                            'fieldString' => 'exampleString',
                            'fieldObject' => null,
                            'objects' => [
                                [
                                    'fieldInt' => 19,
                                    'fieldString' => 'exampleString',
                                    'fieldObject' => null,
                                    'objects' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $result = RequestTest::createFromJson(Arrays::encode($data));
        $data['fieldObject']['fieldInt'] = 9;
        unset($data['objects'][0]['testF']);

        self::assertEquals($data, $result->toArray());
    }

    /**
     * @testdox Проверим конвертацию в массив
     * @group simple
     * @throws ExceptionInterface
     * @throws InternalException
     * @throws UserException
     */
    public function testToArray(): void
    {
        $data = ['fieldInt' => 123, 'fieldString' => null, 'fieldObject' => null, 'objects' => []];
        $result = RequestTest::createFromArray($data);

        self::assertEquals($data, $result->toArray());

        unset($data['fieldString'], $data['fieldObject']);
        self::assertEquals(
            $data,
            $result->toArray(false, [AbstractObjectNormalizer::SKIP_NULL_VALUES => true])
        );
        self::assertEquals(
            ['field_int' => 123, 'objects' => []],
            $result->toArray(true, [AbstractObjectNormalizer::SKIP_NULL_VALUES => true])
        );
    }
}
