# Period
Class for managing time periods.

This library uses [Carbon](https://carbon.nesbot.com/)

If you prefer not to include Carbon in your project, you can use
[SimplePeriod](https://github.com/pfrug/simple-period) where [DateTime](https://www.php.net/manual/es/class.datetime.php) is used instead


## Installation
``` sh
composer require pfrug/period
```

### Two-month date period
```
$period = Period::months(2);
echo $period; // From: 2021-09-25 00:19:43, To: 2021-11-25 00:19:43
```

### Two-yeanrs date period
```
$period = Period::years(2);
echo $period; // From: 2021-09-25 00:19:43, To: 2021-11-25 00:19:43
```

### Period of 3 weeks before and 2 weeks after
```
$period = Period::weeks(3,2);
echo $period; // From: 2021-11-04 00:19:43, To: 2021-12-09 00:19:43
```

### Example of use with Models through "Scopes"

```
// List Posts from the last 2 days

$period = Post::days(2);
$posts = Post::active()
		->byPeriod($period)
		->latest()
		->get();
```

```
// Model Post

public function scopeByPeriod($q, $period ){
	$q->whereBetween('created_at', $period->toArray());
}
```

### Useful functions for creating graphs

If we want to graph the values of a month in intervals of 2 days

```
$period = Period::months(1);
$range = $period->getDatePeriodByTime( 2 , 'day');

foreach( $range as $step ){
  print_r($step->format('Y-m-d'));
}
```
Result:
```
2021-10-25
2021-10-27
2021-10-29
2021-10-31
2021-11-02
2021-11-04
2021-11-06
2021-11-08
2021-11-10
2021-11-12
2021-11-14
2021-11-16
2021-11-18
2021-11-20
2021-11-22
2021-11-24
```

If we want to obtain a range of dates in a certain amount of steps, for example 7
```
$range = $period->getDatePeriod(7);
foreach( $range as $step ){
	print_r($step->format('Y-m-d H:i:s'));
}
```
Resutl:
```
2021-10-25 00:19:43
2021-10-29 10:45:26
2021-11-02 21:11:09
2021-11-07 07:36:52
2021-11-11 18:02:35
2021-11-16 04:28:18
2021-11-20 14:54:01
```

### 120 minute period in Uruguay time zone
```
$period = Period::minutes(120)->toTimezone( TimeZone::TZ_UY);
print_r($period);
```
Result:
```
Libraries\Period Object
(
    [startDate] => DateTime Object
        (
            [date] => 2021-11-24 18:19:43.000000
            [timezone_type] => 3
            [timezone] => America/Montevideo
        )

    [endDate] => DateTime Object
        (
            [date] => 2021-11-24 20:19:43.000000
            [timezone_type] => 3
            [timezone] => America/Montevideo
        )

    [timezone] => UTC
    [outputFormat] => Y-m-d H:i:s
)
```

### Change the format in which dates are displayed
```
$period = Period::months(2);
echo $period; // From: 2021-09-25 00:19:43, To: 2021-11-25 00:19:43

$period->outputFormat = 'Y-m-d';
echo $period; // From: 2021-09-25, To: 2021-11-25

```

### Set output timezone
Default Timezone
```
$period = Period::months(2);
print_r($period);
```
Result:
```
Libraries\Period Object
(
    [startDate] => DateTime Object
        (
            [date] => 2021-09-25 00:19:43.000000
            [timezone_type] => 3
            [timezone] => Europe/Berlin
        )

    [endDate] => DateTime Object
        (
            [date] => 2021-11-25 00:19:43.000000
            [timezone_type] => 3
            [timezone] => Europe/Berlin
        )

    [timezone] => UTC
    [outputFormat] => Y-m-d H:i:s
)
```

Timezone Uruguay
```
$period->toTimezone(TimeZone::TZ_UY);
print_r($period);
```
Result:
```
Libraries\Period Object
(
    [startDate] => DateTime Object
        (
            [date] => 2021-09-24 19:19:43.000000
            [timezone_type] => 3
            [timezone] => America/Montevideo
        )

    [endDate] => DateTime Object
        (
            [date] => 2021-11-24 20:19:43.000000
            [timezone_type] => 3
            [timezone] => America/Montevideo
        )

    [timezone] => UTC
    [outputFormat] => Y-m-d H:i:s
)
```

### Indicating in which timezone the dates were entered we can convert these dates to the appropriate timezone (by default UTC) for example to perform queries in the db

Suppose that users enter a range of dates for a search, the user will enter the dates in their time zone but in the DB the data is stored in UTC, In this case we can create the Period object and convert the dates to UTC indicating in which timezone were entered

Enter dates in Uruguay time zone
```
$period = Period::create( '2021-11-05 13:56', '2021-11-09 13:56:39');

```
Convert dates to UTC
```
$period->convertToTimezone(TimeZone::TZ_UY);
print_r($period);
```
Result:
```
Libraries\Period Object
(
    [startDate] => DateTime Object
        (
            [date] => 2021-11-05 16:56:00.000000
            [timezone_type] => 3
            [timezone] => UTC
        )

    [endDate] => DateTime Object
        (
            [date] => 2021-11-09 16:56:39.000000
            [timezone_type] => 3
            [timezone] => UTC
        )

    [timezone] => UTC
    [outputFormat] => Y-m-d H:i:s
)
```
