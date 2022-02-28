<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Eggheads\CakephpCommon\ORM\Entity;
use Eggheads\CakephpCommon\Lib\Arrays;

/**
 * some comments blabla
 *
 * more comments blabla
 * @property int $id
 * @property string $col_enum = 'val1' Колонка с enum
 * @property string $col_text Описание
 * @property \Eggheads\CakephpCommon\I18n\FrozenTime $col_time Дата создания
 * @property ?TestTableTwo[] $TestTableTwo `table_one_fk` => `id`
 * @property int[] $newField
 * @property int $oldField
 * @tableComment description blabla
 */
class TestTableOne extends Entity
{
    /**
     * @return string
     */
    public function asd()
    {
        return Arrays::encode(['asd' => 'qwe']);
    }

    /**
     * @return int[]
     */
    protected function _getNewField()
    {
        return [];
    }

    /**
     * @return int поле изменилось
     */
    protected function _getOldField()
    {
        return 123;
    }

    /**
     * @return object кривое описание
     */
    protected function _getId()
    {
        return empty($this->_fields['id']) ? null : $this->_fields['id'];
    }
}
