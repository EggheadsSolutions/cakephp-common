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
 * @method string save() bad declaration
 * bla bla more comments
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
