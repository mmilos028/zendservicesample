<?php
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
class MessageTransferController extends Zend_Controller_Action{
	public function indexAction(){
		header('Location: http://www.google.com/');
	}
	//called by database
	//transfer message from database to game server for game clients
	
	//return true if ip address exists
	//return false if ip address not exists
	private function isWhiteListedIP(){
		$config = Zend_Registry::get('config');
		//will check site ip address caller if true
		$ip_addresses = explode(' ', $config->db->ip_address);
		$host_ip_address = IPHelper::getRealIPAddress();
		if(!IPHelper::testPrivateIP($host_ip_address)){
			$errorHelper = new ErrorHelper();
			$message = 'MessageTransfer service: Host with blacklisted ip address ' . $host_ip_address . ' is trying to connect to message transfer web service.';
			$errorHelper->sendMail($message);
			$errorHelper->siteErrorLog($message);
		}
		$status = in_array($host_ip_address, $ip_addresses);
		if(!$status){
			$errorHelper = new ErrorHelper();
			$message = 'MessageTransfer service: Host with blacklisted ip address ' . $host_ip_address . ' is trying to connect to message transfer web service.';
			$errorHelper->sendMail($message);
			$errorHelper->siteErrorLog($message);
		}
		return $status;
	}
	
	//this service is used to update session status
	public function sessionStatusUpdateAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		if(!$this->isWhiteListedIP()){
			die(NO);
		}
		try{
			if(isset($_REQUEST['parent_session_id']) && isset($_REQUEST['session_id']) && isset($_REQUEST['session_name'])) {
				$terminal_session_id = $_REQUEST['parent_session_id'];
				$target_session_id = $_REQUEST['session_id'];
				$session_type = $_REQUEST['session_name'];
				$session_type = str_replace(" ", "", $session_type);
				if(!isset($_REQUEST['kill_reason'])){
					$reason = "INACTIVE";
				}
				else $reason = $_REQUEST['kill_reason'];	//reason for breaking session used in time limit for players setup time limit how much they play			
				$config = Zend_Registry::get("config");
				//sending message through socket
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("MessageTransferController::sessionStatusUpdateAction: " . "Parameters to message transfer service session status update: parent_session_id=" . $terminal_session_id . " session_id=" . $target_session_id . " session_name = " . $session_type . " reason = " . $reason);
                if($config->socket->active == "true") {
                    $message_for_socket = $this->packSessionStatusMessageEncrypted($terminal_session_id, $target_session_id, $session_type, $reason, $config);
                    $this->sendMessageThroughSocket($message_for_socket, $config);
                }
                if($config->websocket->active == "true") {
                    $message_for_websocket = $this->packSessionStatusMessage($terminal_session_id, $target_session_id, $session_type, $reason, $config);
                    $this->sendMessageThroughWebSocket($message_for_websocket, $config);
                }
				die(YES);
			}else{
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceErrorLog("MessageTransferController::sessionStatusUpdateAction: " . "Parameters have not passed to message transfer service for session status update.");
				die(NO);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceErrorLog("MessageTransferController::sessionStatusUpdateAction: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->sendMail("MessageTransferController::sessionStatusUpdateAction: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
			die(NO);
		}
	}
    //called by oracle database to update credit status information
	public function creditStatusUpdateAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if(!$this->isWhiteListedIP()){
			die(NO);
		}
		try{
			if(isset($_REQUEST['credits']) && isset($_REQUEST['session_id'])) {
				$credits = $_REQUEST['credits'];
				$session_id = $_REQUEST['session_id'];
				$config = Zend_Registry::get("config");
				//sending message through socket
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("MessageTransferController::creditStatusUpdateAction: " . "Parameters to message transfer service credit status update: session_id=" . $session_id . " credits=" . $credits);
                if($config->socket->active == "true") {
                    $message_for_socket = $this->packCreditStatusMessageEncrypted($credits, $session_id, $config);
                    $this->sendMessageThroughSocket($message_for_socket, $config);
                }
                if($config->websocket->active == "true") {
                    $message_for_websocket = $this->packCreditStatusMessage($credits, $session_id, $config);
                    $this->sendMessageThroughWebSocket($message_for_websocket, $config);
                }
				unset($config);
				die(YES);
			}else{
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceErrorLog("MessageTransferController::creditStatusUpdateAction: " . "Parameters have not passed to message transfer service for credit status update.");
				die(NO);
			}
		}catch(Zend_Exception $ex){ //ORA 20200
			$code = $ex->getCode();
			/*if received exception is unknown*/
			if($code == "20200"){
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceErrorLog("MessageTransferController::creditStatusUpdateAction: " . "Game server is not available for credit status update!");
				$errorHelper->sendMail("MessageTransferController::creditStatusUpdateAction: " . "Game server is not available for credit status update on message transfer service!");
				die(NO);
			}else{
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceErrorLog("MessageTransferController::creditStatusUpdateAction: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
				$errorHelper->sendMail("MessageTransferController::creditStatusUpdateAction: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
				die(NO);
			}
		}
	}
	//connects to game server socket and sends message
	private function sendMessageThroughSocket($sent_message, $config){
		//Set the ip and port we will listen on
		$address = $config->socket->ip_address;	
		$port = $config->socket->port;
		$timeout = $config->socket->timeout;		
		$errorHelper = new ErrorHelper();			
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($socket === false){
			$errorHelper->serviceErrorLog("MessageTransferController::sendMessageThroughSocket: " . "Cannot execute socket_create() function. Error while creating connection to game server.");
		}
		$res = socket_connect($socket, $address, $port);
		if($res === false){
			$errorHelper->serviceErrorLog("MessageTransferController::sendMessageThroughSocket: " . "Cannot execute socket_connect() function. Error while connecting to game server.");
		}
		$len = strlen($sent_message);
		$offset = 0;
		while ($offset < $len) {
			$sent = socket_write($socket, substr($sent_message, $offset), $len-$offset);
			if ($sent === false) {
				// Error occurred, break the while loop
				break;
			}
			$offset += $sent;
		}
		if ($offset < $len) {
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			$errorHelper->serviceErrorLog("MessageTransferController::sendMessageThroughSocket: " . $errormsg);
		} else {
		// Data sent ok
		}		
		$message = socket_read($socket, 2048);
		$errorHelper->serviceAccessLog("SERVER RESPONSE - MESSAGE TRANSFER: " . $this->encryptMessage($message, $config));
		socket_close($socket);
	}

    private function sendMessageThroughWebSocket($sent_message, $config){
        $errorHelper = new ErrorHelper();
        try {
            $address = $config->websocket->ip_address;
            $web_socket_port = $config->websocket->port;
            $timeout = $config->websocket->timeout;
            $path = $config->websocket->path;
            if(strlen($address) == 0 || strlen($web_socket_port) == 0){
                return; //stop with sending to web socket server if no conf settings found
            }
            require_once('websocket/client/lib/class.websocket_client.php');
            $ports = explode(' ', $web_socket_port);
            foreach($ports as $port) {
                $client = new WebsocketClient();
                $status = $client->connect($address, $port, $path, false);
                if (!$status) {
                    $errorHelper->serviceErrorLog("MessageTransferController::sendMessageThroughWebSocket: Cannot connect to websocket server for HTML5 client. Error while creating connection to game websocket server. <br />" . $status);
                    return;
                }
                //TO DEBUG SENDING MESSAGE
                //$errorHelper->serviceAccessLog("WEBSOCKET TO SEND - MESSAGE TRANSFER: " . $sent_message);
                $result = $client->sendData($sent_message, 'text', true);
                if (!$result) {
                    $errorHelper->serviceErrorLog("MessageTransferController::sendMessageThroughWebSocket: Cannot send data websocket server for HTML5 client. Error while sending message to game websocket server. Message: " . $result['error_message']);
                    return;
                }
                $errorHelper->serviceAccessLog("WEBSOCKET SERVER RESPONSE - MESSAGE TRANSFER: " . $result['response'] . $result['error_message']);
            }
        }catch(Exception $ex){
            $errorHelper->serviceErrorLog("MessageTransferController::sendMessageThroughWebSocket: Cannot send data websocket server for HTML5 client. Exception message: " . $ex->getMessage());
        }
    }

    //encrypts message that is to be send to game server
    /**
     * @param $message
     * @param $config
     * @return string
     */
    private function encryptMessage($message, $config){
		$returnMessage = '';
		$iSize = strlen($message);
		for ($iCR = 0; $iCR < $iSize; $iCR++)
			$returnMessage .= $message[$iCR] ^ chr($config->socket->symbol_ascii);
		return $returnMessage;
	}

    //this message is packed encrypted tp be sent to game server for session status update
    //pack message for regular socket server
    private function packSessionStatusMessageEncrypted($terminal_session_id, $target_session_id, $session_type, $reason, $config){
        $message = "COMMAND_UID=30;SESSION_ID=" . $terminal_session_id . ";TARGET_SESSION_ID=" . $target_session_id . ";SESSION_TYPE=" . $session_type . ";PACKET_ID=0;REASON=" . $reason .";";
        return $this->encryptMessage($message, $config);
    }

    //this message is packed encrypted tp be sent to game server for session status update
	private function packSessionStatusMessage($terminal_session_id, $target_session_id, $session_type, $reason, $config){
		$message = "COMMAND_UID=30;SESSION_ID=" . $terminal_session_id . ";TARGET_SESSION_ID=" . $target_session_id . ";SESSION_TYPE=" . $session_type . ";PACKET_ID=0;REASON=" . $reason .";";
		return $message;
	}

    //this message is packed encrypted to be sent to game server for credit status update
    //pack message for regular socket server
    private function packCreditStatusMessageEncrypted($credits, $session_id, $config){
        $message = "COMMAND_UID=21;SESSION_ID=" . $session_id .";CREDIT_AMOUNT=" . $credits . ";PACKET_ID=0;";
        return $this->encryptMessage($message, $config);
    }

	//this message is packed encrypted to be sent to game server for credit status update
	private function packCreditStatusMessage($credits, $session_id, $config){
		$message = "COMMAND_UID=21;SESSION_ID=" . $session_id .";CREDIT_AMOUNT=" . $credits . ";PACKET_ID=0;";
		return $message;
	}
}