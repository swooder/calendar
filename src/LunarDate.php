<?php
/**
 * Created by PhpStorm.
 * User: woodfish
 * Date: 15-2-28
 * Time: 上午11:27
 */
namespace Woodfish\Component\Date;


use Woodfish\Component\Date\Exception\InvalidDateException;
use Woodfish\Component\Date\Exception\UnSupportLunarDateException;

class LunarDate
{
    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $month;

    /**
     * @var int
     */
    private $day;

    public function __construct($year = null, $month = null, $day = null)
    {
        if ($year === null) {
            $dt = static::fromSolarDate(new SolarDate());

            $year = $dt->getYear();
            $month = $dt->getMonth();
            $day = $dt->getDay();
        }

        $year = intval($year);
        $month = intval($month);
        $day = intval($day);

        if (!CalendarDB::validSolarYear($year)) {
            throw new UnSupportLunarDateException();
        }

        if (!$this->isValid($year, $month, $day)) {
            throw new InvalidDateException();
        }

        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    /**
     * 根据公历日期生成农历日期
     *
     * @param  SolarDate $solarDate
     * @return LunarDate
     */
    public static function fromSolarDate(SolarDate $solarDate)
    {
        $solarDateYear = $solarDate->getYear();

        // 从当年1月1日到当天的天数，此处如果是1月29日，此处算出来是29天
        $accDays = $solarDate->getDayOfYear() + 1;

        $lunarYear = $solarDateYear;
        // 如果从当年1月1日到当天天数小于等于当年1月1日到当年一月一日的数,（如果一月一日是1月29日那getAccDays算出来是28天),说明还没有到农历新年呢
        if ($accDays <= CalendarDB::getAccDays($solarDateYear)) {
            $lunarYear -= 1;
            // 下面计算从上一年1月1日到当天的天数
            $accDays += 365;

            // 如果上一年是闰年那么多一天
            if (SolarDate::isLeapYear($lunarYear)) {
                $accDays += 1;
            }
        }

        $lastAccDays = CalendarDB::getAccDays($lunarYear);
        // accDays 是lunarYear年 1月1日到 当天的天数
        // lastAccDays lunarYear年 1月1日到 lunar_y年一月一日的天数
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
            if ($lunarMonth > $lunarLeapMonth) {
                $lunarMonth--;
                // 闰月用负数表示
                if ($lunarMonth == $lunarLeapMonth) {
                    $lunarMonth *= -1;
                }
            }
        }

        return new LunarDate($lunarYear, $lunarMonth, $lunarDay);
    }

    /**
     * 获得下个合法日期
     *
     * 从startYear这一年开始（包括startYear), 找到一个合法的month月，day日
     * 比如说startYear 2012, month 6, day 30 找到下一个有六月三十的农历日期
     *
     * @warning 此处有while循环
     *
     * @param int $month
     * @param int $day
     * @param int $startYear
     * @param int $shiftDays 如果不存在的日期是否提前或者拖后
     *
     * @return LunarDate
     */
    public static function generateNext($month, $day, $startYear, $shiftDays = 0)
    {
        $year = $startYear;
        $absMonth = abs($month);
        $shifted = 0;
        while (!LunarDate::isValid($year, $absMonth, $day)) {
            if ($shiftDays != 0 && LunarDate::isValid($year, $absMonth, $day + $shiftDays)) {
                $shifted = 1;
                break;
            }
            $year++;
            if (!CalendarDB::validSolarYear($year)) {
                break;
            }
        }

        return new LunarDate($year, $absMonth, $day - $shifted);
    }

    /**
     * @param int $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param int $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * 将_getFakeMonth暴露出去
     * @param int $year
     * @param int $month
     * @return mixed
     */
    public static function getFakeMonth($year, $month)
    {
        return self::_getFakeMonth($year, $month);
    }

    /*
     * 修正月的下标，兼容calendarDB
     */
    private static function _getFakeMonth($year, $month)
    {
        $lunarLeapMonth = CalendarDB::getLunarLeapMonth($year);

        $fakeMonth = $month;

        if ($lunarLeapMonth != 0) {
            if ($month > $lunarLeapMonth) {
                $fakeMonth++;
            } else {
                if ($lunarLeapMonth == -1 * $month) {
                    $fakeMonth = $lunarLeapMonth + 1;
                }
            }
        }

        return $fakeMonth;
    }

