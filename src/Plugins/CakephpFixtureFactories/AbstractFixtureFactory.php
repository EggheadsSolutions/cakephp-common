<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Plugins\CakephpFixtureFactories;

use CakephpFixtureFactories\Factory\BaseFactory;
use Eggheads\CakephpCommon\Error\InternalException;

abstract class AbstractFixtureFactory extends BaseFactory
{
    /**
     * @inerhitDoc
     * @throws InternalException
     */
    protected function getRootTableRegistryName(): string
    {
        return self::parseTableName(static::class);
    }

    /**
     * Получим название таблицы из названия файла или класса
     *
     * @param string $name
     * @return string
     * @throws InternalException
     */
    public static function parseTableName(string $name): string
    {
        $matches = [];
        preg_match('/\\\\([\w]+)Factory$/', $name, $matches);

        if (!$matches) {
            throw new InternalException('Не корректное название класса: ' . $name);
        }

        return $matches[1];
    }
}
