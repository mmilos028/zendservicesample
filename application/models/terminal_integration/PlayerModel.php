<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class PlayerModel{
	public function __construct(){
	}
	
	//validate if player name already exists in database
    /**
     * @param $player_name
     * @return string
     * @throws Zend_Exception
     */
	public function validatePlayerName($player_name){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$VALIDATE_PLAYER_NAME(:p_player_name_in, :p_exist_out)');
			$stmt->bindParam(':p_player_name_in', $player_name);
			$exist = "";
			$stmt->bindParam(':p_exist_out', $exist, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $exist;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}		
	}
	
	//returns player type id in system
    /**
     * @param $session_id
     * @param $player_type_name
     * @return string
     * @throws Zend_Exception
     */
	public function getPlayerTypeID($session_id, $player_type_name){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LIST_PLAYERS_ROLES(:p_session_id_in, :p_player_type_name_in, :p_player_type_ID)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_player_type_name_in', $player_type_name);
			$player_type_id = 0;
			$stmt->bindParam(':p_player_type_ID', $player_type_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $player_type_id;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}		
	}
	
	//manages - update or insert subjects - players into system
	public function manageUser($session_id, $action, $aff_for, $username, $password, $subrole, $mac_address, $email, $country, 
	$currency, $banned, $zip, $phone, $address, $birthday, $first_name, $last_name, $city, $subject_id, $multicurrency, $autoincrement,
	$game_payback, $key_exit, $enter_password, $street_address2 = null, $bank_account = null, $bank_country = null, $swift = null, 
	$iban = null, $receive_mail = null, $site_name = null, $registred_affiliate = null, $password_surf = null, $new_login_kills_sess = NO){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$config = Zend_Registry::get('config');
		$origin_site = $config->origin_site;
		$registred_affiliate = NO;
        /*
		require_once HELPERS_DIR . DS . 'ErrorMailHelper.php';
		$errorMailHelper = new ErrorMailHelper();
		$errorMailHelper->sendMail("MANAGMENT_CORE.M_DOLAR_MANAGE_AFFILIATES(p_session_id_in = $session_id, p_action_in = $action, p_aff_for_in = $aff_for,
			p_name_new_in = $username, p_password_in = $password, p_affiliates_type_in = $subrole,
			p_mac_address_in = $mac_address, p_email_in = $email, p_country_in = $country,
			p_currency_in = $currency, p_banned_in = $banned, p_zip_code_in = $zip,
			p_phone_in = $phone, p_address_in = $address, p_birthday_in = $birthday,
			p_first_name_in = $first_name, p_last_name_in = $last_name, p_city_in = $city,
			p_subject_id_in = $subject_id, p_multi_currency_in = $multicurrency,
			p_auto_credits_increment_in = $autoincrement, p_pay_back_perc = $game_payback,
			p_key_exit_in = $key_exit, p_enter_pass_in = $enter_password, p_ADDRESS2_in = $street_address2,
			p_BANK_ACCOUNT_in = $bank_account, p_BANK_COUNTRY_in = $bank_country,
			p_SWIFT_in = $swift, p_IBAN_in = $iban, p_send_mail_in = $receive_mail,
			p_inactive_time_in = , p_site_name_in = $site_name, p_registred_aff = $registred_affiliate, p_origin_in = $origin_site,
		    p_new_login_kills_sess = $new_login_kills_sess, p_subject_id_dummy_out = null)");
        */
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$MANAGE_AFFILIATES(:p_session_id_in, :p_action_in, :p_aff_for_in, :p_name_new_in, :p_password_in, :p_password_in_surf, :p_affiliates_type_in, :p_mac_address_in, :p_email_in, :p_country_in, :p_currency_in, :p_banned_in, :p_zip_code_in, :p_phone_in, :p_address_in, :p_birthday_in, :p_first_name_in, :p_last_name_in, :p_city_in, :p_subject_id_in, :p_multi_currency_in, :p_auto_credits_increment_in, :p_pay_back_perc, :p_key_exit_in, :p_enter_pass_in, :p_ADDRESS2_in, :p_BANK_ACCOUNT_in, :p_BANK_COUNTRY_in, :p_SWIFT_in, :p_IBAN_in, :p_send_mail_in, :p_inactive_time_in, :p_site_name_in, :p_registred_aff, :p_origin_in, :p_new_login_kills_sess, :p_subject_id_dummy_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_action_in', $action);
			$stmt->bindParam(':p_aff_for_in',$aff_for);
			$stmt->bindParam(':p_name_new_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_password_in_surf', $password_surf);
			$stmt->bindParam(':p_affiliates_type_in', $subrole);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_email_in', $email);
			$stmt->bindParam(':p_country_in', $country);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_banned_in', $banned);
			$stmt->bindParam(':p_zip_code_in', $zip);
			$stmt->bindParam(':p_phone_in', $phone);
			$stmt->bindParam(':p_address_in', $address);
			$stmt->bindParam(':p_birthday_in', $birthday);
			$stmt->bindParam(':p_first_name_in', $first_name);
			$stmt->bindParam(':p_last_name_in', $last_name);
			$stmt->bindParam(':p_city_in', $city);
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$stmt->bindParam(':p_multi_currency_in', $multicurrency);
			$stmt->bindParam(':p_auto_credits_increment_in', $autoincrement);
			$stmt->bindParam(':p_pay_back_perc', $game_payback);
			$stmt->bindParam(':p_key_exit_in', $key_exit);
			$stmt->bindParam(':p_enter_pass_in', $enter_password);
			$stmt->bindParam(':p_ADDRESS2_in', $street_address2);
			$stmt->bindParam(':p_BANK_ACCOUNT_in', $bank_account);
			$stmt->bindParam(':p_BANK_COUNTRY_in', $bank_country);
			$stmt->bindParam(':p_SWIFT_in', $swift);
			$stmt->bindParam(':p_IBAN_in', $iban);
			$stmt->bindParam(':p_send_mail_in', $receive_mail);
			$inactive_time = '';
			$stmt->bindParam(':p_inactive_time_in', $inactive_time);
			$stmt->bindParam(':p_site_name_in', $site_name);
			$stmt->bindParam(':p_registred_aff', $registred_affiliate);
			$stmt->bindParam(':p_origin_in', $origin_site);
            $stmt->bindParam(':p_new_login_kills_sess', $new_login_kills_sess);
			$subject_id_dummy = null;
			$stmt->bindParam(':p_subject_id_dummy_out', $subject_id_dummy, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=> OK);
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
			return array("status"=> NOK);
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex2);
			$errorHelper->serviceError($message, $message);
			return array("status"=> NOK);
		}
	}
		
	//returns player details
	public function getPlayerDetails($session_id, $player_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$SUBJECT_DETAIL(:p_session_id_in, :p_subject_id_in, :p_subject_detail_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $player_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_subject_detail_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return $cursor->current();	
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return false;
		}			
	}

    //changes player password
	public function resetPlayerPassword($bo_session_id, $player_id, $old_password, $new_password){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$RESET_PASSWORD(:p_session_id_in, :p_password_in, :p_subject_id_in, :p_password_new_in)');
			$stmt->bindParam(':p_session_id_in', $bo_session_id);
            $stmt->bindParam(':p_password_in', $old_password);
			$stmt->bindParam(':p_subject_id_in', $player_id);
			$stmt->bindParam(':p_password_new_in', $new_password);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            return array("status"=>OK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$helperErrorMail = new ErrorHelper();
			$helperErrorMail->serviceError($message, $message);
			return array("status"=>NOK);
		}
	}

	//manages - update insert subjects - players into system with email verification for MALTA web site NEW
    /**
     * @param $session_id
     * @param $action
     * @param $aff_for
     * @param $username
     * @param $password
     * @param $subrole
     * @param $mac_address
     * @param $email
     * @param $country
     * @param $currency
     * @param $banned
     * @param $zip
     * @param $phone
     * @param $address
     * @param $birthday
     * @param $first_name
     * @param $last_name
     * @param $city
     * @param $subject_id
     * @param $multicurrency
     * @param $autoincrement
     * @param $game_payback
     * @param $key_exit
     * @param $enter_password
     * @param null $street_address2
     * @param null $bank_account
     * @param null $bank_country
     * @param null $swift
     * @param null $iban
     * @param null $receive_mail
     * @param $checks_list
     * @param $registration_code
     * @param $tid_code
     * @param $language
     * @return array
     * @throws Zend_Exception
     */
	public function managePlayerOnWebSite($session_id, $action, $aff_for, $username, $password, $subrole, $mac_address, $email, $country,
	$currency, $banned, $zip, $phone, $address, $birthday, $first_name, $last_name, $city, $subject_id, $multicurrency, $autoincrement,
	$game_payback, $key_exit, $enter_password, $street_address2 = null, $bank_account = null, $bank_country = null, $swift = null,
	$iban = null, $receive_mail = null, $checks_list, $registration_code, $tid_code, $language){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$config = Zend_Registry::get('config');
		$origin_site = $config->origin_site;
		//DEBUG THIS PART OF CODE - CREATE PLAYER VIA WEB SERVICE
        /*
		$errorHelper = new ErrorHelper();
		$errorHelper->sendMail("CREATE PLAYER ON WEB SITE MANAGE_SUBJECTS.MANAGE_PLAYERS_ON_WEB_CHECK(
		<br /> (Session id) p_session_id_in = {$session_id}
		<br /> (Action) p_action_in = {$action}
		<br /> (Aff for) p_aff_for_in = {$aff_for}
		<br /> (Username) p_name_new_in = {$username}
		<br /> (Password) p_password_in = {$password}
		<br /> (Subrole) p_affiliates_type_in = {$subrole}
		<br /> (Mac address) p_mac_address_in = {$mac_address}
		<br /> (Email) p_email_in = {$email}
		<br /> (Country) p_country_in = {$country}
		<br /> (Currency) p_currency_in = {$currency}
		<br /> (Banned) p_banned_in = {$banned}
		<br /> (Zip) p_zip_code_in = {$zip}
		<br /> (Phone) p_phone_in = {$phone}
		<br /> (Address) p_address_in = {$address}
		<br /> (Birthday) p_birthday_in = {$birthday}
		<br /> (First name) p_first_name_in = {$first_name}
		<br /> (Last name) p_last_name_in = {$last_name}
		<br /> (City) p_city_in = {$city}
		<br /> (Subject id) p_subject_id_in = {$subject_id}
		<br /> (Multicurrency) p_multi_currency_in = {$multicurrency}
		<br /> (Autoincrement) p_auto_credits_increment_in = {$autoincrement}
		<br /> (Game payback) p_pay_back_per = {$game_payback}
		<br /> (Key exit) p_key_exit_in = {$key_exit}
		<br /> (Enter password) p_enter_pass_in = {$enter_password}
		<br /> (Street address 2) p_ADDRESS2_in = {$street_address2}
		<br /> (Bank account) p_BANK_ACCOUNT_in = {$bank_account}
		<br /> (Bank country) p_BANK_COUNTRY_in = {$bank_country}
		<br /> (Swift) p_SWIFT_in = {$swift}
		<br /> (Iban) p_IBAN_in = {$iban}
		<br /> (Receive mail) p_send_mail_in = {$receive_mail}
		<br /> (Inactive time) p_inactive_time_in =
		<br /> (Checks list) p_checks_list_in = {$checks_list}
		<br /> (Origin site) p_origin_in = {$origin_site}
		<br /> (Registration code) p_registration_code = {$registration_code}
		<br /> (Tid code) p_tid_in = {$tid_code}
		<br /> {Default language} p_default_language = {$language}
		 )");
        */
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGE_SUBJECTS.MANAGE_PLAYERS_ON_WEB_CHECK(:p_session_id_in, :p_action_in, :p_aff_for_in, :p_name_new_in, :p_password_in, :p_affiliates_type_in, :p_mac_address_in, :p_email_in, :p_country_in, :p_currency_in, :p_banned_in, :p_zip_code_in, :p_phone_in, :p_address_in, :p_birthday_in, :p_first_name_in, :p_last_name_in, :p_city_in, :p_subject_id_in, :p_multi_currency_in, :p_auto_credits_increment_in, :p_pay_back_perc, :p_key_exit_in, :p_enter_pass_in, :p_ADDRESS2_in, :p_BANK_ACCOUNT_in, :p_BANK_COUNTRY_in, :p_SWIFT_in, :p_IBAN_in, :p_send_mail_in, :p_inactive_time_in, :p_checks_list_in, :p_origin_in, :p_registration_code, :p_tid_in, :p_default_language, :p_subject_id_dummy_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_action_in', $action);
			$stmt->bindParam(':p_aff_for_in',$aff_for);
			$stmt->bindParam(':p_name_new_in', $username);
			$stmt->bindParam(':p_password_in',$password);
			$stmt->bindParam(':p_affiliates_type_in', $subrole);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_email_in', $email);
			$stmt->bindParam(':p_country_in', $country);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_banned_in', $banned);
			$stmt->bindParam(':p_zip_code_in', $zip);
			$stmt->bindParam(':p_phone_in', $phone);
			$stmt->bindParam(':p_address_in', $address);
			$stmt->bindParam(':p_birthday_in', $birthday);
			$stmt->bindParam(':p_first_name_in', $first_name);
			$stmt->bindParam(':p_last_name_in', $last_name);
			$stmt->bindParam(':p_city_in', $city);
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$stmt->bindParam(':p_multi_currency_in', $multicurrency);
			$stmt->bindParam(':p_auto_credits_increment_in', $autoincrement);
			$stmt->bindParam(':p_pay_back_perc', $game_payback);
			$stmt->bindParam(':p_key_exit_in', $key_exit);
			$stmt->bindParam(':p_enter_pass_in', $enter_password);
			$stmt->bindParam(':p_ADDRESS2_in', $street_address2);
			$stmt->bindParam(':p_BANK_ACCOUNT_in', $bank_account);
			$stmt->bindParam(':p_BANK_COUNTRY_in', $bank_country);
			$stmt->bindParam(':p_SWIFT_in', $swift);
			$stmt->bindParam(':p_IBAN_in', $iban);
			$stmt->bindParam(':p_send_mail_in', $receive_mail);
			$inactive_time = '';
			$stmt->bindParam(':p_inactive_time_in', $inactive_time);
			$stmt->bindParam(':p_checks_list_in', $checks_list);
			$stmt->bindParam(':p_origin_in', $origin_site);
			$stmt->bindParam(':p_registration_code', $registration_code);
            $stmt->bindParam(':p_tid_in', $tid_code);
            $stmt->bindParam(':p_default_language', $language);
            $subject_id_dummy = null;
			$stmt->bindParam(':p_subject_id_dummy_out', $subject_id_dummy, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            //DEBUG THIS PART OF CODE - CREATE PLAYER VIA WEB SERVICE
            /*
            $errorHelper = new ErrorHelper();
            $errorHelper->sendMail("CREATE PLAYER ON WEB SITE MANAGE_SUBJECTS.MANAGE_PLAYERS_ON_WEB_CHECK(
            <br /> (Session id) p_session_id_in = {$session_id}
            <br /> (Action) p_action_in = {$action}
            <br /> (Aff for) p_aff_for_in = {$aff_for}
            <br /> (Username) p_name_new_in = {$username}
            <br /> (Password) p_password_in = {$password}
            <br /> (Subrole) p_affiliates_type_in = {$subrole}
            <br /> (Mac address) p_mac_address_in = {$mac_address}
            <br /> (Email) p_email_in = {$email}
            <br /> (Country) p_country_in = {$country}
            <br /> (Currency) p_currency_in = {$currency}
            <br /> (Banned) p_banned_in = {$banned}
            <br /> (Zip) p_zip_code_in = {$zip}
            <br /> (Phone) p_phone_in = {$phone}
            <br /> (Address) p_address_in = {$address}
            <br /> (Birthday) p_birthday_in = {$birthday}
            <br /> (First name) p_first_name_in = {$first_name}
            <br /> (Last name) p_last_name_in = {$last_name}
            <br /> (City) p_city_in = {$city}
            <br /> (Subject id) p_subject_id_in = {$subject_id}
            <br /> (Multicurrency) p_multi_currency_in = {$multicurrency}
            <br /> (Autoincrement) p_auto_credits_increment_in = {$autoincrement}
            <br /> (Game payback) p_pay_back_per = {$game_payback}
            <br /> (Key exit) p_key_exit_in = {$key_exit}
            <br /> (Enter password) p_enter_pass_in = {$enter_password}
            <br /> (Street address 2) p_ADDRESS2_in = {$street_address2}
            <br /> (Bank account) p_BANK_ACCOUNT_in = {$bank_account}
            <br /> (Bank country) p_BANK_COUNTRY_in = {$bank_country}
            <br /> (Swift) p_SWIFT_in = {$swift}
            <br /> (Iban) p_IBAN_in = {$iban}
            <br /> (Receive mail) p_send_mail_in = {$receive_mail}
            <br /> (Inactive time) p_inactive_time_in =
            <br /> (Checks list) p_checks_list_in = {$checks_list}
            <br /> (Origin site) p_origin_in = {$origin_site}
            <br /> (Registration code) p_registration_code = {$registration_code}
            <br /> (Tid code) p_tid_in = {$tid_code}
            <br /> {Default language} p_default_language = {$language}
            <br /> {Player ID} p_subject_id_dummy_out = {$subject_id_dummy}
             )");
            */
			return array("status"=>OK, "player_id"=>$subject_id_dummy);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			if($code == 20713) //ORA-20713: Such combination of first name, last name, and birthday already exists!
				return array("status"=>NOK, "error_code"=>20713, "message"=>"Such combination of first name, last name, and birthday already exists!");
			if($code == 20714) //ORA-20714: Such email already exists!
				return array("status"=>NOK, "error_code"=>20714, "message"=>"Such email already exists!");
			if($code == 20715) //ORA-20715: Such combination of first name, last name, and address already exists!
				return array("status"=>NOK, "error_code"=>20715, "message"=>"Such combination of first name, last name, and address already exists!");
			if($code == 20721) //raise_application_error(-20721, 'Such combination of first name, last name, and phone already exists!');
				return array("status"=>NOK, "error_code"=>20721, "message"=>"Such combination of first name, last name, and phone already exists!");
			if($code == 20729) //raise_application_error(-20729, 'No such affiliate under '||p_white_label_name||' WL!');
				return array("status"=>NOK, "error_code"=>20729, "message"=>"No such affiliate under WL!");
            $errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "Unknown Error!");

		}
	}

	//returns player details - new only for malta system
    /**
     * @param $session_id
     * @param $player_id
     * @return array
     * @throws Zend_Exception
     */
	public function getPlayerDetailsMalta($session_id, $player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.SUBJECT_DETAILS_NEW(:p_session_id_in, :p_subject_id_in, :p_bonus_status_out, :p_subject_detail_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $player_id);
			$bonus_status = '';
			$stmt->bindParam(':p_bonus_status_out', $bonus_status, SQLT_CHR, 255);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_subject_detail_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "bonus_status"=>$bonus_status, "details"=>$cursor->current());
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>$message);
		}
	}

}