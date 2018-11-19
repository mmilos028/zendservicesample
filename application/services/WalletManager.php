<?php
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';


class WalletManager{
	
	/**
	 * document-literal style
	 * /onlinecasinoservice/wallet-integration?wsdl is called to generate wsdl
	 * document-literal style
	 * /onlinecasinoservice/wallet-integration?wsdl&document&literal
	 * document-encoded style
	 * /onlinecasinoservice/wallet-integration?wsdl&document&encoded
	 * rpc-literal style
	 * /onlinecasinoservice/wallet-integration?wsdl&rpc&literal
	 * rpc-encoded style
	 * /onlinecasinoservice/wallet-integration?wsdl&rpc&encoded
	 * 
	 * GGL Integration - returns player balance
	 * customerID: Customer that is playing the Live Dealer
	 * user: User name to authenticate against this interface
	 * password: Password to authenticate against this interface
	 * return balance from customer numeric or return empty string if error
	 * @param integer $customerID
	 * @param string $user
	 * @param string $password
	 * @return string
	 */
	public function getCustomerInfo($customerID, $user, $password){
		if(!isset($customerID) || !isset($user) || !isset($password)){
            return "";
		}
		$customerID = strip_tags($customerID);
		$user = strip_tags($user);
		$password = strip_tags($password);
		/*DEBUG MESSAGE - TRACK WHAT IS SENT*/
		/*
		$errorHelper = new ErrorHelper();
		$mail_message = "WalletManager::getCustomerInfo: <br /> customerID = {$customerID} <br /> user = {$user}";
		$log_message = "WalletManager::getCustomerInfo: customerID = {$customerID} user = {$user}"; 
		$errorHelper->ldcIntegrationError($mail_message, $log_message);
		*/
		///
		try{
			require_once MODELS_DIR . DS . "WalletModel.php";
			$modelWallet = new WalletModel();			
			$arrData = $modelWallet->getCustomerInfo($user, $password, $customerID);	
			//if player exists return credits
			if($arrData['status'] == OK){
				$credits = $arrData['credits'];
				return $credits;
			}else{ 
				//wrong username or password or player not exists or error on database\
				$errorHelper = new ErrorHelper();
				$mail_message = "WalletManager::getCustomerInfo: Wrong username or password or player not exists. <br /> customerID = {$customerID} <br /> user = {$user}";
				$log_message = "WalletManager::getCustomerInfo: Wrong username or password or player not exists. customerID = {$customerID} user = {$user}";
				$errorHelper->ldcIntegrationError($mail_message, $log_message);
                return "";
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WalletManager::getCustomerInfo - exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex) . "<br /> customerID = {$customerID} <br /> user = {$user}";
			$log_message = "WalletManager::getCustomerInfo - exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex) . " customerID = {$customerID} user = {$user}";
			$errorHelper->ldcIntegrationError($mail_message, $log_message);
            return "";
		}
	}
	
