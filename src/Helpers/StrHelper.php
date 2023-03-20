<?php

namespace Pfrug\Period\Helpers;

class StrHelper
{
    /**
     * Convierte un intervalo de tiempo en un string
     * @param DateInterval $interval
     * @return string
     */
    public static function intervalToString($interval)
    {
        $duration = ($interval->y > 0) ? "$interval->y año" . (($interval->y == 1) ? ', ' : 's, ') : '';
        $duration .= ($interval->m > 0) ? "$interval->m mes" . (($interval->m == 1) ? ', ' : 'es, ') : '';
        $duration .= ($interval->d > 0) ? "$interval->d día" . (($interval->d == 1) ? ', ' : 's, ') : '';
        $duration .= "$interval->h hora" . (($interval->h == 1) ? '' : 's') . ", $interval->i minuto" . (($interval->i == 1 ) ? '' : 's');

        return $duration;
    }
}
