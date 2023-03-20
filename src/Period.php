<?php

namespace Pfrug\Period;

use DateTime;
use Carbon\Carbon;
use Pfrug\Period\Helpers\StrHelper;
use Pfrug\Period\Exception\InvalidPeriodException;

class Period
{
    /** @var \DateTime */
    public $startDate;

    /** @var \DateTime */
    public $endDate;

    public $timezone = 'UTC';

    // replaced by constants in class TimeZone
    //public const TZ_UY     = 'America/Montevideo';
    //public const TZ_ES     = 'Europe/Madrid';
    //public const TZ_UTC    = 'UTC';

    public function __construct(DateTime $startDate, DateTime $endDate)
    {
        if ($startDate > $endDate) {
            throw InvalidPeriodException::startDateCannotBeAfterEndDate($startDate, $endDate);
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     *  Creates an instance of Period from the specified dates
     *
     * @param String|DateTime $startDate
     * @param String|DateTime $endDate
     * @return Period
     */
    public static function create($startDate, $endDate = null)
    {
        if (is_string($startDate)) {
            $startDate = Carbon::parse($startDate);
        }
        if (!$endDate) {
            $endDate = Carbon::now();
        } elseif (is_string($endDate)) {
            $endDate = Carbon::parse($endDate);
        }
        return new static($startDate, $endDate);
    }

    /**
     * Creates a Period instance with the specified number of minutes
     *
     * @param Int $numberOfMinutesStart
     * @param Int $numberOfMinutesEnd
     * @return Period
     */
    public static function minutes($numberOfMinutesStart, $numberOfMinutesEnd = 0)
    {
        return self::getStartEndDates($numberOfMinutesStart, $numberOfMinutesEnd, 'minutes');
    }

    /**
     * Creates a Period instance with the specified number of hours
     *
     * @param Int $numberOfHoursStart
     * @param Int $numberOfHoursEnd
     * @return Period
     */
    public static function hours($numberOfHoursStart, $numberOfHoursEnd = 0)
    {
        return self::getStartEndDates($numberOfHoursStart, $numberOfHoursEnd, 'hours');
    }

    /**
     * Creates a Period instance with the specified number of hours
     *
     * @param Int $numberOfDays
     * @param Int $numberOfDaysEnd
     * @return Period
     */
    public static function days($numberOfDays, $numberOfDaysEnd = 0)
    {
        return self::getStartEndDates($numberOfDays, $numberOfDaysEnd, 'days');
    }

    /**
     * Creates a Period instance with the specified number of weeks
     *
     * @param Int $numberOfWeeks
     * @param Int $numberOfWeeksEnd
     * @return Period
     */
    public static function weeks($numberOfWeeks, $numberOfWeeksEnd = 0)
    {
        return self::getStartEndDates($numberOfWeeks, $numberOfWeeksEnd, 'weeks');
    }

    /**
     * creates a Period instance with the specified number of months
     *
     * @param Int $numberOfMonths
     * @param Int $numberOfMonthsEnd
     * @return Period
     */
    public static function months($numberOfMonths, $numberOfMonthsEnd = 0)
    {
        return self::getStartEndDates($numberOfMonths, $numberOfMonthsEnd, 'month');
    }

    /**
     * creates a Period instance with the specified number of years
     *
     * @param Int $numberOfYears
     * @param Int $numberOfYearsEnd
     * @return Period
     */
    public static function years($numberOfYears, $numberOfYearsEnd = 0)
    {
        return self::getStartEndDates($numberOfYears, $numberOfYearsEnd, 'years');
    }

    /**
     * Convert dates created in a given timezone to another
     * @param String $tzIn  Timezone, indicates in which Timezone are the dates entered
     * @param String $tzOut  Timezone, indicates in which Timezone are the dates output, Defaul UTC
     * @return Period
     */
    public function convertToTimezone($tzIn, $tzOut = 'UTC')
    {
        return $this->toTimezone($tzOut, $tzIn);
    }

    /**
     * Converts dates to the specified TimeZone
     * @param String $tzOut  Timezone, indicates in which Timezone are the dates output
     * @param String $tzIn  Timezone, indicates in which Timezone are the dates entered
     * @return Period
     */
    public function toTimezone($tzOut, $tzIn = 'UTC')
    {
        $this->startDate = Carbon::parse(
            $this->startDate->format('Y-m-d H:i:s'),
            $tzIn
        )->tz($tzOut);

        $this->endDate = Carbon::parse(
            $this->endDate->format('Y-m-d H:i:s'),
            $tzIn
        )->tz($tzOut);

        return $this;
    }

    /**
     * Gets the difference between startDate and endDate
     * @param String. Carbon function to obtain the difference between dates,  ej: diffInMinutes, diffInYear etc...
     */
    public function diff($method)
    {
        return $this->startDate->{$method}($this->endDate);
    }

    /*
    public function addHours($numberOfHours)
    {
        $this->startDate->addHours($numberOfHours);
        $this->endDate->addHours($numberOfHours);
    }*/

    /**
     * obtains the set of dates and times, repeating at regular intervals during the start and end date
     *
     * @param Int $interval  intervalo de tiempo
     * @param String $scale . media de tiempo a aplicar { minutes, days, week ,month, year , etc....}
     * @return DatePeriod
     */
    public function getDatePeriodByTime($interval, $scale)
    {
        $step = \Carbon\CarbonInterval::{$scale}($interval);
        $period = new \DatePeriod($this->startDate, $step, $this->endDate);
        return $period;
    }

    /**
     * Obtains a set of dates and times, repeating at regular intervals during the start and end dates
     *
     * @param Int $steps. Number of steps to be obtained
     * @return DatePeriod
     */
    public function getDatePeriod($steps)
    {
        $diff = $this->startDate->diffInSeconds($this->endDate);
        return $this->getDatePeriodByTime(ceil($diff / $steps), 'seconds');
    }

    public function getDiffToString()
    {
        $interval = $this->startDate->diff($this->endDate);
        return StrHelper::intervalToString($interval);
    }

    /**
     * Limits the initial date
     * If the limit date is later than the initial date, the initial date is replaced by the date specified in $limit
     *
     * @param DateTime $limit
     */
    public function limitStartDate(DateTime $limit)
    {
        if ($limit > $this->startDate) {
            $this->startDate = $limit ;
        }
    }

    /**
     * Limit date is the end date
     * If the limit date is earlier than the end date, the end date is replaced by the one specified in $limit
     *
     * @param DateTime $limit
     */
    public function limitEndDate(DateTime $limit)
    {
        if ($limit < $this->endDate) {
            $this->endDate = $limit;
        }
    }

    private static function getStartEndDates($start, $end, $scale)
    {
        $endDate = ($end) ? self::nowAdd($end, $scale) : self::now();
        $startDate = self::nowSub($start, $scale);
        return new static($startDate, $endDate);
    }

    private static function now()
    {

        $now =  Carbon::now();
        return $now->roundSeconds();
    }

    private static function nowSub($quantity, $unit)
    {
        return self::nowModify($quantity, 'sub' . ucfirst($unit));
    }

    private static function nowAdd($quantity, $unit)
    {
        return self::nowModify($quantity, 'add' .  ucfirst($unit));
    }

    private static function nowModify($quantity, $method)
    {
        return (self::now())->$method($quantity);
    }

    /**
     * return startDate and endDate to array
     * @return Array
     */
    public function toArray()
    {
        return [$this->startDate, $this->endDate];
    }

    public function __toString()
    {
        return 'From: ' . $this->startDate . ', To: ' . $this->endDate;
    }
}
