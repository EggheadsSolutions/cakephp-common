<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Http;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Eggheads\CakephpCommon\Http\Items\ProxyItem;
use Eggheads\CakephpCommon\Traits\SingletonTrait;

class ProxyList
{
    use SingletonTrait;

    public const DEFAULT_TABLE_NAME = 'proxy_config';

    /**
     * Список текущих прокси
     *
     * @var ProxyItem[]|null
     */
    private ?array $_proxyList;

    /** @inheritDoc */
    private function __construct()
    {
        $this->_loadProxy();
    }

    /**
     * Получить случайный конфиг прокси
     *
     * @return ProxyItem|null
     */
    public function getConfig(): ?ProxyItem
    {
        if (empty($this->_proxyList)) {
            return null;
        }

        // Специально, дабы статический счётчик в другом процессе не работает, rand возвращает общее значение
        $maxIndex = count($this->_proxyList);
        $index = (int)ConnectionManager::get('default')
                          ->query("SELECT FLOOR(RAND() * $maxIndex) AS random_value")
                          ->fetch('assoc')['random_value'];

        return $this->_proxyList[$index];
    }

    /**
     * Загружаем список прокси
     *
     * @return void
     */
    private function _loadProxy(): void
    {
        $tableName = Configure::read('proxyTableName', self::DEFAULT_TABLE_NAME);
        $rows = ConnectionManager::get('default')
            ->execute("SELECT proxy, username, password FROM $tableName WHERE active = 1")
            ->fetchAll('assoc');
        if ($rows !== false) {
            $this->_proxyList = array_map([ProxyItem::class, 'create'], $rows);
        }
    }
}
