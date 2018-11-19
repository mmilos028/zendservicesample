<?php
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'walletExceptionBean.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'walletUser.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'bookType.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'walletErrorCode.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'setting.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'walletUserApplicationSettings.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'WalletException.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once MODELS_DIR . DS . 'CbcxIntegrationModel.php';

/**
 * 
 * Wallet Service for CBCX casino integration ...
 *
 */
class WalletService{
	
	/**
	* This is not implemented, only to generate walletUserApplicationSettings class
	* @return walletUserApplicationSettings
	*/
	public function GetWalletUserApplicationSettings(){
	}
	
	/**
	* This is not implemented, only to generate setting class
	* @return setting
	*/
	public function GetSetting(){
	}
	
	/**
	 * This is not implemented, only to generate WalletException class
	 * @return WalletException
	*/
	public function GetWalletException(){
	}
	
	/**
	 * 
	 * getUserInfo with userID (player id)
	 * @param string $userID
	 * @return walletUser
	 * @throws WalletException
	*/
	public function GetUserInfoByUserID($userID){
		$userID = strip_tags($userID);
		if(!isset($userID) || strlen($userID) == 0 || intval($userID) <= 0){
			$procedure = "Parameter missing or not valid format.|GetUserInfoByUserID(userID = {$userID})|RESULT = " . walletErrorCode::UNKNOWN_USERID;
			$errorHelper = new ErrorHelper();
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNKNOWN_USERID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_USERID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		//DEBUGGING MESSAGE		
		//$errorHelper = new ErrorHelper();
		//$message = "GetUserInfoByUserID(userID = {$userID})";
		//$errorHelper->cbcxAccess($message, $message);
		
		$modelCbcxIntegration = new CbcxIntegrationModel();
		$user_details = $modelCbcxIntegration->getUserInfoByUserID($userID);
		
		if($user_details['status'] != OK){
			$procedure = "Result from database is NOT OK.{$user_details['database_message']}|GetUserInfoByUserID(userID = {$userID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($user_details['unknown_userid'] == YES){
			$procedure = "Unknown userID from database.|GetUserInfoByUserID(userID = {$userID})|RESULT = " . walletErrorCode::UNKNOWN_USERID;
			$errorHelper = new ErrorHelper();
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNKNOWN_USERID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_USERID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($user_details['unexpected_error'] == YES){
			$procedure = "Unexpected error from database.{$user_details['database_message']}|GetUserInfoByUserID(userID = {$userID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$errorHelper = new ErrorHelper();
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		$walletUser = new walletUser();
		$walletUser->balance = doubleval($user_details["balance"]);
		$walletUser->currencyID = $user_details["currency_id"];
		$walletUser->exchangeRate = "";
		$walletUser->firstName = $user_details["first_name"];
		$walletUser->lastName = $user_details["last_name"];
		$walletUser->title = $user_details["title"];
		$walletUser->userID = $user_details["user_id"];
		$walletUser->webshopUID = '';
		return $walletUser;
	}
	
	/**
	 * 
	 * getUserInfo with sessionID (pc_session_id)
	 * @param string $sessionID
	 * @return walletUser
	 * @throws WalletException
	*/
	public function GetUserInfo($sessionID){
		$sessionID = strip_tags($sessionID);
		if(!isset($sessionID) || strlen($sessionID) == 0 || intval($sessionID) <= 0){
			$procedure = "Parameter missing or not valid format.|GetUserInfo(sessionID = {$sessionID})|RESULT = " . walletErrorCode::EXPIRED_SESSIONID;
			$errorHelper = new ErrorHelper();
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::EXPIRED_SESSIONID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::EXPIRED_SESSIONID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		//DEBUGGING MESSAGE
		//$errorHelper = new ErrorHelper();
		//$message = "GetUserInfo(sessionID = {$sessionID})";
		//$errorHelper->cbcxAccess($message, $message);
		$modelCbcxIntegration = new CbcxIntegrationModel();
		$user_details = $modelCbcxIntegration->getUserInfo($sessionID);
		if($user_details['status'] != OK){
			$procedure = "Result from database is NOT OK.{$user_details['database_message']}|GetUserInfo(sessionID = {$sessionID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($user_details['expired_session_id'] == YES){
			$procedure = "Expired sessionID from database.|GetUserInfo(sessionID = {$sessionID})|RESULT = " . walletErrorCode::EXPIRED_SESSIONID;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::EXPIRED_SESSIONID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::EXPIRED_SESSIONID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($user_details['locked_user'] == YES){
			$procedure = "Locked user from database.|GetUserInfo(sessionID = {$sessionID})|RESULT = " . walletErrorCode::LOCKED_USER;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::LOCKED_USER;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::LOCKED_USER_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($user_details['unexpected_error'] == YES){
			$procedure = "Unexpected error from database.{$user_details['database_message']}|GetUserInfo(sessionID = {$sessionID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		$walletUser = new walletUser();
		$walletUser->balance = doubleval($user_details["balance"]);
		$walletUser->currencyID = $user_details["currency_id"];
		$walletUser->exchangeRate = "";
		$walletUser->firstName = $user_details["first_name"];
		$walletUser->lastName = $user_details["last_name"];
		$walletUser->title = $user_details["title"];
		$walletUser->userID = $user_details["user_id"];
		$walletUser->webshopUID = '';
		return $walletUser;
	}
	
	/**
	*
	* Book transaction...
	* @param string $userID
	* @param double $amount
	* @param bookType $bookType
	* @param boolean $allowInsufficientCredit
	* @return string
	* @throws WalletException
	*/
	public function Book($userID, $amount, $bookType, $allowInsufficientCredit){
		$userID = strip_tags($userID);
		$bookType = strip_tags($bookType);
		if(!isset($userID) || strlen($userID) == 0 || intval($userID) <= 0 || $amount <= 0){
			$procedure = "Parameter missing or not valid format.|Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		//DEBUGGING MESSAGE
		//$errorHelper = new ErrorHelper();
		//$message = "Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})";
		//$errorHelper->cbcxAccess($message, $message);
		if($bookType == 'INC') $bookType = 'INC_CBC';
		if($bookType == 'DEC') $bookType = 'DEC_CBC';
		$modelCbcxIntegration = new CbcxIntegrationModel();
		$result = $modelCbcxIntegration->book($userID, $amount, $bookType);
		if($result['status'] != OK){
			$procedure = "Result from database is NOT OK.|Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['insufficient_credit'] == YES){
			$procedure = "Insufficient credit from database.|Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})|RESULT = " . walletErrorCode::INSUFFICIENT_CREDIT;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::INSUFFICIENT_CREDIT;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::INSUFFICIENT_CREDIT_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['unexpected_error'] == YES){
			$procedure = "Unexpected error from database.{$result['database_message']}|Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['unknown_userid'] == YES){
			$procedure = "Unknown user id from database.|Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})|RESULT = " . walletErrorCode::UNKNOWN_USERID;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNKNOWN_USERID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_USERID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		return $result['book_id'];
	}
	
	/**
	 * 
	 * RollbackBook transaction ...
	 * @param string $bookID
	 * @throws WalletException
	*/
	public function RollbackBook($bookID){
		$bookID = strip_tags($bookID);
		if(!isset($bookID) || strlen($bookID) == 0 || intval($bookID) <= 0){
			$procedure = "Parameter missing or not valid format.|RollbackBook({$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		
		//DEBUGGING MESSAGE
		//$errorHelper = new ErrorHelper();
		//$message = "RollbackBook({$bookID})";
		//$errorHelper->cbcxAccess($message, $message);
		
		$modelCbcxIntegration = new CbcxIntegrationModel();
		$result = $modelCbcxIntegration->rollbackBook($bookID);
		if($result['status'] != OK){
			$procedure = "Result from database NOT OK.{$result['database_message']}|RollbackBook({$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['invalid_book_id'] == YES){
			$procedure = "Invalid Book ID from database.|RollbackBook({$bookID})|RESULT = " . walletErrorCode::UNKNOWN_BOOKID;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNKNOWN_BOOKID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_BOOKID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['others'] == YES){
			$procedure = "Unexpected error from database.{$result['database_message']}|RollbackBook({$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "CBCX INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->cbcxError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $message;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
	}
	
	/**
	 *
	 * TicketInserted notification ... This will not be implemented
	 * @param string $ticketID
	 * @param string $application
	 * @param mixed $receiveDateTime
	 * @param string $domain
	 * @param string $bookID
	 * @throws WalletException
	*/
	public function TicketInserted($ticketID, $application, $receiveDateTime, $domain, $bookID){
		$procedure = "TicketInserted(tickedID = {$ticketID}, application = {$application}, receiveDateTime = {$receiveDateTime}, domain = {$domain}, bookID = {$bookID})";
		$errorHelper = new ErrorHelper();
		$errorHelper->cbcxAccessLog($procedure);
	}
	
	/**
	 * 
	 * RollbackTicketInserted transaction ... This will not be implemented
	 * @param string $ticketID
	 * @param string $application
	 * @param string $bookID
	 * @throws WalletException
	*/ 
	public function RollbackTicketInserted($ticketID, $application, $bookID){
		$procedure = "RollbackTicketInserted(tickedID = {$ticketID}, application = {$application}, bookID = {$bookID})";
		$errorHelper = new ErrorHelper();
		$errorHelper->cbcxAccessLog($procedure);
	}
	
	/**
	 * 
	 * TicketWon transaction ... This will not be implemented
	 * @param string $ticketID
	 * @param string $application
	 * @param string $bookID
	 * @throws WalletException
	*/ 
	public function TicketWon($ticketID, $application, $bookID){
		$procedure = "TicketWon(tickedID = {$ticketID}, application = {$application}, bookID = {$bookID})";
		$errorHelper = new ErrorHelper();
		$errorHelper->cbcxAccessLog($procedure);
	}
	
	/**
	 * 
	 * RollbackTicketWon transaction ... This will not be implemented
	 * @param string $ticketID
	 * @param string $application
	 * @param string $bookID
	 * @throws WalletException
	*/
	public function RollbackTicketWon($ticketID, $application, $bookID){
		$procedure = "RollbackTicketWon(tickedID = {$ticketID}, application = {$application}, bookID = {$bookID})";
		$errorHelper = new ErrorHelper();
		$errorHelper->cbcxAccessLog($procedure);
	}
	
	/**
	 * 
	 * TicketLost transaction ... This will not be implemented
	 * @param string $ticketID
	 * @param string $application
	 * @throws WalletException
	*/
	public function TicketLost($ticketID, $application){
		$procedure = "TicketLost(tickedID = {$ticketID}, application = {$application})";
		$errorHelper = new ErrorHelper();
		$errorHelper->cbcxAccessLog($procedure);
	}
	
	/**
	 * 
	 * RollbackTicketLost transaction ... This will not be implemented
	 * @param string $ticketID
	 * @param string $application
	 * @throws WalletException
	*/ 
	public function RollbackTicketLost($ticketID, $application){
		$procedure = "RollbackTicketLost(tickedID = {$ticketID}, application = {$application})";
		$errorHelper = new ErrorHelper();
		$errorHelper->cbcxAccessLog($procedure);
	}
}
?>