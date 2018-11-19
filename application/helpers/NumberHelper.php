<?php
class NumberHelper{
	/**
		returns number rounded and formated to 2 decimals with thousand separator
	*/
	public static function format_number_double($double_number){
		return number_format($double_number, 2);
	}
	
	public static function format_double($double_number){
		return number_format($double_number, 2);
	}
	
	public static function convert_double($number_string){
		return doubleval($number_string);
	}
	
	public static function format_number_integer($integer_number){
		return number_format($integer_number);
	}
	
	public static function format_integer($integer_number){
		return number_format($integer_number);
	}
	
	public static function convert_integer($number_string){
		return intval($number_string);
	}

    public static function format_english_double($number_string){
        return number_format($number_string, 2, '.', '');
    }
	
}