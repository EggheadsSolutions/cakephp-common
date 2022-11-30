<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Eggheads\CakephpCommon\ORM\Table;

/**
 * @method \TestApp\Model\Entity\TestTableSix newEntity(array | null $data = null, array $options = [])
 * @method \TestApp\Model\Entity\TestTableSix[] newEntities(array $data, array $options = [])
 * @method \TestApp\Model\Entity\TestTableSix patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TestApp\Model\Entity\TestTableSix[] patchEntities($entities, array $data, array $options = [])
 * @method \TestApp\Model\Entity\TestTableSix|false save(\TestApp\Model\Entity\TestTableSix $entity, array | \ArrayAccess $options = null)
 * @method \TestApp\Model\Entity\TestTableSix|false saveArr(array $saveData, \TestApp\Model\Entity\TestTableSix | null $entity = null, array $options = [])
 * @method \TestApp\Model\Query\TestTableSixQuery find(string $type = "all", array | \ArrayAccess $options = null)
 * @method \TestApp\Model\Entity\TestTableSix get($primaryKey, array | \ArrayAccess $options = null)
 * @method \TestApp\Model\Entity\TestTableSix|false getEntity(\TestApp\Model\Entity\TestTableSix | int $entity, array | \ArrayAccess $options = null)
 * @method \TestApp\Model\Entity\TestTableSix|null updateWithLock(\TestApp\Model\Query\TestTableSixQuery | array $queryData, array $updateData)
 */
class TestTableSixTable extends Table
{
    /** @inerhitDoc */
    public static function defaultConnectionName(): string
    {
        return 'postgres';
    }
}
