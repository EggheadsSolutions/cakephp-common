<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Http;

use Cake\Core\Configure;
use Psr\Http\Message\RequestInterface;
use Cake\Http\Client\Response;

/**
 * Класс для работы с прокси
 * Можно отключить использование прокси, добавив параметр конфигурации 'isProxyEnabled' => false
 * В этом случае будет работать как обычный \Eggheads\CakephpCommon\Http\Client
 */
class ProxyClient extends Client
{
    /** @var bool Менять ли прокси после ошибки curl */
    private bool $_isChangeProxyAfterError = true;

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function __construct(array $config = [])
    {
        $this->changeProxy();
        parent::__construct($config);
    }

    /**
     * Меняем прокси при ошибке запроса
     *
     * @inheritDoc
     */
    protected function _sendRequest(RequestInterface $request, $options, callable $errorCallback = null): Response
    {
        if ($this->_isChangeProxyAfterError) {
            return parent::_sendRequest($request, $options, function () {
                $this->changeProxy();
            });
        }
        return parent::_sendRequest($request, $options);
    }

    /**
     * Сменить прокси
     *
     * @return $this
     */
    public function changeProxy(): self
    {
        if (Configure::read('isProxyEnabled', true)) {
            $proxy = ProxyList::getInstance()->getConfig();
            if (!is_null($proxy)) {
                $this->setConfig('proxy', $proxy->toArray());
            }
        }
        return $this;
    }

    /**
     * Отключить смену прокси после ошибки
     *
     * @return $this
     */
    public function doNotChangeProxyAfterError(): self
    {
        $this->_isChangeProxyAfterError = false;
        return $this;
    }
}
