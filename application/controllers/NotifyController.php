<?php
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
//answer is NO / YES
class NotifyController extends Zend_Controller_Action{
	public function indexAction(){
		header('Location: http://www.google.com/');
	}
	
	//return true if ip address exists
	//return false if ip address not exists
	private function isWhiteListedIP(){
		$config = Zend_Registry::get('config');
		//will check site ip address caller if true
		$ip_addresses = explode(' ', $config->db->ip_address);
		$host_ip_address = IPHelper::getRealIPAddress();
		if(!IPHelper::testPrivateIP($host_ip_address)){
			$errorHelper = new ErrorHelper();
			$message = "Notify service: Host with blacklisted ip address {$host_ip_address} is trying to connect to notify web service.";
			$errorHelper->siteError($message, $message);
		}		
		$status = in_array($host_ip_address, $ip_addresses);
		if(!$status){
			$errorHelper = new ErrorHelper();
			$message = "Notify service: Host with blacklisted ip address {$host_ip_address} is trying to connect to notify web service.";
			$errorHelper->siteError($message, $message);
		}
		return $status;
	}
	
	//called by database to notify system players
	public function inactiveAccountAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		//test if ip address allowed to access - test from database ip address
		if(!$this->isWhiteListedIP()){
			die(NO);
		}
		try{
			$player_id = strip_tags($_REQUEST['player_id']);
			$fee = strip_tags($_REQUEST['fee']);
			$currency = strip_tags($_REQUEST['currency']);
			$next_fee_date = strip_tags($_REQUEST['next_fee_date']);
			$reactivate_before_fee_date = strip_tags($_REQUEST['reactivate_before_fee_date']);
			$inactive_time = strip_tags($_REQUEST['inactive_time']);
			if(isset($player_id) && isset($fee) && isset($currency)
			&& isset($next_fee_date) && isset($reactivate_before_fee_date) && isset($inactive_time)) {
                $bo_session_id = null;
				require_once MODELS_DIR . DS . 'PlayerModel.php';
				$modelPlayer = new PlayerModel();
				//get player details
				$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
				if($playerDetails['status'] != OK){
					die(NO);
				}
				$details = $playerDetails['details'];
				$playerUsername = $details['user_name'];
				$playerEmail = $details['email'];
				//get site settings for player with player_id
				require_once MODELS_DIR . DS . 'MerchantModel.php';
				$modelMerchant = new MerchantModel();
				$site_settings = $modelMerchant->findSiteSettings($player_id);
				if($site_settings['status'] != OK){
					die(NO);
				}
				//send mail to player that his account is activated procedure
				$playerMailSendFrom = $site_settings['mail_address_from'];
				$playerMailAddress = $playerEmail;
				$playerSmtpServer = $site_settings['smtp_server_ip'];
				$siteImagesLocation = $site_settings['site_image_location'];
				$casinoName = $site_settings['casino_name'];
				$siteLink = $site_settings['site_link'];
				$contactLink = $site_settings['contact_url_link'];
				$supportLink = $site_settings['support_url_link'];
				$termsLink = $site_settings['terms_url_link'];
                $languageSettings = $site_settings['language_settings'];
				$playerMailRes = WebSiteEmailHelper::getChargeFeeFromPlayerContent($playerUsername,
				$siteImagesLocation, $casinoName, $siteLink, $supportLink, $termsLink, $contactLink, $fee, $currency, $next_fee_date, $reactivate_before_fee_date, $inactive_time, $languageSettings);
				$loggerMessage =  "Player with player_id: " . $player_id . " and player username: " . $playerUsername . " on mail address: " . $playerMailAddress . " has not received mail that he has been charged administration fee due to inactivity. ";
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
				$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				die(YES);
			}else{
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("Parameters have not passed to send mail to player for notification of his inactive account.");
				die(NO);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			die(NO);
		}
	}

