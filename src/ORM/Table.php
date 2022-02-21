<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\ORM;

use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Association\HasOne;
use Eggheads\CakephpCommon\Lib\Env;
use Eggheads\CakephpCommon\Lib\Strings;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * @SuppressWarnings(PHPMD.MethodMix)
 */
class Table extends \Cake\ORM\Table
{
    /**
     * Обёртка для TableRegistry::get() для автодополнения
     *
     * @return static
     */
    public static function instance(): static
    {
        return TableRegistry::getTableLocator()->get(static::_getAlias()); // @phpstan-ignore-line
    }

    /**
     * @inheritdoc
     * Прописывание правильной сущности
     * @phpstan-ignore-next-line
     */
    public function initialize(array $config): void
    {
        if (!Env::isUnitTest() || !empty($config['testInit'])) {
            // в юнит тестах иногда инициализируются классы таблиц при том, что работы с таблицей в базе не происходит
            // и в таких случаях фикстуры обычно не объявлены и таблица в базе не создаётся
            // а _initTimeStampBehavior вызывает получение списка полей, для которого таблица обязательна
            // поэтому добавил проверку
            $this->_initTimeStampBehavior();
        }
        if (empty($config['notForceEntity'])) {
            // для построителя сущностей
            // когда он запускается, то сущности может не быть
            // он ведь и нужен, чтоб её создать
            $this->setEntityClass(self::_getAlias());
        }
        parent::initialize($config);
    }

    /**
     * Автозаполнение полей создания/правки
     */
    private function _initTimeStampBehavior(): void
    {
        $timeStampFields = [];
        $columnList = $this->getSchema()->columns();

        if (in_array('created', $columnList, true)) {
            $timeStampFields['created'] = 'new';
        }
        if (in_array('updated', $columnList, true)) {
            $timeStampFields['updated'] = 'always';
        }
        if (in_array('modified', $columnList, true)) {
            $timeStampFields['modified'] = 'always';
        }

        if (!empty($timeStampFields)) {
            $this->addBehavior('Timestamp', [
                'events' => [
                    'Model.beforeSave' => $timeStampFields,
                ],
            ]);
        }
    }

    /**
     * Возвращает алиас таблицы, используемый тут повсюду
     *
     * @return string
     */
    private static function _getAlias(): string
    {
        $classNameParts = explode('\\', static::class);
        return Strings::replacePostfix(array_pop($classNameParts), 'Table');
    }

    /**
     * Сохранение массивов, чтоб в одну строчку
     *
     * @param array $saveData
     * @param int|Entity|null $entity null для новой записи, сущность или её id для редактирования
     * @param array $options
     * @return bool|Entity
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public function saveArr(array $saveData, Entity|int $entity = null, array $options = []): Entity|bool
    {
        if (empty($entity)) {
            $entity = $this->newEntity([]);
        } else {
            $entity = $this->getEntity($entity);
            if (empty($entity)) {
                return false;
            }
        }
        $entity = $this->patchEntity($entity, $saveData);
        if (!empty($options['dirtyFields'])) {
            $fieldsToDirty = array_keys($saveData);
            if (is_array($options['dirtyFields']) || is_string($options['dirtyFields'])) {
                $fieldsToDirty = array_intersect($fieldsToDirty, (array)$options['dirtyFields']);
            }
            foreach ($fieldsToDirty as $fieldName) {
                $entity->setDirty($fieldName, true);
            }
        }
        return $this->save($entity, $options);
    }

    /**
     * Если аргумент - сущность, то её и возвращает
     * Если число, то вытаскивает по нему сущность
     * Иначе - false
     *
     * @param int|Entity $entity
     * @param array $options
     * @return Entity|false
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public function getEntity(Entity|int $entity, array $options = []): Entity|bool
    {
        if ($entity instanceof Entity) {
            return $entity;
        }
        if (empty($entity)) {
            return false;
        }
        try {
            return $this->get($entity, $options); // @phpstan-ignore-line
        } catch (RecordNotFoundException $e) {
            return false;
        }
    }

    /**
     * Создаёт много сущностей из массива и сохраняет их
     *
     * @param array<int, array> $saveData
     * @param array $options
     * @return array|bool|ResultSet
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @throws Exception
     * @phpstan-ignore-next-line
     */
    public function saveManyArr(array $saveData, array $options = []): ResultSet|bool|array
    {
        return $this->saveMany($this->newEntities($saveData, $options)); // @phpstan-ignore-line
    }