	/**
	 * 
	 * document-literal style
	 * /onlinecasinoservice/wallet-integration?wsdl is called to generate wsdl
	 * document-literal style
	 * /onlinecasinoservice/wallet-integration?wsdl&document&literal
	 * document-encoded style
	 * /onlinecasinoservice/wallet-integration?wsdl&document&encoded
	 * rpc-literal style
	 * /onlinecasinoservice/wallet-integration?wsdl&rpc&literal
	 * rpc-encoded style
	 * /onlinecasinoservice/wallet-integration?wsdl&rpc&encoded
	 * 
	 * GGL Integration - post transaction payin or payout ...
	 * sessionID: session id number from pc session on web site
	 * customerID: Customer that is playing the Live Dealer
	 * amount: Amount to be credited or debited from the customer balance
	 * transactionType: Identifies if it is a credit (1) or a debit (-1) transaction
	 * user: User name to authenticate against this interface
	 * password: Password to authenticate against this interface
	 * return string or numeric (empty if any error or transaction id if successful)
	 * @param integer $sessionID
	 * @param integer $customerID
	 * @param double $amount
	 * @param integer $transactionType
	 * @param string $gameId
	 * @param string $user
	 * @param string $password
	 * @return string
	 */
	public function postTransaction($sessionID, $customerID, $amount, $transactionType, $gameId, $user, $password){
		if(!isset($sessionID) || !is_numeric($sessionID) || !isset($customerID) || !is_numeric($customerID) || !isset($amount) || !isset($transactionType) || !isset($gameId) || !isset($user) || !isset($password)){
			return "";
		}
		if($amount <= 0 || !is_numeric($amount)){
			$errorHelper = new ErrorHelper();
			$mail_message = "WalletManager::postTransaction: Wrong Amount type is sent. <br /> sessionID = {$sessionID} <br /> customerID = {$customerID} <br /> amount = {$amount} <br /> transactionType = {$transactionType} <br /> user = {$user} <br /> game_id = {$gameId}";
			$log_message = "WalletManager::postTransaction: Wrong Amount type is sent. sessionID = {$sessionID} customerID = {$customerID} amount = {$amount} transactionType = {$transactionType} user = {$user} game_id = {$gameId}";
			$errorHelper->ldcIntegrationError($mail_message, $log_message);
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
				$errorHelper = new ErrorHelper();
				$mail_message = "WalletManager::postTransaction: Wrong transaction type is sent. <br /> Transaction type value = {$transactionType}"; 
				$log_message = "WalletManager::postTransaction: Wrong transaction type is sent. Transaction type value = {$transactionType}";  
				$errorHelper->ldcIntegrationError($mail_message, $log_message);
				return "";
			}
			/*DEBUG MESSAGE - TRACK WHAT IS SENT*/
			/*
			$errorHelper = new ErrorHelper();
			$mail_message = "WalletManager::postTransaction: <br /> sessionID = {$sessionID} <br /> customerID = {$customerID} <br /> amount = {$amount} <br /> transactionType = {$transactionType} <br /> user = {$user} <br /> game_id = {$game_id}";
			$log_message = "WalletManager::postTransaction: sessionID = {$sessionID} customerID = {$customerID} amount = {$amount} transactionType = {$transactionType} user = {$user} game_id = {$game_id}";		
			$errorHelper->ldcIntegrationError($mail_message, $log_message);
			*/
			require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
			$modelSubjectTypes = new SubjectTypesModel();
			if($transactionType == PAYIN_TRANSACTION){
				//managment_types.NAME_IN_CREDITS_FROM_GGL
				$transactionType = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.NAME_IN_CREDITS_FROM_GGL');
			}elseif($transactionType == PAYOUT_TRANSACTION){
				//managment_types.NAME_IN_CREDITS_TO_GGL
				$transactionType = $modelSubjectTypes->getSubjectType('MANAGMENT_TYPES.NAME_IN_CREDITS_TO_GGL');
			}else {
				$errorHelper = new ErrorHelper();
				$mail_message = "WalletManager::postTransaction: <br /> TransactionType is empty value. <br /> sessionID = {$sessionID} <br /> customerID = {$customerID} <br /> amount = {$amount} <br /> transactionType = {$transactionType} <br /> user = {$user} <br /> game_id = {$gameId}";
				$log_message = "WalletManager::postTransaction: TransactionType is empty value. sessionID = {$sessionID} customerID = {$customerID} amount = {$amount} transactionType = {$transactionType} user = {$user} game_id = {$gameId}";
				$errorHelper->ldcIntegrationError($mail_message, $log_message);
				return "";
			}
			
			require_once MODELS_DIR . DS . "WalletModel.php";
			$modelWallet = new WalletModel();
			$arrData = $modelWallet->postTransaction($user, $password, $sessionID, $amount, $transactionType, $gameId);
			//if player exists return transaction id from transaction
			if($arrData['status'] == OK){
				if(strlen($arrData['transaction_id']) == 0){
					$errorHelper = new ErrorHelper();
					$mail_message = "WalletManager::postTransaction - exception error: <br /> Empty transaction number returned. <br /> sessionID = {$sessionID} <br /> customerID = {$customerID} <br /> amount = {$amount} <br /> transactionType = {$transactionType} <br /> user = {$user} <br /> game_id = {$gameId}";
					$log_message = "WalletManager::postTransaction - exception error: Empty transaction number returned. sessionID = {$sessionID} customerID = {$customerID} amount = {$amount} transactionType = {$transactionType} user = {$user} game_id = {$gameId}";
					$errorHelper->ldcIntegrationError($mail_message, $log_message);
					return "";
				}
				return (string)$arrData['transaction_id'];
			}else{ 
				//some error occured on transaction only return empty string
				return "";
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WalletManager::postTransaction - exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex) . "<br /> sessionID = {$sessionID} <br /> customerID = {$customerID} <br /> amount = {$amount} <br /> transactionType = {$transactionType} <br /> user = {$user} <br /> game_id = {$gameId}";
			$log_message = "WalletManager::postTransaction - exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex) . " sessionID = {$sessionID} customerID = {$customerID} amount = {$amount} transactionType = {$transactionType} user = {$user} game_id = {$gameId}";
			$errorHelper->ldcIntegrationError($mail_message, $log_message);
			return "";
		}
	}
}