<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 * 
 * Web site web service PLAYER ACCOUNT SETUP ...
 *
 */

class WebSitePlayerAccountSetupManager {

    /**
	 * Change status of terms and conditions for player to CBC
	 * @param int $session_id
	 * @return mixed
	 */
	public static function ChangeTermsAndConditionsForCBC($session_id){
	    try{
			if (strlen($session_id) == 0){
		    	$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			    exit($json_message);
			}
			$session_id = strip_tags($session_id);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$result = $modelWebSite->changeTermsForCBC($session_id);
            if($result['status'] == OK){
                $json_message = Zend_Json::encode(array("status"=>OK, "message"=>$result['message']));
                exit($json_message);
            }else{
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			    exit($json_message);
            }
	    }catch (Zend_Exception $ex){
	    	$errorHelper = new ErrorHelper();
	    	$message = "WebSitePlayerAccountSetupManager::ChangeTermsAndConditionsForCBC(session_id = {$session_id})<br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$errorHelper->siteError($message, $message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
	    }
	}

    /**
	 * Change status of terms and conditions for player to GGL
	 * @param int $session_id
	 * @return mixed
	 */
	public static function ChangeTermsAndConditionsForGGL($session_id){
	    try{
			if (strlen($session_id) == 0){
		    	$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			    exit($json_message);
			}
			$session_id = strip_tags($session_id);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$result = $modelWebSite->changeTermsForGGL($session_id);
            if($result['status'] == OK){
                $json_message = Zend_Json::encode(array("status"=>OK, "message"=>$result['message']));
                exit($json_message);
            }else{
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			    exit($json_message);
            }
	    }catch (Zend_Exception $ex){
	    	$errorHelper = new ErrorHelper();
	    	$message = "WebSitePlayerAccountSetupManager::CheckTermsAndConditions(session_id = {$session_id})<br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$errorHelper->siteError($message, $message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
	    }
	}

    /**
	 * Check status of terms and conditions for player
	 * @param int $session_id
	 * @return mixed
	 */
	public static function CheckTermsAndConditions($session_id){
	    try{
			if (strlen($session_id) == 0){
		    	$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			    exit($json_message);
			}
			$session_id = strip_tags($session_id);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$result = $modelWebSite->checkTermsAndConditions($session_id);
			if($result['status'] == OK){
                $json_message = Zend_Json::encode(array("status"=>OK, "cbc_status"=>$result["cbc_status"], "ggl_status"=>$result["ggl_status"]));
                exit($json_message);
            }else{
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			    exit($json_message);
            }
	    }catch (Zend_Exception $ex){
	    	$errorHelper = new ErrorHelper();
	    	$message = "WebSitePlayerAccountSetupManager::CheckTermsAndConditions(session_id = {$session_id})<br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$errorHelper->siteError($message, $message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
	    }
	}

	/**
	 * This is new procedure with fixed response
	 * Web service checks if ID is still valid. If is valid then returns OK or if not returns NOK status.	
	 * @param string $username
	 * @param int $id
	 * @return string
	 */
	public static function CheckTemporaryID($username, $id){
	    try{
			// checks if all parameters are send (does not call for stored procedure if data are not received)
			if (!isset($id) || !isset($username)){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			    exit($json_message);
			}
			$username = strip_tags($username);	
			require_once MODELS_DIR . DS . 'WebSiteModel.php';		
			$modelWebSite = new WebSiteModel();
			// in return array there will be ID and EMAIL to send email to player
			$status = $modelWebSite->CheckTemporaryID($username, $id);
            $json_message = Zend_Json::encode(array("status"=>OK, "result"=>$status));
            exit($json_message);
	    }catch (Zend_Exception $ex){
	    	$errorHelper = new ErrorHelper();	    	
	    	$message = "Error while checking player temporary id on web site. <br /> Player username: {$username} <br /> Checking player temporary id Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$errorHelper->siteError($message, $message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
	    }
	}

