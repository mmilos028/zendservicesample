<?php
require_once 'Zend/Registry.php';
require_once  MODELS_DIR . DS . 'PlayerModel.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
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
	public function newAccount($session_id, $affiliate_id, $username, $password, $email, 
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
	    	unset($modelPlayer);
	    	return "1";
		}catch(Zend_Exception $ex){			
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return INTERNAL_ERROR;
		}
	}
	/**
	 * 
	 * validates if new player username exists in our database ...
	 * @param string $username
	 * @return mixed
	 */
	public function validatePlayer($username){
		try{
			if(!isset($username))
				return null;
			$username = strip_tags($username);
			$modelPlayer = new PlayerModel();
			$exist = $modelPlayer->validatePlayerName($username);
			unset($modelPlayer);
			return $exist;
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return INTERNAL_ERROR;
		}
	}
}