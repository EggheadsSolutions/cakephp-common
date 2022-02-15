<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\TestSuite\Fixture;

use Eggheads\CakephpCommon\Lib\Env;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\Utility\Inflector;
use Exception;
use UnexpectedValueException;

/**
 * @property TestFixture[] $_loaded
 */
class FixtureManager extends \Cake\TestSuite\Fixture\FixtureManager
{

    /**
     * Дефолтный класс фикстур
     *
     * @var string
     */
    protected string $_baseFixtureClass = TestFixture::class;

    /**
     * определяем дефолтный класс фикстур
     */
    public function __construct()
    {
        if (Env::hasBaseFixtureClass()) {
            $this->_baseFixtureClass = Env::getBaseFixtureClass();
        }
    }

    /**
     * Переопределил метод из класса FixtureManager. Теперь не требует лишних файлов
     *
     * @param TestCase $test The test suite to load fixtures for.
     * @return void
     * @throws UnexpectedValueException when a referenced fixture does not exist.
     * @throws Exception
     */
    protected function _loadFixtures(TestCase $test): void
    {
        if (empty($test->fixtures)) {
            return;
        }
        $testCaseClass = get_class($test);
        foreach ($test->fixtures as $fixture) {
            if (isset($this->_loaded[$fixture]) && method_exists($this->_loaded[$fixture], 'setTestCase')) {
                $this->_loaded[$fixture]->setTestCase($testCaseClass);
                continue;
            }

            if (stripos($fixture, '.') !== false) {
                [$type, $pathName] = explode('.', $fixture, 2);
            } else {
                $type = 'app';
                $pathName = $fixture;
            }
            $path = explode('/', $pathName);
            $name = array_pop($path);
            $additionalPath = implode('\\', $path);

            if ($type === 'core') {
                $baseNamespace = 'Cake';
            } elseif ($type === 'app') {
                $baseNamespace = Configure::read('App.namespace');
            } elseif ($type === 'plugin') {
                [$plugin, $name] = explode('.', $pathName);
                $path = implode('\\', explode('/', $plugin));
                $baseNamespace = Inflector::camelize(str_replace('\\', '\ ', $path));
                $additionalPath = null;
            } else {
                $baseNamespace = '';
                $name = $fixture;
            }
            $tableName = Inflector::underscore($name);
            $name = Inflector::camelize($name);
            $nameSegments = [
                $baseNamespace,
                'Test\Fixture',
                $additionalPath,
                $name . 'Fixture',
            ];
            $className = implode('\\', array_filter($nameSegments));

            if (class_exists($className)) {
                $this->_loaded[$fixture] = new $className(null, $testCaseClass);
            } else {
                $baseClass = $this->_baseFixtureClass;
                $this->_loaded[$fixture] = new $baseClass($tableName, $testCaseClass);
            }
            $this->_fixtureMap[$name] = $this->_loaded[$fixture];
        }
    }
}