    /**
     * Проверка на существование записей
     *
     * @param array $conditions
     * @param array $contain
     * @return bool
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public function exists($conditions, array $contain = []): bool
    {
        return (bool)count(
            $this->find('all')
                ->select(['existing' => 1])
                ->contain($contain)
                ->where($conditions)
                ->limit(1)
                ->enableHydration(false)
                ->toArray()
        );
    }

    /**
     * Ищем одну запись и редактируем её с блокировкой
     *
     * @param array|Query $queryData
     * @param array $updateData
     * @return Entity|null
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     * @throws Exception
     */
    public function updateWithLock(Query|array $queryData, array $updateData): ?Entity
    {
        if (is_array($queryData)) {
            $queryData = $this->find()->where($queryData);
        }
        return $this->getConnection()->transactional(function () use ($queryData, $updateData) {
            $result = $queryData->epilog('FOR UPDATE')
                ->first();
            if (empty($result)) {
                return $result;
            } else {
                $this->patchEntity($result, $updateData);
                return $this->save($result);
            }
        });
    }

    /**
     * Очистить таблицу
     *
     * @return bool
     */
    public function truncate(): bool
    {
        return ((int)$this->getConnection()->execute('TRUNCATE ' . $this->getTable())->errorCode() === 0);
    }

    /**
     * @inheritdoc
     * Добавил возможность более коротких опций
     * @phpstan-ignore-next-line
     */
    public function findList(\Cake\ORM\Query $query, array $options): Query
    {
        if ((count($options) === 1) && empty($options['valueField'])) {
            $newOptions = [];
            foreach ($options as $keyField => $valueField) {
                $selectFields = [$valueField];
                if (is_int($keyField)) {
                    $keyField = $valueField;
                } else {
                    $selectFields[] = $keyField;
                }
                foreach ($selectFields as $field) {
                    $path = explode('.', $field);
                    $fieldName = array_pop($path);
                    if (empty($path)) {
                        $alias = $this->getAlias();
                    } else {
                        $alias = array_pop($path);
                    }
                    $query->select("$alias.$fieldName");
                }
                $newOptions = [
                    'keyField' => $keyField,
                    'valueField' => $valueField,
                ];
            }
            $options = $newOptions;
        }
        return parent::findList($query, $options);
    }

    /**
     * @inheritdoc
     * @phpstan-ignore-next-line
     */
    public function query(): Query
    {
        return new Query($this->getConnection(), $this);
    }

    /**
     * @inheritdoc
     * @phpstan-ignore-next-line
     */
    public function belongsTo($associated, array $options = []): BelongsTo
    {
        return parent::belongsTo($associated, $this->_assocOptions($associated, $options));
    }

    /**
     * Обработать опции создания ассоциаций
     *
     * @param string $assocName
     * @param array $options
     * @return array
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    private function _assocOptions(string $assocName, array $options): array
    {
        if (empty($options['propertyName'])) {
            $options['propertyName'] = $assocName;
        }
        return $options;
    }

    /**
     * @inheritdoc
     * @phpstan-ignore-next-line
     */
    public function hasOne($associated, array $options = []): HasOne
    {
        return parent::hasOne($associated, $this->_assocOptions($associated, $options));
    }

    /**
     * @inheritdoc
     * @phpstan-ignore-next-line
     */
    public function hasMany($associated, array $options = []): HasMany
    {
        return parent::hasMany($associated, $this->_assocOptions($associated, $options));
    }

    /**
     * @inheritdoc
     * @phpstan-ignore-next-line
     */
    public function belongsToMany($associated, array $options = []): BelongsToMany
    {
        return parent::belongsToMany($associated, $this->_assocOptions($associated, $options));
    }
}
