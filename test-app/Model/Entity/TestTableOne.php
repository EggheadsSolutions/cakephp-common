<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Eggheads\CakephpCommon\I18n\FrozenTime;
use Eggheads\CakephpCommon\ORM\Entity;
use Eggheads\CakephpCommon\Lib\Arrays;

/**
 * some comments blabla
 *
 * @property int $id comment1
 * @property int $col_enum
 * @property FrozenTime $col_time = 'CURRENT_TIMESTAMP' some time
 * @property string $oldField
 * @property string $notExists
 * @tableComment description blabla table
 * more comments blabla
 */
class TestTableOne extends Entity
{
    /**
     * @return string
     */
    public function asd(): string
    {
        return Arrays::encode(['asd' => 'qwe']);
    }

    /**
     * @return int[]
     */
    protected function _getNewField(): array
    {
        return [];
    }

    /**
     * @return int поле изменилось
     */
    protected function _getOldField(): int
    {
        return 123;
    }

    /**
     * @return object|null кривое описание
     */
    protected function _getId(): ?object
    {
        return empty($this->_properties['id']) ? null : $this->_properties['id'];
    }
}
