<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class WebSiteBonusManager {

    /**
	* cancel bonus transactions
	* @return mixed
	*/
	public static function cancelBonusTransactions($pc_session_id){
		if(!isset($pc_session_id) || $pc_session_id == 0){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			exit($json_message);
		}
		$pc_session_id = strip_tags($pc_session_id);
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$result = $modelWebSiteBonus->cancelBonusTransactions($pc_session_id);
            if($result['status'] == OK){
                $json_message = Zend_Json::encode(array("status"=>OK, "pc_session_id"=>$result['pc_session_id'], "message"=>$result['message']));
                exit($json_message);
            }else{
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>$result['message']));
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteBonusManager::cancelBonusTransactions >> Error in canceling bonus transactions for player from web site. <br /> PC session id: {$pc_session_id} <br /> Canceling bonus transactions for player on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

	/**
	*
	* @return mixed
	*/
	public static function checkBonusCode($pc_session_id, $bonus_campaign_code, $deposit_amount){
		if(!isset($pc_session_id) || $pc_session_id == 0 || strlen($pc_session_id) == 0 || !isset($bonus_campaign_code) || strlen($bonus_campaign_code) == 0 || !isset($deposit_amount) || strlen($deposit_amount) == 0){
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$result = $modelWebSiteBonus->checkBonusCode($pc_session_id, $bonus_campaign_code, $deposit_amount);
            if($result['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                        "pc_session_id"=>$result['pc_session_id'],
                        "bonus_campaign_code"=>$result['bonus_campaign_code'],
                        "deposit_amount"=> NumberHelper::convert_double($result['deposit_amount']),
                        "deposit_amount_formatted"=> NumberHelper::format_double($result['deposit_amount']),
                        "bonus_code_status"=>$result['bonus_code_status'],
                        "error_message"=>$result['error_message']
                    )
                );
                exit($json_message);
            }
            else
            {
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>$result['message']));
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteBonusManager::checkBonusCode method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSiteBonusManager::checkBonusCode method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

	/**
	*
	* @return mixed
	*/
	public static function checkBonusCodeStatus($pc_session_id, $bonus_campaign_code, $deposit_amount){
		if(!isset($pc_session_id) || $pc_session_id == 0 || strlen($pc_session_id) == 0 || !isset($bonus_campaign_code) || strlen($bonus_campaign_code) == 0 || !isset($deposit_amount) || strlen($deposit_amount) == 0){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$result = $modelWebSiteBonus->checkBonusCodeStatus($pc_session_id, $bonus_campaign_code, $deposit_amount);
            if($result['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                        "message"=>
                            array(
                                "status"=>OK, "pc_session_id"=>$result['pc_session_id'],
                                "bonus_campaign_code"=>$result['bonus_campaign_code'],
                                "deposit_amount"=>NumberHelper::convert_double($result['deposit_amount']),
                                "deposit_amount_formatted"=>NumberHelper::format_double($result['deposit_amount']),
                                "bonus_code_exists_status"=>$result['bonus_code_exists_status']
                            )
                    )
                );
                exit($json_message);
            }else{
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>$result['message']));
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteBonusManager::checkBonusCodeStatus method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSiteBonusManager::checkBonusCodeStatus method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}
	}

	/**
	 *
	 * List player bonus history method ...
	 * @return mixed
	 */
	public static function listPlayerBonusHistory($session_id){
		if(!isset($session_id) || strlen($session_id) == 0){
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$arrData = $modelWebSiteBonus->listPlayersBonusHistory($session_id);
			if($arrData['status'] == NOK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>$arrData['message'], "error_code"=>$arrData['error_code'], "details"=>$$arrData['details']));
                exit($json_message);
			}
			$resArray = array();
			foreach($arrData['report'] as $cur){
				$resArray[] = array(
					"bonus_start_date" => $cur['bonus_start_date'],
					"campaign_name" => $cur['campaign_name'],
					"bonus_amount" => NumberHelper::convert_double($cur['bonus_amount']),
					"bonus_amount_formatted" => NumberHelper::format_double($cur['bonus_amount']),
					"bonus_balance_status" => $cur['bonus_balance_status'],
                    "expire_date"=> $cur['bonus_expire_date']
				);
			}
            $json_message = Zend_Json::encode(array(
				"status"=>OK,
				"data"=>$resArray
			));
            exit($json_message);
		}catch(Zend_Exception $ex){
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION, "error_code"=>$ex->getCode(), "details"=>$message));
            exit($json_message);
		}
	}
	
	/**
	* Check Bonus Available For Country
	* @param string $affiliate_name
	* @param string $ip_address
	* @return mixed
	*/
	public function checkBonusAvailableForCountry($affiliate_name, $ip_address){
		if(strlen($affiliate_name) == 0 || strlen($ip_address) == 0){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		$affiliate_name = strip_tags($affiliate_name);
		$ip_address = strip_tags($ip_address);
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$result = $modelWebSiteBonus->checkBonusAvailableForCountry($ip_address, $affiliate_name);
            if($result['status'] == OK){
				if($result['country_is_prohibited'] == "1"){
					//country is not prohibited
					$status_country_is_prohibited = NO;
				}else{
					//country is prohibited
					$status_country_is_prohibited = YES;
				}
				$json_message = Zend_Json::encode(array("status"=>OK, "country_is_prohibited"=>$status_country_is_prohibited));
				exit($json_message);
            }else{
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
				exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteBonusManager::checkBonusAvailableForCountry method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSiteBonusManager::checkBonusAvailableForCountry method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}
	}

}