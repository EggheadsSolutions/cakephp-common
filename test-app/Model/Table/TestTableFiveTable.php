<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Eggheads\CakephpCommon\ORM\Table;
use Eggheads\CakephpCommon\Lib\Arrays;

/**
 * description 5
 * @method \TestApp\Model\Entity\TestTableFive newEntity(array | null $data = null, array $options = [])
 * @method \TestApp\Model\Entity\TestTableFive[] newEntities(array $data, array $options = [])
 * @method \TestApp\Model\Entity\TestTableFive patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TestApp\Model\Entity\TestTableFive[] patchEntities($entities, array $data, array $options = [])
 * @method \TestApp\Model\Entity\TestTableFive|false save(\TestApp\Model\Entity\TestTableFive $entity, array | \ArrayAccess $options = null)
 * @method \TestApp\Model\Entity\TestTableFive|false saveArr(array $saveData, \TestApp\Model\Entity\TestTableFive | null $entity = null, array $options = [])
 * @method \TestApp\Model\Query\TestTableFiveQuery find(string $type = "all", array | \ArrayAccess $options = null)
 * @method \TestApp\Model\Entity\TestTableFive get($primaryKey, array | \ArrayAccess $options = null)
 * @method \TestApp\Model\Entity\TestTableFive|false getEntity(\TestApp\Model\Entity\TestTableFive | int $entity, array | \ArrayAccess $options = null)
 * @method \TestApp\Model\Entity\TestTableFive|null updateWithLock(\TestApp\Model\Query\TestTableFiveQuery | array $queryData, array $updateData)
 */
class TestTableFiveTable extends Table
{
}
