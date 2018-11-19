<?php
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
class GglManager{
	
	/**
	 * document-literal style
	 * /onlinecasinoservice/ggl-integration?wsdl is called to generate wsdl
	 * document-literal style
	 * /onlinecasinoservice/ggl-integration?wsdl&document&literal
	 * document-encoded style
	 * /onlinecasinoservice/ggl-integration?wsdl&document&encoded
	 * rpc-literal style
	 * /onlinecasinoservice/ggl-integration?wsdl&rpc&literal
	 * rpc-encoded style
	 * /onlinecasinoservice/ggl-integration?wsdl&rpc&encoded
	 * 
	 * GGL Integration - returns player balance
	 * customerID: Customer that is playing the Live Dealer
	 * user: User name to authenticate against this interface
	 * password: Password to authenticate against this interface
	 * return balance from customer numeric or return empty string if error
	 * @param integer $customerID
	 * @param string $user
	 * @param string $password
	 * @return mixed
	 */
	public function getCustomerInfo($customerID, $user, $password){
		if(!isset($customerID) || !isset($user) || !isset($password) || !is_numeric($customerID)){
			return "";
		}
		$customerID = strip_tags($customerID);
		$user = strip_tags($user);
		$password = strip_tags($password);
		/*DEBUG MESSAGE - TRACK WHAT IS SENT*/
		/*
		$errorHelper = new ErrorHelper();
		$mail_message = "GGL Manager - getCustomerInfo: <br /> customerID = {$customerID} <br /> user = {$user}";
		$log_message = "GGL Manager - getCutomerInfo: customerID = {$customerID} user = {$user}"; 
		$errorHelper->gglIntegrationError($mail_message, $log_message);
		*/		
		try{
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . "GglIntegrationModel.php";
			$modelGglIntegration = new GglIntegrationModel();
			$arrData = $modelGglIntegration->getCustomerInfo($user, $password, $customerID);
			//if player exists return credits
			if($arrData['status'] == OK){
				$credits = $arrData['credits'];
				return $credits;
			}else{ 
				//wrong username or password or player not exists or error on database\
                $detected_ip_address = IPHelper::getRealIPAddress();
				$errorHelper = new ErrorHelper();
                $message = "GGL Games Integration Error: <br /> GglManager::getCustomerInfo(customerID = {$customerID}, user = {$user}, password =) <br /> Detected IP Address = {$detected_ip_address} <br /> Error: Wrong username or password or player not exists.";
				$errorHelper->gglIntegrationError($message, $message);
				return "";
			}
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
            $message = "GGL Games Integration Error: <br /> GglManager::getCustomerInfo(customerID = {$customerID}, user = {$user}, password =) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error:<br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->gglIntegrationError($message, $message);
			return "";
		}
	}
	
