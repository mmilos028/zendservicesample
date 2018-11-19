<?php
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'SubjectTypesModel.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class AuthorizationModel {
	//error constants
	public function __construct(){
	}

    /**
     * connect VLT and IO card
     * @param $pin_code
     * @param $serial_number
     * @return array
     * @throws Zend_Exception
     */
    public function connectVltAndIoCard($pin_code, $serial_number){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL VLT_MANAGEMENT.CONNECT_VLT_AND_IO_CARD(:p_pin_code_in, :p_io_serial_number, :p_recycler_status_out, :p_cr_status_out, :p_ca_status_out, :p_ba_status_out, :p_status_out)');
            $stmt->bindParam(':p_pin_code_in', $pin_code);
            $stmt->bindParam(':p_io_serial_number', $serial_number);
            $recycler_status_out = '';
            $stmt->bindParam(':p_recycler_status_out', $recycler_status_out, SQLT_CHR, 255);
            $cr_status_out = '';
            $stmt->bindParam(':p_cr_status_out', $cr_status_out, SQLT_CHR, 255);
            $ca_status_out = '';
            $stmt->bindParam(':p_ca_status_out', $ca_status_out, SQLT_CHR, 255);
            $ba_status_out = '';
            $stmt->bindParam(':p_ba_status_out', $ba_status_out, SQLT_CHR, 255);
            $status_out = '';
            $stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "recycler_status"=>$recycler_status_out, "cr_status"=>$cr_status_out, "ca_status"=>$ca_status_out, "ba_status"=>$ba_status_out,
                "status_out"=>$status_out);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $code = $ex->getCode();
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
     * enable terminal with pin code
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
				$message = "Wrong pin code was sent!!! Mac Address: {$mac_address}"; 
				$errorHelper->serviceErrorLog($message);
				return WRONG_PIN_CODE;
			}
			else{
				$errorHelper = new ErrorHelper();
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);
				return INTERNAL_ERROR;
			}
		}
	}

    /**
     * check if terminal's affiliate is banned
     * @param $mac_address
     * @param $gctype
     * @return array|null
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
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return null;
		}
	}

    /**
     * check panic status for terminal
     * @param $mac_address
     * @return null|string
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
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return null;
		}
	}

    /**
     * call for credit status update
     * session_id, amount, currency
     * Performes login to web lobby
     * @param $mac_address
     * @param $ip_address
     * @param $version
     * @return array|int|mixed|null
     * @throws Zend_Exception
     */
	public function loginWebLobby($mac_address, $ip_address, $version){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGE_WEB.M$LOGIN_WEB_LOBY(:p_mac_address_in, :p_ip_address, :p_version_in, :url_list_out, :p_duration_out, :p_price_out, :p_currency_out, :p_skin_out, :p_id_out, :p_credits_out, :p_show_hide_out, :p_button_position_out, :p_language_out, :p_panic_out, :p_terminal_type)');
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_ip_address', $ip_address);			
			$stmt->bindParam(':p_version_in', $version);			
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
            $terminal_type = "";
			$stmt->bindParam(':p_terminal_type', $terminal_type, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$dbAdapter->closeConnection();
			$cursor->free();
			return array(
                "cursor"=>$cursor,
                "duration"=>$duration_out,
                "price"=>$price_out,
                "currency"=>$currency_out,
                "skin"=>$skin_out,
                "started_session_id"=>$started_session_id,
                "credits"=>$credits_out,
                "show_hide"=>$show_hide,
                "button_position"=>$button_position,
                "language"=>$language,
                "panic_status"=>$panic_status,
                "terminal_type"=>$terminal_type
            );
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			if($code == "20173"){
				return $code;
			}
			else {
				$errorHelper = new ErrorHelper();
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);
				return null;
			}
		}
	}

    /**
     * Performes cashier login
     * @param $session_id
     * @param $access_code
     * @return array|string
     * @throws Zend_Exception
     */
	public function loginWebCashier($session_id, $access_code){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_CASH.M$LOGIN_CASHIER(:p_session_id_in, :p_access_code_in, :p_login_ok_out, :credits_out, :p_affiliate_name_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_access_code_in', $access_code);
			$login_ok = "-1000000000000";
			$stmt->bindParam(':p_login_ok_out', $login_ok, SQLT_CHR, 255);
			$credits_out = "-1000000000000";
			$stmt->bindParam(':credits_out', $credits_out, SQLT_CHR, 255);
			$affiliate_name = "";
			$stmt->bindParam(':p_affiliate_name_out', $affiliate_name, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>$login_ok, "credits_out"=>$credits_out, "affiliate_name"=>$affiliate_name);
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
			return INTERNAL_ERROR;
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex2);
			$errorHelper->serviceError($message, $message);
			return INTERNAL_ERROR;
		}		
	}

    /**
     * Performes cashier login
     * @param $session_id
     * @param $access_code
     * @param $mac_address
     * @param $general_purpose
     * @return array|null
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
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
			return null;
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex2);
			$errorHelper->serviceError($message, $message);
			return null;
		}
	}

    /**
     * Opens terminal session, login terminal into game client application
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
     * @return string
     * @throws Zend_Exception
     */
	public function openTerminalSession($username, $password, $mac_address, $version, $ip_address, $country = null, $city = null, $device_aff_id = null, $gp_mac_address = null, $registred_aff = null){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		/*
		$errorHelper = new ErrorHelper();
		$message = "GAME_SESSION.M\$OPEN_TERMINAL_SESSION (p_user_name_in = $username, p_password_in = $password, p_mac_address_in = $mac_address, p_version_in = $version, p_ip_address_in = $ip_address, p_device_aff_id_in = $device_aff_id,
			p_gp_mac_address_in = $gp_mac_address)";
		$errorHelper->sendMail($message);
		$errorHelper->serviceAccessLog($message);
		*/
		try{
			//$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$OPEN_TERMINAL_SESSION(:p_user_name_in, :p_password_in, :p_mac_address_in, :p_version_in, :p_ip_address_in, :p_device_aff_id_in, :p_gp_mac_address_in, :p_registred_aff_in, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out, :p_user_name_out)');
            $stmt = $dbAdapter->prepare('CALL GAME_SESSION.M$OPEN_TERMINAL_SESSION(:p_user_name_in, :p_password_in, :p_mac_address_in, :p_version_in, :p_ip_address_in, :p_device_aff_id_in, :p_gp_mac_address_in, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out, :p_user_name_out)');
			$stmt->bindParam(':p_user_name_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_version_in', $version);
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$stmt->bindParam(':p_device_aff_id_in', $device_aff_id);
			$stmt->bindParam(':p_gp_mac_address_in', $gp_mac_address);
			//$stmt->bindParam(':p_registred_aff_in', $registred_aff);
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
            $username_out = "";
            $stmt->bindParam(':p_user_name_out', $username_out, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("session_id"=>$session_id_out, "list_games"=>$cursor, "credits"=>$credits_out, "currency"=>$currency, "player_id"=>$player_id, "device"=>$device, "username"=>$username_out);
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
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
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex2);
				$errorHelper->serviceError($message, $message);
				return INTERNAL_ERROR;
			}
			/*if received exception is wrong username or password type*/
			if($code == "20121" || $code == "20454"){
				//sends email when user tries to login with wrong username password				
				$errorHelper = new ErrorHelper();
				$mail_message = "User has tried to login with wrong username or password! <br /> Username: {$username} <br /> Mac Address: {$mac_address} <br /> IP Address: {$ip_address} <br /> Country: {$country} <br /> City: {$city}";  
				$log_message = "User has tried to login with wrong username or password! Username: {$username} Mac Address: {$mac_address} IP Address: {$ip_address} Country: {$country} City: {$city}";
				$errorHelper->serviceErrorLog($log_message);
				return WRONG_USERNAME_PASSWORD;
			}
			/*if received exception is wrong physical address type*/
			if($code == "20122"){
				//sends email when user tries to login with wrong physical address type
				$errorHelper = new ErrorHelper();
				$mail_message = "User has tried to login with wrong physical address! <br /> Username: {$username} <br/> Mac Address: {$mac_address} <br /> IP Address: {$ip_address} <br /> Country: {$country} <br /> City: {$city}";
				$log_message = "User has tried to login with wrong physical address! Username: {$username} Mac Address: {$mac_address} IP Address: {$ip_address} Country: {$country} City: {$city}";
				$errorHelper->serviceError($mail_message, $log_message);
				return WRONG_PHYSICAL_ADDRESS;
			}
			if($code == "20500"){
				//sends email when user tries to login with wrong physical address type
				$errorHelper = new ErrorHelper();
				$mail_message = "User has tried to login with his own active banned limit! <br /> Username: {$username} <br /> Mac Address: {$mac_address} <br /> IP Address: {$ip_address} <br /> Country: {$country} <br /> City: {$city}";
				$log_message = "User has tried to login with his own active banned limit! Username: {$username} Mac Address: {$mac_address} IP Address: {$ip_address} Country: {$country} City: {$city}";
				$errorHelper->serviceError($mail_message, $log_message);
				return PLAYER_BANNED_LIMIT;
			}
			if($code == "20707"){
				//sends email when user tries to login more than allowed number of times
				$errorHelper = new ErrorHelper();
				$mail_message = "User has tried to login with wrong username or password more than allowed number of times! Username: {$username} Mac Address: {$mac_address} IP Address: {$ip_address} Country: {$country} City: {$city}";
				$log_message = "User has tried to login with wrong username or password more than allowed number of times! <br /> Username: {$username} <br /> Mac Address: {$mac_address} <br /> IP Address: {$ip_address} <br /> Country: {$country} <br /> City: {$city}";
				$errorHelper->serviceError($mail_message, $log_message);
				return LOGIN_TOO_MANY_TIMES;
			}
			return INTERNAL_ERROR;
		}
	}
	/** Closes game client session - closes terminal session */
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
			$errorHelper->serviceErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}
	/** Open BO session, login into fictive bo session for game client */
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
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LOGIN_USER(:p_username_in, :p_password_in, :p_ip_address_in, :p_country_name_in, :p_city_in, :p_session_type_name_in, :p_origin_in, :p_session_out, :p_currency_out, :p_multi_currency_out, :p_auto_credit_increment_out, :p_auto_credit_increment_y_out, :p_subject_type_id_out, :p_subject_type_name_out, :p_subject_super_type_id_out, :p_subject_super_type_name_out, :p_session_type_id_out, :p_session_type_name_out, :p_first_name_out, :p_last_name_out, :p_last_time_collect_out, :p_online_casino_out)');
			$stmt->bindParam(":p_username_in", $username);
			$stmt->bindParam(":p_password_in", $password);
			$stmt->bindParam(":p_ip_address_in", $ip_address);
			$country = "";
			$stmt->bindParam(":p_country_name_in", $country);
			$city = "";
			$stmt->bindParam(":p_city_in", $city);
			$modelSubjectTypes = new SubjectTypesModel();
			$session_type_name_in = BACKOFFICE_SESSION;
			//$session_type_name_in = $modelSubjectTypes->getSubjectType("MANAGMENT_TYPES.NAME_IN_BACK_OFFICE");
			$stmt->bindParam(":p_session_type_name_in", $session_type_name_in, SQLT_CHR, 255);
			$origin_site = $config->origin_site;
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
				/*
				$errorHelper = new ErrorHelper();
				$message = "AuthorizationModel - openBoSession - MANAGMENT_CORE.M_LOGIN_USER returns backoffice session: {$result}";
				$errorHelper->serviceError($message, $message);
				*/				
				return 0;
			}
			if($result >= 1){
				return $result;
			}else{
                return 0;
            }
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return 0;
		}		
	}
	/** Close BO session, logout from fictive bo session for game client */
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
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}
	}	
	/** get list of countries from database */
	public function listCountries($session_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_COUNTRIES(:p_session_id_in, :p_countries_list_out)');
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
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}		
	}
	/** validates affiliates name*/
	public function validateAffName($aff_name){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$VALIDATE_AFF_NAME(:p_aff_name_in, :p_currency_out, :p_aff_id_out)');
			$stmt->bindParam(':p_aff_name_in', $aff_name);
			$currency = "";
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 10);
			$aff_id = "-100000000000000";
			$stmt->bindParam(':p_aff_id_out', $aff_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array($currency, $aff_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}		
	}
		
	//member card login
	public function memberCardLogin($username, $password, $barcode, $ip_address, $version, $mac_address, $gp_mac_address, $device_aff_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$subject_id = "";		
		$session_id = "123456789";
		$credits = "123456789";
		$currency = "123456789";
		$player_id = "";
		$player_status = "";
		$device = "";
		$error = "123";
        $username_out = "";
        $affiliate_id_out = "";
		//DEBUG HERE
		//$errorHelper = new ErrorHelper();
		//$message = "PREPAID_CARDS.MEMBER_CARD_LOGIN(:p_username_in = {$username}, :p_password_in = {$password}, :p_prepaid_code = {$barcode}, :p_ip_address = {$ip_address}, :p_version_in = {$version}, :p_mac_address_in = {$mac_address}, :p_gp_mac_address_in = {$gp_mac_address}, :p_device_aff_id_in = {$device_aff_id}, :p_subject_id_created_out, :P_LIST_OF_GAMES_OUT, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_player_status_out, :p_device_out, :p_username_out, :p_aff_id_out, :ERROR_MESSAGES)";
		//$errorHelper->serviceAccess($message, $message);
		////
		try{
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.MEMBER_CARD_LOGIN(:p_username_in, :p_password_in, :p_prepaid_code, :p_ip_address, :p_version_in, :p_mac_address_in, :p_gp_mac_address_in, :p_device_aff_id_in, :p_subject_id_created_out, :P_LIST_OF_GAMES_OUT, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_player_status_out, :p_device_out, :p_username_out, :p_aff_id_out, :ERROR_MESSAGES)');
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_prepaid_code', $barcode);
			$stmt->bindParam(':p_ip_address', $ip_address);
			$stmt->bindParam(':p_version_in', $version);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_gp_mac_address_in', $gp_mac_address);
			$stmt->bindParam(':p_device_aff_id_in', $device_aff_id);
			$stmt->bindParam(':p_subject_id_created_out', $subject_id, SQLT_CHR, 255);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':P_LIST_OF_GAMES_OUT', $cursor);
			$stmt->bindParam(':p_session_id_out', $session_id, SQLT_CHR, 255);
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
			$stmt->bindParam(':p_player_id_out', $player_id, SQLT_CHR, 255);
			$stmt->bindParam(':p_player_status_out', $player_status, SQLT_CHR, 255);
			$stmt->bindParam(':p_device_out', $device, SQLT_CHR, 255);
            $stmt->bindParam(':p_username_out', $username_out, SQLT_CHR, 255);
            $stmt->bindParam(':p_aff_id_out', $affiliate_id_out, SQLT_CHR, 255);
			$stmt->bindParam(':ERROR_MESSAGES', $error, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG HERE
			//$errorHelper = new ErrorHelper();
			//$message = "PREPAID_CARDS.MEMBER_CARD_LOGIN(:p_username_in = '{$username}', :p_password_in = '{$password}', :p_prepaid_code = '{$barcode}', :p_ip_address = '{$ip_address}', :p_version_in = '{$version}', :p_mac_address_in = '{$mac_address}', :p_gp_mac_address_in = '{$gp_mac_address}', :p_device_aff_id_in = '{$device_aff_id}', :p_subject_id_created_out, :P_LIST_OF_GAMES_OUT, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_player_status_out, :p_device_out, :p_username_out, :ERROR_MESSAGES)";
			//$message .= "<br />RESPONSE: p_subject_id_created_out = '{$subject_id}' p_player_id_out = '{$player_id}' p_session_id_out = '{$session_id}' p_credits_out = '{$credits}' p_currency_out = '{$currency}' p_device_out = '{$device}' :ERROR_MESSAGES = '{$error}' p_player_status_out = '{$player_status}' p_username_out = '{$username_out}' p_aff_id_out = '{$affiliate_id_out}'";
			//$errorHelper->serviceAccess($message);
			////
			return array("status"=>OK, "subject_id"=>$subject_id, "player_id"=>$player_id, "session_id"=>$session_id, "credits"=>$credits,
                "currency"=>$currency, "device"=>$device, "message"=>$error, "list_games"=>$cursor, "subject_status"=>$player_status,
                "username"=>$username_out, "affiliate_id"=>$affiliate_id_out);
		}catch(Zend_Db_Cursor_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			if($ex->getMessage() == "Couldn't execute the cursor."){
				//will not send email to administrators if cursor from games could not be executed
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceErrorLog($message);
				return array("status"=>NOK, "subject_id"=>$subject_id, "player_id"=>$player_id, "session_id"=>$session_id, "credits"=>$credits,
                    "currency"=>$currency, "device"=>$device, "message"=>$error, "list_games"=>array(), "subject_status"=>$player_status,
                    "username"=>$username_out, "affiliate_id"=>$affiliate_id_out);
			}else{
				//if not a could not execute cursor error then send on email
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);
				return array("status"=>NOK, "subject_id"=>$subject_id, "player_id"=>$player_id, "session_id"=>$session_id, "credits"=>$credits,
                    "currency"=>$currency, "device"=>$device, "message"=>$error, "list_games"=>array(), "subject_status"=>$player_status,
                    "username"=>$username_out, "affiliate_id"=>$affiliate_id_out);
			}
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $code = $ex->getCode();
            if($code == "20311"){
                $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceError($message, $message);
                return array("status" => NOK, "message" => CASHIER_WRONG_MAC_ADDRESS_ERROR);
            }
            else if($code == "20312"){
                $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceError($message, $message);
                return array("status" => NOK, "message" => CARD_EXPIRED_ERROR);
            }
            else if($code == "20121"){
                $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceErrorLog($message);
                return array("status" => NOK, "message" => INTERNAL_ERROR);
            }
            else {
                $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceError($message, $message);
                return array("status" => NOK, "message" => INTERNAL_ERROR);
            }

		}
	}
	
	/** login fun games */
	public function loginFunGames($session_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL FUN_GAMES.FUN_LOGIN(:browser_session, :p_fun_pc_session_id_out, :p_credits, :p_list_of_games_out, :p_status_out)');
			$stmt->bindParam(':browser_session', $session_id);
			$status = "";
			$fun_pc_session_id = null;
			$credits = "";
			$cursor = array();
			$stmt->bindParam(':p_fun_pc_session_id_out', $fun_pc_session_id, SQLT_CHR, 255);
			$stmt->bindParam(':p_credits', $credits, SQLT_CHR, 255);
			$stmt->bindParam(':p_status_out', $status, SQLT_CHR, 255);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_of_games_out', $cursor);
			$stmt->execute();
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("session_id"=>$fun_pc_session_id, "list_games"=>$cursor, "credits"=>$credits, "status"=>$status);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "AuthorizationModel::loginFunGames <br /> FUN_GAMES.FUN_LOGIN(:browser_session = {$session_id}, :p_fun_pc_session_id_out, :p_credits, :p_list_of_games_out, :p_status_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceErrorLog($message);
			return array("status"=>NOK);
		}
	}
	
	/** logout fun games */
	public function logoutFunGames($session_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL FUN_GAMES.FUN_LOGOUT(:p_session_id_in, :p_status_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$status = "";
			$stmt->bindParam(':p_status_out', $status, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//return array("status"=>$status); 
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "AuthorizationModel::logoutFunGames <br /> FUN_GAMES.FUN_LOGOUT(:p_session_id_in = {$session_id}, :p_status_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceErrorLog($message);
			return array("status"=>NOK);
		}
	}
	
	public function loginBrowser($barcode, $ip_address){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.BROWSER_MEMBER_CARD_LOGIN(:barcode_in, :p_ip_address, :url_list_out, :p_duration_out, :p_price_out, :p_currency_out, :p_skin_out, :p_credits_out, :p_show_hide_out, :p_button_position_out, :p_language_out, :p_session_id_out, :browser_sess_inactive_limit, :error_messages_out)');
			$stmt->bindParam(':barcode_in', $barcode);
			$stmt->bindParam(':p_ip_address', $ip_address);
			$duration = "";
			$stmt->bindParam(':p_duration_out', $duration, SQLT_CHR, 255);
			$price = "";
			$stmt->bindParam(':p_price_out', $price, SQLT_CHR, 255);
			$currency = "";
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
			$skin = "";
			$stmt->bindParam(':p_skin_out', $skin, SQLT_CHR, 255);
			$credits = "";
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$show_hide = "";
			$stmt->bindParam(':p_show_hide_out', $show_hide, SQLT_CHR, 255);
			$button_position = "";
			$stmt->bindParam(':p_button_position_out', $button_position, SQLT_CHR, 255);
			$language = "";
			$stmt->bindParam(':p_language_out', $language, SQLT_CHR, 255);
			$session_id = "";
			$stmt->bindParam(':p_session_id_out', $session_id, SQLT_CHR, 255);
			$browser_sess_inactive_limit = "";
			$stmt->bindParam(':browser_sess_inactive_limit', $browser_sess_inactive_limit, SQLT_CHR, 255);
			$error_messages = "";
			$stmt->bindParam(':error_messages_out', $error_messages, SQLT_CHR, 255);
			$cursor = array();
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':url_list_out', $cursor);
			$stmt->execute();
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "url_list"=>$cursor, "credits"=>$credits, "duration"=>$duration, "price"=>$price, "currency"=>$currency, "skin"=>$skin, "show_hide"=>$show_hide, "button_position"=>$button_position, "language"=>$language, "session_id"=>$session_id, "browser_sess_inactive_limit"=>$browser_sess_inactive_limit, "message"=>$error_messages);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "AuthorizationModel::loginBrowser <br /> PREPAID_CARDS.BROWSER_MEMBER_CARD_LOGIN(:barcode_in = {$barcode}, :p_ip_address = {$ip_address}, :url_list_out, :p_duration_out, :p_price_out, :p_currency_out, :p_skin_out, :p_credits_out, :p_show_hide_out, :p_button_position_out, :p_language_out, :p_session_id_out, :browser_sess_inactive_limit, :error_messages_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceErrorLog($message);
			return array("status"=>NOK);
		}
	}
	
	public function logoutBrowser($session_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.LOGOUT_FROM_BROWSER(:p_session_id_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//return array("status"=>$status); 
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "AuthorizationModel::logoutBrowser <br /> PREPAID_CARDS.LOGOUT_FROM_BROWSER(:p_session_id_in = {$session_id}) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceErrorLog($message);
		}
	}
	
	public function openSessionByBrowser($session_id_in){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PREPAID_CARDS.OPEN_PC_SESS_BY_BROWSER_SESS(:p_session_id_in, :p_subject_id_created_out, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out, :error_messages_out)');
			$stmt->bindParam(':p_session_id_in', $session_id_in);
			$subject_id = "";
			$stmt->bindParam(':p_subject_id_created_out', $subject_id, SQLT_CHR, 255);
			$session_id = "";
			$stmt->bindParam(':p_session_id_out', $session_id, SQLT_CHR, 255);
			$credits = "";
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$currency = "";
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
			$player_id = "";
			$stmt->bindParam(':p_player_id_out', $player_id, SQLT_CHR, 255);
			$device_out = "";
			$stmt->bindParam(':p_device_out', $device_out, SQLT_CHR, 255);
			$error_messages = "";
			$stmt->bindParam(':error_messages_out', $error_messages, SQLT_CHR, 255);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_of_games_out', $cursor);
			$stmt->execute();
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "games"=>$cursor, "subject_id"=>$subject_id, "player_id"=>$player_id, "terminal_status"=>$device_out, "credits"=>$credits, "currency"=>$currency, "session"=>$session_id, "message"=>$error_messages);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "AuthorizationModel::openSessionByBrowser <br /> PREPAID_CARDS.OPEN_PC_SESS_BY_BROWSER_SESS(:p_session_id_in = {$session_id}, :p_subject_id_created_out, :p_list_of_games_out, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out, :error_messages_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceErrorLog($message);
			return array("status"=>NOK);
		}
	}
	
	//login player to game client with token for external integrations
	public function loginPlayerByToken($token, $is_mobile_platform = NO, $ip_address){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$session_id = "";
		$player_id = "";
		$username = "";
		$credits = "";
		$currency = "";
		try{
			$stmt = $dbAdapter->prepare('CALL EXTERNAL_INTEGRATION.LOGIN_PLAYER_BY_TOKEN(:p_token_in, :p_ip_address_in, :p_mobile_y_n, :p_session_id_out, :p_player_id_out, :p_player_username_out, :p_currency_out, :p_credits_out, :p_list_of_games_out, :p_error_message)');
			$stmt->bindParam(':p_token_in', $token);
			$stmt->bindParam(':p_ip_address_in', $ip_address);
            $stmt->bindParam(':p_mobile_y_n', $is_mobile_platform);
			$stmt->bindParam(':p_session_id_out', $session_id, SQLT_CHR, 255);
			$stmt->bindParam(':p_player_id_out', $player_id, SQLT_CHR, 255);
			$stmt->bindParam(':p_player_username_out', $username, SQLT_CHR, 255);
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_of_games_out', $cursor);
			$error_message = "";
			$stmt->bindParam(':p_error_message', $error_message, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG THIS HERE
			//$errorHelper = new ErrorHelper();
			//$message = "EXTERNAL_INTEGRATION.LOGIN_PLAYER_BY_TOKEN(:p_token_in = {$token}, :p_ip_address_in = {$ip_address}, :p_session_id_out = {$session_id}, :p_player_id_out = {$player_id}, :p_player_username_out = {$username}, :p_currency_out = {$currency}, :p_credits_out = {$credits}, :p_list_of_games_out, :p_error_message = {$error_message})";
			//$errorHelper->serviceError($message, $message);
			return array("status"=>OK, "token"=>$token, "ip_address"=>$ip_address, "mobile_platform"=>$is_mobile_platform, "player_id"=>$player_id, "session_id"=>$session_id, "username"=>$username, "credits"=>$credits, "currency"=>$currency, "message"=>$error_message, "list_games"=>$cursor);
		}catch(Zend_Db_Cursor_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			if($ex->getMessage() == "Couldn't execute the cursor."){
				//will not send email to administrators if cursor from games could not be executed
				$message = "AuthorizationModel::loginPlayerByToken <br /> EXTERNAL_INTEGRATION.LOGIN_PLAYER_BY_TOKEN(:p_token_in = {$token}, :p_ip_address_in = {$ip_address}, :p_mobile_platform = {$is_mobile_platform}, :p_session_id_out, :p_player_id_out, :p_player_username_out, :p_currency_out, :p_credits_out, :p_list_of_games_out, :p_error_message) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceErrorLog($message);
				return array("status"=>NOK, "player_id"=>$player_id, "session_id"=>$session_id, "credits"=>$credits, "currency"=>$currency, "message"=>$message, "list_games"=>array());
			}else{
				//if not a could not execute cursor error then send on email
				$message = "AuthorizationModel::loginPlayerByToken <br /> EXTERNAL_INTEGRATION.LOGIN_PLAYER_BY_TOKEN(:p_token_in = {$token}, :p_ip_address_in = {$ip_address}, :p_mobile_platform = {$is_mobile_platform}, :p_session_id_out, :p_player_id_out, :p_player_username_out, :p_currency_out, :p_credits_out, :p_list_of_games_out, :p_error_message) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);
				return array("status"=>NOK, "player_id"=>$player_id, "session_id"=>$session_id, "credits"=>$credits, "currency"=>$currency, "message"=>$message, "list_games"=>array());
			}
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "AuthorizationModel::loginPlayerByToken <br /> EXTERNAL_INTEGRATION.LOGIN_PLAYER_BY_TOKEN(:p_token_in = {$token}, :p_ip_address_in = {$ip_address}, :p_mobile_platform = {$is_mobile_platform}, :p_session_id_out, :p_player_id_out, :p_player_username_out, :p_currency_out, :p_credits_out, :p_list_of_games_out, :p_error_message) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>$message);
		}
	}

    //check affiliate pin code
    public function checkAffiliatePinCode($mac_address, $pin_code){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            /*
            MANAGE_WEB.AFFILIATE_PIN_CODE_CHECK( p_mac_address IN subjects.mac_address%type,
                                      p_pin_code    IN subjects.pin_code%type,
                                      p_status      OUT varchar2);

            */
            $stmt = $dbAdapter->prepare('CALL MANAGE_WEB.AFFILIATE_PIN_CODE_CHECK(:p_mac_address, :p_pin_code, :p_status)');
            $stmt->bindParam(':p_mac_address', $mac_address);
            $stmt->bindParam(':p_pin_code', $pin_code);
            $status = '';
            //No terminal with send mac address!
            //Pin code OK
            //Pin code NOT OK!
            $stmt->bindParam(':p_status', $status, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            //DEBUG HERE
			/*
            $message = "MANAGE_WEB.AFFILIATE_PIN_CODE_CHECK(p_mac_address = {$mac_address}, p_pin_code = {$pin_code}, p_status = {$status})";
            $errorHelper = new ErrorHelper();
            $errorHelper->serviceAccess($message, $message);
			*/
            return array("status"=>OK, "message"=>$status);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $errorHelper = new ErrorHelper();
            $message = "AuthorizationModel::checkAffiliatePinCode <br /> MANAGE_WEB.AFFILIATE_PIN_CODE_CHECK(:p_mac_address, :p_pin_code, :p_status) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>$message);
        }
    }

    //opens anonymous session for HTML5 app
    /**
     * @param $ip_address
     * @return mixed
     * @throws Zend_Exception
     */
    public function openAnonymousSession($ip_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL SITE_LOGIN.M$OPEN_ANONYMOUS_SESSION(:p_ip_address_in, :p_session_id_out)');
            $stmt->bindParam(':p_ip_address_in', $ip_address);
            $session_out = "0";
            $stmt->bindParam(':p_session_id_out', $session_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("session_id"=>$session_out);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $errorHelper = new ErrorHelper();
            $message = "AuthorizationModel::openAnonymousSession <br /> SITE_LOGIN.M\$OPEN_ANONYMOUS_SESSION(p_ip_address_in = {$ip_address}, p_session_id_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
        }
    }

    //checks active mobile session - FOR HTML5 apps
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

    //returns affiliate details
	public function getAffiliateDetails($backoffice_session_id, $affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SUBJECT_DETAIL(:p_session_id_in, :p_subject_id_in, :p_subject_detail_out)');
			$stmt->bindParam(':p_session_id_in', $backoffice_session_id);
			$stmt->bindParam(':p_subject_id_in', $affiliate_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_subject_detail_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cursor"=>$cursor->current());
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperError = new ErrorHelper();
			$helperError->serviceError($message, $message);
			return array("status"=>NOK, "error_message"=>$message);
		}
	}

    //set terminal for affiliate
	public function setTerminalForAffiliate($affiliate_name, $mac_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGE_SUBJECTS.set_terminal_for_affiliate(:p_affiliate_name_in, :p_mac_address_in, :p_status_out)');
			$stmt->bindParam(':p_affiliate_name_in', $affiliate_name);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
            $status_out = "";
            $stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "status_out"=>$status_out);
		}catch(Zend_Exception $ex){
            /*
             raise_application_error(-20300, 'Affiliate name is wrong.');
             raise_application_error(-20301, 'Mac address is already used for other affiliate.');
             raise_application_error(-20302, 'Mac address is already used.');
             raise_application_error(-20399, 'Unhandled exception');
             */
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $code = $ex->getCode();

            $errorHelper = new ErrorHelper();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            if($code != 20302) {
                $errorHelper->serviceError($message, $message);
            }else{
                $errorHelper->serviceErrorLog($message);
            }

            if($code == 20300){
                return array("status"=>NOK, "message"=>"Affiliate name is wrong", "code"=>20300);
            }else if($code == 20301){
                return array("status"=>NOK, "message"=>"Mac address is already used for other affiliate", "code"=>20301);
            }else if($code == 20302){
                return array("status"=>NOK, "message"=>"Mac address is already used", "code"=>20302);
            }else if($code == 20399){
                return array("status"=>NOK, "message"=>"Unhandled exception", "code"=>20399);
            }
            return array("status"=>NOK, "message"=>INTERNAL_ERROR);
		}
	}

    /**
     * check terminal date code from his affiliate
     * @param $mac_address
     * @return array
     * @throws Zend_Exception
     */
	public function checkTerminalDateCode($mac_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.CHECK_TERMINAL_DOB_CHECK(:p_mac_address, :p_date_result)');
			$stmt->bindParam(':p_mac_address', $mac_address);
            $date_code_out = "";
            $stmt->bindParam(':p_date_result', $date_code_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "mac_address"=>$mac_address, "date_code"=>$date_code_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $code = $ex->getCode();

            $errorHelper = new ErrorHelper();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            return array("status"=>NOK, "message"=>INTERNAL_ERROR);
		}
	}

	//list affiliates sites
	public function listAffiliatesSites($affiliate_id, $page_number = 1, $hits_per_page = 1000000, $order_by = 1, $sort_order = 1000000){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGE_WEB.M$LIST_AFF_SITES(:p_aff_id_in, :p_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_aff_sites_out)');
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$id = 0;
			$stmt->bindParam(':p_id_in', $id);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_aff_sites_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			return array("status"=>NOK, "cursor"=>array(0));
		}
	}
}