<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Phinx\Db;

use InvalidArgumentException;

class Table extends \Phinx\Db\Table
{
    /**
     * @inheritdoc
     * Проверка дефолтных значений и комментов к полям
     *
     * @param string $columnName
     * @param ?string $type
     * @param array<string, mixed> $options
     * @return static
     */
    public function addColumn($columnName, $type = null, $options = [])
    {
        if (empty($options['comment'])) {
            throw new InvalidArgumentException('No comment for column ' . $columnName);
        }
        if (!array_key_exists('default', $options) && empty($options['noDefault'])) {
            throw new InvalidArgumentException('No default value for column ' . $columnName);
        }
        if (isset($options['noDefault']) && isset($options['default'])) {
            throw new InvalidArgumentException('The default value for column ' . $columnName . ' is forbidden');
        }
        unset($options['noDefault']);
        parent::addColumn($columnName, $type, $options);
        return $this;
    }

    /**
     * @inheritdoc
     * Проверка на обязательный комментарий к таблице
     */
    public function create()
    {
        if (!array_key_exists('comment', $this->getOptions())) {
            throw new InvalidArgumentException('No comment for table ' . $this->getName());
        }

        parent::create();
    }
}
