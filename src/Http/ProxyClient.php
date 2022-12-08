<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Http;

use Cake\Core\Configure;

/**
 * Класс для работы с прокси
 *
 * Использование:
 * Можно отключить использование прокси, добавив параметр конфигурации 'isProxyEnabled' => false
 * В этом случае будет работать как обычный \Eggheads\CakephpCommon\Http\Client
 */
class ProxyClient extends Client
{
    /** @var string Элемент массива с параметрами в общем конфиге */
    public const CONFIG_FIELD_NAME = 'proxyConfig';

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function __construct(array $config = [])
    {
        if (Configure::read('isProxyEnabled', true)) {
            $this->setConfig('proxy', Configure::read(self::CONFIG_FIELD_NAME));
        }

        parent::__construct($config);
    }
}
