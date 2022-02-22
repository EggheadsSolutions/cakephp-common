<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Test\TestCase\ValueObject;

use Cake\Utility\Text as CakeString;
use Eggheads\CakephpCommon\I18n\FrozenDate;
use Eggheads\CakephpCommon\I18n\FrozenTime;
use Eggheads\CakephpCommon\ValueObject\ValueObject;

/**
 * @method $this setField1(mixed $value)
 * @method $this setField2(mixed $value)
 * @method $this setField3(mixed $value)
 * @method $this setTimeField(mixed $value)
 * @method $this setDateField(mixed $value)
 */
class ValueObjectFixture extends ValueObject
{
    public const EXCLUDE_EXPORT_PROPS = [
        'field2',
    ];

    public const TIME_FIELDS = [
        'timeField',
    ];

    public const DATE_FIELDS = [
        'dateField',
    ];

    /**
     * блаблабла
     * трололо
     *
     * @var string
     */
    public string $field1 = 'asd';

    /**
     * @var string
     */
    public string $field2 = 'qwe';

    /**
     * @var string
     */
    public string $field3;

    /**
     * @var ?CakeString
     */
    public ?CakeString $field4 = null;

    /**
     * @var ?FrozenTime
     */
    public ?FrozenTime $timeField = null;

    /**
     * @var ?FrozenDate
     */
    public ?FrozenDate $dateField = null;
}
