<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
/**
 * 
 * Web site web service PLAYER ACCOUNT MAIL SENDING ...
 *
 */

class WebSitePlayerAccountMailsManager {	
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
				$message = "WebSite service: Host with blacklisted ip address {$host_ip_address} is trying to connect to web site web service.";
				$errorHelper->siteError($message, $message);
			}
			return $status;
		} else{ 
			return true;
		}
	}
	
	/**
	 * web service that calls stored db procedure which from player's personal data finds his username.
	 * All player's details must be identical to database details for player.
	 * If data are same then returns OK and sends mail to player's email with his username info.
	 * If not same then returns NOK status.
	 * @param string $name
	 * @param string $familyname
	 * @param string $birthday
	 * @param string $email
	 * @return string
	 */
	public function ForgotUsername($name, $familyname, $birthday, $email){
	    if (!$this->isSecureConnection()){
			return NON_SSL_CONNECTION;
	    }
	    try{
			//checks if all parameters are send (not to call db stored procedure if not all data received)
			if((!isset($name)) || (!isset($familyname)) || (!isset($birthday)) || (!isset($email))){
			    return NOK_INVALID_DATA;
			}
			$name = strip_tags($name);
			$familyname = strip_tags($familyname);
			$birthday = strip_tags($birthday);
			$email = strip_tags($email);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$result = $modelWebSite->ForgotUsername($name, $familyname, $birthday, $email);
			if($result["status"] == NOK_EXCEPTION){
				return NOK_EXCEPTION;
			}
			$username = $result["username"];
			unset($modelWebSite);
			//if no user is found with this personal data
			if($result["status"] == NOK || strlen($username) == 0){
			    return NOK_NO_USERNAME;
			}
			$player_id = $result['player_id'];
			//find site settings for player with his player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
                $errorHelper = new ErrorHelper();
				$mail_message = "Error while sending player his forgotten username on web site. <br /> Finding player's site settings error. <br /> Player username: {$username} <br /> Player id: {$player_id}";
				$log_message = "Error while sending player his forgotten username on web site. Finding player's site settings error. Player username: {$username} Player id: {$player_id}";
				$errorHelper->siteError($mail_message, $log_message);
				return NOK_EXCEPTION;
			}
			//if username is found, send email to player with is username
			//procedure to send email to player
			$playerName = $name . " " . $familyname;
			$playerUsername = $username;			
			$siteLink = $site_settings['site_link'];
			$siteImagesLocation = $site_settings['site_image_location'];
			$casinoName = $site_settings['casino_name'];
			$playerMailSendFrom = $site_settings['mail_address_from'];
			$playerSmtpServer = $site_settings['smtp_server_ip'];
			$playerMailAddress = $email;			
			$contactLink = $site_settings['contact_url_link'];
			$supportLink = $site_settings['support_url_link'];
			$termsLink = $site_settings['terms_url_link'];
            $languageSettings = $site_settings['language_settings'];

			$playerMailRes = WebSiteEmailHelper::getUsernameEmailToPlayerContent($playerName,
			$playerUsername, $siteImagesLocation, $casinoName, $siteLink, $contactLink,
			$supportLink, $termsLink, $languageSettings);
            $title = $playerMailRes['mail_title'];
            $content = $playerMailRes['mail_message'];
			$loggerMessage =  "Player with player username: {$playerUsername} on mail address: 
			{$playerMailAddress} has not received mail with his forgotten username.";
			WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
			$playerSmtpServer, $title, $content,
			$title, $title, $loggerMessage, $siteImagesLocation);
			return OK;
		  }catch (Zend_Exception $ex){
		  	$errorHelper = new ErrorHelper();
		  	$message = "Error while sending player his forgotten username on web site. <br /> First name: {$name} <br /> Last name:
		  	{$familyname} <br /> Birthday: {$birthday} <br /> Email: {$email} <br /> Forgot username exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
		  	$errorHelper->siteError($message, $message);
			return NOK_EXCEPTION;
	      }
	}
	
	/**
	 * This is with fixed response new procedure
	 * web service that calls stored db procedure which from player's personal data finds his username. Svi podaci moraju da
	 * All player's details must be identical to database details for player.
	 * If data are same then returns OK and sends mail to player's email with his username info.
	 * If not same then returns NOK status.
	 * @param string $name
	 * @param string $familyname
	 * @param string $birthday
	 * @param string $email
	 * @return string
	 */
	public function ForgotUsernameNew($name, $familyname, $birthday, $email){
	    if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
	    }
	    try{
			//checks if all parameters are send (not to call db stored procedure if not all data received)
			if((!isset($name)) || (!isset($familyname)) || (!isset($birthday)) || (!isset($email))){
				return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
			}
			$name = strip_tags($name);
			$familyname = strip_tags($familyname);
			$birthday = strip_tags($birthday);
			$email = strip_tags($email);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';		
			$modelWebSite = new WebSiteModel();
			$result = $modelWebSite->ForgotUsername($name, $familyname, $birthday, $email);
			if($result["status"] == NOK_EXCEPTION){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			$username = $result["username"];
			unset($modelWebSite);
			//if no user is found with this personal data
			if($result["status"] == NOK || strlen($username) == 0){
				return array("status"=>NOK, "message"=>NOK_NO_USERNAME);
			}
			$player_id = $result['player_id'];
			//find site settings for player with his player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
                $errorHelper = new ErrorHelper();
				$mail_message = "Error while sending player his forgotten username on web site. <br /> Finding player's site settings error. <br /> Player username: {$username} <br /> Player id: {$player_id}";
				$log_message = "Error while sending player his forgotten username on web site. Finding player's site settings error. Player username: {$username} Player id: {$player_id}";
				$errorHelper->siteError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			//if username is found, send email to player with is username
			//procedure to send email to player
			$playerName = $name . " " . $familyname;
			$playerUsername = $username;
			$siteLink = $site_settings['site_link'];
			$siteImagesLocation = $site_settings['site_image_location'];
			$casinoName = $site_settings['casino_name'];
			$playerMailSendFrom = $site_settings['mail_address_from'];
			$playerSmtpServer = $site_settings['smtp_server_ip'];
			$playerMailAddress = $email;			
			$contactLink = $site_settings['contact_url_link'];
			$supportLink = $site_settings['support_url_link'];
			$termsLink = $site_settings['terms_url_link'];
            $languageSettings = $site_settings['language_settings'];
			$playerMailRes = WebSiteEmailHelper::getUsernameEmailToPlayerContent($playerName,
			$playerUsername, $siteImagesLocation, $casinoName, $siteLink, $contactLink,
			$supportLink, $termsLink, $languageSettings);
            $title = $playerMailRes['mail_title'];
            $content = $playerMailRes['mail_message'];
			$loggerMessage =  "Player with player username: {$playerUsername} on mail address: 
			{$playerMailAddress} has not received mail with his forgotten username.";
			WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
			$playerSmtpServer, $title, $content,
			$title, $title, $loggerMessage, $siteImagesLocation);
			return array("status"=>OK, "result"=>OK);
		  }catch (Zend_Exception $ex){
		  	$errorHelper = new ErrorHelper();
		  	$message = "Error while sending player his forgotten username on web site. <br /> First name: {$name} <br /> Last name:
		  	{$familyname} <br /> Birthday: {$birthday} <br /> Email: {$email} <br /> Forgot username exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
		  	$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
	      }
	}
	
	/**
	*
	* resend player activation mail procedure
	* @param int $player_id
	* @return mixed
	*/
	public function sendPlayerActivationMail($player_id){
		if(!$this->isSecureConnection()){
			return NON_SSL_CONNECTION;
		}
		if(!isset($player_id)){
			return NOK_INVALID_DATA;
		}
		try{
			$player_id = strip_tags($player_id);
            /*
			require_once MODELS_DIR . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$hashed_password = md5(md5(FICTIVE_BO_PASSWORD));
            $ip_address = IPHelper::getRealIPAddress();
			$bo_session_id = $modelAuthorization->openBoSession(FICTIVE_BO_USERNAME , $hashed_password, $ip_address);
			if($bo_session_id == 0){
				return false;
			}
            */
            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
			if($playerDetails['status'] != OK){
				return false;
			}
			$details = $playerDetails['details'];
			//$modelAuthorization->closeBoSession($bo_session_id);
			$playerUsername = $details['user_name'];
			$playerMailAddress = $details['email'];
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$validationHash = $modelWebSite->getPlayerValidationHash($player_id) . '_' . $player_id;
			if($validationHash != null){
				require_once MODELS_DIR . DS . 'MerchantModel.php';
				$modelMerchant = new MerchantModel();
				$site_settings = $modelMerchant->findSiteSettings($player_id);
				if($site_settings['status'] != OK){
					return false;
				}
				//procedure to send mail to player after his activation
				$playerMailSendFrom = $site_settings['mail_address_from'];
				$playerSmtpServer = $site_settings['smtp_server_ip'];
				$casinoName = $site_settings['casino_name'];
				$siteImagesLocation = $site_settings['site_image_location'];
				$siteLink = $site_settings['site_link'];
				$activationLink = $site_settings['player_activation_link'];
				$playerActivationLink = $activationLink . '?activation_key=' . $validationHash;
				$playerName = $playerDetails['first_name'] . " " . $playerDetails['last_name'];
				$supportLink = $site_settings['support_url_link'];
				$termsLink = $site_settings['terms_url_link'];
				$contactLink = $site_settings['contact_url_link'];
                $languageSettings = $site_settings['language_settings'];
				$playerMailRes = WebSiteEmailHelper::getActivationEmailToPlayerContent($playerName, $playerUsername,
				$siteImagesLocation, $casinoName, $siteLink, $playerActivationLink, $supportLink, $termsLink, $contactLink, $languageSettings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				$loggerMessage = "Player with player username: {$playerUsername} on mail address: {$playerMailAddress}
				has not received mail that his account is activated.";
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
				$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				$status = true;
				return $status;
			}else{
				$status = false;
				return $status;
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = "Error while getting player activation mail from web site. <br /> Player id: {$player_id} <br /> Player resend activation mail on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return false;
		}
	}
	
	/**
	*
	* resend player activation mail procedure new with fixed response
	* @param int $player_id
	* @return mixed
	*/
	public function sendPlayerActivationMailNew($player_id){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
			
		}
		if(!isset($player_id)){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
			$player_id = strip_tags($player_id);
            /*
			require_once MODELS_DIR . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$hashed_password = md5(md5(FICTIVE_BO_PASSWORD));
            $ip_address = IPHelper::getRealIPAddress();
			$bo_session_id = $modelAuthorization->openBoSession(FICTIVE_BO_USERNAME , $hashed_password, $ip_address);
			if($bo_session_id == 0){
				return array("status"=>NOK, "result"=>false);
			}
            */
            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
			if($playerDetails['status'] != OK){
				return array("status"=>NOK, "result"=>false);
			}
			$details = $playerDetails['details'];
			//$modelAuthorization->closeBoSession($bo_session_id);
			$playerUsername = $details['user_name'];
			$playerMailAddress = $details['email'];		
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$validationHash = $modelWebSite->getPlayerValidationHash($player_id) . '_' . $player_id;
			if($validationHash != null){
				require_once MODELS_DIR . DS . 'MerchantModel.php';
				$modelMerchant = new MerchantModel();
				$site_settings = $modelMerchant->findSiteSettings($player_id);
				if($site_settings['status'] != OK){
					return array("status"=>NOK, "result"=>false);
				}
				//procedure to send mail to player after his activation
				$playerMailSendFrom = $site_settings['mail_address_from'];
				$playerSmtpServer = $site_settings['smtp_server_ip'];
				$casinoName = $site_settings['casino_name'];
				$siteImagesLocation = $site_settings['site_image_location'];
				$siteLink = $site_settings['site_link'];
				$activationLink = $site_settings['player_activation_link'];
				$playerActivationLink = $activationLink . '?activation_key=' . $validationHash;
				$playerName = $playerDetails['first_name'] . " " . $playerDetails['last_name'];
				$supportLink = $site_settings['support_url_link'];
				$termsLink = $site_settings['terms_url_link'];
				$contactLink = $site_settings['contact_url_link'];
                $languageSettings = $site_settings['language_settings'];
				$playerMailRes = WebSiteEmailHelper::getActivationEmailToPlayerContent($playerName, $playerUsername,
				$siteImagesLocation, $casinoName, $siteLink, $playerActivationLink, $supportLink, $termsLink, $contactLink, $languageSettings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				$loggerMessage = "Player with player username: {$playerUsername} on mail address: {$playerMailAddress}
				has not received mail that his account is activated.";
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
				$playerSmtpServer, $title, $content,
				$title, $title, $loggerMessage, $siteImagesLocation);
				return array("status"=>OK, "result"=>true);
			}else{
				return array("status"=>NOK, "result"=>false);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = "Error while getting player activation mail from web site. <br /> Player id: {$player_id} <br /> Player resend activation mail on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "result"=>false);
		}
	}

	/**
	 * This is prepared with fixed response
	 * Web service to change player's password using username from player and answer to security question.
	 * Web service calls for db stored procedure:
	 * which checks for data send by service and if data is ok then generated unique (time limited) ID.
	 * then sends mail containing link with this unique ID to web site form to change player's password 
	 * returns status if everything is OK or if not ok NOK status.
	 * @param string $username
	 * @param string $answer
	 * @return string
	 */
	public function ForgotPasswordWithSecurityAnswer($username, $answer){
	    if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
	    }
	    try{
			// checks if all parameters are send (not to call stored procedure in db if they are not)
			if (strlen($username) == 0 || strlen($answer) == 0){
				return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
			}
			$username = strip_tags($username);
			$answer = strip_tags($answer);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';		
			$modelWebSite = new WebSiteModel();
			//in array returning there will be ID and EMAIL address to send email to
			$arrData = $modelWebSite->ForgotPasswordWithSecurityAnswer($username, $answer);
			if($arrData['status'] != OK){
				return array("status"=>NOK, "message"=>$arrData['message']);
			}
			$id = $arrData['id'];
			// Checks here if ID is received. If not then such player does not exist or not a good security answer
			if (strlen($id) == 0){
				return array("status"=>NOK, "message"=>NOK_NO_PLAYER);
			}
			$player_id = $arrData['player_id'];
			//find site settings for player with his player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}			
			// sends email with link with ID as parameter to player's email
			$playerMailSendFrom = $site_settings['mail_address_from'];
			$playerMailAddress = $arrData['email'];
			$playerUsername = $username;
			$playerSmtpServer = $site_settings['smtp_server_ip'];
			$forgotPasswordLink = $site_settings['forgot_pass_link'] . "?username={$playerUsername}&id={$id}";
			$casinoName = $site_settings['casino_name'];
			$siteLink = $site_settings['site_link'];
			$siteImagesLocation = $site_settings['site_image_location'];	
			$contactLink = $site_settings['contact_url_link'];
			$supportLink = $site_settings['support_url_link'];
			$termsLink = $site_settings['terms_url_link'];
            $languageSettings = $site_settings['language_settings'];
			$playerMailRes = WebSiteEmailHelper::getPasswordEmailToPlayerContent($playerUsername, $siteImagesLocation, $casinoName, $siteLink, $forgotPasswordLink,
			$contactLink, $supportLink, $termsLink, $languageSettings);
            $title = $playerMailRes['mail_title'];
            $content = $playerMailRes['mail_message'];
			$loggerMessage =  "Player with player username: {$playerUsername} on mail address: {$playerMailAddress} has not received mail to reset his forgotten password with security answer.";
			WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
			$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
			return array("status"=>OK);
		}catch (Zend_Exception $ex){
			$errorHelper = new ErrorHelper();			
			$message = "Error while sending player mail with link to reset his forgotten password with security answer on web site. <br /> Player username: {$username} <br /> Player answer:
			{$answer} <br /> Exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
	    }
	}

	/**
	 * Web service to change forgotten password for player using username and player's personal data.
	 * Web service calls for db stored procedure
	 * which checks for send data and if they are same in database generateds unique (time limited) ID.
	 * Web service then sends mail containing link (with unique ID) to web site's for to change password
	 * returns status OK if everything is fine
	 * returns status NOK ig there was an error
	 * @param string $username
	 * @param string $name
	 * @param string $familyname
	 * @param string $birthday
	 * @param string $email
	 * @return string
	 */
	public function ForgotPasswordWithPersonalData($username, $name, $familyname, $birthday, $email){
	    if (!$this->isSecureConnection()){
			return NON_SSL_CONNECTION;
	    }
	    try{
			// checks here if all parameters are send (does not call for db stored procedure if they are not send)
			if (!isset($username) || !isset($name) || !isset($familyname) || !isset($birthday) || !isset($email)){
		 	   return NOK_INVALID_DATA;
			}
			$username = strip_tags($username);
			$name = strip_tags($name);
			$familyname = strip_tags($familyname);
			$birthday = strip_tags($birthday);
			$email = strip_tags($email);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';		
			$modelWebSite = new WebSiteModel();
			// in return array there will be ID and EMAIL address to send email to player
			$result = $modelWebSite->ForgotPasswordWithPersonalData($username, $name, $familyname, $birthday, $email);
			if($result['status'] != OK){
				return NOK_EXCEPTION;
			}
			$id = $result['id'];
			$player_id = $result['player_id'];
			unset($modelWebSite);
			// checks if there ID is received. If not then player does not exits, or not a good securitu answer
			if (strlen($id) == 0){
			    return NOK_NO_PLAYER;
			}
			//find site settings for player with his player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
				return NOK_EXCEPTION;
			}
			// sends mail with link with ID parameter
			//send mail to player with link to reset his password on web site mcreel
			$playerMailSendFrom = $site_settings['mail_address_from'];
			$playerMailAddress = $email;
			$playerSmtpServer = $site_settings['smtp_server_ip'];
			$playerForgotPasswordLink = $site_settings['forgot_pass_link']; 			
			$forgotPasswordLink = $playerForgotPasswordLink . "?username=" . $username . "&id=" . $id;
			$playerUsername = $username;
			$siteImagesLocation = $site_settings['site_image_location'];
			$casinoName = $site_settings['casino_name'];
			$siteLink = $site_settings['site_link'];
			$contactLink = $site_settings['contact_url_link'];
			$supportLink = $site_settings['support_url_link'];
			$termsLink = $site_settings['terms_url_link'];
            $languageSettings = $site_settings['language_settings'];
			$playerMailRes = WebSiteEmailHelper::getPasswordEmailToPlayerContent($playerUsername, $siteImagesLocation, $casinoName, $siteLink, $forgotPasswordLink,
                $contactLink, $supportLink, $termsLink, $languageSettings);
            $title = $playerMailRes['mail_title'];
            $content = $playerMailRes['mail_message'];
			$loggerMessage =  "Player with player username: {$playerUsername} on mail address: {$playerMailAddress} has not received mail to reset his forgotten password with personal data.";
			WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
			$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
			return OK;
	    }catch (Zend_Exception $ex){
	    	$errorHelper = new ErrorHelper();
	    	$message = "Error while sending player mail with link to reset his forgotten password with personal data on web site. <br /> Player username: {$username}
            <br />  First name: {$name} <br /> Last name: {$familyname} <br /> Birthday: {$birthday} <br /> Email: {$email} <br /> Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$errorHelper->siteError($message, $message);
			return NOK_EXCEPTION;
	    }
	}
	
	/**
	 * This is prepared procedure with fixed response
	 * Web service to change forgotten password for player using username and player's personal data.
	 * Web service calls for db stored procedure
	 * which checks for send data and if they are same in database generateds unique (time limited) ID.
	 * Web service then sends mail containing link (with unique ID) to web site's for to change password
	 * returns status OK if everything is fine
	 * returns status NOK ig there was an error
	 * @param string $username
	 * @param string $name
	 * @param string $familyname
	 * @param string $birthday
	 * @param string $email
	 * @return string
	 */
	public function ForgotPasswordWithPersonalDataNew($username, $name, $familyname, $birthday, $email){
	    if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
	    }
	    try{
			// checks here if all parameters are send (does not call for db stored procedure if they are not send)
			if (!isset($username) || !isset($name) || !isset($familyname) || !isset($birthday) || !isset($email)){
			   return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
			}
			$username = strip_tags($username);
			$name = strip_tags($name);
			$familyname = strip_tags($familyname);
			$birthday = strip_tags($birthday);
			$email = strip_tags($email);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';		
			$modelWebSite = new WebSiteModel();
			// in return array there will be ID and EMAIL address to send email to player
			$result = $modelWebSite->ForgotPasswordWithPersonalData($username, $name, $familyname, $birthday, $email);
			if($result['status'] != OK){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			$id = $result['id'];
			$player_id = $result['player_id'];
			unset($modelWebSite);
			// checks if there ID is received. If not then player does not exits, or not a good securitu answer
			if (strlen($id) == 0){
				return array("status"=>NOK, "message"=>NOK_NO_PLAYER);
			}
			//find site settings for player with his player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			// sends mail with link with ID parameter
			//send mail to player with link to reset his password on web site mcreel
			$playerMailSendFrom = $site_settings['mail_address_from'];
			$playerMailAddress = $email;
			$playerSmtpServer = $site_settings['smtp_server_ip'];
			$playerForgotPasswordLink = $site_settings['forgot_pass_link']; 			
			$forgotPasswordLink = $playerForgotPasswordLink . "?username=" . $username . "&id=" . $id;
			$playerUsername = $username;
			$siteImagesLocation = $site_settings['site_image_location'];
			$casinoName = $site_settings['casino_name'];
			$siteLink = $site_settings['site_link'];
			$contactLink = $site_settings['contact_url_link'];
			$supportLink = $site_settings['support_url_link'];
			$termsLink = $site_settings['terms_url_link'];
            $languageSettings = $site_settings['language_settings'];
			$playerMailRes = WebSiteEmailHelper::getPasswordEmailToPlayerContent($playerUsername, $siteImagesLocation, $casinoName, $siteLink, $forgotPasswordLink,
                $contactLink, $supportLink, $termsLink, $languageSettings);
            $title = $playerMailRes['mail_title'];
            $content = $playerMailRes['mail_message'];
			$loggerMessage =  "Player with player username: {$playerUsername} on mail address: {$playerMailAddress} has not received mail to reset his forgotten password with personal data.";
			WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
			$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
			return array("status"=>OK, "message"=>OK);
	    }catch (Zend_Exception $ex){
	    	$errorHelper = new ErrorHelper();
	    	$message = "Error while sending player mail with link to reset his forgotten password with personal data on web site. <br /> Player username: {$username}
            <br /> First name: {$name} <br /> Last name: {$familyname} <br /> Birthday: {$birthday} <br /> Email: {$email} <br /> Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
	    }
	}
	
	/**
	*
	* player registration confirmation ...
	* @param string $hash_id
	* @param int $player_id
	* @param string $ip_address
	* @return mixed
	*/
	public function playerRegistrationConfirmation($hash_id, $player_id, $ip_address){
		if(!$this->isSecureConnection()){
			return NON_SSL_CONNECTION;
		}
		if(strlen($hash_id) != 40 || !isset($player_id) || !isset($ip_address)){
			return false;
		}
		$hash_id = strip_tags($hash_id);
		$player_id = strip_tags($player_id);
		$ip_address = strip_tags($ip_address);		
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];		
		require_once MODELS_DIR . DS . 'WebSiteModel.php';
		$modelWebSite = new WebSiteModel();
		$validationHash = $modelWebSite->getPlayerValidationHash($player_id);
		$playerUsername = "";
		$playerEmail = "";
		if($validationHash == $hash_id){
			try{
				require_once MODELS_DIR . DS . 'PlayerModel.php';
				$modelPlayer = new PlayerModel();				
				//get player details
				$playerDetails = $modelPlayer->getPlayerDetailsMalta(null, $player_id);
				if($playerDetails['status'] != OK){
					return false;
				}
				$details = $playerDetails['details'];
				$playerUsername = $details['user_name'];
				$playerEmail = $details['email'];
				//verify player, change him status
				require_once MODELS_DIR . DS . 'DocumentManagmentModel.php';
				$modelDocumentManagment = new DocumentManagmentModel();
				$modelDocumentManagment->setPlayerVerificationStatus(null, $player_id, EMAIL_VERIFIED);
				//get site settings for player with player_id
				require_once MODELS_DIR . DS . 'MerchantModel.php';
				$modelMerchant = new MerchantModel();
				$site_settings = $modelMerchant->findSiteSettings($player_id);
				if($site_settings['status'] != OK){
					return false;
				}
				//send mail to player that his account is activated procedure
				$playerMailSendFrom = $site_settings['mail_address_from'];
				$playerMailAddress = $playerEmail;
				$playerSmtpServer = $site_settings['smtp_server_ip'];			
				$siteImagesLocation = $site_settings['site_image_location'];
				$casinoName = $site_settings['casino_name'];
				$siteLink = $site_settings['site_link'];
				$contactLink = $site_settings['contact_url_link'];
				$supportLink = $site_settings['support_url_linl'];
				$termsLink = $site_settings['terms_url_link'];
                $languageSettings = $site_settings['language_settings'];
				$playerMailRes = WebSiteEmailHelper::getActivatedPlayerEmailToPlayerContent($playerUsername, $playerEmail,
				$siteImagesLocation, $casinoName, $siteLink, $contactLink, $supportLink, $termsLink, $languageSettings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				$loggerMessage =  "Player with player_id: {$player_id} and player username: {$playerUsername} on mail address: {$playerMailAddress} has not received mail that his account is activated.";
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress, 
				$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				return true;
			}catch(Zend_Exception $ex){
				$errorHelper = new ErrorHelper();				
				$mail_message = "Error while sending player email confirming his registration on web site. <br /> Player ID: {$player_id} <br /> Player username: {$playerUsername} <br /> Player email address: {$playerEmail} <br /> Player confirmation registration Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->siteError($mail_message, $log_message);
				return false;
			}
		}else{
			return false;
		}
	}
}