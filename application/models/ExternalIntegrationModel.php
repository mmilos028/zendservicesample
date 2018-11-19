<?php
require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class ExternalIntegrationModel {
	//error constants
	public function __construct(){
	}

    //login player to game client with token for external integrations
    /**
     * @param $token
     * @param $ip_address
     * @param string $mobile_y_n
     * @return array
     * @throws Zend_Exception
     */
	public function loginPlayerByToken($token, $ip_address, $mobile_y_n = NO){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$pc_session_id = "";
        $site_session_id = "";
		$player_id = "";
		$username = "";
		$credits = "";
		$currency = "";
		try{
			$stmt = $dbAdapter->prepare('CALL EXTERNAL_INTEGRATION.LOGIN_PLAYER_BY_TOKEN(:p_token_in, :p_ip_address_in, :p_mobile_y_n_in, :p_pc_sess_id_out, :p_site_sess_id_out, :p_player_id_out, :p_player_username_out, :p_currency_out, :p_credits_out, :p_list_of_games_out, :p_error_message)');
			$stmt->bindParam(':p_token_in', $token);
			$stmt->bindParam(':p_ip_address_in', $ip_address);
            $stmt->bindParam(':p_mobile_y_n_in', $mobile_y_n);
			$stmt->bindParam(':p_pc_sess_id_out', $pc_session_id, SQLT_CHR, 255);
            $stmt->bindParam(':p_site_sess_id_out', $site_session_id, SQLT_CHR, 255);
			$stmt->bindParam(':p_player_id_out', $player_id, SQLT_CHR, 255);
			$stmt->bindParam(':p_player_username_out', $username, SQLT_CHR, 255);
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_of_games_out', $cursor);
			$error_message = "";
			$stmt->bindParam(':p_error_message', $error_message, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG THIS HERE
			//$errorHelper = new ErrorHelper();
			//$message = "EXTERNAL_INTEGRATION.LOGIN_PLAYER_BY_TOKEN(:p_token_in = {$token}, :p_ip_address_in = {$ip_address}, :p_pc_sess_id_out = {$pc_session_id}, :p_site_sess_id_out = {$site_session_id}, :p_player_id_out = {$player_id}, :p_player_username_out = {$username}, :p_currency_out = {$currency}, :p_credits_out = {$credits}, :p_list_of_games_out, :p_error_message = {$error_message})";
			//$errorHelper->serviceError($message, $message);
			return array("status"=>OK, "token"=>$token, "ip_address"=>$ip_address, "mobile_y_n"=>$mobile_y_n, "player_id"=>$player_id, "pc_session_id"=>$pc_session_id, "site_session_id"=>$site_session_id, "username"=>$username, "credits"=>$credits, "currency"=>$currency, "message"=>$error_message, "list_games"=>$cursor);
		}catch(Zend_Db_Cursor_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			if($ex->getMessage() == "Couldn't execute the cursor."){
				//will not send email to administrators if cursor from games could not be executed
				$message = "AuthorizationModel::loginPlayerByToken <br /> EXTERNAL_INTEGRATION.LOGIN_PLAYER_BY_TOKEN(:p_token_in = {$token}, :p_ip_address_in = {$ip_address}, :p_mobile_y_n_in = {$mobile_y_n}, :p_pc_sess_id_out, :p_site_sess_id_out, :p_player_id_out, :p_player_username_out, :p_currency_out, :p_credits_out, :p_list_of_games_out, :p_error_message) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceErrorLog($message);
				return array("status"=>NOK, "player_id"=>$player_id, "pc_session_id"=>$pc_session_id, "site_session_id"=>$site_session_id, "credits"=>$credits, "currency"=>$currency, "message"=>$message, "list_games"=>array());
			}else{
				//if not a could not execute cursor error then send on email
				$message = "AuthorizationModel::loginPlayerByToken <br /> EXTERNAL_INTEGRATION.LOGIN_PLAYER_BY_TOKEN(:p_token_in = {$token}, :p_ip_address_in = {$ip_address}, :p_mobile_y_n_in = {$mobile_y_n}, :p_pc_sess_id_out, :p_site_sess_id_out, :p_player_id_out, :p_player_username_out, :p_currency_out, :p_credits_out, :p_list_of_games_out, :p_error_message) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);
				return array("status"=>NOK, "player_id"=>$player_id, "pc_session_id"=>$pc_session_id, "site_session_id"=>$site_session_id, "credits"=>$credits, "currency"=>$currency, "message"=>$message, "list_games"=>array());
			}
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "AuthorizationModel::loginPlayerByToken <br /> EXTERNAL_INTEGRATION.LOGIN_PLAYER_BY_TOKEN(:p_token_in = {$token}, :p_ip_address_in = {$ip_address}, :p_mobile_y_n_in = {$mobile_y_n}, :p_pc_sess_id_out, :p_site_sess_id_out, :p_player_id_out, :p_player_username_out, :p_currency_out, :p_credits_out, :p_list_of_games_out, :p_error_message) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>$message);
		}
	}

	//get token from our system for player session
    /**
     * @param $username
     * @param $password
     * @param $player_id
     * @param $player_name
     * @param $player_currency
     * @param $white_label_id
     * @param $parent_affiliate_id
     * @param $player_path
     * @return array
     * @throws Zend_Exception
     */
	public function getToken($username, $password, $player_id, $player_name, $player_currency, $white_label_id, $parent_affiliate_id, $player_path){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		//DEBUG THIS HERE
		//$message = "INPUT EXTERNAL_INTEGRATION.GET_TOKEN(:P_AFF_USERNAME = {$username}, :P_AFF_PASSWORD = {$password}, :P_EXT_AFF_NAME = {$player_id}, :P_EXT_WL_NAME = {$white_label_id}, :P_PLAYER_PATH = {$player_path}, :P_EXT_PLAYER_ID = {$player_id}, :P_EXT_PLAYER_NAME = {$player_name}, :P_EXT_PLAYER_CURR = {$player_currency}, :P_ERROR_MESSAGE, :P_TOKEN_OUT)";
		//$errorHelper = new ErrorHelper();
		//$errorHelper->externalIntegrationAccess($message, $message);
		try{
			$stmt = $dbAdapter->prepare('CALL EXTERNAL_INTEGRATION.GET_TOKEN(:P_AFF_USERNAME, :P_AFF_PASSWORD, :P_EXT_AFF_NAME, :P_EXT_WL_NAME, :P_PLAYER_PATH, :P_EXT_PLAYER_ID, :P_EXT_PLAYER_NAME, :P_EXT_PLAYER_CURR, :P_ERROR_MESSAGE, :P_TOKEN_OUT)');
			$stmt->bindParam(':P_AFF_USERNAME', $username);
			$stmt->bindParam(':P_AFF_PASSWORD', $password);
			$stmt->bindParam(':P_EXT_AFF_NAME', $parent_affiliate_id);
            $stmt->bindParam(':P_EXT_WL_NAME', $white_label_id);
            $stmt->bindParam(':P_PLAYER_PATH', $player_path);
			$stmt->bindParam(':P_EXT_PLAYER_ID', $player_id);
            $stmt->bindParam(':P_EXT_PLAYER_NAME', $player_name);
			$stmt->bindParam(':P_EXT_PLAYER_CURR', $player_currency);
			$error_message = "";
			$stmt->bindParam(':P_ERROR_MESSAGE', $error_message, SQLT_CHR, 255);
			$token = "";
			$stmt->bindParam(':P_TOKEN_OUT', $token, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG THIS HERE
			//$message = "OUTPUT EXTERNAL_INTEGRATION.GET_TOKEN(:P_AFF_USERNAME = {$username}, :P_AFF_PASSWORD = {$password}, :P_EXT_AFF_NAME = {$player_id}, :P_EXT_WL_NAME = {$white_label_id}, :P_PLAYER_PATH = {$player_path}, :P_EXT_PLAYER_ID = {$player_id}, :P_EXT_PLAYER_NAME = {$player_name}, :P_EXT_PLAYER_CURR = {$player_currency}, :P_ERROR_MESSAGE, :P_TOKEN_OUT)";
			//$errorHelper = new ErrorHelper();
			//$errorHelper->externalIntegrationAccess($message, $message);
			return array("status"=>OK, "error_message"=>$error_message, "token"=>$token);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "database_message"=>$message, "database_code"=>$code);
		}
	}

    //close player session, notified by external integration client
    /**
     * @param $player_id
     * @return array
     * @throws Zend_Exception
     */
	public function closePcAndSiteSession($player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		//DEBUG THIS HERE
		//$message = "INPUT EXTERNAL_INTEGRATION.CLOSE_PLAYER_SESSION(:player_id = {$player_id})";
		//$errorHelper = new ErrorHelper();
		//$errorHelper->externalIntegrationAccess($message, $message);
		try{
			//$stmt = $dbAdapter->prepare('CALL EXTERNAL_INTEGRATION.CLOSE_PLAYER_SESSIONS(:player_id)');
            $stmt = $dbAdapter->prepare('CALL EXTERNAL_INTEGRATION.BREAK_PLAYER_SESSION(:p_player_id_in)');
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG THIS HERE
			//$message = "OUTPUT EXTERNAL_INTEGRATION.CLOSE_PLAYER_SESSION(:player_id = {$player_id})";
			//$errorHelper = new ErrorHelper();
			//$errorHelper->externalIntegrationAccess($message, $message);
			return array("status"=>OK, "player_id"=>$player_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "database_message"=>$message, "database_code"=>$code);
		}
	}

		//close player session, notified by external integration client
		/**
		 * @param $external_player_id
		 * @return array
		 * @throws Zend_Exception
		 */
	public function getInternalPlayerId($external_player_id){
		/* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL EXTERNAL_INTEGRATION.get_player_id_for_external(:p_external_player, :p_subject_id_out)');
			$stmt->bindParam(':p_external_player', $external_player_id);
			$internal_player_id = '';
			$stmt->bindParam(':p_subject_id_out', $internal_player_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "external_player_id"=>$external_player_id, "internal_player_id"=>$internal_player_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "database_message"=>$message, "database_code"=>$code);
		}
	}
}
