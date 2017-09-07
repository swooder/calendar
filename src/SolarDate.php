<?php
/**
 * Created by PhpStorm.
 * User: woodfish
 * Date: 15-2-28
 * Time: 上午11:27
 */

namespace Woodfish\Component\Date;


use Woodfish\Component\Date\Exception\InvalidDateException;

class SolarDate
{
    /**
     * @var \DateTime
     */
    private $dt;

    /*
     * 公历平年月份天数
     */
    protected static $MonthDays = array(
        0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31, 31,
    );

    protected static $YearDays = array(
        0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334,
    );

    public function __construct($year = null, $month = null, $day = null)
    {
        $year = intval($year ? $year : date('Y'));
        $month = intval($month ? $month : date('m'));
        $day = intval($day ? $day : date('d'));

        try {
            $this->dt = new \DateTime(sprintf("%04d-%02d-%02d 00:00:00", $year, $month, $day));
        } catch (\Exception $e) {
            throw new InvalidDateException();
        }
    }

    /**
     * 获得下个合法日期
     *
     * 从startYear这一年开始（包括startYear), 找到一个合法的month月，day日
     * 比如说startYear 2012, month 2, day 29 找到下一个有2月29的公历日期
     *
     * @warning 此处有while循环
     *
     * @param int $month
     * @param int $day
     * @param int $startYear
     * @param int $shiftDays 如果不存在的日期是否提前或者拖后
     *
     * @return SolarDate
     */
    public static function generateNext($month, $day, $startYear, $shiftDays = 0)
    {
        $year = $startYear;
        $shifted = 0;
        while (!SolarDate::isValid($year, $month, $day)) {
            if ($shiftDays != 0 && SolarDate::isValid($year, $month, $day + $shiftDays)) {
                $shifted = 1;
                break;
            }
            $year++;
        }

        return new SolarDate($year, $month, $day - $shifted);
    }

    public static function isValid($year, $month, $day)
    {
        return (!($year < 1 || $month < 1 || $month > 12 || $day < 1 || $day > static::getSolarMonthDays(
                $year,
                $month
            )));
    }

    /**
     * @return \DateTime
     */
    public function toDateTime()
    {
        return $this->dt;
    }

    /**
     * @return int
     */
    public function getDay()
    {
        return intval($this->dt->format('d'));
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return intval($this->dt->format('Y'));
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        return intval($this->dt->format('m'));
    }

    /**
     * 是否闰年
     *
     * @param int $year
     *
     * @return bool
     */
    public static function isLeapYear($year)
    {
        return ($year % 400 == 0) || (($year % 100 != 0) && ($year % 4 == 0));
    }

    /**
     * 是否闰年
     *
     * @return bool
     */
    public function isLeap()
    {
        return static::isLeapYear($this->getYear());
    }

    public function equals(SolarDate $other, $ignoreYear = false)
    {
        $t = $other->toDateTime();
        if ($ignoreYear) {
            $t->setDate($this->getYear(), $other->getMonth(), $other->getDay());
        }

        return $this->dt == $other->toDateTime();
    }

    public function beforeThan(SolarDate $other, $ignoreYear = false)
    {
        $t = $other->toDateTime();

        if ($ignoreYear) {
            $t->setDate($this->getYear(), $other->getMonth(), $other->getDay());
        }

        return $this->dt < $t;
    }

    public function afterThan(SolarDate $other, $ignoreYear = false)
    {
        $t = $other->toDateTime();

        if ($ignoreYear) {
            $t->setDate($this->getYear(), $other->getMonth(), $other->getDay());
        }

        return $this->dt > $t;
    }

    private function diff(SolarDate $other)
    {
        return $this->dt->diff($other->toDateTime());
    }

    /**
     * 计算两个日期之间相隔多少年，不足12个月的话返回0
     *
     * @param SolarDate $other
     *
     * @return int
     */
    public function diffYears(SolarDate $other)
    {
        $diff = $this->diff($other);

        return ($diff->invert ? -1 : 1) * $diff->y;
    }

