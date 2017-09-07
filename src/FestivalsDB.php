<?php
/**
 * Created by PhpStorm.
 * User: wgx
 * Date: 8/21/15
 * Time: 11:53
 */

namespace Woodfish\Component\Date;


class FestivalsDB
{
    const CHUN_JIE = "春节";
    const YUAN_XIAO = "元宵节";
    const DUAN_WU = "端午节";
    const QI_XI = "七夕";
    const ZHONG_YUAN = "中元节";
    const ZHONG_QIU = "中秋节";
    const CHONG_YANG = "重阳节";
    const LA_BA = "腊八";
    const XIAO_NIAN = "小年";
    const CHU_XI = "除夕";
    const YUAN_DAN = "元旦";
    const QING_REN_JIE = "情人节";
    const FU_NV_JIE = "妇女节";
    const ZHI_SHU_JIE = "植树节";
    const YU_REN_JIE = "愚人节";
    const LAO_DONG_JIE = "劳动节";
    const QING_NIAN_JIE = "青年节";
    const MU_QIN_JIE = "母亲节";
    const ER_TONG_JIE = "儿童节";
    const FU_QIN_JIE = "父亲节";
    const JIAO_SHI_JIE = "教师节";
    const GUO_QIN_JIE = "国庆节";
    const WAN_SHENG_JIE = "万圣节";
    const GAN_EN_JIE = "感恩节";
    const PING_AN_YE = "平安夜";
    const SHENG_DAN_JIE = "圣诞节";

    /**
     * 返回一个月内的所有节日/节气
     * @param int $year
     * @param int $month
     * @param bool $showTerm - 是否显示节气
     * @return array - array[day] = 节日|节气
     */
    public static function getFestivals($year, $month, $showTerm)
    {
        $max = SolarDate::getSolarMonthDays($year, $month);
        $festivals = array();
        for ($day = 1; $day <= $max; ++$day) {
            $festival = self::getFestival($year, $month, $day, $showTerm);
            if ($festival) {
                $festivals[$day] = $festival;
            }
        }

        return $festivals;
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @param bool $showTerm - 是否显示节气
     * @return string
     */
    public static function getFestival($year, $month, $day, $showTerm)
    {
        // 公历节日
        $solar = new SolarDate($year, $month, $day);
        // $solarYear = $solar->getYear();
        $solarMonth = $solar->getMonth();
        $solarDay = $solar->getDay();
        $weekDay = intval($solar->getWeekDay());
        switch ($solarMonth) {
            case 1:
                if ($solarDay == 1) {
                    return self::YUAN_DAN;
                }
                break;

            case 2:
                if ($solarDay == 14) {
                    return self::QING_REN_JIE;
                }
                break;

            case 3:
                if ($solarDay == 8) {
                    return self::FU_NV_JIE;
                } elseif ($solarDay == 12) {
                    return self::ZHI_SHU_JIE;
                }
                break;

            case 4:
                if ($solarDay == 1) {
                    return self::YU_REN_JIE;
                }
                break;

            case 5:
                if ($solarDay == 1) {
                    return self::LAO_DONG_JIE;
                } elseif ($solarDay == 4) {
                    return self::QING_NIAN_JIE;
                } elseif ($weekDay === 0 && (intval($solarDay / 7) + ($solarDay % 7 > 0 ? 1 : 0)) == 2) { // 母亲节 5月第2个星期日
                    return self::MU_QIN_JIE;
                }
                break;

            case 6:
                if ($solarDay == 1) {
                    return self::ER_TONG_JIE;
                } elseif ($weekDay === 0 && (intval($solarDay / 7) + ($solarDay % 7 > 0 ? 1 : 0)) == 3) { // 父亲节 6月第3个星期日
                    return self::FU_QIN_JIE;
                }
                break;

            case 7:
                break;

            case 8:
                break;

            case 9:
                if ($solarDay == 10) {
                    return self::JIAO_SHI_JIE;
                }
                break;

            case 10:
                if ($solarDay == 1) {
                    return self::GUO_QIN_JIE;
                }
                break;

            case 11:
                if ($solarDay == 1) {
                    return self::WAN_SHENG_JIE;
                } elseif ($weekDay === 4 && (intval($solarDay / 7) + ($solarDay % 7 > 0 ? 1 : 0)) == 4) { // 感恩节 11月第4个星期四
                    return self::GAN_EN_JIE;
                }
                break;

            case 12:
                if ($solarDay == 24) {
                    return self::PING_AN_YE;
                } elseif ($solarDay == 25) {
                    return self::SHENG_DAN_JIE;
                }
                break;
        }

        // --------------------------------------
        // 农历节日

        $lunar = LunarDate::fromSolarDate($solar);
        if (!$lunar) {
            return "";
        }

        $lunarYear = $lunar->getYear();
        $lunarMonth = $lunar->getMonth();
        $lunarDay = $lunar->getDay();

        switch ($lunarMonth) {
            case 1:
                if ($lunarDay == 1) {
                    return self::CHUN_JIE;
                } elseif ($lunarDay == 15) {
                    return self::YUAN_XIAO;
                }
                break;

            case 2:
                break;

            case 3:
                break;

            case 4:
                break;

            case 5:
                if ($lunarDay == 5) {
                    return self::DUAN_WU;
                }
                break;

            case 6:
                break;

            case 7:
                if ($lunarDay == 7) {
                    return self::QI_XI;
                } elseif ($lunarDay == 15) {
                    return self::ZHONG_YUAN;
                }
                break;

            case 8:
                if ($lunarDay == 15) {
                    return self::ZHONG_QIU;
                }
                break;

            case 9:
                if ($lunarDay == 9) {
                    return self::CHONG_YANG;
                }
                break;

            case 10:
                break;

            case 11:
                break;

            case 12:
                if ($lunarDay == 8) {
                    return self::LA_BA;
                } elseif ($lunarDay == 23) {
                    return self::XIAO_NIAN;
                } elseif ($lunarDay == CalendarDB::getFakeLunarMonthDays($lunarYear, LunarDate::getFakeMonth($lunarYear, $lunarMonth))) { // 除夕
                    return self::CHU_XI;
                }
                break;
        }

        // --------------------------------------
        // 加入节气

        // 如果不加入节气则把冬至和清明加入节日
        $index = CalendarDB::getTermBySolarDate($year, $month, $day);
        if ($showTerm) {
            if ($index != -1) {
                return CalendarDB::getTermName($index);
            }
        } else {
            if ($index == 6 || $index == 23) {
                return CalendarDB::getTermName($index);
            }
        }

        return '';
    }

}