<?php
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'PlayerModel.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once 'ErrorConstants.php';
/**
 * 
 * Performes player account actions trough web service
 *
 */
class AccountManager {
	/**
	 * 
	 * Create new account for player ...
	 * @param int $session_id
	 * @param int $affiliate_id
	 * @param string $username
	 * @param string $password
	 * @param string $email
	 * @param string $first_name
	 * @param string $last_name
	 * @param string $birthday
	 * @param string $zip
	 * @param string $phone
	 * @param string $city
	 * @param string $address
	 * @param string $country
	 * @param string $currency
	 * @return string
	 */
	public static function newAccount($session_id, $affiliate_id, $username, $password, $email,
	$first_name, $last_name, $birthday, $zip, $phone, $city, $address, $country, $currency){
		try{
			$session_id = strip_tags($session_id);
			$affiliate_id = strip_tags($affiliate_id);
			$username = strip_tags($username);
			$password = strip_tags($password);
			$email = strip_tags($email);
			$first_name = strip_tags($first_name);
			$last_name = strip_tags($last_name);
			$birthday = strip_tags($birthday);
			$zip = strip_tags($zip);
			$phone = strip_tags($phone);
			$city = strip_tags($city);
			$address = strip_tags($address);
			$country = strip_tags($country);
			$currency = strip_tags($currency);
			$modelPlayer = new PlayerModel();
			$player_type_id = $modelPlayer->getPlayerTypeID($session_id, ROLA_PL_PC_PLAYER_INTERNET);
			$password = md5(md5($password));
			$res = $modelPlayer->manageUser($session_id, INSERT, $affiliate_id, $username, 
			$password, $player_type_id, null, $email, $country, $currency, NO, $zip, $phone, 
			$address, $birthday, $first_name, $last_name, $city, null, null, null, null, null, 
			null, null, null, null, null, null, null);

			$json_message = Zend_Json::encode(
			    array(
			        "status"=>OK,
                )
            );
			exit($json_message);
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
			$message = "AccountManager::newAccount call failed <br /> Detected IP address = {$detected_ip_address} <br /> Exception Error: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
		}
	}

    /**
     * @param $backoffice_session_id
     * @param $player_id
     * @param $email
     * @param $banned
     * @param $zip
     * @param $phone
     * @param $address
     * @param $birthday
     * @param $first_name
     * @param $last_name
     * @param $city
     * @param $country
     * @return mixed
     */
    public static function updatePlayer($backoffice_session_id, $player_id, $email, $banned, $zip, $phone, $address, $birthday, $first_name, $last_name, $city, $country){
        $backoffice_session_id = strip_tags($backoffice_session_id);
        $player_id = strip_tags($player_id);
        $email = strip_tags($email);
        $banned = strip_tags($banned);
        $zip = strip_tags($zip);
        $phone = strip_tags($phone);
        $address = strip_tags($address);
        $birthday = strip_tags($birthday);
        $first_name = strip_tags($first_name);
		$last_name = strip_tags($last_name);
        $city = strip_tags($city);
        $country = strip_tags($country);
		/*
		require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
		$errorMailHelper = new ErrorMailHelper();
		$errorMailHelper->sendMail("MANAGMENT_CORE.M_DOLAR_MANAGE_AFFILIATES(p_session_id_in = $session_id, p_action_in = $action, p_aff_for_in = $aff_for,
			p_name_new_in = $name, p_password_in = $password, p_affiliates_type_in = $subrole,
			p_mac_address_in = $mac_address, p_email_in = $email, p_country_in = $country,
			p_currency_in = $currency, p_banned_in = $banned, p_zip_code_in = $zip,
			p_phone_in = $phone, p_address_in = $address, p_birthday_in = $birthday,
			p_first_name_in = $first_name, p_last_name_in = $last_name, p_city_in = $city,
			p_subject_id_in = $subject_id, p_multi_currency_in = $multicurrency,
			p_auto_credits_increment_in = $autoincrement, p_pay_back_perc = $game_payback,
			p_key_exit_in = $key_exit, p_enter_pass_in = $enter_password, p_ADDRESS2_in = $street_address2,
			p_BANK_ACCOUNT_in = $bank_account, p_BANK_COUNTRY_in = $bank_country,
			p_SWIFT_in = $swift, p_IBAN_in = $iban, p_send_mail_in = $receive_mail,
			p_inactive_time_in = $inactive_time, p_site_name_in = $site_name, p_registred_aff = $registred_affiliate, p_origin_in = $origin_site,
		    p_new_login_kills_sess = $new_login_kills_sess, p_subject_id_dummy_out = null)");
		//return array("status"=>OK, "subject_id"=>$subject_id_dummy);
		*/
        try {
            if(strlen($backoffice_session_id) == 0 || strlen($player_id) == 0) {
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>NOK,
                        "error_message"=> $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'PlayerModel.php';
            $modelPlayer = new PlayerModel();
            $result = $modelPlayer->manageUser($backoffice_session_id, UPDATE, null, null, null, null, null, $email, $country, null, $banned, $zip, $phone, $address, $birthday, $first_name, $last_name, $city, $player_id, null, null, null, null, null);
            if($result['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                    )
                );
                exit($json_message);
            }else{
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>NOK,
                    )
                );
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
			$message = "AccountManager::updatePlayer call failed <br /> Detected IP address = {$detected_ip_address} <br /> Exception Error: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
        }
	}

