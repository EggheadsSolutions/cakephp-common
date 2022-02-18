<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\ValueObject;

use Eggheads\CakephpCommon\ValueObject\ValueObject;
use Eggheads\CakephpCommon\I18n\FrozenTime;
use Eggheads\CakephpCommon\I18n\FrozenDate;
use Cake\Utility\Text as CakeString;

/**
 * @method $this setField1(mixed $value)
 * @method $this setField2(mixed $value)
 * @method $this setField3(mixed $value)
 * @method $this setTimeField(mixed $value)
 * @method $this setDateField(mixed $value)
 */
class ValueObjectFixture extends ValueObject
{
    const EXCLUDE_EXPORT_PROPS = [
        'field2',
    ];

    const TIME_FIELDS = [
        'timeField',
    ];

    const DATE_FIELDS = [
        'dateField'
    ];

    /**
     * блаблабла
     * трололо
     *
     * @var string
     */
    public string $field1 = 'asd';

    /** @var string */
    public string $field2 = 'qwe';

    /** @var string */
    public string $field3;

    /** @var ?CakeString */
    public ?CakeString $field4 = null;

    /** @var ?FrozenTime */
    public ?FrozenTime $timeField = null;

    /** @var ?FrozenDate */
    public ?FrozenDate $dateField = null;
}
