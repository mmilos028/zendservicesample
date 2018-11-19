<?php
/**
*	Web service for external integration - transfer messages from our database
*/
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';

class ExternalIntegrationTransferController extends Zend_Controller_Action {
    //error list
	private $INVALID_ACCESS	               = 'INVALID_ACCESS';
	private $INVALID_ACCESS_ID					   = 100;
	private $MISSING_PARAMETERS            = 'MISSING_PARAMETERS';
	private $MISSING_PARAMETERS_ID				 = 101;
	private $UNKNOWN_ERROR								 = 'UNKNOWN_ERROR';
	private $UNKNOWN_ERROR_ID						   = 102;
	private $WEB_SERVICE_ERROR						 = 'WEB_SERVICE_ERROR';
	private $WEB_SERVICE_ERROR_ID					 = 103;
	//operation list
	private $GET_PLAYER_INFO						   = 'GET_PLAYER_INFO';
	private $POST_TRANSACTION						   = 'POST_TRANSACTION';
    private $CLOSE_PLAYER_SESSION        = 'CLOSE_PLAYER_SESSION';
    private $CHECK_TRANSACTION           = 'CHECK_TRANSACTION';

	private $DEBUG_REQUEST = false;
	private $DEBUG_RESPONSE = false;

	public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		//set output header Content-Type to application/json
		header('Content-Type: application/xml');
	}

	public function indexAction(){
		header('Location: http://www.google.com/');
	}

	//return true if ip address exists
	//return false if ip address not exists
	private function isWhiteListedIP(){
		return true;
		/*
		$config = Zend_Registry::get('config');
		//will check site ip address caller if true
		$ip_addresses = explode(' ', $config->db->ip_address);
		$host_ip_address = IPHelper::getRealIPAddress();
		if(!IPHelper::testPrivateIP($host_ip_address)){
			$errorHelper = new ErrorHelper();
			$message = "ExternalIntegrationController: Host with blacklisted ip address {$host_ip_address} is trying to connect to external integration message transfer web service.";
			$errorHelper->externalIntegrationError($message, $message);
		}
		$status = in_array($host_ip_address, $ip_addresses);
		if(!$status){
			$errorHelper = new ErrorHelper();
			$message = "ExternalIntegrationController: Host with blacklisted ip address {$host_ip_address} is trying to connect to external integration message transfer web service.";
			$errorHelper->externalIntegrationError($message, $message);
		}
		return $status;
		*/
	}

    /**
	 *
	 * this service is checking player credits from external integration
	 *
	 * @param string $affiliate_username
	 * @param string $affiliate_password
	 * @param string $player_id
	 * @param string $url
	 * @return mixed
	 */
	public function getPlayerInfoAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		if(!$this->isWhiteListedIP()){
			$response = "<RESPONSE>";
				$response .= "<STATUS>NOK</STATUS>";
				$response .= "<OPERATION>{$this->GET_PLAYER_INFO}</OPERATION>";
				$response .= "<MESSAGE_DESC>{$this->INVALID_ACCESS}</MESSAGE_DESC>";
				$response .= "<MESSAGE_CODE>{$this->INVALID_ACCESS_ID}</MESSAGE_CODE>";
			$response .= "</RESPONSE>";
			if($this->DEBUG_RESPONSE){
				//DEBUG HERE
				$errorHelper = new ErrorHelper();
				$errorHelper->externalIntegrationAccessLog("ExternalIntegrationController::getPlayerInfo DB Response = " . $response);
			}
			exit($response);
		}
		try{
			if(isset($_REQUEST['affiliate_username']) && isset($_REQUEST['affiliate_password']) && isset($_REQUEST['player_id']) && isset($_REQUEST['url'])) {
				$affiliate_username = urldecode(strip_tags(trim($_REQUEST['affiliate_username'])));
				$affiliate_password = strip_tags(trim($_REQUEST['affiliate_password']));
				$player_id = strip_tags(trim($_REQUEST['player_id']));
				$ws_url = urldecode(trim($_REQUEST['url']));
				//sending message to external client integration
				if($this->DEBUG_REQUEST){
					$errorHelper = new ErrorHelper();
					$errorHelper->externalIntegrationAccessLog("ExternalIntegrationController::getPlayerInfoAction (affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, url = {$ws_url})");
				}
				//CALLING CLIENT INTERFACE
				//to implement here decision which client interface to use
				//$start_call_time = microtime(true);
				//if(in_array($affiliate_username, array("MattiIntegration"))){
					//this is Matti integration object
					require_once SERVICES_DIR . DS . 'external_integration' . DS . 'RestXmlExternalIntegration.php';
					$integration = new RestXmlExternalIntegration();
					$integrationResult = $integration->getPlayerInfo($affiliate_username, $affiliate_password, $player_id, $ws_url);
				//}
				/*else{
					require_once SERVICES_DIR . DS . 'external_integration' . DS . 'RestJsonExternalIntegration.php';
					$integration = new RestJsonExternalIntegration();
					$integrationResult = $integration->getPlayerInfo($affiliate_username, $affiliate_password, $player_id, $ws_url);
				}*/
				//measure time to response remote server
				//$end_call_time = microtime(true);
				//$errorHelper = new ErrorHelper();
				//$message_measure = "ExternalIntegrationController::getPlayerInfoAction (affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, url = {$ws_url}) <br /> TIME REQUIRED TO RESPONSE = " . ($end_call_time - $start_call_time) . " seconds";
				//$errorHelper->externalIntegrationAccess($message_measure, $message_measure);

				if($integrationResult['status'] == OK){
					$response = "<RESPONSE>";
						$response .= "<STATUS>OK</STATUS>";
						$response .= "<OPERATION>{$this->GET_PLAYER_INFO}</OPERATION>";
						$response .= "<AFFILIATE_USERNAME>{$affiliate_username}</AFFILIATE_USERNAME>";
						$response .= "<PLAYER_ID>{$player_id}</PLAYER_ID>";
						$response .= "<URL>{$ws_url}</URL>";
						$response .= "<PLAYER_BALANCE>" . $integrationResult['player_balance'] . "</PLAYER_BALANCE>";
						$response .= "<PLAYER_CURRENCY>" . $integrationResult['player_currency'] . "</PLAYER_CURRENCY>";
					$response .= "</RESPONSE>";
					if($this->DEBUG_RESPONSE){
						//DEBUG HERE
						$errorHelper = new ErrorHelper();
						$errorHelper->externalIntegrationAccessLog("2 ExternalIntegrationController::getPlayerInfoAction DB Response = " . $response);
					}
					exit($response);
				}else{
					$response = "<RESPONSE>";
						$response .= "<STATUS>NOK</STATUS>";
						$response .= "<OPERATION>{$this->GET_PLAYER_INFO}</OPERATION>";
						$response .= "<AFFILIATE_USERNAME>{$affiliate_username}</AFFILIATE_USERNAME>";
						$response .= "<PLAYER_ID>{$player_id}</PLAYER_ID>";
						$response .= "<URL>{$ws_url}</URL>";
						$response .= "<MESSAGE_DESC>{$this->WEB_SERVICE_ERROR}</MESSAGE_DESC>";
						$response .= "<MESSAGE_CODE>{$this->WEB_SERVICE_ERROR_ID}</MESSAGE_CODE>";
					$response .= "</RESPONSE>";
					if($this->DEBUG_RESPONSE){
						//DEBUG HERE
						$errorHelper = new ErrorHelper();
						$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::getPlayerInfo DB Response = " . $response);
					}
					exit($response);
				}
			}else{
				$errorHelper = new ErrorHelper();
        $message = "ExternalIntegrationController::getPlayerInfoAction (affiliate_username = {$_REQUEST['affiliate_username']}, affiliate_password = {$_REQUEST['affiliate_password']}, player_id = {$_REQUEST['player_id']}, url = {$_REQUEST['url']})";
				$errorHelper->serviceError($message, $message);
				$response = "<RESPONSE>";
					$response .= "<STATUS>NOK</STATUS>";
					$response .= "<OPERATION>{$this->GET_PLAYER_INFO}</OPERATION>";
					$response .= "<AFFILIATE_USERNAME>{$_REQUEST['affiliate_username']}</AFFILIATE_USERNAME>";
					$response .= "<PLAYER_ID>{$_REQUEST['player_id']}</PLAYER_ID>";
					$response .= "<URL>{$_REQUEST['url']}</URL>";
					$response .= "<MESSAGE_DESC>{$this->MISSING_PARAMETERS}</MESSAGE_DESC>";
					$response .= "<MESSAGE_CODE>{$this->MISSING_PARAMETERS_ID}</MESSAGE_CODE>";
				$response .= "</RESPONSE>";
				if($this->DEBUG_RESPONSE){
					//DEBUG HERE
					$errorHelper = new ErrorHelper();
					$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::getPlayerInfo DB Response = " . $response);
				}
				exit($response);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			$response = "<RESPONSE>";
				$response .= "<STATUS>NOK</STATUS>";
				$response .= "<OPERATION>{$this->GET_PLAYER_INFO}</OPERATION>";
				$response .= "<MESSAGE_DESC>{$this->UNKNOWN_ERROR}</MESSAGE_DESC>";
				$response .= "<MESSAGE_CODE>{$this->UNKNOWN_ERROR_ID}</MESSAGE_CODE>";
			$response .= "</RESPONSE>";
			if($this->DEBUG_RESPONSE){
				//DEBUG HERE
				$errorHelper = new ErrorHelper();
				$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::getPlayerInfo DB Response = " . $response);
			}
			exit($response);
		}
	}

	/**
	 * this service is making external player transactions for external integration on internet
	 *
	 * @param string $affiliate_username
	 * @param string $affiliate_password
	 * @param string $player_id
	 * @param string $amount
	 * @param string $transaction_type
     * @param string $received_date
     * @param string $reservation_id
     * @param string $transaction_id
     * @param string $game_move_id
	 * @param string $game_id
	 * @param string $game_name
	 * @param string $url
	 * @return mixed
	 */
	public function postTransactionAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		if(!$this->isWhiteListedIP()){
			$response = "<RESPONSE>";
				$response .= "<STATUS>NOK</STATUS>";
				$response .= "<OPERATION>{$this->POST_TRANSACTION}</OPERATION>";
				$response .= "<MESSAGE_DESC>{$this->INVALID_ACCESS}</MESSAGE_DESC>";
				$response .= "<MESSAGE_CODE>{$this->INVALID_ACCESS_ID}</MESSAGE_CODE>";
			$response .= "</RESPONSE>";
			if($this->DEBUG_RESPONSE){
				//DEBUG HERE
				$errorHelper = new ErrorHelper();
				$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::postTransaction DB Response = " . $response);
			}
			exit($response);
		}
		try{
			if(isset($_REQUEST['affiliate_username']) && isset($_REQUEST['affiliate_password']) && isset($_REQUEST['player_id']) && isset($_REQUEST['amount']) &&
				isset($_REQUEST['transaction_type']) && isset($_REQUEST['game_id']) && isset($_REQUEST['game_name']) && isset($_REQUEST['url'])){
				$affiliate_username = urldecode(strip_tags(trim($_REQUEST['affiliate_username'])));
				$affiliate_password = strip_tags(trim($_REQUEST['affiliate_password']));
				$player_id = strip_tags(($_REQUEST['player_id']));
				$amount = strip_tags(trim($_REQUEST['amount']));
				$transaction_type = strip_tags(trim($_REQUEST['transaction_type']));
        $received_date = date('d-M-Y H:i:s', time());
        $game_transaction_id = strip_tags(trim($_REQUEST['transaction_id']));
        $game_move_id = strip_tags(trim($_REQUEST['game_move_id']));
				$game_id = strip_tags(trim($_REQUEST['game_id']));
				$game_name = urldecode($_REQUEST['game_name']);
				$ws_url = urldecode(trim($_REQUEST['url']));
        $reservation_id = trim($_REQUEST['reservation_id']);
				if($this->DEBUG_REQUEST){
					$errorHelper = new ErrorHelper();
					$errorHelper->externalIntegrationAccessLog("ExternalIntegrationController::postTransactionAction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, game_id = {$game_id}, game_name = {$game_name}, url = {$ws_url}, reservation_id = {$reservation_id})");
				}
				//CALLING CLIENT INTERFACE
				//to implement here desicion which client interface to use
				//$start_call_time = microtime(true);
				//if(in_array($affiliate_username, array("MattiIntegration"))){
					//this is Matti integration object
					require_once SERVICES_DIR . DS . 'external_integration' . DS . 'RestXmlExternalIntegration.php';
					$integration = new RestXmlExternalIntegration();
					$integrationResult = $integration->postTransaction($affiliate_username, $affiliate_password, $player_id, $amount, $transaction_type, $received_date, $reservation_id, $game_transaction_id, $game_move_id, $game_id, $game_name, $ws_url);
				/*}else{
					require_once SERVICES_DIR . DS . 'external_integration' . DS . 'RestJsonExternalIntegration.php';
					$integration = new RestJsonExternalIntegration();
					$integrationResult = $integration->postTransaction($affiliate_username, $affiliate_password, $player_id, $amount, $transaction_type, $game_id, $game_name, $ws_url);
				}*/
				//measure time to response remote server
				//$end_call_time = microtime(true);
				//$errorHelper = new ErrorHelper();
				//$message_measure = "ExternalIntegrationController::postTransactionAction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, game_id = {$game_id}, game_name = {$game_name}, url = {$ws_url}) <br /> TIME REQUIRED TO RESPONSE = " . ($end_call_time - $start_call_time) . " seconds";
				//$errorHelper->externalIntegrationAccess($message_measure, $message_measure);

				if($integrationResult['status'] == OK){
					$response = "<RESPONSE>";
						$response .= "<STATUS>OK</STATUS>";
						$response .= "<OPERATION>{$this->POST_TRANSACTION}</OPERATION>";
						$response .= "<AFFILIATE_USERNAME>{$affiliate_username}</AFFILIATE_USERNAME>";
						$response .= "<PLAYER_ID>{$player_id}</PLAYER_ID>";
						$response .= "<AMOUNT>{$amount}</AMOUNT>";
						$response .= "<TRANSACTION_TYPE>{$transaction_type}</TRANSACTION_TYPE>";
						$response .= "<GAME_ID>{$game_id}</GAME_ID>";
						$response .= "<GAME_NAME>{$game_name}</GAME_NAME>";
            $response .= "<TRANSACTION_ID>{$integrationResult['transaction_id']}</TRANSACTION_ID>";
						$response .= "<URL>{$ws_url}</URL>";
						$response .= "<PLAYER_BALANCE>{$integrationResult['player_balance']}</PLAYER_BALANCE>";
						$response .= "<PLAYER_CURRENCY>{$integrationResult['player_currency']}</PLAYER_CURRENCY>";
					$response .= "</RESPONSE>";
					if($this->DEBUG_RESPONSE){
						//DEBUG HERE
						$errorHelper = new ErrorHelper();
						$errorHelper->externalIntegrationAccessLog("ExternalIntegrationController::postTransaction DB Response = " . $response);
					}
					exit($response);
				}else{
					$response = "<RESPONSE>";
						$response .= "<STATUS>NOK</STATUS>";
						$response .= "<OPERATION>{$this->POST_TRANSACTION}</OPERATION>";
						$response .= "<AFFILIATE_USERNAME>{$affiliate_username}</AFFILIATE_USERNAME>";
						$response .= "<PLAYER_ID>{$player_id}</PLAYER_ID>";
						$response .= "<AMOUNT>{$amount}</AMOUNT>";
						$response .= "<TRANSACTION_TYPE>{$transaction_type}</TRANSACTION_TYPE>";
						$response .= "<GAME_ID>{$game_id}</GAME_ID>";
						$response .= "<GAME_NAME>{$game_name}</GAME_NAME>";
            $response .= "<TRANSACTION_ID></TRANSACTION_ID>";
						$response .= "<URL>{$ws_url}</URL>";
						$response .= "<MESSAGE_DESC>{$this->WEB_SERVICE_ERROR}</MESSAGE_DESC>";
						$response .= "<MESSAGE_CODE>{$this->WEB_SERVICE_ERROR_ID}</MESSAGE_CODE>";
					$response .= "</RESPONSE>";
					if($this->DEBUG_RESPONSE){
						//DEBUG HERE
						$errorHelper = new ErrorHelper();
						$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::postTransaction DB Response = " . $response);
					}
					exit($response);
				}
			}else{
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceErrorLog("ExternalIntegrationController::postTransactionAction(affiliate_username = {$_REQUEST['affiliate_username']}, affiliate_password = {$_REQUEST['affiliate_password']}, player_id = {$_REQUEST['player_id']}, amount = {$_REQUEST['amount']}, transaction_type = {$_REQUEST['transaction_type']}, game_id = {$_REQUEST['game_id']}, game_name = {$_REQUEST['game_name']}, url = {$_REQUEST['url']})");
				$response = "<RESPONSE>";
					$response .= "<STATUS>NOK</STATUS>";
					$response .= "<OPERATION>{$this->POST_TRANSACTION}</OPERATION>";
					$response .= "<AFFILIATE_USERNAME>{$_REQUEST['affiliate_username']}</AFFILIATE_USERNAME>";
					$response .= "<PLAYER_ID>{$_REQUEST['player_id']}</PLAYER_ID>";
					$response .= "<AMOUNT>{$_REQUEST['amount']}</AMOUNT>";
					$response .= "<TRANSACTION_TYPE>{$_REQUEST['transaction_type']}</TRANSACTION_TYPE>";
					$response .= "<GAME_ID>{$_REQUEST['game_id']}</GAME_ID>";
					$response .= "<GAME_NAME>{$_REQUEST['game_name']}</GAME_NAME>";
          $response .= "<TRANSACTION_ID></TRANSACTION_ID>";
					$response .= "<URL>{$_REQUEST['url']}</URL>";
					$response .= "<MESSAGE_DESC>{$this->MISSING_PARAMETERS}</MESSAGE_DESC>";
					$response .= "<MESSAGE_CODE>{$this->MISSING_PARAMETERS_ID}</MESSAGE_CODE>";
				$response .= "</RESPONSE>";
				if($this->DEBUG_RESPONSE){
					//DEBUG HERE
					$errorHelper = new ErrorHelper();
					$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::postTransaction DB Response = " . $response);
				}
				exit($response);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			$response = "<RESPONSE>";
				$response .= "<STATUS>NOK</STATUS>";
				$response .= "<OPERATION>{$this->POST_TRANSACTION}</OPERATION>";
				$response .= "<MESSAGE_DESC>{$this->UNKNOWN_ERROR}</MESSAGE_DESC>";
				$response .= "<MESSAGE_CODE>{$this->UNKNOWN_ERROR_ID}</MESSAGE_CODE>";
			$response .= "</RESPONSE>";
			if($this->DEBUG_RESPONSE){
				//DEBUG HERE
				$errorHelper = new ErrorHelper();
				$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::postTransaction DB Response = " . $response);
			}
			exit($response);
		}
	}

    //this service is notifying client that player has left games (closing player session) over internet for external integration
	/**
	 *
	 * this service is notifying for closed player session
	 *
	 * @param string $affiliate_username
	 * @param string $affiliate_password
	 * @param string $player_id
	 * @param string $url
	 * @return mixed
	 */
	public function closePlayerSessionAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		if(!$this->isWhiteListedIP()){
			$response = "<RESPONSE>";
				$response .= "<STATUS>NOK</STATUS>";
				$response .= "<OPERATION>{$this->CLOSE_PLAYER_SESSION}</OPERATION>";
				$response .= "<MESSAGE_DESC>{$this->INVALID_ACCESS}</MESSAGE_DESC>";
				$response .= "<MESSAGE_CODE>{$this->INVALID_ACCESS_ID}</MESSAGE_CODE>";
			$response .= "</RESPONSE>";
			if($this->DEBUG_RESPONSE){
				//DEBUG HERE
				$errorHelper = new ErrorHelper();
				$errorHelper->externalIntegrationAccessLog("ExternalIntegrationController::closePlayerSession DB Response = " . $response);
			}
			exit($response);
		}
		try{
			if(isset($_REQUEST['affiliate_username']) && isset($_REQUEST['affiliate_password']) && isset($_REQUEST['player_id']) && isset($_REQUEST['url'])) {
				$affiliate_username = urldecode(strip_tags(trim($_REQUEST['affiliate_username'])));
				$affiliate_password = strip_tags(trim($_REQUEST['affiliate_password']));
				$player_id = strip_tags(trim($_REQUEST['player_id']));
				$ws_url = urldecode(trim($_REQUEST['url']));
				//sending message to external client integration
				if($this->DEBUG_REQUEST) {
					$errorHelper = new ErrorHelper();
					$errorHelper->externalIntegrationAccessLog("ExternalIntegrationController::closePlayerSessionAction (affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, url = {$ws_url})");
				}
				//CALLING CLIENT INTERFACE
				//to implement here decision which client interface to use
				//$start_call_time = microtime(true);
				//if(in_array($affiliate_username, array("MattiIntegration"))){
					//this is Matti integration object
					require_once SERVICES_DIR . DS . 'external_integration' . DS . 'RestXmlExternalIntegration.php';
					$integration = new RestXmlExternalIntegration();
					$integrationResult = $integration->closePlayerSession($affiliate_username, $affiliate_password, $player_id, $ws_url);
				//}
				/*else{
					require_once SERVICES_DIR . DS . 'external_integration' . DS . 'RestJsonExternalIntegration.php';
					$integration = new RestJsonExternalIntegration();
					$integrationResult = $integration->closePlayerSession($affiliate_username, $affiliate_password, $player_id, $ws_url);
				}*/
				//measure time to response remote server
				//$end_call_time = microtime(true);
				//$errorHelper = new ErrorHelper();
				//$message_measure = "ExternalIntegrationController::closePlayerSession (affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, url = {$ws_url}) <br /> TIME REQUIRED TO RESPONSE = " . ($end_call_time - $start_call_time) . " seconds";
				//$errorHelper->externalIntegrationAccess($message_measure, $message_measure);

				if($integrationResult['status'] == OK){
					$response = "<RESPONSE>";
						$response .= "<STATUS>OK</STATUS>";
						$response .= "<OPERATION>{$this->CLOSE_PLAYER_SESSION}</OPERATION>";
						$response .= "<AFFILIATE_USERNAME>{$affiliate_username}</AFFILIATE_USERNAME>";
						$response .= "<PLAYER_ID>{$player_id}</PLAYER_ID>";
						$response .= "<URL>{$ws_url}</URL>";
					$response .= "</RESPONSE>";
					if($this->DEBUG_RESPONSE){
						//DEBUG HERE
						$errorHelper = new ErrorHelper();
						$errorHelper->externalIntegrationAccessLog("2 ExternalIntegrationController::closePlayerSession DB Response = " . $response);
					}
					exit($response);
				}else{
					$response = "<RESPONSE>";
						$response .= "<STATUS>NOK</STATUS>";
						$response .= "<OPERATION>{$this->CLOSE_PLAYER_SESSION}</OPERATION>";
						$response .= "<AFFILIATE_USERNAME>{$affiliate_username}</AFFILIATE_USERNAME>";
						$response .= "<PLAYER_ID>{$player_id}</PLAYER_ID>";
						$response .= "<URL>{$ws_url}</URL>";
						$response .= "<MESSAGE_DESC>{$this->WEB_SERVICE_ERROR}</MESSAGE_DESC>";
						$response .= "<MESSAGE_CODE>{$this->WEB_SERVICE_ERROR_ID}</MESSAGE_CODE>";
					$response .= "</RESPONSE>";
					if($this->DEBUG_RESPONSE){
						//DEBUG HERE
						$errorHelper = new ErrorHelper();
						$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::closePlayerSession DB Response = " . $response);
					}
					exit($response);
				}
			}else{
				$errorHelper = new ErrorHelper();
        $message = "ExternalIntegrationController::closePlayerSessionAction (affiliate_username = {$_REQUEST['affiliate_username']}, affiliate_password = {$_REQUEST['affiliate_password']}, player_id = {$_REQUEST['player_id']}, url = {$_REQUEST['url']})";
				$errorHelper->serviceError($message, $message);
				$response = "<RESPONSE>";
					$response .= "<STATUS>NOK</STATUS>";
					$response .= "<OPERATION>{$this->CLOSE_PLAYER_SESSION}</OPERATION>";
					$response .= "<AFFILIATE_USERNAME>{$_REQUEST['affiliate_username']}</AFFILIATE_USERNAME>";
					$response .= "<PLAYER_ID>{$_REQUEST['player_id']}</PLAYER_ID>";
					$response .= "<URL>{$_REQUEST['url']}</URL>";
					$response .= "<MESSAGE_DESC>{$this->MISSING_PARAMETERS}</MESSAGE_DESC>";
					$response .= "<MESSAGE_CODE>{$this->MISSING_PARAMETERS_ID}</MESSAGE_CODE>";
				$response .= "</RESPONSE>";
				if($this->DEBUG_RESPONSE){
					//DEBUG HERE
					$errorHelper = new ErrorHelper();
					$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::closePlayerSession DB Response = " . $response);
				}
				exit($response);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			$response = "<RESPONSE>";
				$response .= "<STATUS>NOK</STATUS>";
				$response .= "<OPERATION>{$this->CLOSE_PLAYER_SESSION}</OPERATION>";
				$response .= "<MESSAGE_DESC>{$this->UNKNOWN_ERROR}</MESSAGE_DESC>";
				$response .= "<MESSAGE_CODE>{$this->UNKNOWN_ERROR_ID}</MESSAGE_CODE>";
			$response .= "</RESPONSE>";
			if($this->DEBUG_RESPONSE){
				//DEBUG HERE
				$errorHelper = new ErrorHelper();
				$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::closePlayerSession DB Response = " . $response);
			}
			exit($response);
		}
	}

    /**
	 * this service is checking external player transactions for external integration on internet
	 *
	 * @param string $affiliate_username
	 * @param string $affiliate_password
	 * @param string $player_id
	 * @param string $amount
	 * @param string $transaction_type
     * @param string $received_date
     * @param string $reservation_id
     * @param string $transaction_id
     * @param string $game_move_id
	 * @param string $game_id
	 * @param string $game_name
	 * @param string $url
	 * @return mixed
	 */
	public function checkTransactionAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		if(!$this->isWhiteListedIP()){
			$response = "<RESPONSE>";
				$response .= "<STATUS>NOK</STATUS>";
				$response .= "<OPERATION>{$this->CHECK_TRANSACTION}</OPERATION>";
				$response .= "<MESSAGE_DESC>{$this->INVALID_ACCESS}</MESSAGE_DESC>";
				$response .= "<MESSAGE_CODE>{$this->INVALID_ACCESS_ID}</MESSAGE_CODE>";
			$response .= "</RESPONSE>";
			if($this->DEBUG_RESPONSE){
				//DEBUG HERE
				$errorHelper = new ErrorHelper();
				$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::checkTransaction DB Response = " . $response);
			}
			exit($response);
		}
		try{
			if(isset($_REQUEST['affiliate_username']) && isset($_REQUEST['affiliate_password']) && isset($_REQUEST['player_id']) && isset($_REQUEST['amount']) &&
				isset($_REQUEST['transaction_type']) && isset($_REQUEST['url'])){
				$affiliate_username = urldecode(strip_tags(trim($_REQUEST['affiliate_username'])));
				$affiliate_password = strip_tags(trim($_REQUEST['affiliate_password']));
				$player_id = strip_tags(($_REQUEST['player_id']));
				$amount = strip_tags(trim($_REQUEST['amount']));
				$transaction_type = strip_tags(trim($_REQUEST['transaction_type']));
        $received_date = date('d-M-Y H:i:s', time());
        $game_transaction_id = strip_tags(trim($_REQUEST['transaction_id']));
        $game_move_id = strip_tags(trim($_REQUEST['game_move_id']));
				$game_id = strip_tags(trim($_REQUEST['game_id']));
				$game_name = urldecode($_REQUEST['game_name']);
				$ws_url = urldecode(trim($_REQUEST['url']));
        $reservation_id = trim($_REQUEST['reservation_id']);
				if($this->DEBUG_REQUEST){
					$errorHelper = new ErrorHelper();
					$errorHelper->externalIntegrationAccessLog("ExternalIntegrationController::checkTransactionAction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, game_id = {$game_id}, game_name = {$game_name}, url = {$ws_url})");
				}
				//CALLING CLIENT INTERFACE
				require_once SERVICES_DIR . DS . 'external_integration' . DS . 'RestXmlExternalIntegration.php';
				$integration = new RestXmlExternalIntegration();
				$integrationResult = $integration->checkTransaction($affiliate_username, $affiliate_password, $player_id, $amount, $transaction_type, $received_date, $reservation_id, $game_transaction_id, $game_move_id, $game_id, $game_name, $ws_url);

				//measure time to response remote server
				//$end_call_time = microtime(true);
				//$errorHelper = new ErrorHelper();
				//$message_measure = "ExternalIntegrationController::checkTransactionAction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, game_id = {$game_id}, game_name = {$game_name}, url = {$ws_url}) <br /> TIME REQUIRED TO RESPONSE = " . ($end_call_time - $start_call_time) . " seconds";
				//$errorHelper->externalIntegrationAccess($message_measure, $message_measure);

				if($integrationResult['status'] == OK){
					$response = "<RESPONSE>";
						$response .= "<STATUS>OK</STATUS>";
                        $response .= "<TRANSACTION_STATUS>{$integrationResult['transaction_status']}</TRANSACTION_STATUS>";
						$response .= "<OPERATION>{$this->CHECK_TRANSACTION}</OPERATION>";
						$response .= "<AFFILIATE_USERNAME>{$affiliate_username}</AFFILIATE_USERNAME>";
						$response .= "<PLAYER_ID>{$integrationResult['player_id']}</PLAYER_ID>";
						$response .= "<AMOUNT>{$integrationResult['amount']}</AMOUNT>";
						$response .= "<TRANSACTION_TYPE>{$transaction_type}</TRANSACTION_TYPE>";
						$response .= "<GAME_ID>{$integrationResult['game_id']}</GAME_ID>";
						$response .= "<GAME_NAME>{$integrationResult['game_name']}</GAME_NAME>";
                        $response .= "<TRANSACTION_ID>{$integrationResult['transaction_id']}</TRANSACTION_ID>";
						$response .= "<URL>{$ws_url}</URL>";
						$response .= "<PLAYER_BALANCE>{$integrationResult['player_balance']}</PLAYER_BALANCE>";
						$response .= "<PLAYER_CURRENCY>{$integrationResult['player_currency']}</PLAYER_CURRENCY>";
					$response .= "</RESPONSE>";
					if($this->DEBUG_RESPONSE){
						//DEBUG HERE
						$errorHelper = new ErrorHelper();
						$errorHelper->externalIntegrationAccessLog("ExternalIntegrationController::checkTransaction DB Response = " . $response);
					}
					exit($response);
				}else{
					$response = "<RESPONSE>";
						$response .= "<STATUS>NOK</STATUS>";
                        $response .= "<TRANSACTION_STATUS>{$integrationResult['transaction_status']}</TRANSACTION_STATUS>";
						$response .= "<OPERATION>{$this->CHECK_TRANSACTION}</OPERATION>";
						$response .= "<AFFILIATE_USERNAME>{$affiliate_username}</AFFILIATE_USERNAME>";
						$response .= "<PLAYER_ID>{$player_id}</PLAYER_ID>";
						$response .= "<AMOUNT>{$amount}</AMOUNT>";
						$response .= "<TRANSACTION_TYPE>{$transaction_type}</TRANSACTION_TYPE>";
						$response .= "<GAME_ID>{$game_id}</GAME_ID>";
						$response .= "<GAME_NAME>{$game_name}</GAME_NAME>";
                        $response .= "<TRANSACTION_ID></TRANSACTION_ID>";
						$response .= "<URL>{$ws_url}</URL>";
						$response .= "<MESSAGE_DESC>{$this->WEB_SERVICE_ERROR}</MESSAGE_DESC>";
						$response .= "<MESSAGE_CODE>{$this->WEB_SERVICE_ERROR_ID}</MESSAGE_CODE>";
					$response .= "</RESPONSE>";
					if($this->DEBUG_RESPONSE){
						//DEBUG HERE
						$errorHelper = new ErrorHelper();
						$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::checkTransaction DB Response = " . $response);
					}
					exit($response);
				}
			}else{
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceErrorLog("ExternalIntegrationController::checkTransactionAction(affiliate_username = {$_REQUEST['affiliate_username']}, affiliate_password = {$_REQUEST['affiliate_password']}, player_id = {$_REQUEST['player_id']}, amount = {$_REQUEST['amount']}, transaction_type = {$_REQUEST['transaction_type']}, game_id = {$_REQUEST['game_id']}, game_name = {$_REQUEST['game_name']}, url = {$_REQUEST['url']})");
				$response = "<RESPONSE>";
					$response .= "<STATUS>NOK</STATUS>";
					$response .= "<OPERATION>{$this->CHECK_TRANSACTION}</OPERATION>";
					$response .= "<AFFILIATE_USERNAME>{$_REQUEST['affiliate_username']}</AFFILIATE_USERNAME>";
					$response .= "<PLAYER_ID>{$_REQUEST['player_id']}</PLAYER_ID>";
					$response .= "<AMOUNT>{$_REQUEST['amount']}</AMOUNT>";
					$response .= "<TRANSACTION_TYPE>{$_REQUEST['transaction_type']}</TRANSACTION_TYPE>";
					$response .= "<GAME_ID>{$_REQUEST['game_id']}</GAME_ID>";
					$response .= "<GAME_NAME>{$_REQUEST['game_name']}</GAME_NAME>";
          $response .= "<TRANSACTION_ID></TRANSACTION_ID>";
					$response .= "<URL>{$_REQUEST['url']}</URL>";
					$response .= "<MESSAGE_DESC>{$this->MISSING_PARAMETERS}</MESSAGE_DESC>";
					$response .= "<MESSAGE_CODE>{$this->MISSING_PARAMETERS_ID}</MESSAGE_CODE>";
				$response .= "</RESPONSE>";
				if($this->DEBUG_RESPONSE){
					//DEBUG HERE
					$errorHelper = new ErrorHelper();
					$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::checkTransaction DB Response = " . $response);
				}
				exit($response);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			$response = "<RESPONSE>";
				$response .= "<STATUS>NOK</STATUS>";
				$response .= "<OPERATION>{$this->CHECK_TRANSACTION}</OPERATION>";
				$response .= "<MESSAGE_DESC>{$this->UNKNOWN_ERROR}</MESSAGE_DESC>";
				$response .= "<MESSAGE_CODE>{$this->UNKNOWN_ERROR_ID}</MESSAGE_CODE>";
			$response .= "</RESPONSE>";
			if($this->DEBUG_RESPONSE){
				//DEBUG HERE
				$errorHelper = new ErrorHelper();
				$errorHelper->externalIntegrationErrorLog("ExternalIntegrationController::checkTransaction DB Response = " . $response);
			}
			exit($response);
		}
	}
}
