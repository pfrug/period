<?php

namespace Pfrug\Period\Exception;

use DateTime;
use Exception;

class InvalidPeriodException extends Exception
{
    public static function startDateCannotBeAfterEndDate(DateTime $startDate, DateTime $endDate)
    {
        return new static("Start date `{$startDate->format('Y-m-d')}` cannot be after end date `{$endDate->format('Y-m-d')}`.");
    }
}
