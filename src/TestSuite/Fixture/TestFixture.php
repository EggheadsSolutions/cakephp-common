<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\TestSuite\Fixture;

use Eggheads\CakephpCommon\Lib\DB;
use Eggheads\CakephpCommon\Lib\Env;
use Eggheads\CakephpCommon\Lib\Misc;
use Eggheads\CakephpCommon\Lib\Strings;
use Cake\Database\Connection;
use Cake\Utility\Inflector;
use Cake\Datasource\ConnectionInterface;
use Exception;
use ReflectionClass;
use ReflectionException;

class TestFixture extends \Cake\TestSuite\Fixture\TestFixture
{
    /**
     * Полноценный SQL запрос создания MYSQL таблицы без внутренних преобразований типов
     *
     * @var string
     */
    protected string $_createTableSqlQuery = '';

    /**
     * Класс теста, для которого загружаются фикстуры
     *
     * @var ?string $_testCaseClass
     */
    protected ?string $_testCaseClass = null;

    /**
     * Записи глобальной фикстуры
     * Запоминаем их, чтоб не парсить файл кучу раз
     *
     * @var array<int, array<string, mixed>>
     */
    protected array $_defaultFixtureRecords = [];

    /**
     * Записи, объявленные в $records класса-фикстуры
     *
     * @var array<int, array<string, mixed>>
     */
    protected array $_classFixtureRecords = [];

    /**
     * Папка, в которой лежат фикстуры
     *
     * @var string
     */
    protected string $_fixtureFolder = '';

