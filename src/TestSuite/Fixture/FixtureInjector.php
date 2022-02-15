<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\TestSuite\Fixture;

use PHPUnit\Framework\TestSuite;

class FixtureInjector extends \Cake\TestSuite\Fixture\FixtureInjector
{
    /**
     * @inheritdoc
     */
    public function startTestSuite(TestSuite $suite): void
    {
        // сделано специально
    }

    /**
     * @inheritdoc
     */
    public function endTestSuite(TestSuite $suite): void
    {
        $this->_fixtureManager->shutDown();
    }
}
