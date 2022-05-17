<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Http;

use Cake\Core\Configure;

/**
 * Класс для работы с прокси
 * Можно отключить использование прокси, добавив параметр конфигурации 'isProxyEnabled' => false
 * В этом случае будет работать как обычный \Eggheads\CakephpCommon\Http\Client
 */
class ProxyClient extends Client
{
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
}
