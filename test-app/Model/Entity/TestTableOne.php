<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Eggheads\CakephpCommon\ORM\Entity;
use Eggheads\CakephpCommon\Lib\Arrays;

/**
 * some comments blabla
 *
 * @property int $id
 * @property int $col_enum Колонка с enum
 * @property string $col_text = NULL Описание
 * @property \Cake\I18n\Time $col_time = 'CURRENT_TIMESTAMP' Дата создания
 * @property string $oldField
 * @property string $notExists
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
