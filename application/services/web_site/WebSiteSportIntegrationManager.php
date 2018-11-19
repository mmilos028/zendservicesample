<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 * 
 * Web site web service sport integration calls ....
 *
 */

class WebSiteSportIntegrationManager {
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
	 *
	 * openCbcSession to open cbcx casino session
	 * @param int $pc_session_id
	 * @return mixed
	 */
	public function openCbcSession($pc_session_id){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(!isset($pc_session_id)){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		$pc_session_id = strip_tags($pc_session_id);
		try{
			require_once MODELS_DIR . DS . 'CbcxIntegrationModel.php';
			$modelCbcxIntegration = new CbcxIntegrationModel();
			$result = $modelCbcxIntegration->openCbcSession($pc_session_id);
            if($result['status'] == OK){
                return array("status"=>OK, "pc_session_id"=>$result['pc_session_id']);
            }else{
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
		}catch(Zend_Exception $ex){
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
     *
     * Get Sport Betting Session Opened - BETKIOSK integration
     * @param int $pc_session_id
     * @param string $ip_address
     * @return mixed
     */
    public function openBetKioskSession($pc_session_id, $ip_address){
        if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "openBetKioskSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address} )";
        $errorHelper->sportBettingIntegrationError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            $ip_address = strip_tags($ip_address);
            //test if ip address not sent by game client then autodetect it and sent autodetected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
            require_once MODELS_DIR . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->openBetKioskSession($pc_session_id, $ip_address);
            if($result['status'] == OK){
                return array("status"=>OK, "pc_session_id"=>$result['pc_session_id'], "session_id_out"=>$result['session_id_out']);
            }else{
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error AuthorizationManager::openBetKioskSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->sportBettingIntegrationError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
     *
     * Get Sport Betting Session Opened - MaxBet integration
     * @param int $pc_session_id
     * @param string $ip_address
     * @return mixed
     */
    public function openMaxBetSession($pc_session_id, $ip_address){
        if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "openMaxBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address} )";
        $errorHelper->sportBettingIntegrationError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            $ip_address = strip_tags($ip_address);
            //test if ip address not sent by game client then autodetect it and sent autodetected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
            require_once MODELS_DIR . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->openMaxBetSession($pc_session_id, $ip_address);
            if($result['status'] == OK) {
                return array("status" => OK, "pc_session_id" => $result['pc_session_id'], "session_id_out" => $result['session_id_out']);
            }else{
                return array("status" => NOK, "message" => NOK_EXCEPTION);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error AuthorizationManager::openMaxBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->sportBettingIntegrationError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
     *
     * Get Sport Betting Session Opened - MEMOBET integration
     * @param int $pc_session_id
     * @param string $ip_address
     * @return mixed
     */
    public function openMemoBetSession($pc_session_id, $ip_address){
        if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "openMemoBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address} )";
        $errorHelper->sportBettingIntegrationError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            $ip_address = strip_tags($ip_address);
            //test if ip address not sent by game client then autodetect it and sent autodetected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
            require_once MODELS_DIR . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->openMemoBetGameSession($pc_session_id, $ip_address);
            if($result['status'] == OK) {
                return array("status" => OK, "pc_session_id" => $result['pc_session_id'], "session_id_out" => $result['session_id_out']);
            }else{
                return array("status" => NOK, "message" => NOK_EXCEPTION);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error AuthorizationManager::openMemoBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->sportBettingIntegrationError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
     * Close sport betting window - X button event for closing window of sport betting game
     * @param $pc_session_id
     * @return array
     */
    public function closeSportBettingWindow($pc_session_id){
        if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "closeSportBettingWindow(pc_session_id = {$pc_session_id} )";
        $errorHelper->sportBettingIntegrationError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            require_once MODELS_DIR . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->closeSportBettingGameWindow($pc_session_id);
            if($result['status'] == OK) {
                return array("status" => OK, "session_id" => $result['session_id'], "status_out" => $result['status_out']);
            }else{
                return array("status" => NOK, "message" => NOK_EXCEPTION);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error AuthorizationManager::closeSportBettingWindow(pc_session_id = {$pc_session_id}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->sportBettingIntegrationError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
     * Check betkiosk game status
     * @param $pc_session_id
     * @return array
     */
    public function checkBetkioskGameStatus($pc_session_id){
        if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "checkBetkioskGameStatus(pc_session_id = {$pc_session_id})";
        $errorHelper->sportBettingIntegrationError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            require_once MODELS_DIR . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->checkBetkioskGameStatus($pc_session_id);
            if($result['status'] == OK) {
                return array("status" => OK, "status_out" => $result['status_out']);
            }else{
                return array("status" => NOK, "message" => NOK_EXCEPTION);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error WebSiteSportIntegrationManager::checkBetkioskGameStatus(pc_session_id = {$pc_session_id}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->sportBettingIntegrationError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }
}