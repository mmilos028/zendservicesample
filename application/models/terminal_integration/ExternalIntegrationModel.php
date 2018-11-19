<?php
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'SubjectTypesModel.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class ExternalIntegrationModel {
	//error constants
	public function __construct(){
	}
	/*
	 PROCEDURE GET_TOKEN ( p_aff_username     IN subjects.name%type,
                        p_aff_password     IN subjects.password%type,
                        ext_aff_id         IN VARCHAR2,
                        ext_player_id      IN VARCHAR2,
                        ext_player_curr    IN VARCHAR2,
                        p_error_message    OUT VARCHAR2,
                        p_token_out        OUT VARCHAR2
  )
	*/
	//get token from our system for player session
	public function getToken($affiliate_username, $affiliate_password, $external_player_id, $external_player_currency, $external_affiliate_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		//DEBUG THIS HERE
		//$message = "INPUT EXTERNAL_INTEGRATION.GET_TOKEN(:p_aff_username = {$affiliate_username}, :p_aff_password = {$affiliate_password}, :ext_aff_id = {$external_affiliate_id}, :ext_player_id = {$external_player_id}, :ext_player_curr = {$external_player_currency}, :p_error_message, :p_token_out)";
		//$errorHelper = new ErrorHelper();
		//$errorHelper->externalIntegrationAccess($message, $message);
		
		try{
			$stmt = $dbAdapter->prepare('CALL EXTERNAL_INTEGRATION.GET_TOKEN(:p_aff_username, :p_aff_password, :ext_aff_id, :ext_player_id, :ext_player_curr, :p_error_message, :p_token_out)');
			$stmt->bindParam(':p_aff_username', $affiliate_username);
			$stmt->bindParam(':p_aff_password', $affiliate_password);
			$stmt->bindParam(':ext_aff_id', $external_affiliate_id);
			$stmt->bindParam(':ext_player_id', $external_player_id);
			$stmt->bindParam(':ext_player_curr', $external_player_currency);
			$error_message = "";
			$stmt->bindParam(':p_error_message', $error_message, SQLT_CHR, 255);
			$token = "";
			$stmt->bindParam(':p_token_out', $token, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG THIS HERE
			//$message = "OUTPUT EXTERNAL_INTEGRATION.GET_TOKEN(:p_aff_username = {$affiliate_username}, :p_aff_password = {$affiliate_password}, :ext_aff_id = {$external_affiliate_id}, :ext_player_id = {$external_player_id}, :ext_player_curr = {$external_player_currency}, :p_error_message = {$error_message}, :p_token_out = {$token})";
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
	public function closePcAndSiteSession($player_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		//DEBUG THIS HERE
		//$message = "INPUT EXTERNAL_INTEGRATION.CLOSE_PLAYER_SESSION(:player_id = {$player_id})";
		//$errorHelper = new ErrorHelper();
		//$errorHelper->externalIntegrationAccess($message, $message);
		try{
			$stmt = $dbAdapter->prepare('CALL EXTERNAL_INTEGRATION.CLOSE_PLAYER_SESSIONS(:player_id)');
			$stmt->bindParam(':player_id', $player_id);
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
}