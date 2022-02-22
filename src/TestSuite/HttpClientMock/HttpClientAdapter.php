<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\TestSuite\HttpClientMock;

use Eggheads\CakephpCommon\TestSuite\PermanentMocksCollection;
use Cake\Http\Client\Adapter\Stream;
use Cake\Http\Client\Request;
use Cake\Http\Client\Response;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Прослайка на отправку HTTP запросов
 *
 * @package App\Test\Suite
 * @SuppressWarnings(PHPMD.MethodMix)
 * @SuppressWarnings(PHPMD.MethodProps)
 */
class HttpClientAdapter extends Stream
{
    /**
     * Полная инфа по текущему взаимодействию (запрос и ответ)
     *
     * @var array|null
     */
    private ?array $_currentRequestData = null; // @phpstan-ignore-line

    /**
     * Выводить ли информацию о незамоканных запросах
     *
     * @var bool
     */
    private static bool $_debugRequests = true;

    /**
     * Все запросы проверяются на подмену, а также логируются
     *
     * @param Request|RequestInterface $request
     * @return array
     * @throws NetworkExceptionInterface
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    protected function _send(Request|RequestInterface $request): array
    {
        $this->_currentRequestData = [
            'request' => $request,
            'response' => '',
        ];

        $mockData = HttpClientMocker::getMockedData($request);
        if ($mockData !== null) {
            return $this->createResponses([
                'HTTP/1.1 ' . $mockData['status'],
                'Server: nginx/1.2.1',
            ], $mockData['response']);
        } else {
            $result = parent::_send($request);

            if (self::$_debugRequests) {
                PermanentMocksCollection::setHasWarning(true);
                PermanentMocksCollection::setWarningMessage('Вывод в консоль при запросе HTTP');
                file_put_contents('php://stderr', "==============================================================\n");
                file_put_contents('php://stderr', 'Do ' . $request->getMethod() . ' request to ' . $request->getUri() . ', Body: ' . $request->getBody() . "\n");
                file_put_contents('php://stderr', "Response: \n" . $result[0]->getStringBody() . "\n");
                file_put_contents('php://stderr', "==============================================================\n");
            }

            return $result;
        }
    }

    /**
     * @inheritdoc
     * @phpstan-ignore-next-line
     */
    public function createResponses($headers, $content): array
    {
        $result = parent::createResponses($headers, $content);

        $this->_currentRequestData['response'] = end($result);

        HttpClientMocker::addSniff($this->_currentRequestData);
        $this->_currentRequestData = null;

        return $result;
    }

    /**
     * Включаем вывод запросов в консоль
     *
     * @return void
     */
    public static function enableDebug()
    {
        self::$_debugRequests = true;
    }

    /**
     * Выключаем вывод запросов в консоль
     *
     * @return void
     */
    public static function disableDebug()
    {
        self::$_debugRequests = false;
    }
}
