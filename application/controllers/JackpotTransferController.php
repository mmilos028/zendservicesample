<?php
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
//answer is NO / YES
class JackpotTransferController extends Zend_Controller_Action{
	public function indexAction(){
		header('Location: http://www.google.com/');
	}
	//return true if ip address exists
	//return false if ip address not exists
	private function isWhiteListedIP(){
		$config = Zend_Registry::get('config');
		//will check site ip address caller if true
		$ip_addresses = explode(' ', $config->db->ip_address);
		$host_ip_address = IPHelper::getRealIPAddress();
		if(!IPHelper::testPrivateIP($host_ip_address)){
			$errorHelper = new ErrorHelper();
			$message = 'Jackpot Transfer service: Host with blacklisted ip address ' . $host_ip_address . ' is trying to connect to jackpot transfer web service.';
			$errorHelper->serviceError($message, $message);
		}
		$status = in_array($host_ip_address, $ip_addresses);
		if(!$status){
			$errorHelper = new ErrorHelper();
			$message = 'Jackpot Transfer service: Host with blacklisted ip address ' . $host_ip_address . ' is trying to connect to jackpot transfer web service.';
			$errorHelper->serviceError($message, $message);
		}
		return $status;
	}	
	//called by database and transfer message to game server
	//this service is used to update jackpot status for game clients
	public function jackpotUpdateAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		if(!$this->isWhiteListedIP()){
			die(NO);
		}
		try{
			if(isset($_REQUEST['session_id'])) {
				$session_id = $_REQUEST['session_id'];
				$config = Zend_Registry::get("config");
                $errorHelper = new ErrorHelper();
				//sending message through socket
                $errorHelper->serviceAccessLog("Parameters to jackpot message transfer service for jackpot status update: session_id=" . $session_id);
                if($config->socket->active == "true") {
                    $this->sendMessageThroughSocket($this->packSessionStatusMessageEncrypted($session_id, $config), $config);
                }
                /*if($config->websocket->active == "true") {
                    $message_for_websocket = $this->packSessionStatusMessage($session_id, $config);
                    $this->sendMessageThroughWebSocket($message_for_websocket, $config);
                }*/
				die(YES);
			}else{
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("Parameters have not passed to jackpot message transfer service for jackpot status update.");
				die(NO);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			die(NO);
		}
	}
	//connects to game server socket and sends message
	private function sendMessageThroughSocket($message, $config){
		//Set the ip and port we will listen on
		$address = $config->socket->ip_address;	
		$port = $config->socket->port;
		$timeout = $config->socket->timeout;		
		$errorHelper = new ErrorHelper();
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($socket === false){
			$errorHelper->serviceErrorLog("Cannot execute socket_create() function. Error while creating connection to game server.");
		}
		$res = socket_connect($socket, $address, $port);
		if($res === false){
			$errorHelper->serviceErrorLog("Cannot execute socket_connect() function. Error while connection to game server.");
		}
		$len = strlen($message);
		$offset = 0;
		while ($offset < $len) {
			$sent = socket_write($socket, substr($message, $offset), $len-$offset);
			if ($sent === false) {
				// Error occurred, break the while loop
				break;
			}
			$offset += $sent;
		}
		if ($offset < $len) {
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			$errorHelper->serviceErrorLog($errormsg);
		} else {
		// Data sent ok
		}	
		$message = socket_read($socket, 2048);
		$errorHelper->serviceAccessLog("SERVER RESPONSE - JACKPOT TRANSFER: " . $this->encryptMessage($message, $config));
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
                    $errorHelper->serviceErrorLog("JackpotTransferController::sendMessageThroughWebSocket: Cannot connect to websocket server for HTML5 client. Error while creating connection to game websocket server. <br />" . $status);
                    return;
                }
                //TO DEBUG SENDING MESSAGE
                //$errorHelper->serviceAccessLog("WEBSOCKET TO SEND - MESSAGE TRANSFER: " . $sent_message);
                $result = $client->sendData($sent_message, 'text', true);
                if (!$result) {
                    $errorHelper->serviceErrorLog("JackpotTransferController::sendMessageThroughWebSocket: Cannot send data websocket server for HTML5 client. Error while sending message to game websocket server. Message: " . $result['error_message']);
                    return;
                }
                $errorHelper->serviceAccessLog("WEBSOCKET SERVER RESPONSE - MESSAGE TRANSFER: " . $result['response'] . $result['error_message']);
            }
        }catch(Exception $ex){
            $errorHelper->serviceErrorLog("JackpotTransferController::sendMessageThroughWebSocket: Cannot send data websocket server for HTML5 client. Exception message: " . $ex->getMessage());
        }
    }

	//encrypts message that is to be send to game server
	private function encryptMessage($message, $config){
		$returnMessage = '';
		$iSize = strlen($message);
		for ($iCR = 0; $iCR < $iSize; $iCR++)$returnMessage .= $message[$iCR] ^ chr($config->socket->symbol_ascii);	
		return $returnMessage;	
	}

    //this message is packed encrypted tp be sent to game server for session status update
	private function packSessionStatusMessageEncrypted($session_id, $config){
		$message = "COMMAND_UID=42;SESSION_ID=" . $session_id . ";PACKET_ID=0;";
		return $this->encryptMessage($message, $config);
	}

    //this message is packed encrypted tp be sent to game server for session status update
	private function packSessionStatusMessage($session_id, $config){
		$message = "COMMAND_UID=42;SESSION_ID=" . $session_id . ";PACKET_ID=0;";
		return $message;
	}
}