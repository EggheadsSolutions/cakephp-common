<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Eggheads\CakephpCommon\ORM\Entity;

/**
 * @property int $id
 * @tableComment description qweqwe
 * @property string $virtualField
 * @property string|null $virtualFieldOrNull
 * @property int $table_one_fk Колонка связи с one
 * @property string $col_text Описание
 * @property ?TestTableOne $TestTableOne `table_one_fk` => `id`
 */
class TestTableTwo extends Entity
{
    /**
     * @return string whoa!!!
     */
    protected function _getVirtualField()
    {
        return 'whoa, virtual fields!';
    }

    /**
     * @return string|null
     */
    protected function _getVirtualFieldOrNull(): ?string
    {
        return null;
    }
}
