<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';

/**
 *
 * Web site web service PLAYER ACCOUNT...
 *
 */

class WebSitePlayerAccountManager {
	/**
	 * returns false if client is not using allowed ip address
	 */
	private function isSecureConnection(){
		$config = Zend_Registry::get('config');
		//will check site ip address caller if true
		if($config->checkSiteIpAddress == "true"){
			$ip_addresses = explode(' ', $config->siteIpAddress);
			$host_ip_address = IPHelper::getRealIPAddress();
			$status = in_array($host_ip_address, $ip_addresses);
			if(!$status){
				$errorHelper = new ErrorHelper();
				$message = "WebSitePlayerAccountManager service: Host with blacklisted ip address {$host_ip_address} is trying to connect to web site web service.";
				$errorHelper->siteError($message, $message);
			}
			return $status;
		} else{
			return true;
		}
	}

	/**
	* currency list for new player method by using affiliate_id
	* @param int $affiliate_id
    * @param string $tid_code
	* @return mixed
	*/
	public function currencyListForNewPlayer($affiliate_id, $tid_code = ""){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(!isset($affiliate_id) || $affiliate_id == 0){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		$affiliate_id = strip_tags($affiliate_id);
        $tid_code = strip_tags($tid_code);
		try{
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$result = $modelWebSite->currencyListNewPlayer($affiliate_id, $tid_code);
			unset($modelWebSite);
			if($result['status'] == NOK){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			$currency_list = array();
			if($result['status'] == OK){
				foreach($result['result'] as $res){
					$currency_list[] = array(
						'currency' => $res['ics'] . ' - ' . $res['description'],
						'id' => $res['id']
					);
				}
				return array("status"=>OK, "result"=>$currency_list);
			}else{
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "Error in receiving currency list for new player from web site. <br /> Affiliate id: {$affiliate_id} <br /> Currency list for new player on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

	/**
	 *
	 * insert new player method new with fixed response...
	 * @param int $affiliate_id
	 * @param string $username
	 * @param string $password
	 * @param string $email
	 * @param string $first_name
	 * @param string $last_name
	 * @param string $birthday
	 * @param int $country
	 * @param string $zip
	 * @param string $city
	 * @param string $street_address1
	 * @param string $street_address2
	 * @param string $phone
	 * @param string $bank_account
	 * @param int $bank_country
	 * @param string $swift
	 * @param string $iban
	 * @param int $receive_email
	 * @param string $currency
	 * @param string $ip_address
	 * @param string $registration_code
     * @param string $tid_code
     * @param string $language
	 * @return mixed
	 */
	public function insertPlayer($affiliate_id, $username, $password, $email, $first_name, $last_name,
	$birthday, $country, $zip, $city, $street_address1, $street_address2 = '', $phone,
	$bank_account, $bank_country, $swift, $iban, $receive_email = 1, $currency, $ip_address, $registration_code, $tid_code = '', $language = 'en_GB'){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NOK_SSL_CONNECTION);
		}
		if(!isset($username) || !isset($password) || !isset($email) || !isset($first_name) ||
		!isset($last_name) || !isset($birthday) || !isset($country) || !isset($zip) ||
		!isset($city) || !isset($street_address1) || !isset($phone) || !isset($currency) || !isset($ip_address)){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		$affiliate_id = strip_tags($affiliate_id);
		$username = strip_tags($username);
		$password = strip_tags($password);
		$email = strip_tags($email);
		$first_name = strip_tags($first_name);
		$last_name = strip_tags($last_name);
		$birthday = strip_tags($birthday);
		$country = strip_tags($country);
		$zip = strip_tags($zip);
		$city = strip_tags($city);
		$street_address1 = strip_tags($street_address1);
		$street_address2 = strip_tags($street_address2);
		$phone = strip_tags($phone);
		$bank_account = strip_tags($bank_account);
		$bank_country = strip_tags($bank_country);
		$swift = strip_tags($swift);
		$iban = strip_tags($iban);
		$receive_email = strip_tags($receive_email);
		$currency = strip_tags($currency);
		$ip_address = strip_tags($ip_address);
		$registration_code = strip_tags($registration_code);
        $tid_code = strip_tags($tid_code);
        $language = strip_tags($language);
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
        $res = array();
		try{
            require_once MODELS_DIR . DS . 'AuthorizationModel.php';
            $modelAuthorization = new AuthorizationModel();
            $hashed_password = md5(md5(FICTIVE_BO_PASSWORD));
            $bo_session_id = $modelAuthorization->openBoSession(FICTIVE_BO_USERNAME, $hashed_password, $ip_address);
            if($bo_session_id == 0){
                return array("status"=>NOK, "message"=>NOK_INTERNAL_ERROR, "details"=>"Invalid BO Session");
            }
            require_once MODELS_DIR . DS . 'PlayerModel.php';
            $modelPlayer = new PlayerModel();
            //returns false if not successfully or true if successfull inserted player
            $player_type = $modelPlayer->getPlayerTypeID(null, ROLA_PL_PC_PLAYER_INTERNET);
            $password = md5(md5($password));
            $action = INSERT;
            $aff_for = $affiliate_id;
            $subrole = $player_type;
            $mac_address = null;
            $banned = NO; //player is not banned
            $subject_id = null;
            $multicurrency = null;
            $autoincrement = null;
            $game_payback = null;
            $key_exit = null;
            $enter_password = null;
            if(strlen($language) == 0){
                $language = "en_GB";
            }
			//DEBUG BEFORE PLAYER REGISTRATION TO DATABASE
            /*
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("WebSitePlayerAccountManager::insertPlayer CALL FOR MANAGE_SUBJECTS.MANAGE_PLAYERS_ON_WEB_CHECK
			<br /> Session id: {$bo_session_id} <br /> Action: {$action}
			<br /> Aff for: {$aff_for} <br /> Username: {$username}
			<br /> Password: {$password} <br /> Subrole: {$subrole}
			<br /> Mac address: {$mac_address} <br /> Email: {$email}
			<br /> Country: {$country} <br /> Currency: {$currency}
			<br /> Banned: {$banned} <br /> Zip: {$zip}
			<br /> Phone: {$phone} <br /> Address: {$street_address1}
			<br /> Birthday: {$birthday} <br /> First name: {$first_name}
			<br /> Last name: {$last_name} <br /> City: {$city}
			<br /> Subject id: {$subject_id} <br /> Multicurrency: {$multicurrency}
			<br /> Autoincrement: {$autoincrement} <br /> Game payback: {$game_payback}
			<br /> Key exit: {$key_exit} <br /> Enter password: {$enter_password}
			<br /> Street address 2: {$street_address2} <br /> Bank account: {$bank_account}
			<br /> Bank country: {$bank_country} <br /> Swift: {$swift}
			<br /> Iban: {$iban} <br /> Receive mail: {$receive_email}
			<br /> Checks list: 'ALL' <br />
			<br /> Registration code: {$registration_code}
			<br /> Tid code: {$tid_code}
			<br /> Lanugage: {$language} ");
            */
			$res = $modelPlayer->managePlayerOnWebSite($bo_session_id, $action, $aff_for, $username,
				$password, $subrole, $mac_address, $email,
				$country, $currency, $banned, $zip, $phone,
				$street_address1, $birthday, $first_name,
				$last_name, $city, $subject_id, $multicurrency, $autoincrement,	$game_payback, $key_exit,
				$enter_password, $street_address2, $bank_account, $bank_country,
				$swift, $iban, $receive_email, 'ALL', $registration_code, $tid_code, $language);

            //if($res['status'] != OK && isset($res['player_id'])){
            if($res['status'] != OK){
                /*
                $errorHelper = new ErrorHelper();
                $message = "WebSitePlayerAccountManager::insertPlayer Result = {$res['status']}";
			    $errorHelper->serviceError($message, $message);
                */
                $modelAuthorization->closeBoSession($bo_session_id);
                if ($res['error_code'] == 20713) {
                    return array("status" => NOK, "message" => NOK_WRONG_FNAME_LNAME_BIRTHDAY);
                } else if ($res['error_code'] == 20714) {
                    return array("status" => NOK, "message" => NOK_WRONG_MAIL);
                } else if ($res['error_code'] == 20715) {
                    return array("status" => NOK, "message" => NOK_WRONG_FNAME_LNAME_ADDRESS);
                } else if ($res['error_code'] == 20721) {
                    return array("status" => NOK, "message" => NOK_WRONG_FNAME_LNAME_PHONE);
                } else if ($res['error_code'] == 20729) {
                    return array("status" => NOK, "message" => NOK_INVALID_REGISTRATION_CODE);
                } else {
                    return array("status" => NOK, "message" => NOK_INTERNAL_ERROR, "details" => "Unknown error occurred");
                }
            }

            if($res['status'] == OK && isset($res['player_id'])){
                require_once MODELS_DIR . DS . 'DocumentManagmentModel.php';
			    $modelDocumentManagment = new DocumentManagmentModel();
                $modelDocumentManagment->setPlayerVerificationStatus($bo_session_id, $res['player_id'], NOT_VERIFIED);
                //$modelDocumentManagment->setPlayerVerificationStatus($bo_session_id, $res['player_id'], KNOW_YOUR_CUSTOMER);
                //$modelDocumentManagment->setPlayerVerificationStatus($bo_session_id, $res['player_id'], EMAIL_VERIFIED);
                //DEBUG THIS PART OF CODE
                /*
                $errorHelper = new ErrorHelper();
                $errorHelper->sendMail("WebSitePlayerAccountManager::insertPlayer Set Player Verification Status <br />Bo session id: {$bo_session_id} <br />Player ID: {$res['player_id']}");
                */
                $modelAuthorization->closeBoSession($bo_session_id);
                require_once MODELS_DIR . DS . 'WebSiteModel.php';
                $modelWebSite = new WebSiteModel();
                $validationHash = $modelWebSite->getPlayerValidationHash($res['player_id']) . '_' . $res['player_id'];
                if($validationHash != null){
                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $site_settings = $modelMerchant->findSiteSettings($res['player_id']);
                    if($site_settings['status'] != OK){
                        return array("status"=>NOK, "message"=>NOK_INTERNAL_ERROR, "details"=>"Error finding player site settings");
                    }
                    //procedure to send mail to player after his activation
                    $playerMailSendFrom = $site_settings['mail_address_from'];
                    $playerMailAddress = $email;
                    $playerSmtpServer = $site_settings['smtp_server_ip'];
                    $casinoName = $site_settings['casino_name'];
                    $siteImagesLocation = $site_settings['site_image_location'];
                    $siteLink = $site_settings['site_link'];
                    $activationLink = $site_settings['player_activation_link'];
                    $playerActivationLink = $activationLink . '?activation_key=' . $validationHash;
                    $playerName = $first_name . " " . $last_name;
                    $playerUsername = $username;
                    $supportLink = $site_settings['support_url_link'];
                    $termsLink = $site_settings['terms_url_link'];
                    $contactLink = $site_settings['contact_url_link'];
                    $languageSettings = $site_settings['language_settings'];
                    $playerMailRes = WebSiteEmailHelper::getActivationEmailToPlayerContent($playerName, $playerUsername,
                    $siteImagesLocation, $casinoName, $siteLink, $playerActivationLink, $supportLink, $termsLink, $contactLink, $languageSettings);
                    $title = $playerMailRes['mail_title'];
                    $content = $playerMailRes['mail_message'];
                    $loggerMessage = "WebSitePlayerAccountManager::insertPlayer. Player with player username: {$playerUsername} on mail address: {$playerMailAddress}
                    has not received mail that his account is activated.";
                    WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
                    $playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
                    return array("status"=>OK);
                }else{
                    return array("status"=>NOK, "message"=>NOK_INTERNAL_ERROR, "details"=>"Error receiving inserted player validation hash for his activation mail");
                }
            }else{
                return array("status"=>NOK, "message"=>NOK_INTERNAL_ERROR, "details"=>"Error receiving inserted player validation hash for his activation mail");
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSitePlayerAccountManager::insertPlayer. Error while creating new player in web site. <br /> Player username: {$username} <br /> Player email adddress: {$email} <br /> Player registration on web site exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_INTERNAL_ERROR, "details"=>"Error inserting player or seting player verification status");
		}
	}

	/**
	 *
	 * player details method
	 * @param int $session_id
	 * @param string $ip_address
	 * @return mixed
	 */
	public function playerDetails($session_id, $ip_address){
		if(!$this->isSecureConnection()){
			return NON_SSL_CONNECTION;
		}
		if(!isset($session_id) || !isset($ip_address)){
			return false;
		}
		try{
			$session_id = strip_tags($session_id);
			$ip_address = strip_tags($ip_address);
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($session_id);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($session_id, $arrPlayer['player_id']);
			if($playerDetails['status'] != OK){
				return false;
			}
			$details = $playerDetails['details'];
			$bonus_status = $playerDetails['bonus_status'];
			return array(
                "user_name"=>$details['user_name'],
                "first_name"=>StringHelper::filterString($details['first_name']),
                "last_name"=>StringHelper::filterString($details['last_name']),
                "email"=>$details['email'],
                "birthday"=>$details['birthday'],
			    "country_name"=>StringHelper::filterCountry($details['country_name']),
                "country_id"=>$details['country_id'],
                "city"=>StringHelper::filterString($details['city']),
                "zip_code"=>$details['zip_code'],
                "address"=>StringHelper::filterString($details['address']),
                "phone"=>$details['phone'],
                "rola"=>$details['rola'],
			    "super_rola"=>$details['super_rola'],
                "path"=>$details['path'],
                "banned"=>$details['banned'],
                "currency"=>$details['currency'],
                "start_time"=>$details['start_time'],
			    "ip_address"=>$details['ip_address'],
                "address2"=>StringHelper::filterString($details['address2']),
                "bank_account"=>$details['bank_account'],
                "bank_country"=>$details['bank_country'],
                "swift"=>$details['swift'],
                "iban"=>$details['iban'],
                "receive_mail"=>$details['send_mail'],
			    "credit_status"=>$details['credit_status'],
                "total_credits"=>$details['credit_status'],
                "credits"=>$details['credits'],
                "free_credits"=>$details['credits'],
                "credits_restricted"=>$details['credits_restricted'],
                "bonus_restricted"=>$details['bonus_restricted'],
			    "bonus_win_restricted"=>$details['bonus_win_restricted'],
                "promotion"=>$details['promotion'],
                "bonus_status"=>$bonus_status,
                "language"=>$details['bo_default_language'],
                "bank_country_name" => $details['bank_country_name'],
			);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSitePlayerAccountManager::playerDetails. Error while getting player details for web site. <br /> Session id: {$session_id} <br /> IP address: {$ip_address} <br /> Player details on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return false;
		}
	}

	/**
	 *
	 * update player details ...
	 * @param int $session_id
	 * @param string $email
	 * @param string $first_name
	 * @param string $last_name
	 * @param string $birthday
	 * @param int $country
	 * @param string $zip
	 * @param string $city
	 * @param string $street_address1
	 * @param string $street_address2
	 * @param string $phone_number
	 * @param string $bank_account
	 * @param int $bank_country
	 * @param string $swift
	 * @param string $iban
	 * @param int $receive_email
	 * @param string $ip_address
     * @param string $language
	 * @return mixed
	 */
	public function updatePlayer($session_id, $email = "", $first_name = "", $last_name = "",
	$birthday, $country, $zip = "", $city = "", $street_address1 = "", $street_address2 = "",
	$phone_number = "", $bank_account, $bank_country, $swift, $iban, $receive_email = 1, $ip_address, $language = "en_GB"){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NOK_SSL_CONNECTION);
		}
		$session_id = intval(strip_tags($session_id));
		$email = strip_tags($email);
		$first_name = strip_tags($first_name);
		$last_name = strip_tags($last_name);
		$birthday = strip_tags($birthday);
		$country = strip_tags($country);
		$zip = strip_tags($zip);
		$city = strip_tags($city);
		$street_address1 = strip_tags($street_address1);
		$street_address2 = strip_tags($street_address2);
		$phone_number = strip_tags($phone_number);
		$bank_account = strip_tags($bank_account);
		$bank_country = strip_tags($bank_country);
		$swift = strip_tags($swift);
		$iban = strip_tags($iban);
		$receive_email = strip_tags($receive_email);
		$ip_address = strip_tags($ip_address);
        $language = strip_tags($language);
		try{
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once MODELS_DIR . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$hashed_password = md5(md5(FICTIVE_BO_PASSWORD));
			$bo_session_id = $modelAuthorization->openBoSession(FICTIVE_BO_USERNAME , $hashed_password, $ip_address);
			if($bo_session_id == 0){
				return array("status"=>NOK, "message"=>NOK_INTERNAL_ERROR, "details"=>"Invalid BO Session");
			}
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($session_id);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$subrole = $modelPlayer->getPlayerTypeID(null, ROLA_PL_PC_PLAYER_INTERNET);
			$subject_id = $arrPlayer['player_id'];
			$action = UPDATE;
			$aff_for = null;
			$username = null;
			$password = null;
			$mac_address = null;
			$currency = null;
			$banned = NO;
			$multicurrency = null;
			$autoincrement = null;
			$game_payback = null;
			$key_exit = null;
			$enter_password = null;
            $registration_code = null;
            $tid_code = null;
            if(strlen($language) == 0){
                $language = "en_GB";
            }
			try{
				$res = $modelPlayer->managePlayerOnWebSite($bo_session_id, $action, $aff_for, $username,
				$password, $subrole, $mac_address, $email,
				$country, $currency, $banned, $zip, $phone_number,
				$street_address1, $birthday, $first_name,
				$last_name, $city, $subject_id, $multicurrency, $autoincrement,	$game_payback, $key_exit,
				$enter_password, $street_address2, $bank_account, $bank_country,
				$swift, $iban, $receive_email, 'ALL', $registration_code, $tid_code, $language);
			}catch(Zend_Exception $ex){
				$errorHelper = new ErrorHelper();
				$mail_message = "WebSitePlayerAccountManager::updatePlayer. Error while trying to update player details for web site. <br /> Session id: {$session_id} <br /> Player email address: {$email} <br /> Player details on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->siteError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_INTERNAL_ERROR, "details"=>"Error updating player information");
			}
			$modelAuthorization->closeBoSession($bo_session_id);
			if($res['status'] == OK){
				return array("status"=>OK);
			}
			else{
                if ($res['error_code'] == 20713) {
                    return array("status" => NOK, "message" => NOK_WRONG_FNAME_LNAME_BIRTHDAY);
                } else if ($res['error_code'] == 20714) {
                    return array("status" => NOK, "message" => NOK_WRONG_MAIL);
                } else if ($res['error_code'] == 20715) {
                    return array("status" => NOK, "message" => NOK_WRONG_FNAME_LNAME_ADDRESS);
                } else if ($res['error_code'] == 20721) {
                    return array("status" => NOK, "message" => NOK_WRONG_FNAME_LNAME_PHONE);
                } else if ($res['error_code'] == 20729) {
                    return array("status" => NOK, "message" => NOK_INVALID_REGISTRATION_CODE);
                } else {
                    return array("status" => NOK, "message" => NOK_INTERNAL_ERROR, "details" => "Unknown error occured");
                }
                /*
				if($res['error_code'] == 20713){
					return WRONG_FNAME_LNAME_BIRTHDAY;
				}
				else if($res['error_code'] == 20714){
					return WRONG_MAIL;
				}
				else if($res['error_code'] == 20715){
					return WRONG_FNAME_LNAME_ADDRESS;
				}
				else{
					return false;
				}*/
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSitePlayerAccountManager::updatePlayer. Error while trying to update player details for web site. <br /> Session id: {$session_id} <br /> Player email address: {$email} <br /> Player details on web site exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_INTERNAL_ERROR, "details"=>"Error updating player information");
		}
	}

	/**
	 *
	 * Reset password method
	 * @param int $session_id
	 * @param string $password_old
	 * @param string $password_new
	 * @param string $ip_address
	 * @return mixed
	 */
	public function resetPassword($session_id, $password_old, $password_new, $ip_address){
		if(!$this->isSecureConnection()){
			return NON_SSL_CONNECTION;
		}
		if(!isset($session_id) || !isset($password_old) || !isset($password_new) || !isset($ip_address)){
			return false;
		}
        $player_id = "";
		try{
			$session_id = strip_tags($session_id);
			$password_old = strip_tags($password_old);
			$password_new = strip_tags($password_new);
			$ip_address = strip_tags($ip_address);
			$player_id = "";
			$player_username = "";
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
            /*
			require_once MODELS_DIR . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$hashed_password = md5(md5(FICTIVE_BO_PASSWORD));
			$bo_session_id = $modelAuthorization->openBoSession(FICTIVE_BO_USERNAME , $hashed_password, $ip_address);
			if($bo_session_id == 0){
				return false;
			}
            */
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($session_id);
			if($arrPlayer == false){
				return false;
			}
			if($arrPlayer['player_password'] != md5(md5($password_old))){
				return false;
			}
			$player_password = md5(md5($password_new));
			$player_id = $arrPlayer['player_id'];
			$player_username = $arrPlayer['player_name'];
			$res = $modelWebSite->resetPassword($session_id, $arrPlayer['player_id'], $player_password);
			//$modelAuthorization->closeBoSession($bo_session_id);
			//returns true if password reseted successfull or false if failed password reset
			if($res == 1){
				return true;
			}
			else{
				$errorHelper = new ErrorHelper();
				$mail_message = "WebSitePlayerAccountManager::resetPassword. Error while reseting player's password on web site. <br /> Session id: {$session_id} <br /> Player id: {$player_id} <br /> Player name: {$player_username}";
				$log_message = "WebSitePlayerAccountManager::resetPassword. Error while reseting player's password on web site. Session id: {$session_id} Player id: {$player_id} Player name: {$player_username}";
				$errorHelper->siteError($mail_message, $log_message);
				return false;
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSitePlayerAccountManager::resetPassword. Error while reseting player's password on web site. <br /> Session id: {$session_id} <br /> Player id: {$player_id} <br /> Reset Player password exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSitePlayerAccountManager::resetPassword. Reset Player password exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return false;
		}
	}

	/**
	 * Validate player's username
	 * @param string $username
	 * @return mixed
	 */
	public function validatePlayer($username){
		if(!$this->isSecureConnection()){
			return NON_SSL_CONNECTION;
		}
		try{
			if(!isset($username)){
				return false;
			}
			$username = strip_tags($username);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$exist = $modelPlayer->validatePlayerName($username);
			unset($modelPlayer);
            if($exist == YES){
                return YES;
            }else {
                return $exist;
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSitePlayerAccountManager::validatePlayer. Error while validating player's username on web site. <br /> Player name: {$username} <br /> Validate Player username on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSitePlayerAccountManager::validatePlayer. Validate player username exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return false;
		}
	}

    /**
	 * Validate player's phone
	 * @param int $player_id
     * @param string $player_phone
     * @param string $white_label_name
	 * @return mixed
	 */
	public function validatePlayerPhone($player_id, $player_phone, $white_label_name){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		try{
			if(strlen($player_phone) == 0 || strlen($player_id) == 0 || strlen($white_label_name) == 0){
				return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
			}
			$player_id = strip_tags($player_id);
            $player_phone = strip_tags($player_phone);
            $white_label_name = strip_tags($white_label_name);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$result = $modelPlayer->validatePlayerPhone($player_id, $player_phone, $white_label_name);
            if($result['status'] == OK){
                return array("status"=>OK, "phone_available"=>$result['phone_available']);
            }else{
                return array("status"=>NOK, "message"=>$result['message']);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSitePlayerAccountManager::validatePlayerPhone(player_id = {$player_id}, player_phone = {$player_phone}, white_label_name = {$white_label_name}). Error while validating player's phone on web site. <br /> Validate Player phone on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSitePlayerAccountManager::validatePlayerPhone(player_id = {$player_id}, player_phone = {$player_phone}, white_label_name = {$white_label_name}). Validate player phone exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	 * Validate player's email
	 * @param int $player_id
     * @param string $player_email
     * @param string $white_label_name
	 * @return mixed
	 */
	public function validatePlayerEmail($player_id, $player_email, $white_label_name){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		try{
			if(strlen($player_email) == 0 || strlen($player_id) == 0 || strlen($white_label_name) == 0){
				return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
			}
			$player_id = strip_tags($player_id);
            $player_email = strip_tags($player_email);
            $white_label_name = strip_tags($white_label_name);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$result = $modelPlayer->validatePlayerEmail($player_id, $player_email, $white_label_name);
			if($result['status'] == OK){
                return array("status"=>OK, "email_available"=>$result['email_available']);
            }else{
                return array("status"=>NOK, "message"=>$result['message']);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSitePlayerAccountManager::validatePlayerEmail(player_id = {$player_id}, player_email = {$player_email}, white_label_name = {$white_label_name}). Error while validating player's email on web site. <br /> Validate Player email on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSitePlayerAccountManager::validatePlayerEmail(player_id = {$player_id}, player_email = {$player_email}, white_label_name = {$white_label_name}). Validate player email exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	 * Validate player's first name, last name and birthday date
	 * @param int $player_id
     * @param string $player_first_name
     * @param string $player_last_name
     * @param string $player_birthday
     * @param string $white_label_name
	 * @return mixed
	 */
	public function validatePlayerFirstNameLastNameBirthday($player_id, $player_first_name, $player_last_name, $player_birthday, $white_label_name){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		try{
			if(strlen($player_first_name) == 0 || strlen($player_last_name) == 0 || strlen($player_birthday) == 0 || strlen($white_label_name) == 0){
				return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
			}
			$player_id = strip_tags($player_id);
            $player_first_name = strip_tags($player_first_name);
            $player_last_name = strip_tags($player_last_name);
            $player_birthday = strip_tags($player_birthday);
            $white_label_name = strip_tags($white_label_name);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
            $result = $modelPlayer->validatePlayerFirstNameLastNameBirthdate($player_id, $player_first_name, $player_last_name, $player_birthday, $white_label_name);
			if($result['status'] == OK){
                return array("status"=>OK, "combination_available"=>$result['combination_available']);
            }else{
                return array("status"=>NOK, "message"=>$result['message']);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSitePlayerAccountManager::validatePlayerFirstNameLastNameBirthday(player_id = {$player_id}, player_first_name = {$player_first_name}, player_last_name = {$player_last_name}, player_birthday = {$player_birthday}, white_label_name = {$white_label_name}). Error while validating player's first name, last name and birthday combination on web site. <br /> Web Site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSitePlayerAccountManager::validatePlayerFirstNameLastNameBirthday(player_id = {$player_id}, player_first_name = {$player_first_name}, player_last_name = {$player_last_name}, player_birthday = {$player_birthday}, white_label_name = {$white_label_name}). Web site exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	 * Validate player's first name, last name and player address information
	 * @param int $player_id
     * @param string $player_first_name
     * @param string $player_last_name
     * @param string $player_city
     * @param string $player_address
     * @param string $player_address2
     * @param string $white_label_name
	 * @return mixed
	 */
	public function validatePlayerFirstNameLastNameAddress($player_id, $player_first_name, $player_last_name, $player_city, $player_address, $player_address2, $white_label_name){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		try{
			if(strlen($player_first_name) == 0 || strlen($player_last_name) == 0 || strlen($player_city) == 0 || strlen($player_address) == 0 || strlen($player_address2) == 0 || strlen($white_label_name) == 0){
				return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
			}
			$player_id = strip_tags($player_id);
            $player_first_name = strip_tags($player_first_name);
            $player_last_name = strip_tags($player_last_name);
            $player_city = strip_tags($player_city);
            $player_address = strip_tags($player_address);
            $player_address2 = strip_tags($player_address2);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
            $result = $modelPlayer->validatePlayerFirstNameLastNameAddress($player_id, $player_first_name, $player_last_name, $player_city, $player_address, $player_address2, $white_label_name);
			if($result['status'] == OK){
                return array("status"=>OK, "combination_available"=>$result['combination_available']);
            }else{
                return array("status"=>NOK, "message"=>$result['message']);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSitePlayerAccountManager::validatePlayerFirstNameLastNameAddress(player_id = {$player_id}, player_first_name = {$player_first_name}, player_last_name = {$player_last_name}, player_city = {$player_city}, player_address = {$player_address}, player_address2 = {$player_address2}, white_label_name = {$white_label_name}). Error while validating player's first name, last name and address combination on web site. <br /> Web Site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSitePlayerAccountManager::validatePlayerFirstNameLastNameAddress(player_id = {$player_id}, player_first_name = {$player_first_name}, player_last_name = {$player_last_name}, player_city = {$player_city}, player_address = {$player_address}, player_address2 = {$player_address2}, white_label_name = {$white_label_name}). Web site exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	 * Validate player's first name, last name and phone
	 * @param int $player_id
     * @param string $player_first_name
     * @param string $player_last_name
     * @param string $player_phone
     * @param string $white_label_name
	 * @return mixed
	 */
	public function validatePlayerFirstNameLastNamePhone($player_id, $player_first_name, $player_last_name, $player_phone, $white_label_name){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		try{
			if(strlen($player_first_name) == 0 || strlen($player_last_name) == 0 || strlen($player_phone) == 0 || strlen($white_label_name) == 0){
				return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
			}
			$player_id = strip_tags($player_id);
            $player_first_name = strip_tags($player_first_name);
            $player_last_name = strip_tags($player_last_name);
            $player_phone = strip_tags($player_phone);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
            $result = $modelPlayer->validatePlayerFirstNameLastNamePhone($player_id, $player_first_name, $player_last_name, $player_phone, $white_label_name);
			if($result['status'] == OK){
                return array("status"=>OK, "combination_available"=>$result['combination_available']);
            }else{
                return array("status"=>NOK, "message"=>$result['message']);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSitePlayerAccountManager::validatePlayerFirstNameLastNamePhone(player_id = {$player_id}, player_first_name = {$player_first_name}, player_last_name = {$player_last_name}, player_phone = {$player_phone}, white_label_name = {$white_label_name}). Error while validating player's first name, last name and phone combination on web site. <br /> Web Site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSitePlayerAccountManager::validatePlayerFirstNameLastNamePhone(player_id = {$player_id}, player_first_name = {$player_first_name}, player_last_name = {$player_last_name}, player_phone = {$player_phone}, white_label_name = {$white_label_name}). Web site exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	 *
	 * player details method
	 * @param string $session_id
	 * @param string $ip_address
	 * @return mixed
	 */
	public function playerCredits($session_id, $ip_address){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(!isset($session_id) || !isset($ip_address)){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
			$session_id = intval(strip_tags($session_id));
			$ip_address = strip_tags($ip_address);
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
            $result = $modelPlayer->getPlayerCredits($session_id);
			if($result['status'] != OK){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			return array(
                "status"=>OK, "credits"=>$result['credits'], "credit_status"=>$result['credits']
            );
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSitePlayerAccountManager::playerCredits. Error while getting player credits for web site. <br /> Session id: {$session_id} <br /> IP address: {$ip_address} <br /> Player credits on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
}
