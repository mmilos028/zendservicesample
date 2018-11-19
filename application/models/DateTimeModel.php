<?php
class DateTimeModel{
	public function __construct(){
	}
	//returns first day in month
    /**
     * @return bool|string
     */
	public function firstDayInMonth(){
		return date('01-M-Y');
	}
}