	/**
	 * This is fixed procedure with new response
	 * Web service calls for db stored procedure to change password.
	 * If something is wrong (ID is invalid or expired) stored procedure returns NOK 
	 * if everything is fine then returns OK status.
	 * @param string $username
	 * @param int $id
	 * @param string $password
	 * @param string $question
	 * @param string $answer
	 * @return string
	 */
	public static function ChangePassword($username, $id, $password, $question, $answer){
	    try{
			// Checks if all parameters are send (not to call db stored procedure if not received all parameters)
			if (!isset($username) || !isset($id) || !isset($password) || !isset($question) || !isset($answer)){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			    exit($json_message);
			}
			$username = strip_tags($username);
			$password = strip_tags($password);
			$question = strip_tags($question);
			$answer = strip_tags($answer);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';		
			$modelWebSite = new WebSiteModel();
			//in return array will be ID and EMAIL address from player to send email to
			$result = $modelWebSite->ChangePassword($username, $id, $password, $question, $answer);
            $json_message = Zend_Json::encode(array("status"=>OK, "result"=>$result));
            exit($json_message);
	    }catch (Zend_Exception $ex){
	    	$errorHelper = new ErrorHelper();
	    	$mail_message = "Error while changing player's password. <br /> Player username: {$username} <br /> ID: {$id} 
	    	<br /> Question: {$question} <br /> Answer: {$answer} <br /> Checking player temporary id Exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$log_message = "Change Password Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
	    }
	}

	/**
	 * This is new procedure with fixed response	
	 * Web service calls for db stored procedure to set secure question and answer
	 * If it is successfully returns OK if not returns NOK
	 * @param string $username
	 * @param string $password
	 * @param string $question
	 * @param string $answer
	 * @return string
	 */
	public static function SetSecurityQuestionAnswer($username, $password, $question, $answer){
	    try{
			// checks if all parameters are send (not to call for db stored procedure if not all parameters received)
			if (!isset($username) || !isset($password) || !isset($question) || !isset($answer)){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			    exit($json_message);
			}
			$username = strip_tags($username);
			$password = strip_tags($password);
			$question = strip_tags($question);
			$answer = strip_tags($answer);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';		
			$modelWebSite = new WebSiteModel();
			// in returning array there will be ID and EMAIL to send email to player 
			$status = $modelWebSite->SetSecurityQuestionAnswer($username, $password, $question, $answer);
            $json_message = Zend_Json::encode(array("status"=>OK, "result"=>$status));
            exit($json_message);
	    }catch (Zend_Exception $ex){
	    	$errorHelper = new ErrorHelper();
	    	$mail_message = "Error while setting security question answer. <br /> Player username: {$username} <br /> 
	    	Question: {$question} <br /> Answer: {$answer} <br /> Checking player temporary id Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$log_message = "Set Security Question Answer Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
	    }
	}

	/**
	 * This is procedure with fixed response
	 * Web service calls for db stored procedure to read security question
	 * If there was an success returns OK if there is an error returns NOK
	 * @param string $username
	 * @return mixed
	 */
	public static function GetSecurityQuestion($username){
	    try{
			//checks of all parameters are sent (not to call for db stored procedure if all data are not received)
			if (!isset($username)){
			    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			    exit($json_message);
			}
			$username = strip_tags($username);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';	
			$modelWebSite = new WebSiteModel();
			//in returing array will be ID and EMAIL address to send email to player to						
			$arrData = $modelWebSite->GetSecurityQuestionAnswer($username);
            $json_message = Zend_Json::encode(array("status"=>OK, "result"=>$arrData['question']));
            exit($json_message);
	    }catch (Zend_Exception $ex){
	    	$errorHelper = new ErrorHelper();	    	
	    	$mail_message = "Error while getting security question. <br /> Player username: {$username} 
	    	<br /> Get Security Question Answer Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$log_message = "Get Security Question Answer Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
	    	$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
	    }
	}

