<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Plugins\CakephpFixtureFactories;

use DirectoryIterator;

class DiscoverFactories
{
    /** Путь на диске к папке с фабриками фикстур */
    private const FACTORIES_PATH = TESTS . 'Factory';

    /**
     * Список названий таблиц
     *
     * @var string[]
     */
    private static array $_tableNames = [];

    /**
     * Найдем список названий всех используемых таблиц в фабриках фикстур
     *
     * @return string[]
     */
    public static function getTableList(): array
    {
        if (empty(self::$_tableNames)) {
            $files = (new self)->getFileNames();

            foreach ($files as $name) {
                if (in_array($name, self::$_tableNames, true)) {
                    continue;
                }

                self::$_tableNames[] = $name;
            }
        }

        return self::$_tableNames;
    }

    /**
     * Список файлов с фабриками
     *
     * @return string[]
     */
    public function getFileNames(): array
    {
        if (!is_dir(self::FACTORIES_PATH)) {
            return [];
        }

        $result = [];
        $iterator = new DirectoryIterator(self::FACTORIES_PATH);
        while ($iterator->valid()) {
            if ($iterator->isFile()) {
                $matches = [];
                preg_match('/([\w]+)Factory$/', $iterator->getBasename('.php'), $matches);

                if (!empty($matches[1])) {
                    $result[] = $matches[1];
                }
            }

            $iterator->next();
        }

        return $result;
    }
}
