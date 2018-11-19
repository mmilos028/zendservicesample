<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
/**
 * 
 * Class to make internal calls to Vivo Gaming integration casino web service ...
 *
 */
class WebSiteVivoGamingIntegrationManager {

	/**
	 * 
	 * Get encrypted token from Vivo Gaming integration casino here ...
	 * @param int $pc_session_id
     * @param int $player_id
     * @param float $credits
     * @param string $provider_id
	 * @return mixed
	 */
	public static function getVivoGamingToken($pc_session_id, $player_id, $credits, $provider_id){
		if(!isset($pc_session_id)){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		//DEBUG MESSAGES
		/*
		$errorHelper = new ErrorHelper();
		$mail_message = $log_message = "Vivo Gaming integration pc_session_id = {$pc_session_id}";
		$errorHelper->vivoGamingIntegrationError($mail_message, $log_message);
		*/
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
            $player_id = $arrPlayer['player_id'];
            $credits = $details['credits_restricted'];
            */

            require_once MODELS_DIR . DS . 'VivoGamingIntegrationModel.php';
			$modelVivoGaming = new VivoGamingIntegrationModel();
            $result = $modelVivoGaming->getVivoGamingIntegrationToken($pc_session_id, $player_id, $credits, $provider_id);
            if($result['status'] != OK){
                $errorHelper = new ErrorHelper();
                $detected_ip_address = IPHelper::getRealIPAddress();
			    $message = "Error VivoGamingIntegrationManager::getVivoGamingToken(pc_session_id = {$pc_session_id}, player_id = {$player_id}, credits = {$credits}) web service. <br /> Detected IP Address = {$detected_ip_address}";
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

            $resultArray = array(
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

            //DEBUG HERE
            /*
            $errorHelper = new ErrorHelper();
			$message = "WebSiteVivoGamingIntegrationManager::getVivoGamingToken({$pc_session_id})
            <br /> token = {$result['token']}";
			$errorHelper->siteAccess($message, $message);
            */
            $json_message = Zend_Json::encode($resultArray);
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error getVivoGamingToken on Vivo Gaming integration service: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}	
	}

    /**
	 *
	 * Get encrypted token from Vivo Gaming integration casino here ...
	 * @param int $pc_session_id
	 * @return mixed
	 */
	public static function closeVivoGamingIntegrationSession($pc_session_id){
		if(!isset($pc_session_id)){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		//DEBUG MESSAGES
		/*
		$errorHelper = new ErrorHelper();
		$mail_message = $log_message = "Vivo Gaming integration pc_session_id = {$pc_session_id}";
		$errorHelper->vivoGamingIntegrationError($mail_message, $log_message);
		*/
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
            $player_id = $arrPlayer['player_id'];
            $credits = $details['credits_restricted'];
            */

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

            $resultArray = array(
                "status"=>OK,
                "status_out"=>$result['status_out']
            );

            //DEBUG HERE
            /*
            $errorHelper = new ErrorHelper();
			$message = "WebSiteVivoGamingIntegrationManager::closeVivoIntegrationSession({$pc_session_id})
            <br /> token = {$result['token']}";
			$errorHelper->siteAccess($message, $message);
            */
            $json_message = Zend_Json::encode($resultArray);
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error closeVivoIntegrationSession on Vivo Gaming integration service: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}
	}
}