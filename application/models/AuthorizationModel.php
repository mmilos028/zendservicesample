<?php
require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class AuthorizationModel {
	//error constants
	public function __construct(){
	}

	//enable terminal with pin code
    /**
     * @param $pin_code
     * @param $status
     * @param $mac_address
     * @return string
     * @throws Zend_Exception
     */
	public function enableTerminal($pin_code, $status, $mac_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$ENABLE_TERMINAL(:p_pin_code_in, :p_status_in, :p_mac_address_in)');
			$stmt->bindParam(':p_pin_code_in', $pin_code);
			$stmt->bindParam(':p_status_in', $status);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return "1";
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			if($code == "20911"){
				$errorHelper = new ErrorHelper();
				$message = "Wrong pin code was sent!!! <br /> PLAY_CORE.M\$ENABLE_TERMINAL(:p_pin_code_in = {$pin_code}, :p_status_in = {$status}, :p_mac_address_in = {$mac_address}) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);
				return WRONG_PIN_CODE;
			}
			else{
				$errorHelper = new ErrorHelper();
				$message = "PLAY_CORE.M\$ENABLE_TERMINAL(:p_pin_code_in = {$pin_code}, :p_status_in = {$status}, :p_mac_address_in = {$mac_address}) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);
				return INTERNAL_ERROR;
			}
		}
	}
	//check if terminal's affiliate is banned
    /**
     * @param $mac_address
     * @param $gctype
     * @return mixed
     * @throws Zend_Exception
     */
	public function checkAffiliateForTerminal($mac_address, $gctype){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CHECK_AFFILIATE_FOR_TERMINAL(:p_mac_address_in, :gc_type_in, :y_n_out, :p_reason_out)');
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':gc_type_in', $gctype);
			$status = ''; // Y is banned, N - is not banned
			$stmt->bindParam(':y_n_out', $status, SQLT_CHR, 255);
			$reason = 0;
			$stmt->bindParam(':p_reason_out', $reason, SQLT_INT);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array($status, $reason);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MANAGMENT_CORE.M\$CHECK_AFFILIATE_FOR_TERMINAL(:p_mac_address_in = {$mac_address}, :gc_type_in = {$gctype}, :y_n_out, :p_reason_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return null;
		}
	}

	//check panic status for terminal
    /**
     * @param $mac_address
     * @return mixed
     * @throws Zend_Exception
     */
	public function checkPanic($mac_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$CHECK_PANIC(:p_mac_address_in, :p_panic_out)');
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$panic = ''; // Y is in panic, N - is not in panic
			$stmt->bindParam(':p_panic_out', $panic, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $panic;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "PLAY_CORE.M\$CHECK_PANIC(:p_mac_address_in, :p_panic_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return null;
		}
	}

	/* call for credit status update*/
	/*session_id, amount, currency*/
	/** Performes login to web lobby*/
    /**
     * @param $mac_address
     * @return int|mixed|null
     * @throws Zend_Exception
     */
	public function loginWebLobby($mac_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGE_WEB.M$LOGIN_WEB_LOBY(:p_mac_address_in, :url_list_out, :p_duration_out, :p_price_out, :p_currency_out, :p_skin_out, :p_id_out, :p_credits_out, :p_show_hide_out, :p_button_position_out, :p_language_out, :p_panic_out)');
			$stmt->bindParam(':p_mac_address_in', $mac_address);			
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':url_list_out', $cursor);
			$duration_out = "";
			$stmt->bindParam(':p_duration_out', $duration_out, SQLT_CHR, 255);
			$price_out = "";
			$stmt->bindParam(':p_price_out', $price_out, SQLT_CHR, 255);
			$currency_out = "";
			$stmt->bindParam(':p_currency_out', $currency_out, SQLT_CHR, 255);
			$skin_out = "";
			$stmt->bindParam(':p_skin_out', $skin_out, SQLT_CHR, 255);
			$started_session_id = "";
			$stmt->bindParam(':p_id_out', $started_session_id, SQLT_CHR, 255);
			$credits_out = "";
			$stmt->bindParam(':p_credits_out', $credits_out, SQLT_CHR, 255);
			$show_hide = "";
			$stmt->bindParam(':p_show_hide_out', $show_hide, SQLT_CHR, 255);
			$button_position = "";
			$stmt->bindParam(':p_button_position_out', $button_position, SQLT_CHR, 255);
			$language = "";
			$stmt->bindParam(':p_language_out', $language, SQLT_CHR, 255);
			$panic_status = "";
			$stmt->bindParam(':p_panic_out', $panic_status, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$dbAdapter->closeConnection();
			$cursor->free();
			return array($cursor, $duration_out, $price_out, $currency_out, $skin_out, $started_session_id, $credits_out, $show_hide, $button_position, $language, $panic_status);
		}catch(Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			if($code == "20173"){
                $errorHelper = new ErrorHelper();
                $message = "MANAGE_WEB.M\$LOGIN_WEB_LOBY(:p_mac_address_in = {$mac_address}, :url_list_out, :p_duration_out, :p_price_out, :p_currency_out, :p_skin_out, :p_id_out, :p_credits_out, :p_show_hide_out, :p_button_position_out, :p_language_out, :p_panic_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceError($message, $message);
				return $code;
			}
			else {
				$errorHelper = new ErrorHelper();
                $message = "MANAGE_WEB.M\$LOGIN_WEB_LOBY(:p_mac_address_in = {$mac_address}, :url_list_out, :p_duration_out, :p_price_out, :p_currency_out, :p_skin_out, :p_id_out, :p_credits_out, :p_show_hide_out, :p_button_position_out, :p_language_out, :p_panic_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);
				return null;
			}
		}
	}
	
	/** Performes cashier login */
    /**
     * @param $session_id
     * @param $access_code
     * @return string
     * @throws Zend_Exception
     */
	public function loginWebCashier($session_id, $access_code){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_CASH.M$LOGIN_CASHIER(:p_session_id_in, :p_access_code_in, :p_login_ok_out, :credits_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_access_code_in', $access_code);
			$login_ok = "-1000000000000";
			$stmt->bindParam(':p_login_ok_out', $login_ok, SQLT_CHR, 255);
			$credits_out = "-1000000000000";
			$stmt->bindParam(':credits_out', $credits_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>$login_ok, "credits_out"=>$credits_out);
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "WEB_CASH.M\$LOGIN_CASHIER(:p_session_id_in={$session_id}, :p_access_code_in={$access_code}, :p_login_ok_out, :credits_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
			return INTERNAL_ERROR;
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "WEB_CASH.M\$LOGIN_CASHIER(:p_session_id_in={$session_id}, :p_access_code_in={$access_code}, :p_login_ok_out, :credits_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
			$errorHelper->serviceError($message, $message);
			return INTERNAL_ERROR;
		}
		
	}
	/** Performes cashier login */
    /**
     * @param $session_id
     * @param $access_code
     * @param $mac_address
     * @param $general_purpose
     * @return mixed
     * @throws Zend_Exception
     */
	public function loginCashier($session_id, $access_code, $mac_address, $general_purpose){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$LOGIN_CASHIER(:p_session_id_in, :p_access_code_in, :p_mac_address_in, :p_general_purpose_in, :p_login_ok_out, :credits_in_out, :no_games_out, :credits_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_access_code_in', $access_code);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_general_purpose_in', $general_purpose);
			$login_ok = "-1000000000000";
			$stmt->bindParam(':p_login_ok_out', $login_ok, SQLT_CHR, 255);
			$credits_in = "-1000000000000";
			$stmt->bindParam(':credits_in_out', $credits_in, SQLT_CHR, 255);
			$no_games = "-1000000000000";
			$stmt->bindParam(':no_games_out', $no_games, SQLT_CHR, 255);
			$credits_out = "-1000000000000";
			$stmt->bindParam(':credits_out', $credits_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>$login_ok, "credits_in"=>$credits_in, "no_games"=>$no_games, "credits_out"=>$credits_out);
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();			
			$errorHelper = new ErrorHelper();
			$message = "PLAY_CORE.M\$LOGIN_CASHIER(:p_session_id_in = {$session_id}, :p_access_code_in = {$access_code}, :p_mac_address_in = {$mac_address}, :p_general_purpose_in = {$general_purpose}, :p_login_ok_out, :credits_in_out, :no_games_out, :credits_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
			return null;
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "PLAY_CORE.M\$LOGIN_CASHIER(:p_session_id_in = {$session_id}, :p_access_code_in = {$access_code}, :p_mac_address_in = {$mac_address}, :p_general_purpose_in = {$general_purpose}, :p_login_ok_out, :credits_in_out, :no_games_out, :credits_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
			$errorHelper->serviceError($message, $message);
			return null;
		}
	}

	/**	Opens terminal session, login terminal into game client application */
    /**
     * @param $username
     * @param $password
     * @param $mac_address
     * @param $version
     * @param $ip_address
     * @param null $country
     * @param null $city
     * @param null $device_aff_id
     * @param null $gp_mac_address
     * @param null $registred_aff
     * @return mixed
     * @throws Zend_Exception
     */
	public function openTerminalSession($username, $password, $mac_address, $version, $ip_address, $country = null, $city = null, $device_aff_id = null, $gp_mac_address = null, $registred_aff = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		/*
		$errorHelper = new ErrorHelper();
		$message = "PLAY_CORE.M\$OPEN_TERMINAL_SESSION (p_user_name_in = $username, p_password_in = $password, p_mac_address_in = $mac_address, p_version_in = $version, p_ip_address_in = $ip_address, p_country_in = $country, p_city_in = $city, p_device_aff_id_in = $device_aff_id,
			p_gp_mac_address_in = $gp_mac_address, p_registred_aff_in = $registred_aff)";			
		$errorHelper->sendMail($message);
		$errorHelper->serviceAccessLog($message);
		*/
		try{
			$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$OPEN_TERMINAL_SESSION(:p_user_name_in, :p_password_in, :p_mac_address_in, :p_version_in, :p_ip_address_in, :p_country_in, :p_city_in, :p_device_aff_id_in, :p_gp_mac_address_in, :p_registred_aff_in, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out)');
			$stmt->bindParam(':p_user_name_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_version_in', $version);
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$stmt->bindParam(':p_country_in', $country);
			$stmt->bindParam(':p_city_in', $city);
			$stmt->bindParam(':p_device_aff_id_in', $device_aff_id);
			$stmt->bindParam(':p_gp_mac_address_in', $gp_mac_address);
			$stmt->bindParam(':p_registred_aff_in', $registred_aff);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_of_games_out', $cursor);
			$session_id_out = "123456789";
			$stmt->bindParam(':p_session_id_out', $session_id_out, SQLT_CHR, 255);
			$credits_out = "123456789";
			$stmt->bindParam(':p_credits_out', $credits_out, SQLT_CHR, 255);
			$currency = "123456789";
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
			$player_id = "";
			$stmt->bindParam(':p_player_id_out', $player_id, SQLT_CHR, 255);
			$device = "";
			$stmt->bindParam(':p_device_out', $device, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("session_id"=>$session_id_out, "list_games"=>$cursor, "credits"=>$credits_out, "currency"=>$currency, "player_id"=>$player_id, "device"=>$device);
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
			return INTERNAL_ERROR;
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();			
			$code = $ex2->getCode();
			//return $code;
			/*if received exception is unknown*/
			if($code != "20121" && $code != "20454" && $code != "20122" && $code != "1422" && $code != "20500" && $code != "20707"){
				$errorHelper = new ErrorHelper();
                $message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
				$errorHelper->serviceError($message, $message);
				return INTERNAL_ERROR;
			}
			/*if received exception is wrong username or password type*/
			if($code == "20121" || $code == "20454"){
				//sends email when user tries to login with wrong username password				
				$errorHelper = new ErrorHelper();
                $message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> User has tried to login with wrong username or password! <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
				$errorHelper->serviceError($message, $message);
				return WRONG_USERNAME_PASSWORD;
			}
			/*if received exception is wrong physical address type*/
			if($code == "20122"){
				//sends email when user tries to login with wrong physical address type
				$errorHelper = new ErrorHelper();
                $message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> User has tried to login with wrong physical address! <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
				$errorHelper->serviceError($message, $message);
				return WRONG_PHYSICAL_ADDRESS;
			}
			if($code == "20500"){
				//sends email when user tries to login with wrong physical address type
				$errorHelper = new ErrorHelper();
                $message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> User has tried to login with his own active banned limit! <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
				$errorHelper->serviceError($message, $message);
				return PLAYER_BANNED_LIMIT;
			}
			if($code == "20707"){
				//sends email when user tries to login more than allowed number of times
				$errorHelper = new ErrorHelper();
                $message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> User has tried to login with wrong username or password more than allowed number of times! <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
				$errorHelper->serviceError($message, $message);
				return LOGIN_TOO_MANY_TIMES;
			}
			return INTERNAL_ERROR;
		}
	}

	/**	Opens terminal session, login terminal into game client application */
    /**
     * @param $username
     * @param $password
     * @param $mac_address
     * @param $version
     * @param $ip_address
     * @param null $country
     * @param null $city
     * @param null $device_aff_id
     * @param null $gp_mac_address
     * @param null $registred_aff
     * @return mixed
     * @throws Zend_Exception
     */
	public function openTerminalSessionMalta($username, $password, $mac_address, $version, $ip_address, $country = null, $city = null, $device_aff_id = null, $gp_mac_address = null, $registred_aff = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		/*
		$errorHelper = new ErrorHelper();
		$message = "PLAY_CORE.OPEN_TERMINAL_SESSION (p_user_name_in = $username, p_password_in = $password, p_mac_address_in = $mac_address, p_version_in = $version, p_ip_address_in = $ip_address, p_country_in = $country, p_city_in = $city, p_device_aff_id_in = $device_aff_id,
			p_gp_mac_address_in = $gp_mac_address, p_registred_aff_in = $registred_aff)";			
		$errorHelper->sendMail($message);
		$errorHelper->serviceAccessLog($message);
		*/
		try{
			$stmt = $dbAdapter->prepare('CALL PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in, :p_password_in, :p_mac_address_in, :p_version_in, :p_ip_address_in, :p_country_in, :p_city_in, :p_device_aff_id_in, :p_gp_mac_address_in, :p_registred_aff_in, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out)');
			$stmt->bindParam(':p_user_name_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_version_in', $version);
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$stmt->bindParam(':p_country_in', $country);
			$stmt->bindParam(':p_city_in', $city);
			$stmt->bindParam(':p_device_aff_id_in', $device_aff_id);
			$stmt->bindParam(':p_gp_mac_address_in', $gp_mac_address);
			$stmt->bindParam(':p_registred_aff_in', $registred_aff);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_of_games_out', $cursor);
			$session_id_out = "123456789";
			$stmt->bindParam(':p_session_id_out', $session_id_out, SQLT_CHR, 255);
			$credits_out = "123456789";
			$stmt->bindParam(':p_credits_out', $credits_out, SQLT_CHR, 255);
			$currency = "123456789";
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
			$player_id = "";
			$stmt->bindParam(':p_player_id_out', $player_id, SQLT_CHR, 255);
			$device = "";
			$stmt->bindParam(':p_device_out', $device, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("session_id"=>$session_id_out, "list_games"=>$cursor, "credits"=>$credits_out, "currency"=>$currency, "player_id"=>$player_id, "device"=>$device);
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
			return INTERNAL_ERROR;
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();			
			$code = $ex2->getCode();
			//return $code;
			/*if received exception is unknown*/
			if($code != "20121" && $code != "20454" && $code != "20122" && $code != "1422" && $code != "20500" && $code != "20707"){
				$errorHelper = new ErrorHelper();
                $message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> Unknown Error occurred! <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
				$errorHelper->serviceError($message, $message);
				return INTERNAL_ERROR;
			}
			/*if received exception is wrong username or password type*/
			if($code == "20121" || $code == "20454"){
				//sends email when user tries to login with wrong username password				
				$errorHelper = new ErrorHelper();
                $message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> User has tried to login with wrong username or password! <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
				$errorHelper->serviceError($message, $message);
				return WRONG_USERNAME_PASSWORD;
			}
			/*if received exception is wrong physical address type*/
			if($code == "20122"){
				//sends email when user tries to login with wrong physical address type
				$errorHelper = new ErrorHelper();
                $message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> User has tried to login with wrong physical address! <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
				$errorHelper->serviceError($message, $message);
				return WRONG_PHYSICAL_ADDRESS;
			}
			if($code == "20500"){
				//sends email when user tries to login with wrong physical address type
				$errorHelper = new ErrorHelper();
                $message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> User has tried to login with his own active banned limit! <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
				$errorHelper->serviceError($message, $message);
				return PLAYER_BANNED_LIMIT;
			}
			if($code == "20707"){
				//sends email when user tries to login more than allowed number of times
				$errorHelper = new ErrorHelper();
				$message = "PLAY_CORE.OPEN_TERMINAL_SESSION_MALTA(:p_user_name_in = {$username}, :p_password_in, :p_mac_address_in = {$mac_address}, :p_version_in = {$version}, :p_ip_address_in = {$ip_address}, :p_country_in = {$country}, :p_city_in = {$city}, :p_device_aff_id_in = {$device_aff_id}, :p_gp_mac_address_in = {$gp_mac_address}, :p_registred_aff_in = {$registred_aff}, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out) <br /> User has tried to login with wrong username or password more than allowed number of times! <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex2);
				$errorHelper->serviceError($message, $message);
				return LOGIN_TOO_MANY_TIMES;
			}
			return INTERNAL_ERROR;
		}
	}

	/** Closes game client session - closes terminal session */
    /**
     * @param $session_id
     * @throws Zend_Exception
     */
	public function closeTerminalSession($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$LOG_OUT_TERMINAL_SESSION(:p_session_id_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "PLAY_CORE.M\$LOG_OUT_TERMINAL_SESSION(:p_session_id_in={$session_id}) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceErrorLog($message);
		}
	}
	/** Open BO session, login into fictive bo session for game client */
    /**
     * @param $username
     * @param $password
     * @param $ip_address
     * @return int
     * @throws Zend_Exception
     */
	public function openBoSession($username, $password, $ip_address){
		//DEBUG THIS PART OF CODE - CREATE PLAYER VIA WEB SERVICE		
		/*
		$errorHelper = new ErrorHelper();
		$errorHelper->sendMail("MANAGMENT_CORE.M\$LOGIN_USER
		<br /> p_username_in: {$username}
		<br /> p_password_in: {$password}
		<br /> p_ip_address_in: {$ip_address}
		<br /> p_country_name_in: ''
		<br /> p_city_in: ''
		<br /> p_session_type_name_in: 'Back office'
		<br /> p_origin_in: 'GENUINE'");
		*/
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$config = Zend_Registry::get("config");
		$dbAdapter->beginTransaction();
		$result = -100000;
        $country = "";
        $city = "";
        $session_type_name_in = BACKOFFICE_SESSION;
        $origin_site = $config->origin_site;
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LOGIN_USER(:p_username_in, :p_password_in, :p_ip_address_in, :p_country_name_in, :p_city_in, :p_session_type_name_in, :p_origin_in, :p_session_out, :p_currency_out, :p_multi_currency_out, :p_auto_credit_increment_out, :p_auto_credit_increment_y_out, :p_subject_type_id_out, :p_subject_type_name_out, :p_subject_super_type_id_out, :p_subject_super_type_name_out, :p_session_type_id_out, :p_session_type_name_out, :p_first_name_out, :p_last_name_out, :p_last_time_collect_out, :p_online_casino_out)');
			$stmt->bindParam(":p_username_in", $username);
			$stmt->bindParam(":p_password_in", $password);
			$stmt->bindParam(":p_ip_address_in", $ip_address);
			$stmt->bindParam(":p_country_name_in", $country);
			$stmt->bindParam(":p_city_in", $city);
			$stmt->bindParam(":p_session_type_name_in", $session_type_name_in, SQLT_CHR, 255);
			$stmt->bindParam(":p_origin_in", $origin_site);
			$stmt->bindParam(":p_session_out", $result, SQLT_CHR, 255);
			$currency = "";
			$stmt->bindParam(":p_currency_out", $currency, SQLT_CHR, 10);
			$multi_currency = "";
			//logged user is multicurrency
			$stmt->bindParam(":p_multi_currency_out", $multi_currency, SQLT_CHR, 255);
			$auto_credit_increment = "-100000000000000000000";
			//enabled autoincrement credits amount
			$stmt->bindParam(":p_auto_credit_increment_out", $auto_credit_increment, SQLT_CHR, 255);
			$auto_credit_increment_y = NO;
			//if Y then autocredits is enabled if N then autocredits is disabled
			$stmt->bindParam(":p_auto_credit_increment_y_out", $auto_credit_increment_y, SQLT_CHR, 10);
			$subject_type_id = 0;
			//number of affiliate that is logging in
			$stmt->bindParam(":p_subject_type_id_out", $subject_type_id, SQLT_CHR, 255);
			$subject_type_name = "";
			//affiliates name that is logging in
			$stmt->bindParam(":p_subject_type_name_out", $subject_type_name, SQLT_CHR, 255);
			$session_type_id = 0;
			//number of parent affiliate that logged affiliate belongs to
			$subject_super_type_id = 0;
			$stmt->bindParam(":p_subject_super_type_id_out", $subject_super_type_id, SQLT_CHR, 255);
			//name of parent affiliate
			$subject_super_type_name= "";
			$stmt->bindParam(':p_subject_super_type_name_out', $subject_super_type_name, SQLT_CHR, 255);
			//session type number for logged in user
			$session_type_id = 0;
			$stmt->bindParam(":p_session_type_id_out", $session_type_id, SQLT_CHR, 255);
			//session type name from logged in user
			$session_type_name = "";
			$stmt->bindParam(":p_session_type_name_out", $session_type_name, SQLT_CHR, 255);
			$first_name = "";
			$stmt->bindParam(":p_first_name_out", $first_name, SQLT_CHR, 255);
			$last_name = "";
			$stmt->bindParam(":p_last_name_out", $last_name, SQLT_CHR, 255);
			$last_time_collect = "";
			$stmt->bindParam(":p_last_time_collect_out", $last_time_collect, SQLT_CHR, 255);
			$is_online_casino = "";
			$stmt->bindParam(":p_online_casino_out", $is_online_casino, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			if($result == -1 || $result == -2 || $result == -3 || $result == -4 || $result == -100000){
				//DEBUG THIS PART OF CODE
				$errorHelper = new ErrorHelper();
				$message = "AuthorizationModel::openBoSession($username, ,$ip_address) returns backoffice session: {$result}";
				$errorHelper->serviceError($message, $message);

				return 0;
			}else if($result >= 1){
				return $result;
			}else {
                $errorHelper = new ErrorHelper();
				$message = "AuthorizationModel::openBoSession($username, ,$ip_address) returns backoffice session: {$result}";
				$errorHelper->serviceError($message, $message);

                return 0;
            }
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "MANAGMENT_CORE.M\$LOGIN_USER(:p_username_in={$username}, :p_password_in=, :p_ip_address_in={$ip_address}, :p_country_name_in={$country}, :p_city_in={$city}, :p_session_type_name_in={$session_type_name_in}, :p_origin_in={$origin_site}, :p_session_out, :p_currency_out, :p_multi_currency_out, :p_auto_credit_increment_out, :p_auto_credit_increment_y_out, :p_subject_type_id_out, :p_subject_type_name_out, :p_subject_super_type_id_out, :p_subject_super_type_name_out, :p_session_type_id_out, :p_session_type_name_out, :p_first_name_out, :p_last_name_out, :p_last_time_collect_out, :p_online_casino_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return 0;
		}
	}

	/** Close BO session, logout from fictive bo session for game client */
    /**
     * @param $session_id
     * @throws Zend_Exception
     */
	public function closeBoSession($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{	
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CLOSE_SESSION(:p_session_id_in, :p_subject_id_in, :p_broken_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$subject_id = 0;
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$broken= YES;
			$stmt->bindParam(':p_broken_in', $broken, SQLT_CHR, 5);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();	
			$errorHelper = new ErrorHelper();
            $message = "MANAGMENT_CORE.M\$CLOSE_SESSION(:p_session_id_in={$session_id}, :p_subject_id_in=0, :p_broken_in) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}
	}

	/** get list of countries from database */
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public function listCountries($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$LIST_COUNTRIES(:p_session_id_in, :p_countries_list_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_countries_list_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return $cursor;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "MANAGMENT_CORE.M\$LIST_COUNTRIES(:p_session_id_in={$session_id}, :p_countries_list_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}		
	}

	/** validates affiliates name*/
    /**
     * @param $aff_name
     * @return mixed
     * @throws Zend_Exception
     */
	public function validateAffName($aff_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$VALIDATE_AFF_NAME(:p_aff_name_in, :p_currency_out, :p_aff_id_out)');
			$stmt->bindParam(':p_aff_name_in', $aff_name);
			$currency = "";
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
			$aff_id = "-100000000000000";
			$stmt->bindParam(':p_aff_id_out', $aff_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("currency" => $currency, "affiliate_id" => $aff_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "MANAGMENT_CORE.M\$VALIDATE_AFF_NAME(:p_aff_name_in = {$aff_name}, :p_currency_out, :p_aff_id_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}		
	}

    //checks active mobile session - FOR HTML5 apps
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
    public function checkActiveMobileSession($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.CHECK_ACTIVE_MOBILE_SESS(:p_session_id, :p_status)');
            $stmt->bindParam(':p_session_id', $session_id);
            $status_out = "";
            $stmt->bindParam(':p_status', $status_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "status_out"=>$status_out);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $errorHelper = new ErrorHelper();
            $message = "AuthorizationModel::checkActiveMobileSession <br /> MANAGMENT_CORE.CHECK_ACTIVE_MOBILE_SESS(p_session_id = {$session_id}, p_status) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "error_message"=>CursorToArrayHelper::getExceptionTraceAsString($ex));
        }
    }
}