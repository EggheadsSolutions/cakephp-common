<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\TestSuite\HttpClientMock;

use Cake\Http\Client\Adapter\Stream;
use Eggheads\CakephpCommon\TestSuite\PermanentMocksCollection;
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
     * @param RequestInterface $request
     * @param array $options
     * @return array
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function send(RequestInterface $request, array $options): array
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
            $result = parent::send($request, $options);

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
