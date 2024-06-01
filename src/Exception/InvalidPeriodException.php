<?php

namespace Pfrug\Period\Exception;

use DateTime;
use Exception;

/**
 * @package Pfrug\Period
 */
class InvalidPeriodException extends Exception
{
    /**
     * Generates an exception message indicating that the start date cannot be after the end date.
     * @param DateTime $startDate The start date of the period.
     * @param DateTime $endDate The end date of the period.
     * @return InvalidPeriodException The exception instance.
     */
    public static function startDateCannotBeAfterEndDate(DateTime $startDate, DateTime $endDate)
    {
        return new static("Start date `{$startDate->format('Y-m-d')}` cannot be after end date `{$endDate->format('Y-m-d')}`.");
    }
}
