<?php

namespace Pfrug\Period\Tests;

use PHPUnit\Framework\TestCase;
use Pfrug\Period\Period;
use Pfrug\Period\TimeZone;
use Pfrug\Period\Exception\InvalidPeriodException;

class PeriodTest extends TestCase
{
    public function createByMinutes()
    {
        $period = Period::minutes(2);
        $diff = $period->startDate->diff($period->endDate);
        $this->assertTrue($diff->i == 2);
    }

    public function createByHours()
    {
        $period = Period::hours(2);
        $diff = $period->startDate->diff($period->endDate);
        $this->assertTrue($diff->h == 2);
    }

    public function createByDays()
    {
        $period = Period::days(2);
        $diff = $period->startDate->diff($period->endDate);
        $this->assertTrue($diff->d == 2);
    }

    public function createByWeeks()
    {
        $period = Period::weeks(2);
        $diff = $period->startDate->diff($period->endDate);
        $this->assertTrue($diff->d == 14);
    }

    public function createByMonths()
    {
        $period = Period::months(2);
        $diff = $period->startDate->diff($period->endDate);
        $this->assertTrue($diff->m == 2);
        return $period;
    }

    public function createByYears()
    {
        $period = Period::years(2);
        $diff = $period->startDate->diff($period->endDate);
        $this->assertTrue($diff->y == 2);
    }

    public function testConvertToTimezone()
    {
        $period = Period::create('2021-11-05 18:56', '2021-11-09 13:56:39');
        $startHour = $period->startDate->format('H');

        $period->timezone = TimeZone::TZ_UTC;
        $period->convertToTimezone(TimeZone::TZ_UY);
        $hoursDiff = $period->startDate->format('H') - $startHour;

        $this->assertTrue($hoursDiff == 3);
    }

    public function testToTimezone()
    {
        // UTC -> UY
        $period = Period::create('2022-05-16 17:27', '2022-05-16 17:50');
        $hourUTC = $period->startDate->format('H');

        $period->toTimezone(TimeZone::TZ_UY, TimeZone::TZ_UTC);

        $hoursDiff = $hourUTC - $period->startDate->format('H');

        $this->assertTrue($hoursDiff == 3);
    }

    public function testGetDatePeriodByTime()
    {
        $days = rand(5, 20);
        $steps = rand(1, $days);
        $rangeCount = ceil($days / $steps);

        $scale = 'days';
        $period = Period::$scale($days);
        $range = $period->getDatePeriodByTime($steps, $scale);
        $this->assertTrue(iterator_count($range) == $rangeCount);

        $scale = 'months';
        $period = Period::$scale($days);
        $range = $period->getDatePeriodByTime($steps, $scale);
        $this->assertTrue(iterator_count($range) == $rangeCount);

        $scale = 'years';
        $period = Period::$scale($days);
        $range = $period->getDatePeriodByTime($steps, $scale);
        $this->assertTrue(iterator_count($range) == $rangeCount);
    }

    public function testGetDatePeriod()
    {

        $steps = rand(1, 20);
        $period = Period::months(1);
        $range = $period->getDatePeriod($steps);

        $this->assertTrue(iterator_count($range) == $steps);
    }

    public function testGetDiffToString()
    {
        $period = Period::months(2);
        $this->assertTrue($period->getDiffToString() == '2 meses, 0 horas, 0 minutos');
        $period = Period::create('2020-04-16 17:27', '2022-05-10 19:50');
        $this->assertTrue($period->getDiffToString() == '2 años, 24 días, 2 horas, 23 minutos');
    }

    public function testLimitStartDate()
    {

        $yesterday = new \DateTime('yesterday');
        $period = Period::days(5);
        $period->limitStartDate($yesterday);

        $this->assertTrue($period->startDate == $yesterday);
    }

    public function testLimitEndDate()
    {
        $yesterday = new \DateTime('yesterday');
        $period = Period::days(5);
        $period->limitEndDate($yesterday);

        $this->assertTrue($period->endDate == $yesterday);
    }

    public function testToArray()
    {
        $period = Period::days(5);
        $this->assertTrue(is_array($period->toArray()));
    }

    public function testException()
    {
        $this->expectException(InvalidPeriodException::class);

        $yesterday = new \DateTime('yesterday');
        $tomorrow = new \DateTime('tomorrow');
        Period::create($tomorrow, $yesterday);
    }
}