    /**
     * Конструктор с функцией установки таблицы
     *
     * @param ?string $table
     * @param ?string $caseClass
     * @throws Exception
     */
    public function __construct(?string $table = null, ?string $caseClass = null)
    {
        $this->_testCaseClass = $caseClass;
        if (!empty($table)) {
            $this->table = $table;
        }
        $this->_classFixtureRecords = (empty($this->records) ? [] : $this->records);
        if (Env::hasFixtureFolder()) {
            $this->_fixtureFolder = Env::getFixtureFolder();
        }
        if (empty($this->_fixtureFolder)) {
            throw new Exception('Не указана папка с фикстурами!');
        }
        parent::__construct();
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function init(): void
    {
        if (!isset($this->import['table']) || !isset($this->import['connection'])) {
            $this->_getCreateQuery();
        }
        $this->_loadFixtureData();
        parent::init();
    }

    /**
     * Получает запрос CREATE TABLE для текущей таблицы и сохраняет его в $this->_createTableSqlQuery
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     */
    private function _getCreateQuery(): void
    {
        $connectionName = $this->getTableLocator()->get($this->table)::defaultConnectionName();
        $this->import['connection'] = $connectionName;

        if (!empty($this->table)) {
            $this->import['table'] = $this->table;
        } else {
            $class = Misc::namespaceSplit(static::class, true);
            $table = Inflector::underscore(Strings::replacePostfix($class, 'Fixture'));
            $this->import['table'] = $table;
        }

        // false - чтобы вместо default не подставлялся test
        $structureConnection = DB::getConnection($this->import['connection'], false);
        // phpcs:ignore
        if (@!$structureConnection->isConnected()) {
            $structureConnection->disconnect();
            $structureConnection->connect();
        }
        $createData = $structureConnection->query('SHOW CREATE TABLE ' . $this->import['table'])->fetch();
        // $createData всегда не пустой. Если таблицы нет, то запрос SHOW CREATE TABLE кинет исключение
        $this->_createTableSqlQuery = $createData[1]; //0 - название таблицы, 1 - запрос CREATE TABLE
    }

    /**
     * Для какого теста загружаем фикстуры
     *
     * @param ?string $caseClass
     * @throws Exception
     */
    public function setTestCase(?string $caseClass): void
    {
        if ($this->_testCaseClass == $caseClass) {
            return;
        }
        $this->_testCaseClass = $caseClass;
        $this->_loadFixtureData();
    }

    /**
     * Возвращает полное имя файла, из которого нужно взять фикстуры
     *
     * @return string
     * @throws ReflectionException
     */
    private function _getFixtureFileName(): string
    {
        $fixtureFile = $this->import['table'] . '.xml';
        if (!empty($this->_testCaseClass)) {
            $testCaseFile = (new ReflectionClass($this->_testCaseClass))->getFileName();
            $testCaseDirectory = dirname($testCaseFile);

            $localFixtureFile = $testCaseDirectory . DS . $fixtureFile;
            if (file_exists($localFixtureFile)) {
                return $localFixtureFile;
            }
        }
        if (empty($this->_classFixtureRecords)) {
            $defaultFixtureFile = $this->_fixtureFolder . $fixtureFile;
            if (file_exists($defaultFixtureFile)) {
                return $defaultFixtureFile;
            }
        }
        return '';
    }

    /**
     * Загружает данные из наших XML-файликов фикстур и сохраняет их в $this->records
     *
     * @throws Exception
     */
    private function _loadFixtureData(): void
    {
        $fixtureDataFile = $this->_getFixtureFileName();
        if (empty($fixtureDataFile)) {
            $this->records = $this->_classFixtureRecords;
            return;
        }

        $isDefaultFixture = (dirname($fixtureDataFile) == $this->_fixtureFolder);

        if ($isDefaultFixture && !empty($this->_defaultFixtureRecords)) {
            $this->records = $this->_defaultFixtureRecords;
            return;
        }

        $fixtureXml = simplexml_load_file($fixtureDataFile);

        if (empty($fixtureXml) || !isset($fixtureXml['statement'])) {
            throw new Exception('Incorrect import xml file ' . $fixtureDataFile);
        }

        $this->records = [];
        $recordCount = count($fixtureXml->row);
        for ($rowNum = 0; $rowNum < $recordCount; $rowNum++) {
            $recordXML = $fixtureXml->row[$rowNum];
            $recordArray = [];
            $fieldCount = count($recordXML->field);
            for ($fieldNum = 0; $fieldNum < $fieldCount; $fieldNum++) {
                $fieldName = (string)$recordXML->field[$fieldNum]['name'];
                $xsi = $recordXML->field[$fieldNum]->attributes('xsi', true);
                if (!empty($xsi['nil']) && ((string)$xsi['nil'] == 'true')) {
                    $recordArray[$fieldName] = null;
                } else {
                    $recordArray[$fieldName] = (string)$recordXML->field[$fieldNum];
                }
            }
            $this->records[] = $recordArray;
        }

        if ($isDefaultFixture) {
            $this->_defaultFixtureRecords = $this->records;
        }
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function create(ConnectionInterface $testConnection): bool
    {
        if (empty($this->_schema)) {
            return false;
        }

        try {
            /** @var Connection $testConnection */
            $testConnection->execute($this->_createTableSqlQuery);
        } catch (Exception $e) {
            throw new Exception('Не удалось создать таблицу ' . $this->table . ': ' . $e->getMessage(), 0, $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function insert(ConnectionInterface $testConnection): \Cake\Database\StatementInterface|bool
    {
        /**  @var Connection $testConnection */
        try {
            // тут всё то же самое, что и в parent::insert()
            // но в $query->insert() не передаётся $types
            if (isset($this->records) && !empty($this->records)) {
                $values = $this->records;
                $fields = array_keys($values[0]);

                $query = $testConnection->newQuery()
                    ->insert($fields)
                    ->into($this->table);

                foreach ($values as $row) {
                    $query->values($row);
                }
                $statement = $query->execute();
                $statement->closeCursor();

                return $statement;
            }
            return true;
        } catch (Exception $e) {
            throw new Exception('Не удалось загрузить фикстуры для ' . $this->table . ': ' . $e->getMessage(), 0, $e);
        }
    }
}
