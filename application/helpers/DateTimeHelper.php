<?php
class DateTimeHelper{
    /**
    returns date in format Y-m-d (2014-12-30)
    accept any date format
     */
    public static function getDateFormat1($dateValue){
        return date('Y-m-d', strtotime($dateValue));
    }

    /**
    returns date in format d-m-Y HH:MM:ss
    accepts date in format 4/3/2014 5:45:13 AM
     */
    public static function getDateFormat2($dateValue){
        return date('d-m-Y H:i:s', strtotime($dateValue));
    }

    //returns date format example: 21-Oct-2013
    public static function getDateFormat3($dateValue){
        return date('d-M-Y', strtotime($dateValue));
    }

    //returns date format example: 28-Nov-2014 12:01:19
    public static function getDateFormat4($dateValue){
        return date('d-M-Y H:i:s', strtotime($dateValue));
    }

    //returns date format with start time 00:00:00
    public static function getDateFormat5($dateValue){
        $time = strtotime($dateValue);
        return date('d-M-Y 00:00:00', $time);
    }

    //returns date format with start time 23:59:59
    public static function getDateFormat6($dateValue){
        $time = strtotime($dateValue);
        return date('d-M-Y 23:59:59', $time);
    }

    //returns date format in format 21-Oct-2013 for date from yesterday (one day before current day)
    public static function getDateFormat7($dateValue){
        $dateValueTime = strtotime($dateValue);
        $yesterdayTime = $dateValueTime - 86400;
        return date('d-M-Y', $yesterdayTime);
    }

    //returns date format example: 28-Nov-2014 12:01:19 from current time
    public static function getDateFormat8(){
        return date('d-M-Y H:i:s', time());
    }

    //returns date format example: 16.10.2013
    public static function getDateFormat9($dateValue){
        return date('d.m.Y', strtotime($dateValue));
    }

    //returns date format example: 28-Nov-2014 12:01:19 - hour between 01-12
    public static function getDateFormat10($dateValue){
        return date('d-M-Y g:i:s A', strtotime($dateValue));
    }
}