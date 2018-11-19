<?php
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'sport_betting_integration' . DS . 'walletExceptionBean.php';
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'sport_betting_integration' . DS . 'walletUser.php';
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'sport_betting_integration' . DS . 'bookType.php';
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'sport_betting_integration' . DS . 'walletErrorCode.php';
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'sport_betting_integration' . DS . 'setting.php';
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'sport_betting_integration' . DS . 'walletUserApplicationSettings.php';
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'sport_betting_integration' . DS . 'WalletException.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'SportBettingIntegrationModel.php';
require_once HELPERS_DIR . DS . 'DateTimeHelper.php';

/**
 * 
 * Wallet Service for Sport Betting integration ...
 *
 */
class WalletService{
	var $DEBUG_MODE = false;
	/**
	* This is not implemented, only to generate walletUserApplicationSettings class
	* @return walletUserApplicationSettings
	*/
	protected function GetWalletUserApplicationSettings(){
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
	 * GetUserInfoByUserID(userID) throws WalletException
	 * @param string $userID
	 * @return walletUser
	 * @throws WalletException
	*/
	public function GetUserInfoByUserID($userID){
		$userID = strip_tags($userID);
		if(!isset($userID) || strlen($userID) == 0 || intval($userID) <= 0){
			$procedure = "Parameter missing or not valid format.|GetUserInfoByUserID(userID = {$userID})|RESULT = " . walletErrorCode::UNKNOWN_USERID;
			$errorHelper = new ErrorHelper();
			$message = "SPORT BET INTEGRATION Error: " . $procedure;
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNKNOWN_USERID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_USERID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		//DEBUGGING MESSAGE
        if($this->DEBUG_MODE) {
            $errorHelper = new ErrorHelper();
            $message = "GetUserInfoByUserID(userID = {$userID})";
            $errorHelper->sportBettingIntegrationAccess($message, $message);
        }
		
		$modelSportBettingIntegration = new SportBettingIntegrationModel();
		$user_details = $modelSportBettingIntegration->getUserInfoByUserID($userID);
		
		if($user_details['status'] != OK){
			$procedure = "Result from database is NOT OK.{$user_details['database_message']}|GetUserInfoByUserID(userID = {$userID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
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
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper->sportBettingIntegrationError($message, $message);
			
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
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
        if($user_details['currency_id'] == "ZWD"){
            $currency = "EUR";
        }else{
            $currency = $user_details['currency_id'];
        }
		$walletUser = new walletUser();
		$walletUser->balance = doubleval($user_details["balance"]);
		$walletUser->currencyID = $currency;
		$walletUser->exchangeRate = "";
		$walletUser->firstName = $user_details["first_name"];
		$walletUser->lastName = $user_details["last_name"];
		$walletUser->title = $user_details["title"];
		$walletUser->userID = $user_details["user_id"];
		$walletUser->webshopUID = '';
        $walletUser->countryID = $user_details['country_id'];
        $walletUser->languageID = $user_details['language_id'];
        $walletUser->parentAffiliateID = $user_details['parent_affiliate_id'];
        $walletUser->path = $user_details['path'];
		return $walletUser;
	}
	
	/**
	 * 
	 * GetUserInfo(sessionID) throws WalletException
	 * @param string $sessionID
	 * @return walletUser
	 * @throws WalletException
	*/
	public function GetUserInfo($sessionID){
		$sessionID = strip_tags($sessionID);
		if(!isset($sessionID) || strlen($sessionID) == 0 || intval($sessionID) <= 0){
			$procedure = "Parameter missing or not valid format.|GetUserInfo(sessionID = {$sessionID})|RESULT = " . walletErrorCode::EXPIRED_SESSIONID;
			$errorHelper = new ErrorHelper();
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::EXPIRED_SESSIONID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::EXPIRED_SESSIONID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		//DEBUGGING MESSAGE
        if($this->DEBUG_MODE) {
            $errorHelper = new ErrorHelper();
            $message = "GetUserInfo(sessionID = {$sessionID})";
            $errorHelper->sportBettingIntegrationAccess($message, $message);
        }
		$modelSportBettingIntegration = new SportBettingIntegrationModel();
		$user_details = $modelSportBettingIntegration->getUserInfo($sessionID);
		if($user_details['status'] != OK){
			$procedure = "Result from database is NOT OK.{$user_details['database_message']}|GetUserInfo(sessionID = {$sessionID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($user_details['expired_session_id'] == YES){
			$procedure = "Expired sessionID from database.|GetUserInfo(sessionID = {$sessionID})|RESULT = " . walletErrorCode::EXPIRED_SESSIONID;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::EXPIRED_SESSIONID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::EXPIRED_SESSIONID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($user_details['locked_user'] == YES){
			$procedure = "Locked user from database.|GetUserInfo(sessionID = {$sessionID})|RESULT = " . walletErrorCode::LOCKED_USER;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::LOCKED_USER;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::LOCKED_USER_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($user_details['unexpected_error'] == YES){
			$procedure = "Unexpected error from database.{$user_details['database_message']}|GetUserInfo(sessionID = {$sessionID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
        if($user_details['currency_id'] == "ZWD"){
            $currency = "EUR";
        }else{
            $currency = $user_details['currency_id'];
        }
		$walletUser = new walletUser();
		$walletUser->balance = doubleval($user_details["balance"]);
		$walletUser->currencyID = $currency;
		$walletUser->exchangeRate = "";
		$walletUser->firstName = $user_details["first_name"];
		$walletUser->lastName = $user_details["last_name"];
		$walletUser->title = $user_details["title"];
		$walletUser->userID = $user_details["user_id"];
		$walletUser->webshopUID = '';
        $walletUser->countryID = $user_details['country_id'];
        $walletUser->languageID = $user_details['language_id'];
        $walletUser->parentAffiliateID = $user_details['parent_affiliate_id'];
        $walletUser->path = $user_details['path'];
		return $walletUser;
	}

	/**
	*
	* Book(userID, amount, bookType, allowInsufficientCredit) throws WalletException
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
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		//DEBUGGING MESSAGE
        if($this->DEBUG_MODE) {
            $errorHelper = new ErrorHelper();
            $message = "Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})";
            $errorHelper->sportBettingIntegrationAccess($message, $message);
        }
		if($bookType == 'INC') $bookType = 'INC_MB';
		if($bookType == 'DEC') $bookType = 'DEC_MB';
		$modelSportBettingIntegration = new SportBettingIntegrationModel();
		$result = $modelSportBettingIntegration->book($userID, $amount, $bookType);
		if($result['status'] != OK){
			$procedure = "Result from database is NOT OK.|Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['insufficient_credit'] == YES){
			$procedure = "Insufficient credit from database.|Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})|RESULT = " . walletErrorCode::INSUFFICIENT_CREDIT;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::INSUFFICIENT_CREDIT;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::INSUFFICIENT_CREDIT_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['unexpected_error'] == YES){
			$procedure = "Unexpected error from database.{$result['database_message']}|Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['unknown_userid'] == YES){
			$procedure = "Unknown user id from database.|Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})|RESULT = " . walletErrorCode::UNKNOWN_USERID;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNKNOWN_USERID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_USERID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
        //this is new to return status if false bookType (not INC or DEC)
        if($result['wrong_book_type'] == YES) {
            $procedure = "Unknown book type from database.|Book(userID = {$userID}, amount = {$amount}, bookType = {$bookType}, allowInsufficientCredit = {$allowInsufficientCredit})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);

			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
        }
		return $result['book_id'];
	}
	
	/**
	 * 
	 * RollbackBook(bookID) throws WalletException
	 * @param string $bookID
	 * @throws WalletException
	*/
	public function RollbackBook($bookID){
		$bookID = strip_tags($bookID);
		if(!isset($bookID) || strlen($bookID) == 0 || intval($bookID) <= 0){
			$procedure = "Parameter missing or not valid format.|RollbackBook({$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		
		//DEBUGGING MESSAGE
        if($this->DEBUG_MODE) {
            $errorHelper = new ErrorHelper();
            $message = "RollbackBook({$bookID})";
            $errorHelper->sportBettingIntegrationAccess($message, $message);
        }
		$modelSportBettingIntegration = new SportBettingIntegrationModel();
		$result = $modelSportBettingIntegration->rollbackBook($bookID);
		if($result['status'] != OK){
			$procedure = "Result from database NOT OK.{$result['database_message']}|RollbackBook({$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['invalid_book_id'] == YES){
			$procedure = "Invalid Book ID from database.|RollbackBook({$bookID})|RESULT = " . walletErrorCode::UNKNOWN_BOOKID;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNKNOWN_BOOKID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_BOOKID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['others'] == YES){
			$procedure = "Unexpected error from database.{$result['database_message']}|RollbackBook({$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);
			
			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
	}

    /**
     *
     * TicketInserted(ticketID, application, receiveDateTime, domain, bookID) throws WalletException
     * @param string $ticketID
     * @param string $application
     * @param mixed $receiveDateTime
     * @param string $domain
     * @param string $bookID
     * @throws WalletException
     */
    public function TicketInserted($ticketID, $application, $receiveDateTime, $domain, $bookID){
        $ticketID = strip_tags($ticketID);
        $application = strip_tags($application);
        $domain = strip_tags($domain);
        $bookID = strip_tags($bookID);
        $receiveDateTime = DateTimeHelper::getDateFormat10($receiveDateTime);
        if(!isset($ticketID) || strlen($ticketID) == 0 || !isset($bookID) || strlen($bookID) == 0){
            $procedure = "Parameter missing or not valid format.|TicketInserted(ticketID = {$ticketID}, application = {$application}, receiveDateTime = {$receiveDateTime},
            domain = {$domain}, bookID = {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }

        //DEBUGGING MESSAGE
        if($this->DEBUG_MODE) {
            $errorHelper = new ErrorHelper();
            $message = "TicketInserted(ticketID = {$ticketID}, application = {$application}, receiveDateTime = {$receiveDateTime}, domain = {$domain}, bookID = {$bookID})";
            $errorHelper->sportBettingIntegrationAccess($message, $message);
        }
        $modelSportBettingIntegration = new SportBettingIntegrationModel();
        $result = $modelSportBettingIntegration->ticketInserted($ticketID, $application, $receiveDateTime, $domain, $bookID);

        if($result['status'] != OK){
			$procedure = "Result from database NOT OK.{$result['database_message']}|TicketInserted({$ticketID}, {$application}, {$receiveDateTime}, {$domain}, {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);

			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['invalid_book_id'] == YES){
			$procedure = "Invalid Book ID from database.{$result['database_message']}|TicketInserted({$ticketID}, {$application}, {$receiveDateTime}, {$domain}, {$bookID})|RESULT = " . walletErrorCode::UNKNOWN_BOOKID;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);

			$message = walletErrorCode::UNKNOWN_BOOKID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_BOOKID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['unexpected_error'] == YES){
			$procedure = "Unexpected error from database.{$result['database_message']}|TicketInserted({$ticketID}, {$application}, {$receiveDateTime}, {$domain}, {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);

			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
    }

    /**
     *
     * RollbackTicketInserted(ticketID, application, bookID) throws WalletException
     * @param string $ticketID
     * @param string $application
     * @param string $bookID
     * @throws WalletException
     */
    public function RollbackTicketInserted($ticketID, $application, $bookID){
        $ticketID = strip_tags($ticketID);
        $application = strip_tags($application);
        $bookID = strip_tags($bookID);
        if(!isset($ticketID) || strlen($ticketID) == 0 || !isset($bookID) || strlen($bookID) == 0){
            $procedure = "Parameter missing or not valid format.|RollbackTicketInserted(ticketID = {$ticketID}, application = {$application}, bookID = {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }

        //DEBUGGING MESSAGE
        if($this->DEBUG_MODE) {
            $errorHelper = new ErrorHelper();
            $message = "RollbackTicketInserted(ticketID = {$ticketID}, application = {$application}, bookID = {$bookID})";
            $errorHelper->sportBettingIntegrationAccess($message, $message);
        }
        $modelSportBettingIntegration = new SportBettingIntegrationModel();
        $result = $modelSportBettingIntegration->rollbackTicketInserted($ticketID, $application, $bookID);

        if($result['status'] != OK){
			$procedure = "Result from database NOT OK.{$result['database_message']}|RollbackTicketInserted({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);

			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
        if($result['invalid_ticket_id'] == YES){
            $procedure = "Invalid Ticket ID from database.{$result['database_message']}|RollbackTicketInserted({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNKNOWN_TICKETID;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNKNOWN_TICKETID;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_TICKETID_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
		if($result['invalid_book_id'] == YES){
			$procedure = "Invalid Book ID from database.{$result['database_message']}|RollbackTicketInserted({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNKNOWN_BOOKID;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);

			$message = walletErrorCode::UNKNOWN_BOOKID;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_BOOKID_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
		if($result['unexpected_error'] == YES){
			$procedure = "Unexpected error from database.{$result['database_message']}|RollbackTicketInserted({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
			$message = "SPORT BET INTEGRATION: " . $procedure;
			$errorHelper = new ErrorHelper();
			$errorHelper->sportBettingIntegrationError($message, $message);

			$message = walletErrorCode::UNEXPECTED_ERROR;
			$walletExceptionBean = new walletExceptionBean();
			$walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
			$walletExceptionBean->info = $procedure;
			$walletExceptionBean->message = $message;
			throw new WalletException($message, $walletExceptionBean);
		}
    }

	/**
	 * 
	 * TicketWon(ticketID, application, bookID) throws WalletException
	 * @param string $ticketID
	 * @param string $application
	 * @param string $bookID
	 * @throws WalletException
	*/ 
	public function TicketWon($ticketID, $application, $bookID){
        $ticketID = strip_tags($ticketID);
        $application = strip_tags($application);
        $bookID = strip_tags($bookID);
        if(!isset($ticketID) || strlen($ticketID) == 0 || !isset($bookID) || strlen($bookID) == 0){
            $procedure = "Parameter missing or not valid format.|TicketWon(ticketID = {$ticketID}, application = {$application}, bookID = {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }

        //DEBUGGING MESSAGE
        if($this->DEBUG_MODE) {
            $errorHelper = new ErrorHelper();
            $message = "TicketWon(ticketID = {$ticketID}, application = {$application}, bookID = {$bookID})";
            $errorHelper->sportBettingIntegrationAccess($message, $message);
        }
        $modelSportBettingIntegration = new SportBettingIntegrationModel();
        $result = $modelSportBettingIntegration->ticketWon($ticketID, $application, $bookID);

        if($result['status'] != OK){
            $procedure = "Result from database NOT OK.{$result['database_message']}|TicketWon({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
        if($result['invalid_book_id'] == YES){
            $procedure = "Invalid Book ID from database.{$result['database_message']}|TicketWon({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNKNOWN_BOOKID;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNKNOWN_BOOKID;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_BOOKID_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
        if($result['invalid_ticket_id'] == YES){
            $procedure = "Invalid Ticket ID from database.{$result['database_message']}|TicketWon({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNKNOWN_TICKETID;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNKNOWN_TICKETID;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_TICKETID_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
        if($result['unexpected_error'] == YES){
            $procedure = "Unexpected error from database.{$result['database_message']}|TicketWon({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
	}

	/**
	 * 
	 * RollbackTicketWon(ticketID, application, bookID) throws WalletException
	 * @param string $ticketID
	 * @param string $application
	 * @param string $bookID
	 * @throws WalletException
	*/
	public function RollbackTicketWon($ticketID, $application, $bookID){
        $ticketID = strip_tags($ticketID);
        $application = strip_tags($application);
        $bookID = strip_tags($bookID);
        if(!isset($ticketID) || strlen($ticketID) == 0 || !isset($bookID) || strlen($bookID) == 0){
            $procedure = "Parameter missing or not valid format.|RollbackTicketWon(ticketID = {$ticketID}, application = {$application}, bookID = {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }

        //DEBUGGING MESSAGE
        if($this->DEBUG_MODE) {
            $errorHelper = new ErrorHelper();
            $message = "RollbackTicketWon(ticketID = {$ticketID}, application = {$application}, bookID = {$bookID})";
            $errorHelper->sportBettingIntegrationAccess($message, $message);
        }
        $modelSportBettingIntegration = new SportBettingIntegrationModel();
        $result = $modelSportBettingIntegration->rollbackTicketWon($ticketID, $application, $bookID);

        if($result['status'] != OK){
            $procedure = "Result from database NOT OK.{$result['database_message']}|RollbackTicketWon({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
        if($result['invalid_book_id'] == YES){
            $procedure = "Invalid Book ID from database.{$result['database_message']}|RollbackTicketWon({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNKNOWN_BOOKID;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNKNOWN_BOOKID;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_BOOKID_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
        if($result['invalid_ticket_id'] == YES){
            $procedure = "Invalid Ticket ID from database.{$result['database_message']}|RollbackTicketWon({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNKNOWN_TICKETID;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNKNOWN_TICKETID;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_TICKETID_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
        if($result['unexpected_error'] == YES){
            $procedure = "Unexpected error from database.{$result['database_message']}|RollbackTicketWon({$ticketID}, {$application}, {$bookID})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
	}
	
	/**
	 * 
	 * TicketLost(ticketID, application) throws WalletException
	 * @param string $ticketID
	 * @param string $application
	 * @throws WalletException
	*/
	public function TicketLost($ticketID, $application)
    {
        $ticketID = strip_tags($ticketID);
        $application = strip_tags($application);
        if (!isset($ticketID) || strlen($ticketID) == 0) {
            $procedure = "Parameter missing or not valid format.|TicketLost(ticketID = {$ticketID}, application = {$application})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }

        //DEBUGGING MESSAGE
        if($this->DEBUG_MODE) {
            $errorHelper = new ErrorHelper();
            $message = "TicketLost(ticketID = {$ticketID}, application = {$application})";
            $errorHelper->sportBettingIntegrationAccess($message, $message);
        }
        $modelSportBettingIntegration = new SportBettingIntegrationModel();
        $result = $modelSportBettingIntegration->ticketLost($ticketID, $application);

        if($result['status'] != OK){
            $procedure = "Result from database NOT OK.{$result['database_message']}|TicketLost({$ticketID}, {$application})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
        if($result['invalid_ticket_id'] == YES){
            $procedure = "Invalid Ticket ID from database.{$result['database_message']}|TicketLost({$ticketID}, {$application})|RESULT = " . walletErrorCode::UNKNOWN_TICKETID;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNKNOWN_TICKETID;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_TICKETID_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
        if($result['unexpected_error'] == YES){
            $procedure = "Unexpected error from database.{$result['database_message']}|TicketLost({$ticketID}, {$application})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
	}
	
	/**
	 * 
	 * RollbackTicketLost(ticketID, application) throws WalletException
	 * @param string $ticketID
	 * @param string $application
	 * @throws WalletException
	*/ 
	public function RollbackTicketLost($ticketID, $application){
        $ticketID = strip_tags($ticketID);
        $application = strip_tags($application);
        if(!isset($ticketID) || strlen($ticketID) == 0){
            $procedure = "Parameter missing or not valid format.|RollbackTicketLost(ticketID = {$ticketID}, application = {$application})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }

        //DEBUGGING MESSAGE
        if($this->DEBUG_MODE) {
            $errorHelper = new ErrorHelper();
            $message = "TicketLost(ticketID = {$ticketID}, application = {$application})";
            $errorHelper->sportBettingIntegrationAccess($message, $message);
        }
        $modelSportBettingIntegration = new SportBettingIntegrationModel();
        $result = $modelSportBettingIntegration->rollbackTicketLost($ticketID, $application);

        if($result['status'] != OK){
            $procedure = "Result from database NOT OK.{$result['database_message']}|RollbackTicketLost({$ticketID}, {$application})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
        if($result['invalid_ticket_id'] == YES){
            $procedure = "Invalid Ticket ID from database.{$result['database_message']}|RollbackTicketLost({$ticketID}, {$application})|RESULT = " . walletErrorCode::UNKNOWN_TICKETID;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNKNOWN_TICKETID;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNKNOWN_TICKETID_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
        if($result['unexpected_error'] == YES){
            $procedure = "Unexpected error from database.{$result['database_message']}|RollbackTicketLost({$ticketID}, {$application})|RESULT = " . walletErrorCode::UNEXPECTED_ERROR;
            $message = "SPORT BET INTEGRATION: " . $procedure;
            $errorHelper = new ErrorHelper();
            $errorHelper->sportBettingIntegrationError($message, $message);

            $message = walletErrorCode::UNEXPECTED_ERROR;
            $walletExceptionBean = new walletExceptionBean();
            $walletExceptionBean->errorCode = walletErrorCode::UNEXPECTED_ERROR_CODE;
            $walletExceptionBean->info = $procedure;
            $walletExceptionBean->message = $message;
            throw new WalletException($message, $walletExceptionBean);
        }
	}
}
?>