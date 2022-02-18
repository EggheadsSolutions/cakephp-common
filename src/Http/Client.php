<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Http;

use Cake\Http\Client\Response;
use Eggheads\CakephpCommon\Lib\Env;

class Client extends \Cake\Http\Client
{
    /**
     * Client constructor.
     *
     * @param array $config
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public function __construct(array $config = ['redirect' => 2])
    {
        // возможность глобального переопределения адаптора отправки запросов
        if (Env::hasHttpClientAdapter()) {
            $config['adapter'] = Env::getHttpClientAdapter();
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     * Обернул в try/catch для, дабы чтобы код не валилися
     * @phpstan-ignore-next-line
     */
    protected function _doRequest($method, $url, $data, $options): Response
    {
        if (!empty($data) && is_array($data)) { // костыль от попытки загрузить файл, если строка начинается с '@'
            $data = http_build_query($data);
        }

        return parent::_doRequest($method, $url, $data, $options);
    }
}
