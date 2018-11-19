<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
/**
 * 
 * Class to make internal calls to LDC (GGL) casino web service ...
 *
 */
class LdcIntegrationManager {	
	
	/**
	 * 
	 * tests if communication is done through whitelisted ip address range
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
				$message = 'GGL Integration service: Host with blacklisted ip address ' . $host_ip_address . ' is trying to connect to LDC (GGL) integration web service.';
				$errorHelper->siteError($message, $message);
			}
			return $status;
		} else 
			return true;
	}
	
	/**
	 * 
	 * Get encrypted token from LDC casino here ...
	 * @param int $site_session_id
	 * @return mixed
	 */
	public function getEncryptedToken($site_session_id){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(!isset($site_session_id)){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		//DEBUG MESSAGES
		/*
		$errorHelper = new ErrorHelper();
		$mail_message = $log_message = "getEncryptedToken site_session_id = {$site_session_id}";
		$errorHelper->ldcIntegrationError($mail_message, $log_message);
		*/
		try{
			$site_session_id = intval(strip_tags($site_session_id));
			$config = Zend_Registry::get('config');
            $ldcUser = $config->ldcUser;
            $ldcPassword = $config->ldcPassword;
			$ldcMasterClientWebServiceURL = $config->ldcMasterClientWebServiceURL;
			$client = new Zend_Soap_Client($ldcMasterClientWebServiceURL);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId(intval($site_session_id));
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($site_session_id, $arrPlayer['player_id']);
			if($playerDetails['status'] != OK){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			$details = $playerDetails['details'];

            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            $site_settings = $modelMerchant->findSiteSettings($arrPlayer['player_id']);
            $casino_name = $site_settings['casino_name'];
            switch($casino_name){
                case 'Casino400.com':
                    $ldcUser = 'MULTIWIN24';
                    $ldcPassword = '363194bdcdb66b8cc71d6361b81155b2';
                    break;
                case 'MultiWin24.com':
                    $ldcUser = 'MULTIWIN24';
                    $ldcPassword = '363194bdcdb66b8cc71d6361b81155b2';
                    break;
                case 'XLNTcasino.com':
                    $ldcUser = 'XLNTCASINO';
                    $ldcPassword = '6ef4e978efb2eecf02c5746c83b6474a';
                    break;
                default:
                    $ldcUser = $config->ldcUser;
			        $ldcPassword = $config->ldcPassword;
            }
			/* get encrypted token to open GGL (LDC) lobby for player */
			$paramsGetEncryptedToken = array(
			    "clientUser" => $ldcUser,
			    "clientPassword" => $ldcPassword,
			    "customerID" => $arrPlayer['player_id'],
			    "agentID" => $details['aff_id'],
			    "nickname" => $details['user_name']
			);
			$ldcLobbyURL = $config->ldcLobbyURL;
			//returns encrypted token string as result
			$responseGetEncryptedToken = (array)$client->getEncryptedToken($paramsGetEncryptedToken);
			//set currency to GGL (LDC) for this player
			$paramsSetPlayerCurrency = array(
				"clientUser" => $ldcUser,
				"clientPassword" => $ldcPassword,
				"customerID" => $arrPlayer['player_id'],
				"currency" => $details['currency']
			);
			//returns int 1 as accepted result
			$responseSetPlayerCurrencyCode = $client->setPlayerCurrencyCode($paramsSetPlayerCurrency);
			//$responseSetPlayerCurrencyCode['setPlayerCurrencyCodeResult'] //should always return 1			
			$token = $responseGetEncryptedToken['getEncryptedTokenResult'];

            //DEBUG HERE
            /*
            $errorHelper = new ErrorHelper();
			$message = "LdcIntegrationManager::getEncryptedToken({$site_session_id})
            <br /> ldcUser = {$ldcUser}
            <br /> ldcPassword = {$ldcPassword}
            <br /> agentID = {$details['aff_id']}
            <br /> nickname = {$details['user_name']}
            <br /> customerID = {$arrPlayer['player_id']}
            <br /> currency = {$details['currency']}
            <br /> token = {$token}";
			$errorHelper->siteAccess($message, $message);
            */
			return array("status"=>OK, "url"=>$ldcLobbyURL, "data"=>$token);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error getEncryptedToken on LDC integration service: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}	
	}

    /**
     * Check betkiosk game status
     * @param $pc_session_id
     * @return array
     */
    public function checkGGLGameStatus($pc_session_id){
        if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "checkGGLGameStatus(pc_session_id = {$pc_session_id})";
        $errorHelper->ldcIntegrationError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            require_once MODELS_DIR . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->checkGGLGameStatus($pc_session_id);
            if($result['status'] == OK) {
                return array("status" => OK, "status_out" => $result['status_out']);
            }else{
                return array("status" => NOK, "message" => NOK_EXCEPTION);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "GGL Integration Error <br />Error LdcIntegrationManager::checkGGLGameStatus(pc_session_id = {$pc_session_id}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->ldcIntegrationError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }
}