    //called by database to notify system players
	public function afterChargingFeeFromPlayerAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		//test if ip address allowed to access - test from database ip address
		if(!$this->isWhiteListedIP()){
			die(NO);
		}
		try{
			$player_id = strip_tags($_REQUEST['player_id']);
			$fee = strip_tags($_REQUEST['fee']);
			$currency = strip_tags($_REQUEST['currency']);
			$next_fee_date = strip_tags($_REQUEST['next_fee_date']);
			$reactivate_before_fee_date = strip_tags($_REQUEST['reactivate_before_fee_date']);
			$inactive_time = strip_tags($_REQUEST['inactive_time']);
			if(isset($player_id) && isset($fee) && isset($currency)
			&& isset($next_fee_date) && isset($reactivate_before_fee_date) && isset($inactive_time)) {
                $bo_session_id = null;
				require_once MODELS_DIR . DS . 'PlayerModel.php';
				$modelPlayer = new PlayerModel();
				//get player details
				$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
				if($playerDetails['status'] != OK){
					die(NO);
				}
				$details = $playerDetails['details'];
				$playerUsername = $details['user_name'];
				$playerEmail = $details['email'];
				//get site settings for player with player_id
				require_once MODELS_DIR . DS . 'MerchantModel.php';
				$modelMerchant = new MerchantModel();
				$site_settings = $modelMerchant->findSiteSettings($player_id);
				if($site_settings['status'] != OK){
					die(NO);
				}
				//send mail to player that his account is activated procedure
				$playerMailSendFrom = $site_settings['mail_address_from'];
				$playerMailAddress = $playerEmail;
				$playerSmtpServer = $site_settings['smtp_server_ip'];
				$siteImagesLocation = $site_settings['site_image_location'];
				$casinoName = $site_settings['casino_name'];
				$siteLink = $site_settings['site_link'];
				$contactLink = $site_settings['contact_url_link'];
				$supportLink = $site_settings['support_url_link'];
				$termsLink = $site_settings['terms_url_link'];
                $languageSettings = $site_settings['language_settings'];
				$playerMailRes = WebSiteEmailHelper::getChargeFeeFromPlayerContent($playerUsername,
				$siteImagesLocation, $casinoName, $siteLink, $supportLink, $termsLink, $contactLink, $fee, $currency, $next_fee_date, $reactivate_before_fee_date, $inactive_time, $languageSettings);
				$loggerMessage =  "Player with player_id: " . $player_id . " and player username: " . $playerUsername . " on mail address: " . $playerMailAddress . " has not received mail that he was charged administration fee due to inactivity. ";
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
				$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				die(YES);
			}else{
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("Parameters have not passed to send mail to player for notification after charding him inactivity fee.");
				die(NO);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			die(NO);
		}
	}

    //called by database to notify system players
	public function beforeChargingFeeFromPlayerAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		//test if ip address allowed to access - test from database ip address
		if(!$this->isWhiteListedIP()){
			die(NO);
		}
		try{
			$player_id = strip_tags($_REQUEST['player_id']);
			$fee = strip_tags($_REQUEST['fee']);
			$currency = strip_tags($_REQUEST['currency']);
			$next_fee_date = strip_tags($_REQUEST['next_fee_date']);
			$reactivate_before_fee_date = strip_tags($_REQUEST['reactivate_before_fee_date']);
			$inactive_time = strip_tags($_REQUEST['inactive_time']);
			if(isset($player_id) && isset($fee) && isset($currency)
			&& isset($next_fee_date) && isset($reactivate_before_fee_date) && isset($inactive_time)) {
                $bo_session_id = null;
				require_once MODELS_DIR . DS . 'PlayerModel.php';
				$modelPlayer = new PlayerModel();
				//get player details
				$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
				if($playerDetails['status'] != OK){
					die(NO);
				}
				$details = $playerDetails['details'];
				$playerUsername = $details['user_name'];
				$playerEmail = $details['email'];
				//get site settings for player with player_id
				require_once MODELS_DIR . DS . 'MerchantModel.php';
				$modelMerchant = new MerchantModel();
				$site_settings = $modelMerchant->findSiteSettings($player_id);
				if($site_settings['status'] != OK){
					die(NO);
				}
				//send mail to player that his account is activated procedure
				$playerMailSendFrom = $site_settings['mail_address_from'];
				$playerMailAddress = $playerEmail;
				$playerSmtpServer = $site_settings['smtp_server_ip'];
				$siteImagesLocation = $site_settings['site_image_location'];
				$casinoName = $site_settings['casino_name'];
				$siteLink = $site_settings['site_link'];
				$contactLink = $site_settings['contact_url_link'];
				$supportLink = $site_settings['support_url_link'];
				$termsLink = $site_settings['terms_url_link'];
                $languageSettings = $site_settings['language_settings'];
				$playerMailRes = WebSiteEmailHelper::getBeforeChargeFeeFromPlayerContent($playerUsername,
				$siteImagesLocation, $casinoName, $siteLink, $supportLink, $termsLink, $contactLink, $fee, $currency, $next_fee_date, $reactivate_before_fee_date, $inactive_time, $languageSettings);
				$loggerMessage =  "Player with player_id: " . $player_id . " and player username: " . $playerUsername . " on mail address: " . $playerMailAddress . " has not received mail that he will be charged due to inactivity. ";
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
				$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				die(YES);
			}else{
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("Parameters have not passed to send mail to player for notification before charging him inactivity fee.");
				die(NO);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			die(NO);
		}
	}

