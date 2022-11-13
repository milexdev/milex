<?php

namespace Milex\ReportBundle\Scheduler\Enum;

class SchedulerEnum
{
    const UNIT_NOW     = 'NOW';
    const UNIT_DAILY   = 'DAILY';
    const UNIT_WEEKLY  = 'WEEKLY'; //Defined in report.js too
    const UNIT_MONTHLY = 'MONTHLY'; //Defined in report.js too

    const DAY_MO        = 'MO';
    const DAY_TU        = 'TU';
    const DAY_WE        = 'WE';
    const DAY_TH        = 'TH';
    const DAY_FR        = 'FR';
    const DAY_SA        = 'SA';
    const DAY_SU        = 'SU';
    const DAY_WEEK_DAYS = 'WEEK_DAYS';

    const MONTH_FREQUENCY_FIRST = '1';
    const MONTH_FREQUENCY_LAST  = '-1';

    /**
     * @return array
     */
    public static function getUnitEnumForSelect()
    {
        return [
            'milex.report.schedule.unit.now'   => self::UNIT_NOW,
            'milex.report.schedule.unit.day'   => self::UNIT_DAILY,
            'milex.report.schedule.unit.week'  => self::UNIT_WEEKLY,
            'milex.report.schedule.unit.month' => self::UNIT_MONTHLY,
        ];
    }

    /**
     * @return array
     */
    public static function getDayEnumForSelect()
    {
        return [
            'milex.report.schedule.day.monday'    => self::DAY_MO,
            'milex.report.schedule.day.tuesday'   => self::DAY_TU,
            'milex.report.schedule.day.wednesday' => self::DAY_WE,
            'milex.report.schedule.day.thursday'  => self::DAY_TH,
            'milex.report.schedule.day.friday'    => self::DAY_FR,
            'milex.report.schedule.day.saturday'  => self::DAY_SA,
            'milex.report.schedule.day.sunday'    => self::DAY_SU,
            'milex.report.schedule.day.week_days' => self::DAY_WEEK_DAYS,
        ];
    }

    /**
     * @return array
     */
    public static function getMonthFrequencyForSelect()
    {
        return [
            'milex.report.schedule.month_frequency.first' => self::MONTH_FREQUENCY_FIRST,
            'milex.report.schedule.month_frequency.last'  => self::MONTH_FREQUENCY_LAST,
        ];
    }

    /**
     * @return array
     */
    public static function getWeekDays()
    {
        return [
            self::DAY_MO,
            self::DAY_TU,
            self::DAY_WE,
            self::DAY_TH,
            self::DAY_FR,
        ];
    }
}