	/**
	 * 
	 * Procedure that sets player limits for playing in casino with fixed response to client ...
	 * @param int $session_id
	 * @param int $monthly_deposit_limit
	 * @param string $monthly_deposit_limit_start_date
	 * @param string $monthly_deposit_limit_end_date
	 * @param int $weekly_deposit_limit
	 * @param string $weekly_deposit_start_date
	 * @param string $weekly_deposit_end_date
	 * @param int $daily_deposit_limit
	 * @param string $daily_deposit_start_date
	 * @param string $daily_deposit_end_date
	 * @param int $monthly_max_loss_limit
	 * @param string $monthly_max_loss_start_date
	 * @param string $monthly_max_loss_end_date
	 * @param int $weekly_max_loss_limit
	 * @param string $weekly_max_loss_start_date
	 * @param string $weekly_max_loss_end_date
	 * @param int $daily_max_loss_limit
	 * @param string $daily_max_loss_start_date
	 * @param string $daily_max_loss_end_date
	 * @param string $max_stake_start_date
	 * @param string $max_stake_end_date
	 * @param float $max_stake
	 * @param int $time_limit_minutes
	 * @param string $banned_start_date
	 * @param string $banned_end_date
	 * @param int $banned_status
	 * @return mixed
	 */
	public static function responsibleGamingSetup($session_id,
		$monthly_deposit_limit = 0, $monthly_deposit_limit_start_date = "", $monthly_deposit_limit_end_date = "", 
		$weekly_deposit_limit = 0, $weekly_deposit_start_date = "", $weekly_deposit_end_date = "",
		$daily_deposit_limit = 0, $daily_deposit_start_date = "", $daily_deposit_end_date = "",
		$monthly_max_loss_limit = 0, $monthly_max_loss_start_date = "", $monthly_max_loss_end_date = "",
		$weekly_max_loss_limit = 0, $weekly_max_loss_start_date = "", $weekly_max_loss_end_date = "",
		$daily_max_loss_limit = 0, $daily_max_loss_start_date = "", $daily_max_loss_end_date = "",
		$max_stake_start_date = "", $max_stake_end_date = "", $max_stake = 0.00, $time_limit_minutes = 0, 
		$banned_start_date = "", $banned_end_date = "", $banned_status = 0){
		try{
			if(!isset($session_id)){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			    exit($json_message);
			}
			$session_id = strip_tags($session_id);
			$monthly_deposit_limit = strip_tags($monthly_deposit_limit);
			$monthly_deposit_limit_start_date = strip_tags($monthly_deposit_limit_start_date);
			$monthly_deposit_limit_end_date = strip_tags($monthly_deposit_limit_end_date);
			$weekly_deposit_limit = strip_tags($weekly_deposit_limit);
			$weekly_deposit_start_date = strip_tags($weekly_deposit_start_date);
			$weekly_deposit_end_date = strip_tags($weekly_deposit_end_date);
			$daily_deposit_limit = strip_tags($daily_deposit_limit);
			$daily_deposit_start_date = strip_tags($daily_deposit_start_date);
			$daily_deposit_end_date = strip_tags($daily_deposit_end_date);
			$monthly_max_loss_limit = strip_tags($monthly_max_loss_limit);
			$monthly_max_loss_start_date = strip_tags($monthly_max_loss_start_date);
			$monthly_max_loss_end_date = strip_tags($monthly_max_loss_end_date);
			$weekly_max_loss_limit = strip_tags($weekly_max_loss_limit);
			$weekly_max_loss_start_date = strip_tags($weekly_max_loss_start_date);
			$weekly_max_loss_end_date = strip_tags($weekly_max_loss_end_date);
			$daily_max_loss_limit = strip_tags($daily_max_loss_limit);
			$daily_max_loss_start_date = strip_tags($daily_max_loss_start_date);
			$daily_max_loss_end_date = strip_tags($daily_max_loss_end_date);
			$max_stake_start_date = strip_tags($max_stake_start_date);
			$max_stake_end_date = strip_tags($max_stake_end_date);
			$max_stake = strip_tags($max_stake);
			$time_limit_minutes = strip_tags($time_limit_minutes);
			$banned_start_date = strip_tags($banned_start_date);
			$banned_end_date = strip_tags($banned_end_date);
			$banned_status = strip_tags($banned_status);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			//process monthly deposit limit
			require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
			$modelSubjectTypes = new SubjectTypesModel();
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_DEPOSIT_MONTHLY');
			$res = $modelWebSite->manageLimits($session_id, $name, $monthly_deposit_limit, $monthly_deposit_limit_start_date, $monthly_deposit_limit_end_date, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
			    exit($json_message);
			}
			//process weekly deposit limit
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_DEPOSIT_WEEKLY');
			$res = $modelWebSite->manageLimits($session_id, $name, $weekly_deposit_limit, $weekly_deposit_start_date, $weekly_deposit_end_date, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
			    exit($json_message);
			}
			//process daily deposit limit
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_DEPOSIT_DAILY');
			$res = $modelWebSite->manageLimits($session_id, $name, $daily_deposit_limit, $daily_deposit_start_date, $daily_deposit_end_date, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
			    exit($json_message);
			}
			//process maximum loss stake monthly
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_LOSS_MONTHLY');
			$res = $modelWebSite->manageLimits($session_id, $name, $monthly_max_loss_limit, $monthly_max_loss_start_date, $monthly_max_loss_end_date, null); //poslednji je bio max_loss_duration koji je uklonjen kao nepotreban pa se salje null
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
			    exit($json_message);
			}
			//process maximum loss stake weekly
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_LOSS_WEEKLY');
			$res = $modelWebSite->manageLimits($session_id, $name, $weekly_max_loss_limit, $weekly_max_loss_start_date, $weekly_max_loss_end_date, null); //poslednji je bio max_loss_duration koji je uklonjen kao nepotreban pa se salje null
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
			    exit($json_message);
			}
			//process maximum loss stake daily
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_LOSS_DAILY');
			$res = $modelWebSite->manageLimits($session_id, $name, $daily_max_loss_limit, $daily_max_loss_start_date, $daily_max_loss_end_date, null); //poslednji je bio max_loss_duration koji je uklonjen kao nepotreban pa se salje null
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
			    exit($json_message);
			}			
			//process maximum stake limit
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.NAME_IN_MAXIMUM_STAKE');
			$res = $modelWebSite->manageLimits($session_id, $name, $max_stake, $max_stake_start_date, $max_stake_end_date, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
			    exit($json_message);
			}
			//process time limit spent in casino
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.NAME_IN_TIME_LIMIT');
			$res = $modelWebSite->manageLimits($session_id, $name, $time_limit_minutes, null, null, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
			    exit($json_message);
			}
			//process banned user limit
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.NAME_IN_BANNED_USER');
			$res = $modelWebSite->manageLimits($session_id, $name, $banned_status, $banned_start_date, $banned_end_date, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
			    exit($json_message);
			}			
			$json_message = Zend_Json::encode(array("status"=>OK, "result"=>OK));
            exit($json_message);
		}catch(Zend_Exception $ex){		
			$errorHelper = new ErrorHelper();
			$mail_message = "Error while player setting his responsible gaming setup. <br /> Session ID: {$session_id}
			<br /> Responsible Gaming Setup Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}
	}
	
	/**
	*
	* Procedure that sets player limits with delay for playing in casino ...
	* @param int $session_id
	* @param int $monthly_deposit_limit
	* @param string $monthly_deposit_limit_start_date
	* @param string $monthly_deposit_limit_end_date
	* @param int $weekly_deposit_limit
	* @param string $weekly_deposit_start_date
	* @param string $weekly_deposit_end_date
	* @param int $daily_deposit_limit
	* @param string $daily_deposit_start_date
	* @param string $daily_deposit_end_date
	* @param int $monthly_max_loss_limit
	* @param string $monthly_max_loss_start_date
	* @param string $monthly_max_loss_end_date
	* @param int $weekly_max_loss_limit
	* @param string $weekly_max_loss_start_date
	* @param string $weekly_max_loss_end_date
	* @param int $daily_max_loss_limit
	* @param string $daily_max_loss_start_date
	* @param string $daily_max_loss_end_date
	* @param string $max_stake_start_date
	* @param string $max_stake_end_date
	* @param float $max_stake
	* @param int $time_limit_minutes
	* @param string $banned_start_date
	* @param string $banned_end_date
	* @param int $banned_status
	* @return mixed
	*/
	public static function responsibleGamingSetupDelay($session_id,
	$monthly_deposit_limit = 0, $monthly_deposit_limit_start_date = "", $monthly_deposit_limit_end_date = "",
	$weekly_deposit_limit = 0, $weekly_deposit_start_date = "", $weekly_deposit_end_date = "",
	$daily_deposit_limit = 0, $daily_deposit_start_date = "", $daily_deposit_end_date = "",
	$monthly_max_loss_limit = 0, $monthly_max_loss_start_date = "", $monthly_max_loss_end_date = "",
	$weekly_max_loss_limit = 0, $weekly_max_loss_start_date = "", $weekly_max_loss_end_date = "",
	$daily_max_loss_limit = 0, $daily_max_loss_start_date = "", $daily_max_loss_end_date = "",
	$max_stake_start_date = "", $max_stake_end_date = "", $max_stake = 0.00, $time_limit_minutes = 0,
	$banned_start_date = "", $banned_end_date = "", $banned_status = 0){
			try{
			if(!isset($session_id)){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			    exit($json_message);
			}
			$session_id = strip_tags($session_id);
			$monthly_deposit_limit = strip_tags($monthly_deposit_limit);
			$monthly_deposit_limit_start_date = strip_tags($monthly_deposit_limit_start_date);
			$monthly_deposit_limit_end_date = strip_tags($monthly_deposit_limit_end_date);
			$weekly_deposit_limit = strip_tags($weekly_deposit_limit);
			$weekly_deposit_start_date = strip_tags($weekly_deposit_start_date);
			$weekly_deposit_end_date = strip_tags($weekly_deposit_end_date);
			$daily_deposit_limit = strip_tags($daily_deposit_limit);
			$daily_deposit_start_date = strip_tags($daily_deposit_start_date);
			$daily_deposit_end_date = strip_tags($daily_deposit_end_date);
			$monthly_max_loss_limit = strip_tags($monthly_max_loss_limit);
			$monthly_max_loss_start_date = strip_tags($monthly_max_loss_start_date);
			$monthly_max_loss_end_date = strip_tags($monthly_max_loss_end_date);
			$weekly_max_loss_limit = strip_tags($weekly_max_loss_limit);
			$weekly_max_loss_start_date = strip_tags($weekly_max_loss_start_date);
			$weekly_max_loss_end_date = strip_tags($weekly_max_loss_end_date);
			$daily_max_loss_limit = strip_tags($daily_max_loss_limit);
			$daily_max_loss_start_date = strip_tags($daily_max_loss_start_date);
			$daily_max_loss_end_date = strip_tags($daily_max_loss_end_date);
			$max_stake_start_date = strip_tags($max_stake_start_date);
			$max_stake_end_date = strip_tags($max_stake_end_date);
			$max_stake = strip_tags($max_stake);
			$time_limit_minutes = strip_tags($time_limit_minutes);
			$banned_start_date = strip_tags($banned_start_date);
			$banned_end_date = strip_tags($banned_end_date);
			$banned_status = strip_tags($banned_status);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			//process monthly deposit limit
			require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
			$modelSubjectTypes = new SubjectTypesModel();
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_DEPOSIT_MONTHLY');
			$res = $modelWebSite->manageLimitsWDelay($session_id, $name, $monthly_deposit_limit, $monthly_deposit_limit_start_date, $monthly_deposit_limit_end_date, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
                exit($json_message);
			}
			//process weekly deposit limit
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_DEPOSIT_WEEKLY');
			$res = $modelWebSite->manageLimitsWDelay($session_id, $name, $weekly_deposit_limit, $weekly_deposit_start_date, $weekly_deposit_end_date, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
                exit($json_message);
			}
			//process daily deposit limit
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_DEPOSIT_DAILY');
			$res = $modelWebSite->manageLimitsWDelay($session_id, $name, $daily_deposit_limit, $daily_deposit_start_date, $daily_deposit_end_date, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
                exit($json_message);
			}
			//process maximum loss stake monthly
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_LOSS_MONTHLY');
			$res = $modelWebSite->manageLimitsWDelay($session_id, $name, $monthly_max_loss_limit, $monthly_max_loss_start_date, $monthly_max_loss_end_date, null); //poslednji je bio max_loss_duration koji je uklonjen kao nepotreban pa se salje null
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
                exit($json_message);
			}
			//process maximum loss stake weekly
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_LOSS_WEEKLY');
			$res = $modelWebSite->manageLimitsWDelay($session_id, $name, $weekly_max_loss_limit, $weekly_max_loss_start_date, $weekly_max_loss_end_date, null); //poslednji je bio max_loss_duration koji je uklonjen kao nepotreban pa se salje null
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
                exit($json_message);
			}
			//process maximum loss stake daily
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_LOSS_DAILY');
			$res = $modelWebSite->manageLimitsWDelay($session_id, $name, $daily_max_loss_limit, $daily_max_loss_start_date, $daily_max_loss_end_date, null); //poslednji je bio max_loss_duration koji je uklonjen kao nepotreban pa se salje null
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
                exit($json_message);
			}
			
			//process maximum wager limit - POSLEDNJI DODATI
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.MAXIMUM_WAGER_DAILY');
			$res = $modelWebSite->manageLimitsWDelay($session_id, $name, $max_stake, $max_stake_start_date, $max_stake_end_date, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
                exit($json_message);
			}
			
			//process time limit spent in casino
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.NAME_IN_TIME_LIMIT');
			$res = $modelWebSite->manageLimitsWDelay($session_id, $name, $time_limit_minutes, null, null, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
                exit($json_message);
			}			
			//process banned user limit
			$name = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.NAME_IN_BANNED_USER');
			$res = $modelWebSite->manageLimitsWDelay($session_id, $name, $banned_status, $banned_start_date, $banned_end_date, null);
			if(!$res){
				$json_message = Zend_Json::encode(array("status"=>NOK, "result"=>NOK));
                exit($json_message);
			}
			$json_message = Zend_Json::encode(array("status"=>OK, "result"=>OK));
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();			
			$mail_message = "Error while player setting his responsible gaming setup with delay. <br /> Session ID: {$session_id} <br /> Responsible Gaming Setup Delay Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}
	}
	
	/**
	* Ban player
	* @param string $username
	* @param string $password
	* @return mixed
	*/
	public static function banPlayer($username, $password){
		try{
			if(!isset($username) || !isset($password)){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			    exit($json_message);
			}
			$username = strip_tags($username);
			$password = strip_tags($password);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$result = $modelWebSite->banPlayer($username, $password);
            if($result['status'] == OK) {
                $json_message = Zend_Json::encode(array("status"=>OK, "result"=>YES));
                exit($json_message);
            }else{
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>$result['message']));
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();			
			$mail_message = "Error while player banned himself on web site. <br /> Player name: {$username} <br /> Player banned himself on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "Player banned himself on web site exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}
	}
	
	/**
	* Web service to unlock players account locked from sending his wrong password 
	* @param int $player_id
	* @return string
	*/
	public static function unlockAccount($player_id){
		try{
			// checks if all parameters are send (does not call for stored procedure if data are not received)
			if (!isset($player_id)){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			    exit($json_message);
			}
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			// in return array there will be ID and EMAIL to send email to player
			$result = $modelWebSite->resetWrongLoginsLeft($player_id);
			if($result['status'] == OK){
                $json_message = Zend_Json::encode(array("status"=>OK));
                exit($json_message);
            }else{
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
                exit($json_message);
            }
		}catch (Zend_Exception $ex){
			$errorHelper = new ErrorHelper();			
			$mail_message = "Error while unlocking player account on web site. <br /> Player id: {$player_id} <br /> Unlocking player account Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "Check Temporary ID Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);			
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}
	}
}