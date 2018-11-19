<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once 'ExternalIntegration.php';

class RestXmlExternalIntegration implements ExternalIntegration{
	//error constants
    private $DEBUG_GET_PLAYER_INFO = true;
    private $DEBUG_POST_TRANSACTION = true;
    private $DEBUG_CHECK_TRANSACTION = true;
    private $DEBUG_CLOSE_PLAYER_SESSION = true;
	public function __construct(){
	}

    /**
     * @param string $affiliate_username
     * @param string $affiliate_password
     * @param string $player_id
     * @param string $ws_url
     * @return mixed
     */
	public function getPlayerInfo($affiliate_username, $affiliate_password, $player_id, $ws_url)
    {
		try{
			$fields = array(
				'request' => 'get-player-info',
				'affiliate_username' => urlencode($affiliate_username),
				'affiliate_password' => $affiliate_password,
				'player_id' => $player_id,
        'username' => 'rilgordCasino',
        'password' => 'a3F0rstng8an'
			);
            $fields_string = "";
			foreach($fields as $key=>$value) {
				$fields_string .= $key.'='.$value.'&';
			}
			rtrim($fields_string, '&');

			//$ws_url .= "?" . $fields_string;
			//start post init to get player credits information
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $ws_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			//disable ssl verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_ENCODING,  'gzip');
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Connection: keep-alive'
		    ));
			$data = curl_exec($ch);
			if(curl_errno($ch)){
				//there was an error sending request to integration client
				$error_message = curl_error($ch);
				//DEBUG HERE

				$errorHelper = new ErrorHelper();
				$message = "RestXmlExternalIntegration::getPlayerInfo(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, ws_url = {$ws_url}) <br /> Exception message: <br /> {$error_message}";
				$errorHelper->externalIntegrationError($message, $message);

				$result = array(
					"status"=>NOK,
					"affiliate_username"=>urldecode($affiliate_username),
					"affiliate_password"=>$affiliate_password,
					"player_id"=>$player_id,
					"url"=>urldecode($ws_url),
					"player_balance"=>"",
					"player_currency"=>"",
          "white_label_id"=>"",
          "parent_affiliate_id"=>""
				);
				return $result;
			}else{
				//we received player information from external integration
				//converts XML structure string into PHP object
				$xmlResponseString = simplexml_load_string($data);
				//DEBUG HERE
        if($this->DEBUG_GET_PLAYER_INFO) {
            $errorHelper = new ErrorHelper();
            $message = "RestXmlExternalIntegration::getPlayerInfo(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, ws_url = {$ws_url}) <br /> Response = " . $data;
            $errorHelper->externalIntegrationAccessLog($message);
        }
				//return result as PHP object
				$result = array(
					"status"=>trim((string)$xmlResponseString->status),
					"affiliate_username"=>urldecode(trim((string)$xmlResponseString->affiliate_username)),
					"affiliate_password"=>trim((string)$xmlResponseString->affiliate_password),
					"player_id"=>$player_id,
					"url"=>urldecode($ws_url),
					"player_balance"=>trim((string)$xmlResponseString->player_balance),
					"player_currency"=>trim((string)$xmlResponseString->player_currency),
          "white_label_id"=>trim((string)$xmlResponseString->white_label_id),
          "parent_affiliate_id"=>trim((string)$xmlResponseString->parent_affiliate_id)
				);
				curl_close($ch);
				return $result;
			}
		}catch(Zend_Exception $ex){

			//there was an error in players void purchase transaction
			$errorHelper = new ErrorHelper();
			//DEBUG HERE
            $error_message = $ex->getMessage();
			$message = "RestXmlExternalIntegration::getPlayerInfo(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, ws_url = {$ws_url}) <br /> Exception message: <br /> {$error_message}";
			$errorHelper->externalIntegrationErrorLog($message);

			$result = array(
				"status"=>NOK,
				"affiliate_username"=>urldecode($affiliate_username),
				"affiliate_password"=>$affiliate_password,
				"player_id"=>$player_id,
				"url"=>urldecode($ws_url),
				"player_balance"=>"",
				"player_currency"=>"",
                "white_label_id"=>"",
                "parent_affiliate_id"=>""
			);
			return $result;
		}
	}

    /**
     * @param string $affiliate_username
     * @param string $affiliate_password
     * @param string $player_id
     * @param string $amount
     * @param string $transaction_type
     * @param string $received_date
     * @param string $reservation_id
     * @param string $game_transaction_id
     * @param string $game_move_id
     * @param string $game_id
     * @param $game_name
     * @param $ws_url
     * @return mixed
     */
	public function postTransaction($affiliate_username, $affiliate_password, $player_id, $amount, $transaction_type, $received_date, $reservation_id, $game_transaction_id, $game_move_id, $game_id, $game_name, $ws_url)
    {
		try{
			$fields = array(
				"request" => "post-transaction",
				"affiliate_username" => urlencode($affiliate_username),
				"affiliate_password" => $affiliate_password,
				"player_id" => $player_id,
				"amount" => $amount,
				"transaction_type" => $transaction_type,
        "received_date" => urlencode($received_date),
        "reservation_id" => $reservation_id,
        "game_transaction_id" => $game_transaction_id,
        "game_move_id" => $game_move_id,
				"game_id" => $game_id,
				"game_name" => urlencode($game_name),
        'username' => 'rilgordCasino',
        'password' => 'a3F0rstng8an',
        'ws_url'=>$ws_url
			);

            //DEBUG HERE
            if($this->DEBUG_POST_TRANSACTION) {
                $errorHelper = new ErrorHelper();
                $message = "Sends to Client. RestXmlExternalIntegration::postTransaction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password},
                player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, received_date = {$received_date}, reservation_id = {$reservation_id}, game_transaction_id = {$game_transaction_id},
                game_move_id = {$game_move_id}, game_id = {$game_id}, game_name = {$game_name}, ws_url = {$ws_url})";
                $errorHelper->externalIntegrationAccessLog($message);
            }
            $fields_string = "";
			foreach($fields as $key=>$value) {
				$fields_string .= $key.'='.$value.'&';
			}
			rtrim($fields_string, '&');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $ws_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			//disable ssl verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_ENCODING,  'gzip');
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Connection: keep-alive'
		    ));
			$data = curl_exec($ch);
			if(curl_errno($ch)){
				//there was an error sending post
				$error_message = curl_error($ch);
        if($this->DEBUG_POST_TRANSACTION) {
  				//DEBUG HERE
  				$errorHelper = new ErrorHelper();
  				$message = "Returns from client. RestXmlExternalIntegration::postTransaction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password},
                      player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, received_date = {$received_date}, reservation_id={$reservation_id}, game_transaction_id = {$game_transaction_id},
                      game_move_id = {$game_move_id}, game_id = {$game_id}, game_name = {$game_name}, ws_url = {$ws_url}) <br /> Exception message: <br /> {$error_message}";
  				$errorHelper->externalIntegrationErrorLog($message);
        }
				$result = array(
					"status" => NOK,
					"affiliate_username" => urldecode($affiliate_username),
					"affiliate_password" => $affiliate_password,
					"player_id" => $player_id,
					"amount" => $amount,
					"transaction_type" => $transaction_type,
                      "received_date" => urldecode($received_date),
                      "reservation_id" => $reservation_id,
                      "game_transaction_id" => $game_transaction_id,
                      "game_move_id" => $game_move_id,
					"game_id" => $game_id,
					"game_name" => urldecode($game_name),
					"url" => urldecode($ws_url),
					"player_balance" => "",
					"player_currency" => "",
          "transaction_id" => ""
				);
				return $result;
			}else{
				//we received player information from external integration
				//converts XML structure string into PHP object
				$xmlResponseString = simplexml_load_string($data);
				//DEBUG HERE
                if($this->DEBUG_POST_TRANSACTION) {
                    $errorHelper = new ErrorHelper();
                    $message = "Returns from client. RestXmlExternalIntegration::postTransaction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password},
                player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, received_date = {$received_date}, reservation_id = {$reservation_id}, game_transaction_id = {$game_transaction_id},
                game_move_id = {$game_move_id}, game_id = {$game_id}, game_name = {$game_name}, ws_url = {$ws_url}) <br /> Response = " . $data;
                    $errorHelper->externalIntegrationAccessLog($message);
                }
				//return result as PHP object
				$result = array(
					"status" => trim((string)$xmlResponseString->status),
					"affiliate_username" => urldecode(trim((string)$xmlResponseString->affiliate_username)),
					"affiliate_password" => trim((string)$xmlResponseString->affiliate_password),
					"player_id" => $player_id,
					"amount" => $amount,
					"transaction_type" => $transaction_type,
                      "received_date" => urldecode($received_date),
                      "reservation_id" => $reservation_id,
                      "game_transaction_id" => $game_transaction_id,
                      "game_move_id" => $game_move_id,
					"game_id" => $game_id,
					"game_name" => urldecode($game_name),
					"url" => urldecode($ws_url),
					"player_balance" => trim((string)$xmlResponseString->player_balance),
					"player_currency" => trim((string)$xmlResponseString->player_currency),
                    "transaction_id" => trim((string)$xmlResponseString->transaction_id),
				);
				curl_close($ch);
				return $result;
			}
		}catch(Zend_Exception $ex){
			//there was an error
			$errorHelper = new ErrorHelper();
      $error_message = $ex->getMessage();

  			//DEBUG HERE
  			$message = "Exception Error: RestXmlExternalIntegration::postTransaction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password},
  			player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, received_date = {$received_date}, reservation_id = {$reservation_id}, game_transaction_id = {$game_transaction_id},
  			game_move_id = {$game_move_id}, game_id = {$game_id}, game_name = {$game_name}, ws_url = {$ws_url}) <br /> Exception message: <br /> {$error_message}";
  			$errorHelper->externalIntegrationErrorLog($message);

			$result = array(
				"status" => NOK,
				"affiliate_username" => urldecode($affiliate_username),
				"affiliate_password" => $affiliate_password,
				"player_id" => $player_id,
				"amount" => $amount,
				"transaction_type" => $transaction_type,
                "received_date" => urldecode($received_date),
                "reservation_id" => $reservation_id,
                "game_transaction_id" => $game_transaction_id,
                "game_move_id" => $game_move_id,
				"game_id" => $game_id,
				"game_name" => urldecode($game_name),
				"url" => urldecode($ws_url),
				"player_balance" => "",
				"player_currency" => "",
                "transaction_id" => "",
			);
			return $result;
		}
	}

    /**
     * @param string $affiliate_username
     * @param string $affiliate_password
     * @param string $player_id
     * @param string $ws_url
     * @return mixed
     */
    public function closePlayerSession($affiliate_username, $affiliate_password, $player_id, $ws_url)
    {
        try{
			$fields = array(
				'request' => 'close-player-session',
				'affiliate_username' => urlencode($affiliate_username),
				'affiliate_password' => $affiliate_password,
				'player_id' => $player_id,
                'username' => 'rilgordCasino',
                'password' => 'a3F0rstng8an',
                'ws_url'=>$ws_url
			);
            $fields_string = "";
			foreach($fields as $key=>$value) {
				$fields_string .= $key.'='.$value.'&';
			}
			rtrim($fields_string, '&');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $ws_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			//disable ssl verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_ENCODING,  'gzip');
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Connection: keep-alive'
		    ));
			$data = curl_exec($ch);
			if(curl_errno($ch)){
				//there was an error sending request to integration client
				$error_message = curl_error($ch);
        if($this->DEBUG_CLOSE_PLAYER_SESSION){
				//DEBUG HERE
  				$errorHelper = new ErrorHelper();
  				$message = "RestXmlExternalIntegration::closePlayerSession(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, ws_url = {$ws_url}) <br /> Exception message: <br /> {$error_message}";
  				$errorHelper->externalIntegrationErrorLog($message);
        }
				$result = array(
					"status"=>NOK,
					"affiliate_username"=>urldecode($affiliate_username),
					"affiliate_password"=>$affiliate_password,
					"player_id"=>$player_id,
					"url"=>urldecode($ws_url)
				);
				return $result;
			}else{
				//we received player information from external integration
				//converts XML structure string into PHP object
				$xmlResponseString = simplexml_load_string($data);
				//DEBUG HERE
        if($this->DEBUG_CLOSE_PLAYER_SESSION) {
            $errorHelper = new ErrorHelper();
            $message = "RestXmlExternalIntegration::closePlayerSession(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, ws_url = {$ws_url}) <br /> Response = " . $data;
            $errorHelper->externalIntegrationAccessLog($message);
        }
				//return result as PHP object
				$result = array(
					"status"=>trim((string)$xmlResponseString->status),
					"affiliate_username"=>urldecode(trim((string)$xmlResponseString->affiliate_username)),
					"affiliate_password"=>trim((string)$xmlResponseString->affiliate_password),
					"player_id"=>$player_id,
					"url"=>urldecode($ws_url)
				);
				curl_close($ch);
				return $result;
			}
		}catch(Zend_Exception $ex){
			//there was an error
			$errorHelper = new ErrorHelper();
      $error_message = $ex->getMessage();
			//DEBUG HERE

  			$message = "RestXmlExternalIntegration::closePlayerSession(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password}, player_id = {$player_id}, ws_url = {$ws_url}) <br /> Exception message: <br /> {$error_message}";
  			$errorHelper->externalIntegrationErrorLog($message);

			$result = array(
				"status"=>NOK,
				"affiliate_username"=>urldecode($affiliate_username),
				"affiliate_password"=>$affiliate_password,
				"player_id"=>$player_id,
				"url"=>urldecode($ws_url)
			);
			return $result;
		}
    }

    /**
     * @param string $affiliate_username
     * @param string $affiliate_password
     * @param string $player_id
     * @param string $amount
     * @param string $transaction_type
     * @param string $received_date
     * @param string $reservation_id
     * @param string $game_transaction_id
     * @param string $game_move_id
     * @param string $game_id
     * @param $game_name
     * @param $ws_url
     * @return mixed
     */
	public function checkTransaction($affiliate_username, $affiliate_password, $player_id, $amount, $transaction_type, $received_date, $reservation_id, $game_transaction_id, $game_move_id, $game_id, $game_name, $ws_url)
    {
		try{
			$fields = array(
				"request" => "check-transaction",
				"affiliate_username" => urlencode($affiliate_username),
				"affiliate_password" => $affiliate_password,
				"player_id" => $player_id,
				"amount" => $amount,
				"transaction_type" => $transaction_type,
                "received_date" => urlencode($received_date),
                "reservation_id" => $reservation_id,
                "game_transaction_id" => $game_transaction_id,
                "game_move_id" => $game_move_id,
				"game_id" => $game_id,
				"game_name" => urlencode($game_name),
                'username' => 'rilgordCasino',
                'password' => 'a3F0rstng8an',
                'ws_url'=>$ws_url
			);
            //DEBUG HERE
            if($this->DEBUG_CHECK_TRANSACTION) {
                $errorHelper = new ErrorHelper();
                $message = "Sends to Client. RestXmlExternalIntegration::checkTransaction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password},
            player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, received_date = {$received_date}, reservation_id = {$reservation_id}, game_transaction_id = {$game_transaction_id},
            game_move_id = {$game_move_id}, game_id = {$game_id}, game_name = {$game_name}, ws_url = {$ws_url})";
                $errorHelper->externalIntegrationAccessLog($message);
            }
            $fields_string = "";
			foreach($fields as $key=>$value) {
				$fields_string .= $key.'='.$value.'&';
			}
			rtrim($fields_string, '&');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $ws_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			//disable ssl verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_ENCODING,  'gzip');
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Connection: keep-alive'
		    ));
			$data = curl_exec($ch);
			if(curl_errno($ch)){
				//there was an error
				$error_message = curl_error($ch);
				//DEBUG HERE
        if($this->DEBUG_CHECK_TRANSACTION) {
  				$errorHelper = new ErrorHelper();
  				$message = "Returns from client. RestXmlExternalIntegration::checkTransaction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password},
                      player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, received_date = {$received_date}, reservation_id={$reservation_id}, game_transaction_id = {$game_transaction_id},
                      game_move_id = {$game_move_id}, game_id = {$game_id}, game_name = {$game_name}, ws_url = {$ws_url}) <br /> Exception message: <br /> {$error_message}";
  				$errorHelper->externalIntegrationErrorLog($message);
        }
				$result = array(
					"status" => NOK,
                    "transaction_status"=>"",
					"affiliate_username" => urldecode($affiliate_username),
					"affiliate_password" => $affiliate_password,
					"player_id" => $player_id,
					"amount" => $amount,
					"transaction_type" => $transaction_type,
                      "received_date" => urldecode($received_date),
                      "reservation_id" => $reservation_id,
                      "game_transaction_id" => $game_transaction_id,
                      "game_move_id" => $game_move_id,
					"game_id" => $game_id,
					"game_name" => urldecode($game_name),
					"url" => urldecode($ws_url),
					"player_balance" => "",
					"player_currency" => "",
                    "transaction_id" => ""
				);
				return $result;
			}else{
				//we received player information from external integration
				//converts XML structure string into PHP object
				$xmlResponseString = simplexml_load_string($data);
				//DEBUG HERE
        if($this->DEBUG_CHECK_TRANSACTION) {
            $errorHelper = new ErrorHelper();
            $message = "Returns from client. RestXmlExternalIntegration::checkTransaction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password},
        player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, received_date = {$received_date}, reservation_id = {$reservation_id}, game_transaction_id = {$game_transaction_id},
        game_move_id = {$game_move_id}, game_id = {$game_id}, game_name = {$game_name}, ws_url = {$ws_url}) <br /> Response = " . $data;
            $errorHelper->externalIntegrationAccessLog($message);
        }
				//return result as PHP object
				$result = array(
					"status" => trim((string)$xmlResponseString->status),
                    "transaction_status"=>trim((string)$xmlResponseString->transaction_status),
					"affiliate_username" => urldecode(trim((string)$xmlResponseString->affiliate_username)),
					"affiliate_password" => trim((string)$xmlResponseString->affiliate_password),
					"player_id" => trim((string)$xmlResponseString->player_id),
					"amount" => trim((string)$xmlResponseString->amount),
					"transaction_type" => $transaction_type,
                      "received_date" => urldecode($received_date),
                      "reservation_id" => trim((string)$xmlResponseString->reservation_id),
                      "game_transaction_id" => $game_transaction_id,
                      "game_move_id" => $game_move_id,
					"game_id" => trim((string)$xmlResponseString->game_id),
					"game_name" => urldecode(trim((string)$xmlResponseString->game_name)),
					"url" => urldecode($ws_url),
					"player_balance" => trim((string)$xmlResponseString->player_balance),
					"player_currency" => trim((string)$xmlResponseString->player_currency),
                    "transaction_id" => trim((string)$xmlResponseString->transaction_id),
				);
				curl_close($ch);
				return $result;
			}
		}catch(Zend_Exception $ex){
			//there was an error
			$errorHelper = new ErrorHelper();
      $error_message = $ex->getMessage();
			//DEBUG HERE

  			$message = "Exception Error: RestXmlExternalIntegration::checkTransaction(affiliate_username = {$affiliate_username}, affiliate_password = {$affiliate_password},
  			player_id = {$player_id}, amount = {$amount}, transaction_type = {$transaction_type}, received_date = {$received_date}, reservation_id = {$reservation_id}, game_transaction_id = {$game_transaction_id},
  			game_move_id = {$game_move_id}, game_id = {$game_id}, game_name = {$game_name}, ws_url = {$ws_url}) <br /> Exception message: <br /> {$error_message}";
  			$errorHelper->externalIntegrationErrorLog($message);

			$result = array(
				"status" => NOK,
                "transaction_status"=>"",
				"affiliate_username" => urldecode($affiliate_username),
				"affiliate_password" => $affiliate_password,
				"player_id" => $player_id,
				"amount" => $amount,
				"transaction_type" => $transaction_type,
                "received_date" => urldecode($received_date),
                "reservation_id" => $reservation_id,
                "game_transaction_id" => $game_transaction_id,
                "game_move_id" => $game_move_id,
				"game_id" => $game_id,
				"game_name" => urldecode($game_name),
				"url" => urldecode($ws_url),
				"player_balance" => "",
				"player_currency" => "",
                "transaction_id" => "",
			);
			return $result;
		}
	}
}
