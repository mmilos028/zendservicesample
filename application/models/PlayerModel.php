<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class PlayerModel{
	public function __construct(){
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
			$errorHelper = new ErrorHelper();

			$stored_procedure_details = "
			CREATE PLAYER ON WEB SITE MANAGE_SUBJECTS.MANAGE_PLAYERS_ON_WEB_CHECK(
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
             )
			";

			$message = $stored_procedure_details . "<br /><br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
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
            return array("status"=>NOK, "Unknown Error!");

		}
	}
	
	//manages - update inser subjects - players into system with email verification - for malta web site old
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
     * @return array
     * @throws Zend_Exception
     */
	private function manageUserWChecks($session_id, $action, $aff_for, $username, $password, $subrole, $mac_address, $email, $country,
	$currency, $banned, $zip, $phone, $address, $birthday, $first_name, $last_name, $city, $subject_id, $multicurrency, $autoincrement,
	$game_payback, $key_exit, $enter_password, $street_address2 = null, $bank_account = null, $bank_country = null, $swift = null, 
	$iban = null, $receive_mail = null, $checks_list){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$config = Zend_Registry::get('config');
		$origin_site = $config->origin_site;
		//DEBUG THIS PART OF CODE - CREATE PLAYER VIA WEB SERVICE		
		/*
		$errorHelper = new ErrorHelper();
		$errorHelper->sendMail("MANAGE AFFILIATE_W_CHECKS 
		<br /> Session id: {$session_id} <br /> Action: {$action}
		<br /> Aff for: {$aff_for} <br /> Username: {$username}
		<br /> Password: {$password} <br /> Subrole: {$subrole}
		<br /> Mac address: {$mac_address} <br /> Email: {$email}
		<br /> Country: {$country} <br /> Currency: {$currency}
		<br /> Banned: {$banned} <br /> Zip: {$zip}
		<br /> Phone: {$phone} <br /> Address: {$address}
		<br /> Birthday: {$birthday} <br /> First name: {$first_name}
		<br /> Last name: {$last_name} <br /> City: {$city}
		<br /> Subject id: {$subject_id} <br /> Multicurrency: {$multicurrency}
		<br /> Autoincrement: {$autoincrement} <br /> Game payback: {$game_payback}
		<br /> Key exit: {$key_exit} <br /> Enter password: {$enter_password}
		<br /> Street address 2: {$street_address2} <br /> Bank account: {$bank_account}
		<br /> Bank country: {$bank_country} <br /> Swift: {$swift}
		<br /> Iban: {$iban} <br /> Receive mail: {$receive_mail}
		<br /> Checks list: {$checks_list} <br /> Origin site: {$origin_site}");
		*/
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$MANAGE_AFFILIATES_w_checks(:p_session_id_in, :p_action_in, :p_aff_for_in, :p_name_new_in, :p_password_in, :p_affiliates_type_in, :p_mac_address_in, :p_email_in, :p_country_in, :p_currency_in, :p_banned_in, :p_zip_code_in, :p_phone_in, :p_address_in, :p_birthday_in, :p_first_name_in, :p_last_name_in, :p_city_in, :p_subject_id_in, :p_multi_currency_in, :p_auto_credits_increment_in, :p_pay_back_perc, :p_key_exit_in, :p_enter_pass_in, :p_ADDRESS2_in, :p_BANK_ACCOUNT_in, :p_BANK_COUNTRY_in, :p_SWIFT_in, :p_IBAN_in, :p_send_mail_in, :p_inactive_time_in, :p_subject_id_dummy_out, :p_checks_list_in, :p_origin_in)');
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
			$subject_id_dummy = null;
			$stmt->bindParam(':p_subject_id_dummy_out', $subject_id_dummy, SQLT_CHR, 255);
			$stmt->bindParam(':p_checks_list_in', $checks_list);
			$stmt->bindParam(':p_origin_in', $origin_site);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>1, "player_id"=>$subject_id_dummy);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);			
			if($code == 20713) //ORA-20713: Such combination of first name, last name, and birthday already exists!
				return array("status"=>20713);
			if($code == 20714) //ORA-20714: Such email already exists!
				return array("status"=>20714);			
			if($code == 20715) //ORA-20715: Such combination of first name, last name, and address already exists!			
				return array("status"=>20715);
			if($code == 20721) //raise_application_error(-20721, 'Such combination of first name, last name, and phone already exists!');
				return array("status"=>20721);
			return array("status"=>0);
		}
	}

	//returns player type id in system
    /**
     * @param $session_id
     * @param $player_type_name
     * @return mixed
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
     * @param null $site_name
     * @param null $registred_affiliate
     * @return int
     * @throws Zend_Exception
     */
	public function manageUser($session_id, $action, $aff_for, $username, $password, $subrole, $mac_address, $email, $country,
	$currency, $banned, $zip, $phone, $address, $birthday, $first_name, $last_name, $city, $subject_id, $multicurrency, $autoincrement,
	$game_payback, $key_exit, $enter_password, $street_address2 = null, $bank_account = null, $bank_country = null, $swift = null, 
	$iban = null, $receive_mail = null, $site_name = null, $registred_affiliate = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$config = Zend_Registry::get('config');
		$origin_site = $config->origin_site;
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$MANAGE_AFFILIATES(:p_session_id_in, :p_action_in, :p_aff_for_in, :p_name_new_in, :p_password_in, :p_affiliates_type_in, :p_mac_address_in, :p_email_in, :p_country_in, :p_currency_in, :p_banned_in, :p_zip_code_in, :p_phone_in, :p_address_in, :p_birthday_in, :p_first_name_in, :p_last_name_in, :p_city_in, :p_subject_id_in, :p_multi_currency_in, :p_auto_credits_increment_in, :p_pay_back_perc, :p_key_exit_in, :p_enter_pass_in, :p_ADDRESS2_in, :p_BANK_ACCOUNT_in, :p_BANK_COUNTRY_in, :p_SWIFT_in, :p_IBAN_in, :p_send_mail_in, :p_inactive_time_in, :p_site_name_in, :p_registred_aff, :p_origin_in, :p_subject_id_dummy_out)');
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
			$stmt->bindParam(':p_site_name_in', $site_name);
			$stmt->bindParam(':p_registred_aff', $registred_affiliate);
			$stmt->bindParam(':p_origin_in', $origin_site);
			$subject_id_dummy = null;
			$stmt->bindParam(':p_subject_id_dummy_out', $subject_id_dummy, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return 1;
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
			return 0;
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex2);
			$errorHelper->serviceError($message, $message);
			return 0;
		}
	}
		
	//returns player details
    /**
     * @param $session_id
     * @param $player_id
     * @return array
     * @throws Zend_Exception
     */
	private function getPlayerDetails($session_id, $player_id){
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

    //validate if player name already exists in database
    /**
     * @param $player_name
     * @return mixed
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
			//$errorHelper->siteError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    //validate if player phone already exists in database
    /**
     * @param $player_id
     * @param $player_phone
     * @param $white_label_name
     * @return array
     * @throws Zend_Exception
     */
	public function validatePlayerPhone($player_id, $player_phone, $white_label_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.check_phone(:p_phone_in, :p_player_id_in, :p_wl_name_in, :p_phone_available_out)');
			$stmt->bindParam(':p_phone_in', $player_phone);
            $stmt->bindParam(':p_player_id_in', $player_id);
            $stmt->bindParam(':p_wl_name_in', $white_label_name);
			$phone_available = "";
			$stmt->bindParam(':p_phone_available_out', $phone_available, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "phone_available"=>$phone_available);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			//$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    //validate if player email already exists in database
    /**
     * @param $player_id
     * @param $player_email
     * @param $white_label_name
     * @return array
     * @throws Zend_Exception
     */
	public function validatePlayerEmail($player_id, $player_email, $white_label_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
            /*
            CS_USER_UTILITY.check_email(
            p_email_in     IN subjects.email%type,
            p_player_id_in IN subjects.id%type,
            p_email_available_out OUT managment_types.yes%type)
            */
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.check_email(:p_email_in, :p_player_id_in, :p_wl_name_in, :p_email_available_out)');
			$stmt->bindParam(':p_email_in', $player_email);
            $stmt->bindParam(':p_player_id_in', $player_id);
            $stmt->bindParam(':p_wl_name_in', $white_label_name);
			$email_available = "";
			$stmt->bindParam(':p_email_available_out', $email_available, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "email_available"=>$email_available);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			//$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    //check unique player first name, last name and birthday date combination for player
    /**
     * @param $player_id
     * @param $first_name
     * @param $last_name
     * @param $birthday
     * @param $white_label_name
     * @return array
     * @throws Zend_Exception
     */
    public function validatePlayerFirstNameLastNameBirthdate($player_id, $first_name, $last_name, $birthday, $white_label_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.check_fname_lname_birth(:p_first_name_in, :p_last_name_in, :p_birthday_in, :p_player_id_in, :p_wl_name_in, :p_combination_available_out)');
			$stmt->bindParam(':p_first_name_in', $first_name);
            $stmt->bindParam(':p_last_name_in', $last_name);
            $stmt->bindParam(':p_birthday_in', $birthday);
            $stmt->bindParam(':p_player_id_in', $player_id);
            $stmt->bindParam(':p_wl_name_in', $white_label_name);
			$combination_available = "";
			$stmt->bindParam(':p_combination_available_out', $combination_available, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "combination_available"=>$combination_available);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			//$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
    }

    //check unique player first name, last name and address combination for player
    /**
     * @param $player_id
     * @param $first_name
     * @param $last_name
     * @param $city
     * @param $address
     * @param $address2
     * @param $white_label_name
     * @return array
     * @throws Zend_Exception
     */
    public function validatePlayerFirstNameLastNameAddress($player_id, $first_name, $last_name, $city, $address, $address2, $white_label_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.check_fname_lname_addr(:p_first_name_in, :p_last_name_in, :p_city_in, :p_address_in, :p_address2_in, :p_player_id_in, :p_wl_name_in, :p_combination_available_out)');
			$stmt->bindParam(':p_first_name_in', $first_name);
            $stmt->bindParam(':p_last_name_in', $last_name);
            $stmt->bindParam(':p_city_in', $city);
            $stmt->bindParam(':p_address_in', $address);
            $stmt->bindParam(':p_address2_in', $address2);
            $stmt->bindParam(':p_player_id_in', $player_id);
            $stmt->bindParam(':p_wl_name_in', $white_label_name);
			$combination_available = "";
			$stmt->bindParam(':p_combination_available_out', $combination_available, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "combination_available"=>$combination_available);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			//$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
    }

    //check unique player first name, last name and address combination for player
    /**
     * @param $player_id
     * @param $first_name
     * @param $last_name
     * @param $phone
     * @param $white_label_name
     * @return array
     * @throws Zend_Exception
     */
    public function validatePlayerFirstNameLastNamePhone($player_id, $first_name, $last_name, $phone, $white_label_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.check_fname_lname_phone(:p_first_name_in, :p_last_name_in, :p_phone_in, :p_player_id_in, :p_wl_name_in, :p_combination_available_out)');
			$stmt->bindParam(':p_first_name_in', $first_name);
            $stmt->bindParam(':p_last_name_in', $last_name);
            $stmt->bindParam(':p_phone_in', $phone);
            $stmt->bindParam(':p_player_id_in', $player_id);
            $stmt->bindParam(':p_wl_name_in', $white_label_name);
			$combination_available = "";
			$stmt->bindParam(':p_combination_available_out', $combination_available, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "combination_available"=>$combination_available);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			//$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
    }

    //return player credits procedure
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
    public function getPlayerCredits($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$CREDIT_STATUS(:p_session_id_in, :p_credits_out, :p_status_out, :p_session_name_out, :p_session_closed_status)');
            //$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.credit_status(:p_session_id_in, :p_credits_out)');
            $stmt = $dbAdapter->prepare('CALL WEB_REPORTS.credit_status(:p_session_id_in, :p_credits_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
            $credits = '';
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "credits"=>$credits);
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