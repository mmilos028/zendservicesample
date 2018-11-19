<?php
class DateTimeModel{
	public function __construct(){
	}
	//returns first day in month
	public function firstDayInMonth(){
		return date('01-M-Y');
	}
}