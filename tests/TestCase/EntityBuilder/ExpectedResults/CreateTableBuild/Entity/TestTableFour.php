<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Eggheads\CakephpCommon\ORM\Entity;

/**
 * @property int $id
 * @property int $table_four_fk Колонка связи с one
 * @tableComment description 4
 */
class TestTableFour extends Entity
{

}
