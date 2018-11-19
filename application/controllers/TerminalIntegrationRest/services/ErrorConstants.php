<?php

class ErrorConstants
{
    public static $GENERAL_ERROR = 0;
    public static $EMAIL_NOT_AVAILABLE = 1;
    public static $USERNAME_NOT_AVAILABLE = 2;
    public static $METHOD_NOT_ALLOWED = 3;
    public static $INVALID_REQUEST = 4;
    public static $INVALID_OPERATION = 5;
    public static $MISSING_PARAMETERS = 6;
    public static $PLAYER_BANNED_LIMIT = 7;
    public static $NOK_EXCEPTION = 8;
    public static $AFFILIATE_BANNED = 9;
    public static $PLAYER_SELF_BANNED = 10;
    public static $WRONG_USERNAME_OR_PASSWORD = 11;
    public static $WRONG_PHYSICAL_ADDRESS = 12;
    public static $LOGIN_TOO_MANY_TIMES = 13;
    public static $PANIC_STATUS_ACTIVE = 14;
    public static $NOT_DEFINED_GAMES_OR_PARAMETERS = 15;
    public static $WRONG_PIN_CODE = 16;
    public static $CASHIER_WRONG_MAC_ADDRESS_ERROR = 17;
    public static $CARD_EXPIRED_ERROR = 18;
    public static $INVALID_CURRENCY = 19;
    public static $AFFILIATE_NAME_WRONG = 20;
    public static $MAC_ADDRESS_ALREADY_USED_FOR_OTHER_AFFILIATE = 21;
    public static $MAC_ADDRESS_ALREADY_USED = 22;
    public static $BAD_MAC_ADDRESS = 23;
    public static $NOK_WRONG_FNAME_LNAME_BIRTHDAY = 24;
    public static $NOK_WRONG_EMAIL = 25;
    public static $NOK_WRONG_FNAME_LNAME_ADDRESS = 26;
    public static $NOK_WRONG_FNAME_LNAME_PHONE = 27;
    public static $NOK_INVALID_REGISTRATION_CODE = 28;

    private static $messages = array(
        array(
            "message_no" => 0,
            "message_text" => "GENERAL_ERROR",
            "message_description" => "Unknown error occurred"
        ),
        array(
            "message_no" => 1,
            "message_text" => "EMAIL_NOT_AVAILABLE",
            "message_description" => "Email is not available, already taken in our database"
        ),
        array(
            "message_no" => 2,
            "message_text" => "USERNAME_NOT_AVAILABLE",
            "message_description" => "Username is not available, already taken in our database"
        ),
        array(
            "message_no" => 3,
            "message_text" => "Method not allowed",
            "message_description" => "Method is not allowed, not allowed service operation"
        ),
        array(
            "message_no" => 4,
            "message_text" => "Invalid request",
            "message_description" => "Invalid request, not a valid JSON message, missing operation field in JSON message"
        ),
        array(
            "message_no" => 5,
            "message_text" => "Invalid operation",
            "message_description" => "Invalid operation, operation field value not recognized in web service"
        ),
        array(
            "message_no" => 6,
            "message_text" => "MISSING_PARAMETERS",
            "message_description" => "One or more parameters are missing"
        ),
        array(
            "message_no" => 7,
            "message_text" => "PLAYER_BANNED_LIMIT",
            "message_description" => "Player is banned limit"
        ),
        array(
            "message_no" => 8,
            "message_text" => "NOK_EXCEPTION",
            "message_description" => "Exception occurred in database or web services"
        ),
        array(
            "message_no" => 9,
            "message_text" => "AFFILIATE_BANNED",
            "message_description" => "Affiliate is banned"
        ),
        array(
            "message_no" => 10,
            "message_text" => "PLAYER_SELF_BANNED",
            "message_description" => "Player is self-banned"
        ),
        array(
            "message_no" => 11,
            "message_text" => "WRONG_USERNAME_OR_PASSWORD",
            "message_description" => "Wrong username or password"
        ),
        array(
            "message_no" => 12,
            "message_text" => "WRONG_PHYSICAL_ADDRESS",
            "message_description" => "Wrong physical address of device/terminal"
        ),
        array(
            "message_no" => 13,
            "message_text" => "LOGIN_TOO_MANY_TIMES",
            "message_description" => "Login too many times"
        ),
        array(
            "message_no" => 14,
            "message_text" => "PANIC_STATUS_ACTIVE",
            "message_description" => "Panic status active"
        ),
        array(
            "message_no" => 15,
            "message_text" => "NOT_DEFINED_GAMES_OR_PARAMETERS",
            "message_description" => "Not defined games or parameters"
        ),
        array(
            "message_no" => 16,
            "message_text" => "WRONG_PIN_CODE",
            "message_description" => "Wrong Pin Code"
        ),
        array(
            "message_no" => 17,
            "message_text" => "CASHIER_WRONG_MAC_ADDRESS_ERROR",
            "message_description" => "Cashier Wrong Mac Address",
        ),
        array(
            "message_no" => 18,
            "message_text" => "CARD_EXPIRED_ERROR",
            "message_description" => "Card Expired",
        ),
        array(
            "message_no" => 19,
            "message_text" => "INVALID_CURRENCY",
            "message_description" => "Currency Not Found",
        ),
        array(
            "message_no" => 20,
            "message_text" => "AFFILIATE_NAME_WRONG",
            "message_description" => "Affiliate name is wrong",
        ),
        array(
            "message_no" => 21,
            "message_text" => "MAC_ADDRESS_ALREADY_USED_FOR_OTHER_AFFILIATE",
            "message_description" => "Mac Address is already used for other affiliate",
        ),
        array(
            "message_no" => 22,
            "message_text" => "MAC_ADDRESS_ALREADY_USED",
            "message_description" => "Mac Address is already used"
        ),
        array(
            "message_no" => 23,
            "message_text" => "BAD_MAC_ADDRESS",
            "message_description" => "Bad Mac Address"
        ),
        array(
            "message_no" => 24,
            "message_text" => "NOK_WRONG_FNAME_LNAME_BIRTHDAY",
            "message_description" => "Wrong First name, Last name and Birthday. Combination already exists."
        ),
        array(
            "message_no" => 25,
            "message_text" => "NOK_WRONG_EMAIL",
            "message_description" => "Wrong Email. Already exists."
        ),
        array(
            "message_no" => 26,
            "message_text" => "NOK_WRONG_FNAME_LNAME_ADDRESS",
            "message_description" => "Wrong First name, Last name and Address. Combination already exists."
        ),
        array(
            "message_no" => 27,
            "message_text" => "NOK_WRONG_FNAME_LNAME_PHONE",
            "message_description" => "Wrong First name, Last name and Phone. Combination already exists."
        ),
        array(
            "message_no" => 28,
            "message_text" => "NOK_INVALID_REGISTRATION_CODE",
            "message_description" => "Wrong Registration Code value"
        )
    );

    public static function getErrorMessages(){
        return self::$messages;
    }

    public static function getErrorMessage($index){
        return self::$messages[$index];
    }


}
