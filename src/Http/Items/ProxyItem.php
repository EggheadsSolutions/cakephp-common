<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Http\Items;

use Eggheads\CakephpCommon\ValueObject\ValueObject;

class ProxyItem extends ValueObject
{
    /** @var string Прокси вида ip:port */
    public string $proxy;

    /** @var string Имя пользователя */
    public string $username;

    /** @var string Пароль */
    public string $password;
}
