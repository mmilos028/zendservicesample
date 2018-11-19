<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 * 
 * Web site web service BONUS setup, reports ...
 *
 */

class WebSiteBonusManager {	
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
				$message = "WebSiteBonusManager service: Host with blacklisted ip address {$host_ip_address} is trying to connect to web site web service.";
				$errorHelper->siteError($message, $message);
			}
			return $status;
		} else{ 
			return true;
		}
	}

	/**
	* currency list for new player method by using affiliate_id
	* @param int $pc_session_id
	* @return mixed
	*/
	public function cancelBonusTransactions($pc_session_id){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(!isset($pc_session_id) || $pc_session_id == 0){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		$pc_session_id = strip_tags($pc_session_id);
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$result = $modelWebSiteBonus->cancelBonusTransactions($pc_session_id);			
            if($result['status'] == OK){
                return array("status"=>OK, "pc_session_id"=>$result['pc_session_id'], "message"=>$result['message']);
            }else{
                return array("status"=>NOK, "message"=>$result['message']);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteBonusManager::cancelBonusTransactions >> Error in canceling bonus transactions for player from web site. <br /> PC session id: {$pc_session_id} <br /> Canceling bonus transactions for player on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
	
	/**
	*
	* @param int $pc_session_id
	* @param string $bonus_campaign_code
	* @param double $deposit_amount
	* @return mixed
	*/
	public function checkBonusCode($pc_session_id, $bonus_campaign_code, $deposit_amount){
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(!isset($pc_session_id) || $pc_session_id == 0 || strlen($pc_session_id) == 0 || !isset($bonus_campaign_code) || strlen($bonus_campaign_code) == 0 || !isset($deposit_amount) || strlen($deposit_amount) == 0){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		$pc_session_id = strip_tags($pc_session_id);
		$bonus_campaign_code = strip_tags($bonus_campaign_code);
		$deposit_amount = strip_tags($deposit_amount);
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$result = $modelWebSiteBonus->checkBonusCode($pc_session_id, $bonus_campaign_code, $deposit_amount);
            if($result['status'] == OK){
                return array("status"=>OK, "pc_session_id"=>$result['pc_session_id'], "bonus_campaign_code"=>$result['bonus_campaign_code'],
                    "deposit_amount"=>$result['deposit_amount'], "bonus_code_status"=>$result['bonus_code_status'], "error_message"=>$result['error_message']);
            }
            else
            {
                return array("status"=>NOK, "message"=>$result['message']);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteBonusManager::checkBonusCode method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSiteBonusManager::checkBonusCode method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
	
	/**
	*
	* @param int $pc_session_id
	* @param string $bonus_campaign_code
	* @param double $deposit_amount
	* @return mixed
	*/
	public function checkBonusCodeStatus($pc_session_id, $bonus_campaign_code, $deposit_amount){
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(!isset($pc_session_id) || $pc_session_id == 0 || strlen($pc_session_id) == 0 || !isset($bonus_campaign_code) || strlen($bonus_campaign_code) == 0 || !isset($deposit_amount) || strlen($deposit_amount) == 0){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		$pc_session_id = strip_tags($pc_session_id);
		$bonus_campaign_code = strip_tags($bonus_campaign_code);
		$deposit_amount = strip_tags($deposit_amount);
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$result = $modelWebSiteBonus->checkBonusCodeStatus($pc_session_id, $bonus_campaign_code, $deposit_amount);
            if($result['status'] == OK){
                return array("status"=>OK, "pc_session_id"=>$result['pc_session_id'], "bonus_campaign_code"=>$result['bonus_campaign_code'],
                    "deposit_amount"=>$result['deposit_amount'], "bonus_code_exists_status"=>$result['bonus_code_exists_status']);
            }else{
                return array("status"=>NOK, "message"=>$result['message']);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteBonusManager::checkBonusCodeStatus method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSiteBonusManager::checkBonusCodeStatus method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * 
	 * List player bonus history method ...
	 * @param int $session_id
	 * @return mixed
	 */
	public function listPlayerBonusHistory($session_id){
		if(!$this->isSecureConnection()){
			return array(
				"status"=>NOK,
				"message"=>NON_SSL_CONNECTION
			);
		}
		if(!isset($session_id) || strlen($session_id) == 0){
			return array(
				"status"=>NOK,
				"message"=>NOK_INVALID_DATA
			);
		}
		$session_id = strip_tags($session_id);
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$arrData = $modelWebSiteBonus->listPlayersBonusHistory($session_id);
			if($arrData['status'] == NOK){
				return array("status"=>NOK, "message"=>$arrData['message'], "error_code"=>$arrData['error_code'], "details"=>$$arrData['details']);
			}
			$resArray = array();
			foreach($arrData['report'] as $cur){
				$resArray[] = array(
					"bonus_start_date" => $cur['bonus_start_date'], 
					"campaign_name" => $cur['campaign_name'], 
					"bonus_amount" => $cur['bonus_amount'], 
					"bonus_balance_status" => $cur['bonus_balance_status'],
                    "expire_date"=> $cur['bonus_expire_date']
				);
			}
			return array(
				"status"=>OK,
				"data"=>$resArray
			);
		}catch(Zend_Exception $ex){
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			return array(
				"status"=>NOK,
				"message"=>NOK_EXCEPTION,
				"error_code"=>$ex->getCode(),
				"details"=>$message
			);
		}
	}
	
	/**
	* Check Bonus Available For Country
	* If country_is_prohibited == YES then country is prohibited
	* If country_is_prohibited == NO then country is not prohibited
	* @param int $affiliate_id
	* @param string $ip_address
	* @return mixed
	*/
	public function checkBonusAvailableForCountry($affiliate_id, $ip_address){
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(strlen($affiliate_id) == 0 || strlen($ip_address) == 0){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		$affiliate_id = strip_tags($affiliate_id);
		$ip_address = strip_tags($ip_address);
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$result = $modelWebSiteBonus->checkBonusAvailableForCountry($ip_address, $affiliate_id);
            if($result['status'] == OK){
				if($result['country_is_prohibited'] == "1"){
					//country is not prohibited
					$status_country_is_prohibited = NO;
				}else{
					//country is prohibited
					$status_country_is_prohibited = YES;
				}
                return array("status"=>OK, "country_is_prohibited"=>$status_country_is_prohibited);
            }else{
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "checkBonusAvailableForCountry method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "checkBonusAvailableForCountry method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
}