    //called by database to notify system players
	public function beforeClosingAccountForPlayerAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		//test if ip address allowed to access - test from database ip address
		if(!$this->isWhiteListedIP()){
			die(NO);
		}
		try{
			$player_id = strip_tags($_REQUEST['player_id']);
			$fee = strip_tags($_REQUEST['fee']);
			$currency = strip_tags($_REQUEST['currency']);
			$next_fee_date = strip_tags($_REQUEST['next_fee_date']);
			$reactivate_before_fee_date = strip_tags($_REQUEST['reactivate_before_fee_date']);
			$inactive_time = strip_tags($_REQUEST['inactive_time']);
			if(isset($player_id) && isset($fee) && isset($currency)
			&& isset($next_fee_date) && isset($reactivate_before_fee_date) && isset($inactive_time)) {
                $bo_session_id = null;
				require_once MODELS_DIR . DS . 'PlayerModel.php';
				$modelPlayer = new PlayerModel();
				//get player details
				$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
				if($playerDetails['status'] != OK){
					die(NO);
				}
				$details = $playerDetails['details'];
				$playerUsername = $details['user_name'];
				$playerEmail = $details['email'];
				//get site settings for player with player_id
				require_once MODELS_DIR . DS . 'MerchantModel.php';
				$modelMerchant = new MerchantModel();
				$site_settings = $modelMerchant->findSiteSettings($player_id);
				if($site_settings['status'] != OK){
					die(NO);
				}
				//send mail to player that his account is activated procedure
				$playerMailSendFrom = $site_settings['mail_address_from'];
				$playerMailAddress = $playerEmail;
				$playerSmtpServer = $site_settings['smtp_server_ip'];
				$siteImagesLocation = $site_settings['site_image_location'];
				$casinoName = $site_settings['casino_name'];
				$siteLink = $site_settings['site_link'];
				$contactLink = $site_settings['contact_url_link'];
				$supportLink = $site_settings['support_url_link'];
				$termsLink = $site_settings['terms_url_link'];
                $languageSettings = $site_settings['language_settings'];
				$playerMailRes = WebSiteEmailHelper::getPlayerBeforeAccountClosingContent($playerUsername,
				$siteImagesLocation, $casinoName, $siteLink, $supportLink, $termsLink, $contactLink, $fee, $currency, $next_fee_date, $reactivate_before_fee_date, $inactive_time, $languageSettings);
				$loggerMessage =  "Player with player_id: " . $player_id . " and player username: " . $playerUsername . " on mail address: " . $playerMailAddress . " has not received mail that his account has been closed due to inactivity. ";
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
				$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				die(YES);
			}else{
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("Parameters have not passed to send mail to player for his account closure notification.");
				die(NO);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			die(NO);
		}
	}

    //called by database to notify system players
	public function afterClosingAccountForPlayerAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		//test if ip address allowed to access - test from database ip address
		if(!$this->isWhiteListedIP()){
			die(NO);
		}
		try{
			$player_id = strip_tags($_REQUEST['player_id']);
			$fee = strip_tags($_REQUEST['fee']);
			$currency = strip_tags($_REQUEST['currency']);
			$next_fee_date = strip_tags($_REQUEST['next_fee_date']);
			$reactivate_before_fee_date = strip_tags($_REQUEST['reactivate_before_fee_date']);
			$inactive_time = strip_tags($_REQUEST['inactive_time']);
			if(isset($player_id) && isset($fee) && isset($currency)
			&& isset($next_fee_date) && isset($reactivate_before_fee_date) && isset($inactive_time)) {
                $bo_session_id = null;
				require_once MODELS_DIR . DS . 'PlayerModel.php';
				$modelPlayer = new PlayerModel();
				//get player details
				$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
				if($playerDetails['status'] != OK){
					die(NO);
				}
				$details = $playerDetails['details'];
				$playerUsername = $details['user_name'];
				$playerEmail = $details['email'];
				//get site settings for player with player_id
				require_once MODELS_DIR . DS . 'MerchantModel.php';
				$modelMerchant = new MerchantModel();
				$site_settings = $modelMerchant->findSiteSettings($player_id);
				if($site_settings['status'] != OK){
					die(NO);
				}
				//send mail to player that his account is activated procedure
				$playerMailSendFrom = $site_settings['mail_address_from'];
				$playerMailAddress = $playerEmail;
				$playerSmtpServer = $site_settings['smtp_server_ip'];
				$siteImagesLocation = $site_settings['site_image_location'];
				$casinoName = $site_settings['casino_name'];
				$siteLink = $site_settings['site_link'];
				$contactLink = $site_settings['contact_url_link'];
				$supportLink = $site_settings['support_url_link'];
				$termsLink = $site_settings['terms_url_link'];
                $languageSettings = $site_settings['language_settings'];
				$playerMailRes = WebSiteEmailHelper::getPlayerForAccountClosingContent($playerUsername,
				$siteImagesLocation, $casinoName, $siteLink, $supportLink, $termsLink, $contactLink, $fee, $currency, $next_fee_date, $reactivate_before_fee_date, $inactive_time, $languageSettings);
				$loggerMessage =  "Player with player_id: " . $player_id . " and player username: " . $playerUsername . " on mail address: " . $playerMailAddress . " has not received mail that his account has been closed due to inactivity. ";
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
				$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
				die(YES);
			}else{
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("Parameters have not passed to send mail to player for his account closure notification.");
				die(NO);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			die(NO);
		}
	}


}