	/**
	 * 
	 * document-literal style
	 * /onlinecasinoservice/ggl-integration?wsdl is called to generate wsdl
	 * document-literal style
	 * /onlinecasinoservice/ggl-integration?wsdl&document&literal
	 * document-encoded style
	 * /onlinecasinoservice/ggl-integration?wsdl&document&encoded
	 * rpc-literal style
	 * /onlinecasinoservice/ggl-integration?wsdl&rpc&literal
	 * rpc-encoded style
	 * /onlinecasinoservice/ggl-integration?wsdl&rpc&encoded
	 * 
	 * GGL Integration - post transaction payin or payout ...
	 * sessionID: session id number from pc session on web site
	 * customerID: Customer that is playing the Live Dealer
	 * amount: Amount to be credited or debited from the customer balance
	 * transactionType: Identifies if it is a credit (1) or a debit (-1) transaction
	 * gameId: Identifies type of game played with GGL
	 * user: User name to authenticate against this interface
	 * password: Password to authenticate against this interface
	 * return string or numeric (empty if any error or transaction id if successful)
	 * @param integer $sessionID
	 * @param integer $customerID
	 * @param double $amount
	 * @param int $transactionType
	 * @param string $gameId
	 * @param string $user
	 * @param string $password
	 * @return mixed
	 */
	public function postTransaction($sessionID, $customerID, $amount, $transactionType, $gameId, $user, $password){
		if(!isset($sessionID) || !is_numeric($sessionID) || !isset($customerID) || !is_numeric($customerID) || !isset($amount) || !isset($transactionType) || !isset($gameId) || !isset($user) || !isset($password)){
			return "";
		}
		if($amount <= 0 || !is_numeric($amount)){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
            $message = "GGL Games Integration Error: <br /> GglManager::postTransaction(sessionID = {$sessionID}, customerID = {$customerID}, amount = {$amount}, transactionType = {$transactionType}, gameId = {$gameId}, user = {$user}, password=) <br /> Detected IP Address = {$detected_ip_address} <br /> Error: <br /> Wrong Amount type is sent.";
			$errorHelper->gglIntegrationError($message, $message);
			return "";
		}
		$sessionID = strip_tags($sessionID);
		$customerID = strip_tags($customerID);
		$amount = strip_tags($amount);
		$transactionType = strip_tags($transactionType);
		$gameId = strip_tags($gameId);
		$user = strip_tags($user);
		$password = strip_tags($password);
		try{
			//credit or debit transaction type
			if($transactionType != PAYIN_TRANSACTION && $transactionType != PAYOUT_TRANSACTION){
                $detected_ip_address = IPHelper::getRealIPAddress();
				$errorHelper = new ErrorHelper();
                $message = "GGL Games Integration Error: <br /> GglManager::postTransaction(sessionID = {$sessionID}, customerID = {$customerID}, amount = {$amount}, transactionType = {$transactionType}, gameId = {$gameId}, user = {$user}, password=) <br /> Detected IP Address = {$detected_ip_address} <br /> Error: <br /> Wrong transaction type is sent.";
				$errorHelper->gglIntegrationError($message, $message);
				return "";
			}
			/*DEBUG MESSAGE - TRACK WHAT IS SENT*/
			/*
			$errorHelper = new ErrorHelper();
			$mail_message = "GGL Manager - postTransaction: <br /> sessionID = {$sessionID} <br /> customerID = {$customerID} <br /> amount = {$amount} <br /> transactionType = {$transactionType} <br /> user = {$user} <br /> game_id = {$game_id}";
			$log_message = "GGL Manager - postTransaction: sessionID = {$sessionID} customerID = {$customerID} amount = {$amount} transactionType = {$transactionType} user = {$user} game_id = {$game_id}";		
			$errorHelper->gglIntegrationError($mail_message, $log_message);
			*/
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'SubjectTypesModel.php';
			$modelSubjectTypes = new SubjectTypesModel();
			if($transactionType == PAYIN_TRANSACTION){
				//managment_types.NAME_IN_CREDITS_FROM_GGL
				$transactionType = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.NAME_IN_CREDITS_FROM_GGL');
			}elseif($transactionType == PAYOUT_TRANSACTION){
				//managment_types.NAME_IN_CREDITS_TO_GGL
				$transactionType = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.NAME_IN_CREDITS_TO_GGL');
			}else {
                $detected_ip_address = IPHelper::getRealIPAddress();
				$errorHelper = new ErrorHelper();
                $message = "GGL Games Integration Error: <br /> GglManager::postTransaction(sessionID = {$sessionID}, customerID = {$customerID}, amount = {$amount}, transactionType = {$transactionType}, gameId = {$gameId}, user = {$user}, password=) <br /> Detected IP Address = {$detected_ip_address} <br /> Error: <br /> TransactionType is empty value.";
				$errorHelper->gglIntegrationError($message, $message);
				return "";
			}
			
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . "GglIntegrationModel.php";
			$modelGglIntegration = new GglIntegrationModel();
			$arrData = $modelGglIntegration->postTransaction($user, $password, $sessionID, $amount, $transactionType, $gameId);
			//if player exists return transaction id from transaction
			if($arrData['status'] == OK){
				if(strlen($arrData['transaction_id']) == 0){
                    $detected_ip_address = IPHelper::getRealIPAddress();
					$errorHelper = new ErrorHelper();
                    $message = "GGL Games Integration Error: <br /> GglManager::postTransaction(sessionID = {$sessionID}, customerID = {$customerID}, amount = {$amount}, transactionType = {$transactionType}, gameId = {$gameId}, user = {$user}, password=) <br /> Detected IP Address = {$detected_ip_address} <br /> Error: <br /> Empty transaction number returned.";
					$errorHelper->gglIntegrationError($message, $message);
					return "";
				}
				return $arrData['transaction_id'];
			}else{ 
				//some error occured on transaction only return empty string
				return "";
			}
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
            $message = "GGL Games Integration Error: <br /> GglManager::postTransaction(sessionID = {$sessionID}, customerID = {$customerID}, amount = {$amount}, transactionType = {$transactionType}, gameId = {$gameId}, user = {$user}, password=) <br /> Detected IP Address = {$detected_ip_address} <br /> Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->gglIntegrationError($message, $message);
			return "";
		}
	}
}