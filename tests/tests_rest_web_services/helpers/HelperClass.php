<?php

class HelperClass {

    public static function randomString($prefix = '', $suffix = '', $number_of_letters = 4)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        $characters_length = strlen($characters);
        for ($i = 0; $i < $number_of_letters; $i++) {
            $randstring .= $characters[rand(0, $characters_length - 1)];
        }
        return $prefix . $randstring . $suffix;
    }

    public static function player_random_name($prefix = 'tstpl', $suffix = '', $number_of_letters = 4){
        return self::RandomString($prefix, $suffix, $number_of_letters);
    }

    public static function random_number($prefix = 'tstpl', $suffix = '', $min_value = 1000, $max_value = 9999){
        $random_name = random_int($min_value, $max_value);
        return $prefix . $random_name . $suffix;
    }
}