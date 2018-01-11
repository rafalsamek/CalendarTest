<?php
/**
 * Created by PhpStorm.
 * User: rsamek
 * Date: 10/01/18
 * Time: 05:29
 */

namespace Calendar;


use DateTimeInterface;
use DateTime;
use DateInterval;

class Calendar implements CalendarInterface
{
    private $datetime;
    public function __construct(DateTimeInterface $datetime)
    {
        $this->datetime = $datetime;
    }

    public function getDay()
    {
        return (int) $this->datetime->format('j');
    }

    public function getWeekDay()
    {
        $weekday = (int) $this->datetime->format('w');
        return $weekday == 0 ? 7 : $weekday;
    }

    public function getFirstWeekDay()
    {
        $firstDayOfMonth = new DateTime(date("Y-m-01", $this->datetime->getTimestamp()));
        $weekday = (int) $firstDayOfMonth->format('w');
        return $weekday == 0 ? 7 : $weekday;
    }

    public function getFirstWeek()
    {
        $firstDayOfMonth = new DateTime(date("Y-m-01", $this->datetime->getTimestamp()));
        return (int) $firstDayOfMonth->format('W');
    }

    public function getNumberOfDaysInThisMonth()
    {
        return cal_days_in_month(CAL_GREGORIAN, $this->datetime->format('n'), $this->datetime->format('Y'));
    }

    public function getNumberOfDaysInPreviousMonth()
    {
        $previousMonthDatetime = new DateTime(date("Y-m-01", strtotime($this->datetime->format("Y-m-01") . " -1 month")));
        return cal_days_in_month(CAL_GREGORIAN, $previousMonthDatetime->format('n'), $previousMonthDatetime->format('Y'));
    }

    public function getCalendar()
    {
        $calendar = array();
        $day = $this->getFirstDayOfCalendar();
        while($day <= $this->getLastDayOfCalendar()) {
            $this->addDayToCalendar($calendar, $day);
            $day->add(new DateInterval('P1D'));
        }
        return $calendar;
    }

    private function addDayToCalendar(&$calendar, DateTime $day)
    {
        $weekNumber = (int) $day->format('W');
        $previousWeekDay = clone $this->datetime;
        $previousWeekDay->sub(new DateInterval('P7D'));
        $previousWeekNumber = (int) $previousWeekDay->format('W');
        if(!array_key_exists($weekNumber, $calendar)) {
            $calendar[$weekNumber] = array();
        }
        $calendar[$weekNumber][$day->format('j')] = $weekNumber == $previousWeekNumber;
    }

    private function getFirstDayOfCalendar()
    {
        $day = new DateTime(date("Y-m-01", $this->datetime->getTimestamp()));
        $firstWeekDay = $this->getFirstWeekDay();
        if($firstWeekDay > 1) {
            $diff = 'P' . ($firstWeekDay - 1) . 'D';
            $day->sub(new DateInterval($diff));
        }
        return $day;
    }

    private function getLastDayOfCalendar()
    {
        $numberOfDaysInThisMonth = $this->getNumberOfDaysInThisMonth();
        $day = new DateTime(date("Y-m-$numberOfDaysInThisMonth 23:59:59", $this->datetime->getTimestamp()));
        $lastDayNumber = $day->format('w');
        if($lastDayNumber > 0) {
            $diff = 'P' . (7 - $lastDayNumber) . 'D';
            $day->add(new DateInterval($diff));
        }
        return $day;
    }
}