    /**
     * 是否为合法的农历年月日
     *
     * @param $year
     * @param $month
     * @param $day
     *
     * @return bool
     */
    private static function isValid($year, $month, $day)
    {
        $lunarLeapMonth = CalendarDB::getLunarLeapMonth($year);
        if ($month < 0) {
            if ($lunarLeapMonth != $month * -1) {
                return false;
            }
        } else {
            if ($month < 1 || $month > 12) {
                return false;
            }
        }

        return $day > 0 && $day <= CalendarDB::getFakeLunarMonthDays($year, self::_getFakeMonth($year, $month));
    }

    /**
     * 与指定日期的相隔天数
     *
     * 返回小于0则代表当前日期在指定日期的未来。 大于0则表示指定日期是未来的日期。
     *
     * @param LunarDate $other
     *
     * @return int
     */
    public function diffDays(LunarDate $other)
    {
        return $this->toSolarDate()->diffDays($other->toSolarDate());
    }

    /*
     * 返回XX年XX月XX日
     */
    public function formatString()
    {
        return static::format($this->month, $this->day, $this->year);
    }

    public function formatStringWithOutYear()
    {
        return static::format($this->month, $this->day);
    }

    /**
     * 格式化输出农历日期
     *
     * @param int $month
     * @param int $day
     * @param int|null $year
     *
     * @return string
     */
    public static function format($month, $day, $year = null)
    {
        $sb = '';

        if ($year < 0 || $year > 9999 || $month < -12 || $month > 12 || $day > 30 || $day < 1) {
            return $sb;
        }

        if ($year) {
            $sb = CalendarDB::getLunarYearName($year) . "年";
        }

        $sb = $sb . CalendarDB::getLunarMonthName($month);
        $sb = $sb . CalendarDB::getLunarDayName($day);

        return $sb;
    }

    /**
     * @return SolarDate
     */
    public function toSolarDate()
    {
        $fakeMonth = self::_getFakeMonth($this->year, $this->month);
        // 1月1日 到农历一月一日有多少天
        $accDays = CalendarDB::getAccDays($this->year);
        // accDays 再加上农历一月一日 到农历当天的天数
        for ($i = 1; $i < $fakeMonth; $i++) {
            $accDays += CalendarDB::getFakeLunarMonthDays($this->year, $i);
        }

        $accDays += $this->day;
        // accDays此时是1月1日到当天的天数。

        $solarYear = $this->year;
        $solarMonth = 1;
        $monthDays = SolarDate::getSolarMonthDays($solarYear, $solarMonth);
        while ($accDays > $monthDays) {
            $accDays -= $monthDays;
            $solarMonth++;
            if ($solarMonth > 12) {
                $solarMonth -= 12;
                $solarYear += 1;
            }
            $monthDays = SolarDate::getSolarMonthDays($solarYear, $solarMonth);
        }
        $solarDay = $accDays;

        return new SolarDate($solarYear, $solarMonth, $solarDay);
    }

    public function beforeThan(LunarDate $other, $ignoreYear = false)
    {
        if ($ignoreYear) {
            if (abs($other->getMonth()) === abs($this->getMonth())) {
                return $this->getDay() < $other->getDay();
            } else {
                return abs($this->getMonth()) < abs($other->getMonth());
            }
        } else {
            return $this->toSolarDate()->beforeThan($other->toSolarDate(), $ignoreYear);
        }
    }

    public function equals(LunarDate $other, $ignoreYear = false)
    {
        if ($ignoreYear) {
            return abs($this->month) == abs($other->getMonth()) && $this->day == $other->getDay();
        } else {
            return $this->year == $other->getYear() && $this->month == $other->getMonth() && $this->day == $other->getDay();
        }
    }

    public function afterThan(LunarDate $other, $ignoreYear = false)
    {
        return !$this->equals($other, $ignoreYear) && !$this->beforeThan($other, $ignoreYear);
    }

    public function addDays($days)
    {
        $solarDate = $this->toSolarDate();
        $solarDate = $solarDate->addDays($days);

        $newLunarData = static::fromSolarDate($solarDate);
        $this->setYear($newLunarData->getYear());
        $this->setMonth($newLunarData->getMonth());
        $this->setDay($newLunarData->getDay());

        return $this;
    }

    public function getWeekDay()
    {
        return $this->toSolarDate()->getWeekDay();
    }

    public function getWeekDayName()
    {
        return $this->toSolarDate()->getWeekDayName();
    }
}
