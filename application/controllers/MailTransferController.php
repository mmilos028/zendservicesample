<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class MailTransferController extends Zend_Controller_Action{
	
	public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
	}
	
	public function indexAction(){
		header('Location: http://www.google.com/');
	}

    //
    // This is called from backoffice to send mail to player with unlock link to his account
    //
    public function sendUnlockPlayerAccountMailAction(){
        $player_id = $this->_getParam('player_id');
        if(!isset($player_id)){
            exit(NOK);
        }
        try{
			//DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE
			//require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("MailTransferController::sendUnlockPlayerAccountMail method. " .
			//"<br /> Player id = {$player_id}");
			//$errorHelper->merchantErrorLog("MailTransferController::sendUnlockPlayerAccountMail method. " .
			//" Player id = {$player_id}");

            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			//retrieve player details - required for player email address
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
			if($playerDetails['status'] != OK){
				exit(NOK_EXCEPTION);
			}
			$details = $playerDetails['details'];
			//get site settings for player with player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
				exit(NOK);
			}
            $playerMailAddress = $details['email'];
			$playerUsername = $details['user_name'];
            $casinoName = $site_settings['casino_name'];
            $siteLink = $site_settings['site_link'];
			$siteImagesLocation = $site_settings['site_image_location'];
			$playerMailSendFrom = $site_settings['mail_address_from'];
			$playerSmtpServer = $site_settings['smtp_server_ip'];
            $contactLink = $site_settings['contact_url_link'];
			$supportLink = $site_settings['support_url_link'];
			$termsLink = $site_settings['terms_url_link'];
            $languageSettings = $site_settings['language_settings'];
            $playerUnlockLink = $site_settings['unlock_url_link'] . "?id=" . $player_id;
			if(isset($playerSmtpServer) && isset($playerMailSendFrom) && isset($playerMailAddress)){
                $playerMailRes = WebSiteEmailHelper::getUnlockPlayerEmailToPlayerContent($playerUsername, $siteImagesLocation, $casinoName, $siteLink, $playerUnlockLink, $supportLink, $termsLink, $contactLink, $languageSettings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				$loggerMessage =  "Player with mail address: {$playerMailAddress} username: {$playerUsername}, has not received his unlock account mail. <br />His mail title: {$title} . His mail content: <br /> {$content}";
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress, $playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				exit(OK);
			}else{
				require_once HELPERS_DIR . DS . 'ErrorHelper.php';
				$errorHelper = new ErrorHelper();
				$message = "MailTransferController::sendUnlockPlayerAccountMail action exception message: playerSmtpServer = {$playerSmtpServer} playerMailSendFrom = {$playerMailSendFrom} playerMailAddress = {$playerMailAddress}";
                $errorHelper->merchantError($message, $message);
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
            $message = "MailTransferController::sendUnlockPlayerAccountMail(player_id = {$player_id}) action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->merchantError($message, $message);
			exit(NOK);
		}
    }

    //
    // This is called from backoffice to send mail to player with his lost username in mail content
    //
    public function sendPlayerLostUsernameMailAction(){
        $player_id = $this->_getParam('player_id');
        if(!isset($player_id)){
            exit(NOK);
        }
        try{
			//DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE
			//require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("MailTransferController::sendPlayerLostUsernameMail method. " .
			//"<br /> Player id = {$player_id}");
			//$errorHelper->merchantErrorLog("MailTransferController::sendPlayerLostUsernameMail method. " .
			//" Player id = {$player_id}");

            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			//retrieve player details - required for player email address
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
			if($playerDetails['status'] != OK){
				exit(NOK_EXCEPTION);
			}
			$details = $playerDetails['details'];
			//get site settings for player with player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
				exit(NOK);
			}
            $playerMailAddress = $details['email'];
			$playerUsername = $details['user_name'];
            $playerName = $details['first_name'] . " " . $details['last_name'];
            $siteLink = $site_settings['site_link'];
			$siteImagesLocation = $site_settings['site_image_location'];
            $casinoName = $site_settings['casino_name'];
			$playerMailSendFrom = $site_settings['mail_address_from'];
			$playerSmtpServer = $site_settings['smtp_server_ip'];
            $contactLink = $site_settings['contact_url_link'];
			$supportLink = $site_settings['support_url_link'];
			$termsLink = $site_settings['terms_url_link'];
            $languageSettings = $site_settings['language_settings'];
			if(isset($playerSmtpServer) && isset($playerMailSendFrom) && isset($playerMailAddress)){
                $playerMailRes = WebSiteEmailHelper::getUsernameEmailToPlayerContent($playerName,
			        $playerUsername, $siteImagesLocation, $casinoName, $siteLink, $contactLink, $supportLink, $termsLink, $languageSettings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				$loggerMessage =  "Player with mail address: {$playerMailAddress} username: {$playerUsername}, has not received his notification mail. His mail title: {$title} . His mail content: <br /> {$content}";
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress, $playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				exit(OK);
			}else{
				require_once HELPERS_DIR . DS . 'ErrorHelper.php';
				$errorHelper = new ErrorHelper();
				$message = "MailTransferController::sendCustomMailToPlayer(player_id = {$player_id}) action exception message: playerSmtpServer = {$playerSmtpServer} playerMailSendFrom = {$playerMailSendFrom} playerMailAddress = {$playerMailAddress}";
                $errorHelper->merchantError($message, $message);
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
            $message = "MailTransferController::sendCustomMailToPlayer(player_id = {$player_id}) action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->merchantError($message, $message);
			exit(NOK);
		}
    }

	//
	// This is called from backoffice to send custom mail content to player
	// that his payout request has been denied
	//
	public function sendCustomMailToPlayerAction(){
        $player_id = $this->_getParam('player_id');
        $send_email_from = $this->_getParam('send_email_from');
        $player_email_address = $this->_getParam('player_email_address');
        $mail_title = $this->_getParam('mail_title');
        $mail_content = $this->_getParam('mail_content');
        if(strlen($player_id)==0 && strlen($send_email_from)==0 && strlen($player_email_address)==0 && strlen($mail_content)==0 && strlen($mail_title)==0){
            exit(NOK);
        }
		try{
			//DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE
            /*
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("MailTransferController::sendCustomMailToPlayer(player_id = {$player_id}, send_email_from = {$send_email_from}, player_email_address = {$player_email_address},
                mail_title = {$mail_title}, mail_content = {$mail_content})");
            */

            $bo_session_id = null;
			//get site settings for player with player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
				exit(NOK);
			}
			$siteImagesLocation = $site_settings['site_image_location'];
			$playerSmtpServer = $site_settings['smtp_server_ip'];
			if(isset($playerSmtpServer) && isset($send_email_from) && isset($player_email_address)){
				$playerMailContent = WebSiteEmailHelper::getCustomMailContent($mail_title, $mail_content, $siteImagesLocation);
				$loggerMessage =  "Player with mail address: {$player_email_address} has not received his notification mail. His mail title: {$mail_title} His mail content: {$mail_content}";
				WebSiteEmailHelper::sendMailToPlayer($send_email_from, $player_email_address, $playerSmtpServer, $mail_title, $playerMailContent, $mail_title, $mail_title, $loggerMessage, $siteImagesLocation);
				exit(OK);
			}else{
				require_once HELPERS_DIR . DS . 'ErrorHelper.php';
				$errorHelper = new ErrorHelper();
                $message = "MailTransferController::sendCustomMailToPlayer(player_id = {$player_id}, send_email_from = {$send_email_from}, player_email_address = {$player_email_address},
                mail_title = {$mail_title}, mail_content = {$mail_content}) action exception message: playerSmtpServer = {$playerSmtpServer} playerMailSendFrom = {$send_email_from} player_email_address = {$player_email_address}";
                $errorHelper->merchantError($message, $message);
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
            $message = "MailTransferController::sendCustomMailToPlayer(player_id = {$player_id}, send_email_from = {$send_email_from}, player_email_address = {$player_email_address},
                mail_title = {$mail_title}, mail_content = {$mail_content}) action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			exit(NOK);
		}
	}
	
	//
	// This is called from backoffice to send custom mail content to support
	//
	public function sendCustomMailToSupportAction(){
        $player_id = $this->_getParam('player_id');
        $supportAddress = $this->_getParam('support_address');
        $mailTitle = $this->_getParam('mail_title');
        $mailContent = $this->_getParam('mail_content');
        if(!isset($player_id) && !isset($supportAddress) && !isset($mailContent) && !isset($mailTitle)){
            exit(NOK);
        }
		try{
			//DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE
			//require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("MailTransferController::sendCustomMailToSupport method. " .
			//"<br /> Support address = {$supportAddress}");
			//$errorHelper->merchantErrorLog("MailTransferController::sendCustomMailToSupport method. " .
			//" Support address = {$supportAddress}");

            $bo_session_id = null;
			//get site settings for player with player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
				exit(NOK);
			}
			$siteImagesLocation = $site_settings['site_image_location'];
			$playerMailSendFrom = $site_settings['mail_address_from'];
			$playerSmtpServer = $site_settings['smtp_server_ip'];
			if(isset($playerSmtpServer) && isset($playerMailSendFrom) && isset($supportAddress)){
				$playerMailContent = WebSiteEmailHelper::getCustomMailContent($mailTitle, $mailContent, $siteImagesLocation);
				$loggerMessage =  "Support with mail address: {$supportAddress} has not received his notification mail. His mail title: {$mailTitle} His mail content: {$mailContent}";
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $supportAddress, $playerSmtpServer, $mailTitle, $playerMailContent, $mailTitle, $mailTitle, $loggerMessage, $siteImagesLocation);
				exit(OK);
			}else{
				require_once HELPERS_DIR . DS . 'ErrorHelper.php';
				$errorHelper = new ErrorHelper();
                $message = "MailTransferController::sendCustomMailToSupport(player_id = {$player_id}) action exception message: playerSmtpServer = {$playerSmtpServer} playerMailSendFrom = {$playerMailSendFrom} supportAddress = {$supportAddress}";
                $errorHelper->merchantError($message, $message);
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
            $message = "MailTransferController::sendCustomMailToSupport(player_id = {$player_id}) action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			exit(NOK);
		}
	}
	
	//
	// This is called from backoffice to send to player his reset password email with link
	// Receives only player_id
	//
	public function playerResetPasswordEmailAction(){
        $player_id = strip_tags($this->_getParam('player_id', null));
        if(!isset($player_id)){
            $data = array("status"=>NOK, "message"=>NOK_INVALID_DATA);
            exit(Zend_Json::encode($data));
        }
		try{
			//DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE
			//require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("MailTransferController::playerResetPasswordEmail method: <br /> Player ID = {$player_id}");

            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			//retrieve player details - required for player email addres
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
			if($playerDetails['status'] != OK){
				$data = array("status"=>NOK, "message"=>NOK_EXCEPTION, "details"=>"Invalid Player Details");
				exit(Zend_Json::encode($data));
			}
			$details = $playerDetails['details'];
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			//in return array there will be ID and EMAIL address to send email to player								
			$player_username = $details['user_name'];
			//DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE
			//require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("MailTransferController::playerResetPasswordEmailAction method: <br /> Player ID = {$player_id} Player Username = {$player_username}");
			$player_first_name = $details['first_name'];
			$player_last_name = $details['last_name'];
			$player_birthday = $details['birthday'];
			$player_email = $details['email'];
			$result = $modelWebSite->GetSecurityQuestionAnswer($player_username);
			$answer = $result['answer'];
			$result = $modelWebSite->ForgotPasswordWithPersonalData($player_username, $player_first_name, $player_last_name, $player_birthday, $player_email);
			$id = $result['id']; //temporary id for link to reset player's password
			if(strlen($id) == 0){
				$data = array("status"=>NOK, "message"=>NOK, "details"=>"No Temporary ID to reset player password");
				exit(Zend_Json::encode($data));
			}
			//get site settings for player with player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
				$data = array("status"=>NOK, "message"=>NOK, "details"=>"No site settings for player");
				exit(Zend_Json::encode($data));
			}
			$playerMailAddress = $details['email'];	
			$playerForgotPasswordLink = $site_settings['forgot_pass_link'];
			$forgotPasswordLink = $playerForgotPasswordLink . "?username=" . $player_username . "&id=" . $id;
			$playerUsername = $details['user_name'];
			$playerMailSendFrom = $site_settings['mail_address_from'];
			$playerSmtpServer = $site_settings['smtp_server_ip'];
			$siteImagesLocation = $site_settings['site_image_location'];
			$casinoName = $site_settings['casino_name'];
			$siteLink = $site_settings['site_link'];
			$contactLink = $site_settings['contact_url_link'];
			$supportLink = $site_settings['support_url_link'];
			$termsLink = $site_settings['terms_url_link'];
            $languageSettings = $site_settings['language_settings'];
			if(isset($playerSmtpServer) && isset($playerMailSendFrom) && isset($playerMailAddress)){
				$playerMailRes = WebSiteEmailHelper::getPasswordEmailToPlayerContent($playerUsername, $siteImagesLocation, $casinoName, $siteLink, $forgotPasswordLink,
                    $contactLink, $supportLink, $termsLink, $languageSettings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				$loggerMessage =  "Player with mail address: {$playerMailAddress} has not received his notification mail. His mail title: {$title} His mail content: {$content}";
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress, $playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				$data = array("status"=>OK, "message"=>OK);
				exit(Zend_Json::encode($data));
			}else{
				require_once HELPERS_DIR . DS . 'ErrorHelper.php';
				$errorHelper = new ErrorHelper();
				$message = "MailTransferController::playerResetPasswordEmailAction(player_id = {$player_id}) exception message: playerSmtpServer = {$playerSmtpServer} playerMailSendFrom = {$playerMailSendFrom} playerMailAddress = {$playerMailAddress}";
                $errorHelper->merchantError($message, $message);
				$data = array("status"=>NOK, "message"=>NOK, "details"=>"Player email settings are missing");
				exit(Zend_Json::encode($data));
			}
		}catch(Zend_Exception $ex){
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
            $message =  "MailTransferController::playerResetPasswordEmailAction(player_id = {$player_id}) exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->merchantError($message, $message);
			$data = array("status"=>NOK, "message"=>NOK_EXCEPTION, "details"=>"Exception during sending mail to player");
			exit(Zend_Json::encode($data));
		}
	}
	
	//
	//	This is called from backoffice to send to player his activation mail after player account is created
	//
	public function playerActivationEmailAction(){
        $player_id = strip_tags($this->_getParam('player_id'));
        if(!isset($player_id)){
            exit(NOK);
        }
		try{
			//DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE
			//require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("MailTransferController::playerActivationEmailAction method: <br /> Player ID = {$player_id}");
            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			//retrieve player details - required for player email addres
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
			if($playerDetails['status'] != OK){
				exit(NOK_EXCEPTION);
			}
			$details = $playerDetails['details'];
			$playerUsername = $details['user_name'];
			//DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE
			//require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("MailTransferController::playerActivationEmailAction method: <br /> Player ID = {$player_id} Player Username = {$player_username}");
			//get site settings for player with player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
				exit(NOK);
			}
            require_once MODELS_DIR . DS . 'WebSiteModel.php';
            $modelWebSite = new WebSiteModel();
            $validationHash = $modelWebSite->getPlayerValidationHash($player_id) . '_' . $player_id;
			$playerMailAddress = $details['email'];	
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
			if(isset($playerSmtpServer) && isset($playerMailSendFrom) && isset($playerMailAddress)){
				$playerMailRes = WebSiteEmailHelper::getActivationEmailToPlayerContent($playerName, $playerUsername,
				$siteImagesLocation, $casinoName, $siteLink, $playerActivationLink, $supportLink, $termsLink, $contactLink, $languageSettings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				$loggerMessage = "Player with player username: {$playerUsername} on mail address: {$playerMailAddress}
				has not received mail that his account is activated.";				
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress, $playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				exit(OK);
			}else{
				require_once HELPERS_DIR . DS . 'ErrorHelper.php';
				$errorHelper = new ErrorHelper();
				$message = "MailTransferController::playerActivationEmailAction(player_id = {$player_id}) exception message: playerSmtpServer = {$playerSmtpServer} playerMailSendFrom = {$playerMailSendFrom} playerMailAddress = {$playerMailAddress}";
                $errorHelper->merchantError($message, $message);
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
            $message = "MailTransferController::playerActivationEmailAction(player_id = {$player_id}) exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->merchantError($message, $message);
			exit(NOK);
		}
	}

    //
	//	This is called from backoffice to send to player his activation mail after player account is created
	//
	public function playerVerificationStatusChangedAction(){
        $player_id = strip_tags($this->_getParam('player_id'));
        if(!isset($player_id)){
            exit(NOK);
        }
        $transactionId = strip_tags($this->_getParam('transaction_id'));
        $transactionAmount = strip_tags($this->_getParam('transaction_amount'));
        $transactionCurrency = strip_tags($this->_getParam('transaction_currency'));
		try{
			//DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE
			//require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("MailTransferController::playerVerificationStatusChangedAction method: <br /> Player ID = {$player_id}");
            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			//retrieve player details - required for player email addres
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
			if($playerDetails['status'] != OK){
				exit(NOK_EXCEPTION);
			}
			$details = $playerDetails['details'];
			$playerUsername = $details['user_name'];
			//DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE
			//require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("MailTransferController::playerVerificationStatusChangedAction method: <br /> Player ID = {$player_id} Player Username = {$player_username}");
			//get site settings for player with player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$site_settings = $modelMerchant->findSiteSettings($player_id);
			if($site_settings['status'] != OK){
				exit(NOK);
			}
			$playerMailAddress = $details['email'];
			$playerMailSendFrom = $site_settings['mail_address_from'];
			$playerSmtpServer = $site_settings['smtp_server_ip'];
			$casinoName = $site_settings['casino_name'];
			$siteImagesLocation = $site_settings['site_image_location'];
			$siteLink = $site_settings['site_link'];
			$supportLink = $site_settings['support_url_link'];
			$termsLink = $site_settings['terms_url_link'];
			$contactLink = $site_settings['contact_url_link'];
            $languageSettings = $site_settings['language_settings'];
            $privacyPolicyLink = $site_settings['privacy_policy_link'];
			if(isset($playerSmtpServer) && isset($playerMailSendFrom) && isset($playerMailAddress)){
                $playerMailRes = WebSiteEmailHelper::getPlayerChangesVerificationStatusContent($playerUsername, $transactionId, $transactionAmount, $transactionCurrency,
	                $siteImagesLocation, $casinoName, $siteLink, $contactLink, $supportLink, $termsLink, $privacyPolicyLink, $languageSettings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				$loggerMessage = "Player with player username: {$playerUsername} on mail address: {$playerMailAddress}
				has not received mail that his verification status has changed.";
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress, $playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				exit(OK);
			}else{
				require_once HELPERS_DIR . DS . 'ErrorHelper.php';
				$errorHelper = new ErrorHelper();
				$message = "MailTransferController::playerVerificationStatusChangedAction(player_id = {$player_id}) exception message: playerSmtpServer = {$playerSmtpServer} playerMailSendFrom = {$playerMailSendFrom} playerMailAddress = {$playerMailAddress}";
                $errorHelper->merchantError($message, $message);
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
            $message = "MailTransferController::playerVerificationStatusChangedAction(player_id = {$player_id}) exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->merchantError($message, $message);
			exit(NOK);
		}
	}

    //
	//sends mail from customer to administrator of backoffice
    //
	public function sendCustomerMailToAdministratorAction(){
        $mailTo = $this->_getParam('mail_to');
        $mailFrom = $this->_getParam('mail_from');
        $mailTitle = $this->_getParam('mail_title');
        $mailContent = $this->_getParam('mail_content');
        if(!isset($mailTo) && !isset($mailFrom) && !isset($mailContent) && !isset($mailTitle)){
            exit(NOK);
        }
		try{
			//DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE
			//require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("MailTransferController::sendCustomerMailToAdministrator method. " .
			//"<br /> Support address = {$supportAddress} <br /> Mail title = {$mailTitle} <br /> Mail content = {$mailContent}");
			//$errorHelper->merchantErrorLog("MailTransferController::sendCustomerMailToAdministrator method. " .
			//" Support address = {$supportAddress} <br /> Mail title = {$mailTitle} <br /> Mail content = {$mailContent}");					
			if(isset($mailTo) && isset($mailFrom) && isset($mailContent)){
				$mailContent = WebSiteEmailHelper::getAdministratorMailContent($mailTitle, $mailContent);
				WebSiteEmailHelper::sendMailToAdministrator($mailTo, $mailFrom, $mailTitle, $mailContent);				
				exit(OK);
			}else{
				require_once HELPERS_DIR . DS . 'ErrorHelper.php';
				$errorHelper = new ErrorHelper();
				$message = "MailTransferController::sendCustomerMailToAdministrator action exception message: mailTo = {$mailTo} mailFrom = {$mailFrom} mailTitle = {$mailTitle} mailContent = {$mailContent}";
				$errorHelper->merchantError($message, $message);
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
			$message = "MailTransferController::sendCustomerMailToAdministrator action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantErrorLog($message, $message);
			exit(NOK);
		}
	}
}