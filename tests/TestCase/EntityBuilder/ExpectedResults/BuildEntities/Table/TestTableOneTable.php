<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Eggheads\CakephpCommon\ORM\Table;
use Eggheads\CakephpCommon\Lib\Arrays;

/**
 * bla bla old comments
 * @method \TestApp\Model\Entity\TestTableOne newEntity(array | null $data = null, array $options = [])
 * @method \TestApp\Model\Entity\TestTableOne[] newEntities(array $data, array $options = [])
 * @method \TestApp\Model\Entity\TestTableOne patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TestApp\Model\Entity\TestTableOne[] patchEntities($entities, array $data, array $options = [])
 * @method \TestApp\Model\Entity\TestTableOne|false save(\TestApp\Model\Entity\TestTableOne $entity, array | \ArrayAccess $options = null)
 * bla bla more comments
 * @method \TestApp\Model\Entity\TestTableOne|false saveArr(array $saveData, \TestApp\Model\Entity\TestTableOne | null $entity = null, array $options = [])
 * @method \TestApp\Model\Query\TestTableOneQuery find(string $type = "all", array | \ArrayAccess $options = null)
 * @method \TestApp\Model\Entity\TestTableOne get($primaryKey, array | \ArrayAccess $options = null)
 * @method \TestApp\Model\Entity\TestTableOne|false getEntity(\TestApp\Model\Entity\TestTableOne | int $entity, array | \ArrayAccess $options = null)
 * @method \TestApp\Model\Entity\TestTableOne|null updateWithLock(\TestApp\Model\Query\TestTableOneQuery | array $queryData, array $updateData)
 * @method \TestApp\Model\Entity\TestTableOne touch(\TestApp\Model\Entity\TestTableOne $entity, string $eventName = 'Model.beforeSave')
 */
class TestTableOneTable extends Table
{
    /** @var string */
    public $asd;

    /**
     * @return string
     */
    public function qwe()
    {
        return Arrays::encode(['asd' => 'qwe']);
    }

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->hasMany('TestTableTwo', ['foreignKey' => 'table_one_fk']);
        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'col_time' => 'always',
                ],
            ],
        ]);
    }
}
