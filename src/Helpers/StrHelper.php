<?php

namespace Pfrug\Period\Helpers;

/**
 * @package Pfrug\Period
 */
class StrHelper
{
    /**
     * Converts a time interval into a string.
     *
     * @param DateInterval $interval
     * @return string
     */
    public static function intervalToString($interval)
    {
        $duration = ($interval->y > 0) ? "$interval->y year" . (($interval->y == 1) ? ', ' : 's, ') : '';
        $duration .= ($interval->m > 0) ? "$interval->m month" . (($interval->m == 1) ? ', ' : 's, ') : '';
        $duration .= ($interval->d > 0) ? "$interval->d day" . (($interval->d == 1) ? ', ' : 's, ') : '';
        $duration .= "$interval->h hour" . (($interval->h == 1) ? '' : 's') . ", $interval->i minute" . (($interval->i == 1 ) ? '' : 's');

        return $duration;
    }
}
