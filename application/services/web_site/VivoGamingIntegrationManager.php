<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
/**
 *
 * Class to make internal calls to Vivo Gaming casino web service ...
 *
 */
class VivoGamingIntegrationManager {


	private $DEBUG = false;

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
				$message = 'Vivo Gaming Integration service: Host with blacklisted ip address ' . $host_ip_address . ' is trying to connect to Vivo Gaming integration web service.';
				$errorHelper->siteError($message, $message);
			}
			return $status;
		} else
			return true;
	}

	/**
	 *
	 * Get Token from Vivo Gaming Casino here ...
	 * @param int $pc_session_id
     * @param int $player_id
     * @param float $credits
     * @param string $game_id
	 * @return mixed
	 */
	public function getVivoGamingToken($pc_session_id, $player_id, $credits, $game_id){
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(!isset($pc_session_id)){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		//DEBUG MESSAGES
		if($this->DEBUG){
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = "getVivoGamingToken(pc_session_id = {$pc_session_id}, player_id = {$player_id}, credits = {$credits}, game_id = {$game_id})";
			$errorHelper->vivoGamingIntegrationError($mail_message, $log_message);
		}
		try{
			$pc_session_id = intval(strip_tags($pc_session_id));

            /*
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId(intval($pc_session_id));
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($pc_session_id, $arrPlayer['player_id']);
			if($playerDetails['status'] != OK){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			$details = $playerDetails['details'];
            $credits = $details['credits'];
            $player_id = $details['player_id'];
            */

        require_once MODELS_DIR . DS . 'VivoGamingIntegrationModel.php';
				$modelVivoGaming = new VivoGamingIntegrationModel();
        $result = $modelVivoGaming->getVivoGamingIntegrationToken($pc_session_id, $player_id, $credits, $game_id);
        if($result['status'] != OK){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
	    			$message = "Error VivoGamingIntegrationManager::getVivoGamingToken(pc_session_id = {$pc_session_id}, player_id = {$player_id}, credits = {$credits}, game_id = {$game_id}) web service. <br /> Detected IP Address = {$detected_ip_address}";
	    			$errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }

        $config = Zend_Registry::get('config');
        $bet_soft_operator_id = $config->vivoGamingBetSoftOperatorId;

        $live_game_unified_operator_id = $config->vivoGamingLiveGameUnifiedOperatorId;

        $spinomenal_operator_id = $config->vivoGamingSpinomenalOperatorId;
        $spinomenal_partner_id = $config->vivoGamingSpinomenalPartnerId;

        $tom_horn_operator_id = $config->vivoGamingTomHornOperatorId;
        $tom_horn_partner_id = $config->vivoGamingTomHornPartnerId;

        return array(
            "status"=>OK,
            "token"=>$result['token'],
            "bet_soft_operator_id"=>$bet_soft_operator_id,
            "bet_soft_partner_id"=>"",
            "live_game_unified_operator_id"=>$live_game_unified_operator_id,
            "live_game_unified_partner_id"=>"",
            "spinomenal_operator_id"=>$spinomenal_operator_id,
            "spinomenal_partner_id"=>$spinomenal_partner_id,
            "tom_horn_operator_id"=>$tom_horn_operator_id,
            "tom_horn_partner_id"=>$tom_horn_partner_id
        );
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error getToken on Vivo Gaming integration service: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	 *
	 * close vivo integration session ...
	 * @param int $pc_session_id
	 * @return mixed
	 */
	public function closeVivoIntegrationSession($pc_session_id){
        //DEBUG MESSAGES
    if($this->DEBUG){
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = "Vivo Gaming integration pc_session_id = {$pc_session_id}";
			$errorHelper->vivoGamingIntegrationError($mail_message, $log_message);
		}
		if(!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(!isset($pc_session_id)){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
				$pc_session_id = intval(strip_tags($pc_session_id));

	      require_once MODELS_DIR . DS . 'VivoGamingIntegrationModel.php';
				$modelVivoGaming = new VivoGamingIntegrationModel();
	      $result = $modelVivoGaming->closeVivoIntegrationSession($pc_session_id);
	      if($result['status'] != OK){
	          $errorHelper = new ErrorHelper();
	          $detected_ip_address = IPHelper::getRealIPAddress();
	    			$message = "Error VivoGamingIntegrationManager::closeVivoIntegrationSession(pc_session_id = {$pc_session_id}) web service. <br /> Detected IP Address = {$detected_ip_address}";
	    			$errorHelper->serviceError($message, $message);
	          return array("status"=>NOK, "message"=>NOK_EXCEPTION);
	      }
	      return array(
	          "status"=>OK,
	          "status_out"=>$result['status_out']
	      );
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error closeVivoIntegrationSession on Vivo Gaming integration service: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
}
