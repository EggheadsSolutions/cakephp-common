<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\HTTP\ClientTest;

use Eggheads\CakephpCommon\Http\Client;
use Eggheads\CakephpCommon\TestSuite\AppTestCase;
use Eggheads\Mocks\MethodMocker;

class ClientTest extends AppTestCase
{
    /**
     * Тестируем _getCurlErrorCode
     *
     * @see Client::_getCurlErrorCode()
     */
    public function testGetCurlErrorCode(): void
    {
        $client = new Client();
        self::assertEquals(28, MethodMocker::callPrivate($client, '_getCurlErrorCode', ['cURl Error (28) other']));
        self::assertEquals(1, MethodMocker::callPrivate($client, '_getCurlErrorCode', ['cURl Error (1)']));
        self::assertEquals(21, MethodMocker::callPrivate($client, '_getCurlErrorCode', ['Curl ERROR (21)']));
    }
}
