<?php

namespace Pfrug\Period;

use DateTime;
use Carbon\Carbon;
use Pfrug\Period\Helpers\StrHelper;
use Pfrug\Period\Exception\InvalidPeriodException;

/**
 * Class for managing time periods.
 *
 * @package Pfrug\Period
 * @author P.Frugone <frugone@gmail.com>
 */
class Period
{
    /**
     * The start date of the period.
     * @var DateTime
     */
    public $startDate;

    /**
     * The end date of the period.
     * @var DateTime
     */
    public $endDate;

    /**
     * The timezone for the period.
     * @var string
     */
    public $timezone = 'UTC';

    /**
     * Period constructor.
     *
     * @param DateTime $startDate The start date of the period.
     * @param DateTime $endDate The end date of the period.
     * @throws InvalidPeriodException if the start date is after the end date.
     */
    public function __construct(DateTime $startDate, DateTime $endDate)
    {
        if ($startDate > $endDate) {
            throw InvalidPeriodException::startDateCannotBeAfterEndDate($startDate, $endDate);
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Creates an instance of Period from the specified dates.
     *
     * @param string|DateTime $startDate
     * @param string|DateTime $endDate
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
     * Creates a Period instance with the specified number of minutes.
     *
     * @param int $numberOfMinutesStart
     * @param int $numberOfMinutesEnd
     * @return Period
     */
    public static function minutes($numberOfMinutesStart, $numberOfMinutesEnd = 0)
    {
        return self::getStartEndDates($numberOfMinutesStart, $numberOfMinutesEnd, 'minutes');
    }

    /**
     * Creates a Period instance with the specified number of hours.
     *
     * @param int $numberOfHoursStart
     * @param int $numberOfHoursEnd
     * @return Period
     */
    public static function hours($numberOfHoursStart, $numberOfHoursEnd = 0)
    {
        return self::getStartEndDates($numberOfHoursStart, $numberOfHoursEnd, 'hours');
    }

    /**
     * Creates a Period instance with the specified number of days.
     *
     * @param int $numberOfDays
     * @param int $numberOfDaysEnd
     * @return Period
     */
    public static function days($numberOfDays, $numberOfDaysEnd = 0)
    {
        return self::getStartEndDates($numberOfDays, $numberOfDaysEnd, 'days');
    }

    /**
     * Creates a Period instance with the specified number of weeks.
     *
     * @param int $numberOfWeeks
     * @param int $numberOfWeeksEnd
     * @return Period
     */
    public static function weeks($numberOfWeeks, $numberOfWeeksEnd = 0)
    {
        return self::getStartEndDates($numberOfWeeks, $numberOfWeeksEnd, 'weeks');
    }

    /**
     * Creates a Period instance with the specified number of months.
     *
     * @param int $numberOfMonths
     * @param int $numberOfMonthsEnd
     * @return Period
     */
    public static function months($numberOfMonths, $numberOfMonthsEnd = 0)
    {
        return self::getStartEndDates($numberOfMonths, $numberOfMonthsEnd, 'month');
    }

    /**
     * Creates a Period instance with the specified number of years.
     *
     * @param int $numberOfYears
     * @param int $numberOfYearsEnd
     * @return Period
     */
    public static function years($numberOfYears, $numberOfYearsEnd = 0)
    {
        return self::getStartEndDates($numberOfYears, $numberOfYearsEnd, 'years');
    }

    /**
     * Converts dates created in a given timezone to another.
     *
     * @param string $tzIn The timezone in which the dates were entered.
     * @param string $tzOut The timezone in which the dates will be output. Default is 'UTC'.
     * @return Period
     */
    public function convertToTimezone($tzIn, $tzOut = 'UTC')
    {
        return $this->toTimezone($tzOut, $tzIn);
    }

    /**
     * Converts dates to the specified timezone.
     *
     * @param string $tzOut The timezone in which the dates will be output.
     * @param string $tzIn The timezone in which the dates were entered.
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
     * Gets the difference between startDate and endDate.
     *
     * @param string $method Carbon function to obtain the difference between dates, e.g., diffInMinutes, diffInYears, etc.
     * @return mixed The result of the Carbon difference method.
     */
    public function diff($method)
    {
        return $this->startDate->{$method}($this->endDate);
    }

    /**
     * Obtains the set of dates and times, repeating at regular intervals during the start and end date.
     *
     * @param int $interval The time interval.
     * @param string $scale The time unit to apply (e.g., minutes, days, weeks, months, years, etc.).
     * @return \DatePeriod The set of dates and times.
     */
    public function getDatePeriodByTime($interval, $scale)
    {
        $step = \Carbon\CarbonInterval::{$scale}($interval);
        $period = new \DatePeriod($this->startDate, $step, $this->endDate);
        return $period;
    }

    /**
     * Obtains a set of dates and times, repeating at regular intervals during the start and end dates.
     *
     * @param int $steps The number of steps to be obtained.
     * @return \DatePeriod The set of dates and times.
     */
    public function getDatePeriod($steps)
    {
        $diff = $this->startDate->diffInSeconds($this->endDate);
        return $this->getDatePeriodByTime(ceil($diff / $steps), 'seconds');
    }

    /**
     * Returns the difference between startDate and endDate as a string.
     *
     * @return string The formatted interval between startDate and endDate.
     */
    public function getDiffToString()
    {
        $interval = $this->startDate->diff($this->endDate);
        return StrHelper::intervalToString($interval);
    }

    /**
     * Limits the initial date.
     * If the limit date is later than the initial date, the initial date is replaced by the date specified in $limit.
     *
     * @param DateTime $limit
     */
    public function limitStartDate(DateTime $limit)
    {
        if ($limit > $this->startDate) {
            $this->startDate = $limit;
        }
    }

    /**
     * Limits the end date.
     * If the limit date is earlier than the end date, the end date is replaced by the one specified in $limit.
     *
     * @param DateTime $limit
     */
    public function limitEndDate(DateTime $limit)
    {
        if ($limit < $this->endDate) {
            $this->endDate = $limit;
        }
    }

    /**
     * Gets the start and end dates based on the given parameters.
     *
     * @param int $start The start time quantity.
     * @param int|null $end The end time quantity, or null for the current time.
     * @param string $scale The unit of time (e.g., 'days', 'hours').
     * @return static An instance with the calculated start and end dates.
     */
    private static function getStartEndDates($start, $end, $scale)
    {
        $endDate = ($end) ? self::nowAdd($end, $scale) : self::now();
        $startDate = self::nowSub($start, $scale);
        return new static($startDate, $endDate);
    }

    /**
     * Gets the current date and time with seconds rounded.
     *
     * @return Carbon The current date and time with seconds rounded.
     */
    private static function now()
    {
        $now = Carbon::now();
        return $now->roundSeconds();
    }

    /**
     * Subtracts a specified quantity of time from the current date and time.
     *
     * @param int $quantity The quantity of time to subtract.
     * @param string $unit The unit of time (e.g., 'days', 'hours').
     * @return Carbon The modified date and time.
     */
    private static function nowSub($quantity, $unit)
    {
        return self::nowModify($quantity, 'sub' . ucfirst($unit));
    }

    /**
     * Adds a specified quantity of time to the current date and time.
     *
     * @param int $quantity The quantity of time to add.
     * @param string $unit The unit of time (e.g., 'days', 'hours').
     * @return Carbon The modified date and time.
     */
    private static function nowAdd($quantity, $unit)
    {
        return self::nowModify($quantity, 'add' . ucfirst($unit));
    }

    /**
     * Modifies the current date and time by adding or subtracting a specified quantity of time.
     *
     * @param int $quantity The quantity of time to modify.
     * @param string $method The method to apply (e.g., 'addDays', 'subHours').
     * @return Carbon The modified date and time.
     */
    private static function nowModify($quantity, $method)
    {
        return (self::now())->$method($quantity);
    }

    /**
     * Returns the startDate and endDate as an array.
     *
     * @return array An array containing the startDate and endDate.
     */
    public function toArray()
    {
        return [$this->startDate, $this->endDate];
    }

    /**
     * Returns a string representation of the date range.
     *
     * @return string The formatted date range string.
     */
    public function __toString()
    {
        return 'From: ' . $this->startDate . ', To: ' . $this->endDate;
    }
}
