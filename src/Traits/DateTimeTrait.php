<?php
declare(strict_types=1);

namespace Eggheads\CakephpCommon\Traits;

use Eggheads\CakephpCommon\I18n\FrozenDate;
use Eggheads\CakephpCommon\I18n\FrozenTime;

/**
 * Помощник по работе с датами
 *
 * @package App\Lib
 */
trait DateTimeTrait
{
    /**
     * @var array|string[][][]
     */
    private static array $_getRussianDate = [
        'replacements' => [
            'full_month_list' => [
                'January' => 'январь',
                'February' => 'февраль',
                'March' => 'март',
                'April' => 'апрель',
                'May' => 'май',
                'June' => 'июнь',
                'July' => 'июль',
                'August' => 'август',
                'September' => 'сентябрь',
                'October' => 'октябрь',
                'November' => 'ноябрь',
                'December' => 'декабрь',
            ],
            'full_month_genetive_list' => [
                'January' => 'января',
                'February' => 'февраля',
                'March' => 'марта',
                'April' => 'апреля',
                'May' => 'мая',
                'June' => 'июня',
                'July' => 'июля',
                'August' => 'августа',
                'September' => 'сентября',
                'October' => 'октября',
                'November' => 'ноября',
                'December' => 'декабря',
            ],
            'short_month_list' => [
                'Jan' => 'Янв',
                'Feb' => 'Фев',
                'Mar' => 'Мар',
                'Apr' => 'Апр',
                'May' => 'Май',
                'Jun' => 'Июн',
                'Jul' => 'Июл',
                'Aug' => 'Авг',
                'Sep' => 'Сен',
                'Oct' => 'Окт',
                'Nov' => 'Ноя',
                'Dec' => 'Дек',
            ],
            'full_day_list' => [
                'Monday' => 'Понедельник',
                'Tuesday' => 'Вторник',
                'Wednesday' => 'Среда',
                'Thursday' => 'Четверг',
                'Friday' => 'Пятница',
                'Saturday' => 'Суббота',
                'Sunday' => 'Воскресенье',
            ],
            'short_day_list' => [
                'Mon' => 'Пн',
                'Tue' => 'Вт',
                'Wed' => 'Ср',
                'Thu' => 'Чт',
                'Fri' => 'Пт',
                'Sat' => 'Сб',
                'Sun' => 'Вс',
            ],
        ],
    ];

    /**
     * $format FI - месяц в именительном падеже, FR - месяц в родительном падеже, M - краткое название
     *     месяца, l - день недели, D - краткий день недели с указанием "сегодня" и "завтра"
     *
     * @inheritDoc
     */
    public function format($format)
    {
        $result = parent::format(str_replace(['FI', 'FR'], ['F', 'F'], $format));

        if (strstr($format, 'FI')) {
            $result = str_replace(array_keys(self::$_getRussianDate['replacements']['full_month_list']), self::$_getRussianDate['replacements']['full_month_list'], $result);
        }

        if (strstr($format, 'FR')) {
            $result = str_replace(array_keys(self::$_getRussianDate['replacements']['full_month_genetive_list']), self::$_getRussianDate['replacements']['full_month_genetive_list'], $result);
        }

        if (strstr($format, 'M')) {
            $result = str_replace(array_keys(self::$_getRussianDate['replacements']['short_month_list']), self::$_getRussianDate['replacements']['short_month_list'], $result);
        }

        if (strstr($format, 'l')) {
            $result = str_replace(array_keys(self::$_getRussianDate['replacements']['full_day_list']), self::$_getRussianDate['replacements']['full_day_list'], $result);
        }

        if (strstr($format, 'D')) {
            $result = str_replace(array_keys(self::$_getRussianDate['replacements']['short_day_list']), self::$_getRussianDate['replacements']['short_day_list'], $result);
        }
        return $result;
    }

    /**
     * Определяем ближайшую дату счёта
     *
     * На основе start_billing_date, переданной через конструктор, и $closeToDate находим число в месяце
     *
     * @param FrozenDate|FrozenTime|null $closeToDate
     * @return static
     */
    public function getClosestBillingDate(FrozenTime|FrozenDate $closeToDate = null): static
    {
        // При сравнении FrozenTime с Time вылетают приколюхи с временной зоной
        $closeDate = static::class::parse(($closeToDate ?: static::class::now())->format('Y-m-d'));

        $billDay = (int)$this->format('d');
        $lastMonthDay = (int)$closeDate->modify('last day of this month')->format('d');

        $closestDay = ($billDay <= $lastMonthDay) ? $billDay : $lastMonthDay;

        return $closeDate->day($closestDay);
    }

    /**
     * Получаем дату на первый день месяца
     *
     * @return static
     */
    public function getFirstDateOfMonth(): static
    {
        return static::class::parse($this->format('Y-m' . '-01'));
    }
}