    /*
     * 计算两个日期之间相隔多少个月
     *
     * @param LunarDate $other
     *
     * @return int
     */
    public function diffMonths(SolarDate $other)
    {
        $diff = $this->diff($other);

        return ($diff->invert ? -1 : 1) * (($diff->y * 12) + $diff->m);
    }

    /*
     * 与指定日期的相隔天数，返回小于0则代表当前日期在指定日期的未来。 大于0则表示指定日期是未来的日期。
     *
     * @param LunarDate $other
     * @return int
     */
    public function diffDays(SolarDate $other)
    {
        $diff = $this->diff($other);

        return ($diff->invert ? -1 : 1) * $diff->days;
    }

    public function formatString()
    {
        return $this->dt->format('Y年m月d日');
    }

    public function formatStringWithOutYear()
    {
        return $this->dt->format('m月d日');
    }

    public static function format($month, $day, $year = null)
    {
        if ($year) {
            return sprintf("%04d年%02d月%02d日", $year, $month, $day);
        }

        return sprintf("%02d月%02d日", $month, $day);
    }

    /**
     * 添加n个月
     *
     * @param int $months
     *
     * @return $this
     */
    public function addMonths($months)
    {
        $this->dt->modify("$months month");

        return $this;
    }

    /**
     * @param int $days
     *
     * @return $this
     */
    public function addDays($days)
    {
        $this->dt->modify("$days days");

        return $this;
    }

    /*
     * 返回日期为星期几，0为星期天，1为星期一以此类推
     */
    public function getWeekDay()
    {
        return intval($this->dt->format('w'));
    }

    public function getWeekDayName()
    {
        return CalendarDB::$weekdayNames[$this->getWeekDay()];
    }

    /**
     * 计算当年1月1日到日期的天数
     *
     * @return int days
     */
    public function getDayOfYear()
    {
        $firstDayOfYear = new SolarDate($this->getYear(), 1, 1);

        return $firstDayOfYear->diffDays($this);
    }

    public function toLunarDate()
    {
        $year = intval($this->dt->format('Y'));
        $month = intval($this->dt->format('m'));
        $day = intval($this->dt->format('d'));

        $accDays = $this->getSolarYearDays($year, $month, $day);
        $lunarYear = $year;
        if ($accDays <= CalendarDB::getAccDays($year)) {
            $lunarYear -= 1;
            $accDays += 365;
            if ($this->isLeapYear($lunarYear)) {
                $accDays += 1;
            }
        }

        // accDays 是lunarYear年 1月1日到 当天的天数
        // lastAccDays lunarYear年 1月1日到 lunar_y年一月一日的天数
        $lastAccDays = CalendarDB::getAccDays($lunarYear);
        for ($lunarMonth = 1; $lunarMonth <= 13; $lunarMonth++) {
            $nextAccDays = $lastAccDays + CalendarDB::getFakeLunarMonthDays($lunarYear, $lunarMonth);
            if ($accDays <= $nextAccDays) {
                break;
            }
            $lastAccDays = $nextAccDays;
        }

        $lunarDay = $accDays - $lastAccDays;
        $lunarLeapMonth = CalendarDB::getLunarLeapMonth($lunarYear);
        if ($lunarLeapMonth != 0) {
            $lunarMonth -= 1;
            // 闰月用负数表示
            if ($lunarMonth == $lunarLeapMonth) {
                $lunarMonth *= -1;
            }
        }
        return new LunarDate($lunarYear, $lunarMonth, $lunarDay);


    }

    public static function getSolarYearDays($year, $month, $day)
    {
        $position = $month - 1;
        $days = static::$YearDays[$position] + $day;
        if ($month > 2 && static::isLeapYear($year)) {
            $days += 1;
        }
        return $days;
    }

    /**
     * 公历平年月份天数
     *
     * @param int $year
     * @param int $month
     *
     * @return int
     */
    public static function getSolarMonthDays($year, $month)
    {
        if (empty($year)) {
            return static::$MonthDays[$month];
        } else {
            if ($month == 2 && static::isLeapYear($year)) {
                return static::$MonthDays[$month] + 1;
            } else {
                return static::$MonthDays[$month];
            }
        }
    }
}