	/**
	 * 
	 * validates if new player username exists in our database ...
	 * @param string $username
	 * @return mixed
	 */
	public static function validatePlayer($username){
		try{
			if(!isset($username)) {
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>NOK,
                        "error_message"=> $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
			$username = strip_tags($username);
			$modelPlayer = new PlayerModel();
			$exist = $modelPlayer->validatePlayerName($username);

			$json_message = Zend_Json::encode(
			    array(
			        "status" => OK,
                    "status_out"=> $exist
                )
            );
			exit($json_message);
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
			$message = "AccountManager::validatePlayer(username = {$username}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
		}
	}

    /**
     * This call resets player's old_password with new_password
     * @param $bo_session_id
     * @param $player_id
     * @param $old_password
     * @param $new_password
     * @return mixed
     */
    public static function resetPlayerPassword($bo_session_id, $player_id, $old_password, $new_password){
        try{
			if(strlen($bo_session_id) == 0 || strlen($player_id) == 0 || strlen($old_password) || strlen($new_password)) {
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>NOK,
                        "error_message"=> $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
            $bo_session_id = strip_tags($bo_session_id);
            $player_id = strip_tags($player_id);
			$old_password = md5(md5(strip_tags($old_password)));
            $new_password = md5(md5(strip_tags($new_password)));
			$modelPlayer = new PlayerModel();
            $result = $modelPlayer->resetPlayerPassword($bo_session_id, $player_id, $old_password, $new_password);
            if($result["status"] == OK) {
                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "status_out"=> OK
                    )
                );
                exit($json_message);
            }else{
                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "status_out" => NOK
                    )
                );
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
			$message = "AccountManager::resetPlayerPassword(session_id = {$bo_session_id}, player_id = {$player_id}, old_password=, new_password=) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
		}
    }

    /**
     * @param $backoffice_session_id
     * @param $player_id
     * @return mixed
     */
	public static function playerDetails($backoffice_session_id, $player_id){
		try{
            if(strlen($backoffice_session_id) == 0 || strlen($player_id) == 0) {
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>NOK,
                        "error_message"=> $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'PlayerModel.php';
            $modelPlayer = new PlayerModel();
			$player = $modelPlayer->getPlayerDetails($backoffice_session_id, $player_id);
            if(!$player){
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>NOK,
                        "error_message"=> $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
            $checkbox_banned = false;
            if($player['banned'] == YES){
                $checkbox_banned = true;
            }
			$arrData = array(
                "status" => OK,
                "data" => array(
                    "aff_id" =>$player['aff_id'],
                    "user_name"=>$player['user_name'],
                    "first_name"=>$player['first_name'],
                    "last_name"=>$player['last_name'],
                    "email"=>$player['email'],
                    "birthday"=>$player['birthday'],
                    "city"=>$player['city'],
                    "mac_address"=>$player['mac_address'],
                    "zip_code"=>$player['zip_code'],
                    "address"=>$player['address'],
                    "banned"=>$player['banned'],
                    "checkbox_banned"=>$checkbox_banned,
                    "admin_option"=>$player['admin_option'],
                    "credits"=>NumberHelper::convert_double($player['credits']),
                    "credits_formatted"=>NumberHelper::format_double($player['credits']),
                    "remaining_cash"=>NumberHelper::format_double($player['remaining_cash']),
                    "phone"=>$player['phone'],
                    "super_role"=>$player['super_rola'],
                    "role"=>$player['rola'],
                    "path"=>$player['path'],
                    "currency"=>$player['currency'],
                    "ba_name"=>$player['ba_name'],
                    "ba_type_name"=>$player['ba_type_name'],
                    "ba_id"=>$player['ba_id'],
                    "ba_type_id"=>$player['ba_type_id'],
                    "multi_currency"=>$player['multi_currency'],
                    "start_time"=>$player['start_time'],
                    "last_login_ip_country"=>$player['ip_address'],
                    "key_exit"=>$player['key_exit'],
                    "pass"=>$player['pass'],
                    "country_name"=>$player['country_name'],
                    "one_box"=>$player['one_box'],
                    "tree_banned"=>$player['tree_banned'],
                    "address2"=>$player['address2'],
                    "bank_account"=>$player['bank_account'],
                    "bank_country"=>$player['bank_country'],
                    "swift"=>$player['swift'],
                    "iban"=>$player['iban'],
                    "send_mail"=>$player['send_mail'],
                    "country_id"=>$player['country_id'],
                    "inactive_time"=>$player['inactive_time'],
                    "auto_credits_increment"=>$player['auto_credits_increment'],
                    "email_verified"=>$player['email_verified'],
                    "kyc_verified"=>$player['kyc_verified'],
                    "withdraw"=> NumberHelper::format_double($player['withdraw']),
                    "last_login_date"=>$player['last_login'],
                    "credit_status"=>NumberHelper::convert_double($player['credit_status']),
                    "credit_status_formatted"=>NumberHelper::format_double($player['credit_status']),
                    "created_by"=>$player['created_by'],
                    "created_by_date"=>$player['creation_date'],
                    "edited_by"=>$player['edited_by'],
                    "last_change_date"=>$player['last_change_date']
                )
			);
	        $json_message = Zend_Json::encode(
			    $arrData
            );
			exit($json_message);
		}catch(Zend_Exception $ex){
			$detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
			$message = "AccountManager::playerDetails(backoffice_session_id = {$backoffice_session_id}, player_id= {$player_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
		}
	}
}