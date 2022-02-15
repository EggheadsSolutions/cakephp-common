<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\TestSuite;

use Cake\TestSuite\TestCase;
use Eggheads\CakephpCommon\Error\InternalException;
use ReflectionException;

/**
 * @SuppressWarnings(PHPMD.MethodMix)
 */
abstract class AppTestCase extends TestCase
{
    use TestCaseTrait;

    /**
     * @inheritdoc
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::_setUpBeforeClass();
    }

    /** @inheritdoc
     * @throws InternalException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_setUp();
    }

    /** @inheritdoc
     * @throws ReflectionException
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->_tearDown();
    }
}
