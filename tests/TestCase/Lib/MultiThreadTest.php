<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\Lib;

use Eggheads\CakephpCommon\Lib\Env;
use Eggheads\CakephpCommon\Lib\MultiThreads;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;

class MultiThreadTest extends AppTestCase
{
    /** Многопоточный запуск */
    public function test(): void
    {
        $mt = MultiThreads::getInstance();

        $maxThreads = Env::getThreadsLimit();
        $maxTests = 10;
        for ($i = 0; $i < $maxTests; $i++) {
            $mt->run(function () {
                usleep(rand(0, 1000));
            });
            self::assertLessThanOrEqual($maxThreads, $mt->getTotalThreads());
        }
        $mt->waitThreads();
    }
}
