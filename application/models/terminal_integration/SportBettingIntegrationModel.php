<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
class SportBettingIntegrationModel{
	public function __construct(){
	}

    /**
        Close MaxBet window game for mobile html5 games
     *
    */
    public function closeSportBettingGameWindowForMobilePlatform($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.CLOSE_MB_SESSION_MOBILE(:p_session_id_in, :p_session_id_out, :p_list_of_games_out, :p_credits_out, :p_currency_out, :p_user_name_out, :p_status)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $session_id_out = "";
            $stmt->bindParam(':p_session_id_out', $session_id_out, SQLT_CHR, 255);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_of_games_out', $cursor);
            $credits_out = "";
            $stmt->bindParam(':p_credits_out', $credits_out, SQLT_CHR, 255);
            $currency_out = "";
            $stmt->bindParam(':p_currency_out', $currency_out, SQLT_CHR, 255);
            $user_name_out = "";
            $stmt->bindParam(':p_user_name_out', $user_name_out, SQLT_CHR, 255);
            $status = "";
            $stmt->bindParam(':p_status', $status, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $cursor->execute();
			$cursor->free();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "session_id"=>$session_id_out, "status_out"=>$status, "credits"=>$credits_out,
                "currency"=>$currency_out, "username"=>$user_name_out, "list_games"=>$cursor);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.CLOSE_MB_SESSION_MOBILE(:p_session_id_in = {$session_id}, :p_session_id_out =, :p_list_of_games_out, :p_credits_out, :p_currency_out, :p_user_name_out, :p_status) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
        Close MaxBet window game
     *
    */
    public function closeSportBettingGameWindow($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.CLOSE_MB_SESSION(:p_session_id_in, :p_status)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $status = "";
            $stmt->bindParam(':p_status', $status, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "session_id"=>$session_id, "status_out"=>$status);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.CLOSE_MB_SESSION(p_session_id_in = {$session_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
        Performes opening sport betting session by pc session id when player clicks on SPORT INTEGRATION Game Button
     *
    */
    public function openBetKioskSession($pc_session_id, $ip_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.OPEN_BETKIOSK_SESSION(:p_session_id_in, :p_ip_address_in,:p_session_id_out)');
            $stmt->bindParam(':p_session_id_in', $pc_session_id);
            $stmt->bindParam(':p_ip_address_in', $ip_address);
            $session_id_out="";
            $stmt->bindParam(':p_session_id_out', $session_id_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "pc_session_id"=>$pc_session_id, "session_id_out"=>$session_id_out);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.OPEN_BETKIOSK_SESSION(p_session_id_in = {$pc_session_id}, p_ip_address_in = {$ip_address}, p_session_id_out =) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
        Performes opening sport betting session by pc session id when player clicks on SPORT INTEGRATION Game Button
     *
    */
    public function openMaxBetSession($pc_session_id, $ip_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.OPEN_MAXBET_SESSION(:p_session_id_in, :p_ip_address_in, :p_session_id_out)');
            $stmt->bindParam(':p_session_id_in', $pc_session_id);
            $stmt->bindParam(':p_ip_address_in', $ip_address);
            $session_id_out="";
            $stmt->bindParam(':p_session_id_out', $session_id_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "pc_session_id"=>$pc_session_id, "session_id_out"=>$session_id_out);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.OPEN_MAXBET_SESSION(p_session_id_in = {$pc_session_id}, p_ip_address_in = {$ip_address}, p_session_id_out =) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
        Performes opening sport betting session by pc session id when player clicks on SPORT INTEGRATION Game Button
     *
    */
    public function openMemoBetGameSession($pc_session_id, $ip_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.OPEN_MEMO_SESSION(:p_session_id_in, :p_ip_address_in, :p_session_id_out)');
            $stmt->bindParam(':p_session_id_in', $pc_session_id);
            $stmt->bindParam(':p_ip_address_in', $ip_address);
            $session_id_out="";
            $stmt->bindParam(':p_session_id_out', $session_id_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "pc_session_id"=>$pc_session_id, "session_id_out"=>$session_id_out);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.OPEN_MEMO_SESSION(p_session_id_in = {$pc_session_id}, p_ip_address_in = {$ip_address}, p_session_id_out =) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
		Returns Player information based on PC Session ID from Game Client
	*/
	public function getUserInfo($pc_session_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL BETKIOSK.getUserInfo(:p_session_id_in, :userID, :firstName, :lastName, :title, :languageID, :balance, :currencyID, :countryID, :parentID, :playerPath,
			:EXPIRED_SESSIONID, :LOCKED_USER, :UNEXPECTED_ERROR)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$user_id = "";
			$stmt->bindParam(':userID', $user_id, SQLT_CHR, 255);
			$first_name = "";
			$stmt->bindParam(':firstName', $first_name, SQLT_CHR, 255);
			$last_name = "";
			$stmt->bindParam(':lastName', $last_name, SQLT_CHR, 255);
			$title = "";
			$stmt->bindParam(':title', $title, SQLT_CHR, 255);
			$languageID = "";
			$stmt->bindParam(':languageID', $languageID, SQLT_CHR, 255);
			$balance = "";
			$stmt->bindParam(':balance', $balance, SQLT_CHR, 255);
			$currency_id = "";
			$stmt->bindParam(':currencyID', $currency_id, SQLT_CHR, 255);
			$expired_session_id = "";
            $country_id = "";
            $stmt->bindParam(':countryID', $country_id, SQLT_CHR, 255);
            $parent_id = "";
            $stmt->bindParam(':parentID', $parent_id, SQLT_CHR, 255);
            $player_path = "";
            $stmt->bindParam(':playerPath', $player_path, SQLT_CHR, 255);
			$stmt->bindParam(':EXPIRED_SESSIONID', $expired_session_id, SQLT_CHR, 255);
			$locked_user = "";
			$stmt->bindParam(':LOCKED_USER', $locked_user, SQLT_CHR, 255);
			$unexpected_error = "";
			$stmt->bindParam(':UNEXPECTED_ERROR', $unexpected_error, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "session_id"=>$pc_session_id, "user_id"=>$user_id, "first_name"=>$first_name,
			"last_name"=>$last_name, "title"=>$title, "language_id"=>$languageID, "balance"=>$balance,
			"currency_id"=>$currency_id, "country_id"=>$country_id, "parent_affiliate_id"=>$parent_id, "path"=>$player_path,
            "expired_session_id"=>$expired_session_id, "locked_user"=>$locked_user, "unexpected_error"=>$unexpected_error);
		}catch(Zend_Exception $ex){
			//RAISE_APPLICATION_ERROR(-20232,'Currency is not valid !!!!!');
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
			$message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.getUserInfo(p_session_id = {$pc_session_id}, userID, firstName, lastName, title, languageID, balance, currencyID, countryID, parentID, playerPath, EXPIRED_SESSIONID, LOCKED_USER, UNEXPECTED_ERROR) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
		}
	}

    /**
    Returns player information based on UserID (player id) from GameClient
     */
    public function getUserInfoByUserID($user_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.getUserInfoByUserID(:userID, :firstName, :lastName, :title, :languageID, :balance, :currencyID, :countryID, :parentID, :playerPath,
            :UNKNOWN_USERID, :UNEXPECTED_ERROR)');
            $stmt->bindParam(':userID', $user_id);
            $first_name = "";
            $stmt->bindParam(':firstName', $first_name, SQLT_CHR, 255);
            $last_name = "";
            $stmt->bindParam(':lastName', $last_name, SQLT_CHR, 255);
            $title = "";
            $stmt->bindParam(':title', $title, SQLT_CHR, 255);
            $languageID = "";
            $stmt->bindParam(':languageID', $languageID, SQLT_CHR, 255);
            $balance = "";
            $stmt->bindParam(':balance', $balance, SQLT_CHR, 255);
            $currency_id = "";
            $stmt->bindParam(':currencyID', $currency_id, SQLT_CHR, 255);
            $country_id = "";
            $stmt->bindParam(':countryID', $country_id, SQLT_CHR, 255);
            $parent_id = "";
            $stmt->bindParam(':parentID', $parent_id, SQLT_CHR, 255);
            $player_path = "";
            $stmt->bindParam(':playerPath', $player_path, SQLT_CHR, 255);
            $unknown_userid = "";
            $stmt->bindParam(':UNKNOWN_USERID', $unknown_userid, SQLT_CHR, 255);
            $unexpected_error = "";
            $stmt->bindParam(':UNEXPECTED_ERROR', $unexpected_error, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "user_id"=>$user_id, "first_name"=>$first_name,
                "last_name"=>$last_name, "title"=>$title, "language_id"=>$languageID, "balance"=>$balance,
                "currency_id"=>$currency_id, "country_id"=>$country_id, "parent_affiliate_id"=>$parent_id, "path"=>$player_path,
                "unknown_userid"=>$unknown_userid, "unexpected_error"=>$unexpected_error);
        }catch(Zend_Exception $ex){
            //RAISE_APPLICATION_ERROR(-20232,'Currency is not valid !!!!!');
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.getUserInfoByUserID(userID = {$user_id}, :firstName, :lastName, :title, :languageID, :balance, :currencyID, :countryID, :parentID, :playerPath, :UNKNOWN_USERID, :UNEXPECTED_ERROR) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
        }
    }

    /**
    Performes payin/payout transaction made on betting
     */
    public function book($user_id, $amount, $book_type){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.BOOK(:userID, :amount, :BookType, :BookID, :INSUFFICIENT_CREDIT, :UNEXPECTED_ERROR, :UNKNOWN_USERID, :WRONG_BOOK_TYPE)');
            $stmt->bindParam(':userID', $user_id);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':BookType', $book_type);
            $book_id = "";
            $stmt->bindParam(':BookID', $book_id, SQLT_CHR, 255);
            $insufficient_credit = "";
            $stmt->bindParam(':INSUFFICIENT_CREDIT', $insufficient_credit, SQLT_CHR, 255);
            $unexpected_error = "";
            $stmt->bindParam(':UNEXPECTED_ERROR', $unexpected_error, SQLT_CHR, 255);
            $unknown_userid = "";
            $stmt->bindParam(':UNKNOWN_USERID', $unknown_userid, SQLT_CHR, 255);
            $wrong_book_type = "";
            $stmt->bindParam(':WRONG_BOOK_TYPE', $wrong_book_type, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "book_id"=>$book_id,
                "insufficient_credit"=>$insufficient_credit, "unexpected_error"=>$unexpected_error,
                "unknown_userid"=>$unknown_userid, "wrong_book_type"=>$wrong_book_type);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.BOOK(userID = {$user_id}, amount = {$amount}, BookType = {$book_type}, :BookID, :INSUFFICIENT_CREDIT, :UNEXPECTED_ERROR, :UNKNOWN_USERID, :WRONG_BOOK_TYPE) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
        }
    }

	/**
		Rollbacks transaction made on betting
	*/
	public function rollbackBook($book_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL BETKIOSK.rollbackBook(:bookID, :p_invalid_book_id, :p_others)');
			$stmt->bindParam(':bookID', $book_id);
			$invalid_book_id = "";
			$stmt->bindParam(':p_invalid_book_id', $invalid_book_id);
			$others = "";
			$stmt->bindParam(':p_others', $others, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "book_id"=>$book_id, 
			"invalid_book_id"=>$invalid_book_id, "others"=>$others);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.rollbackBook(bookID = {$book_id}, p_invalid_book_id, p_others) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
		}
	}

    /**
    Ticket inserted event
    BETKIOSK.ticketInserted  (ticketID         IN sport_bet_reservations.ticket_ID%type,
    application      IN sport_bet_reservations.application%type,
    receiveDateTime  IN sport_bet_reservations.received_ticket_time%type,
    domain           IN sport_bet_reservations.domain%type,
    bookID           IN sport_bet_reservations.ID%TYPE,
    unknown_bookid   OUT managment_types.yes%type,
    unexpected_error OUT managment_types.yes%type)
    */
    public function ticketInserted($ticket_id, $application, $receiveDateTime, $domain, $bookID){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.ticketInserted(:ticketID, :application, :receiveDateTime, :domain, :bookID, :unknown_bookid, :unexpected_error)');
            $stmt->bindParam(':ticketID', $ticket_id);
            $stmt->bindParam(':application', $application);
            $stmt->bindParam(':receiveDateTime', $receiveDateTime);
            $stmt->bindParam(':domain', $domain);
            $stmt->bindParam(':bookID', $bookID);
            $unknown_bookid = "";
            $stmt->bindParam(':unknown_bookid', $unknown_bookid, SQLT_CHR, 255);
            $unexpected_error = "";
            $stmt->bindParam(':unexpected_error', $unexpected_error, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "ticketID"=>$ticket_id, "application"=>$application, "receiveDateTime"=>$receiveDateTime, "domain"=>$domain, "book_id"=>$bookID,
                "invalid_book_id"=>$unknown_bookid, "unexpected_error"=>$unexpected_error);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.ticketInserted(ticketID = {$ticket_id}, application = {$application}, receiveDateTime = {$receiveDateTime},
                domain = {$domain}, bookID = {$bookID}, unknown_bookid, unexpected_error) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
        }
    }

    /*
    PROCEDURE rollbackTicketInserted  (ticketID         IN sport_bet_reservations.ticket_ID%type,
    application      IN sport_bet_reservations.application%type,
    bookID           IN sport_bet_reservations.ID%TYPE,
    unknown_ticketid OUT managment_types.yes%type,
    unknown_bookid   OUT managment_types.yes%type,
    unexpected_error OUT managment_types.yes%type)
    */
    public function rollbackTicketInserted($ticket_id, $application, $bookID){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.rollbackTicketInserted(:ticketID, :application, :bookID, :unknown_ticketid, :unknown_bookid, :unexpected_error)');
            $stmt->bindParam(':ticketID', $ticket_id);
            $stmt->bindParam(':application', $application);
            $stmt->bindParam(':bookID', $bookID);
            $unknown_ticketid = "";
            $stmt->bindParam(':unknown_ticketid', $unknown_ticketid, SQLT_CHR, 255);
            $unknown_bookid = "";
            $stmt->bindParam(':unknown_bookid', $unknown_bookid, SQLT_CHR, 255);
            $unexpected_error = "";
            $stmt->bindParam(':unexpected_error', $unexpected_error, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "ticketID"=>$ticket_id, "application"=>$application, "bookID"=>$bookID,
               "invalid_ticketid"=>$unknown_ticketid, "invalid_book_id"=>$unknown_bookid, "unexpected_error"=>$unexpected_error
            );
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.rollbackTicketInserted(ticketID = {$ticket_id}, application = {$application}, bookID = {$bookID}, unknown_bookid, unexpected_error) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
        }
    }

    /*
    PROCEDURE ticketWon  (ticketID         IN sport_bet_reservations.ticket_ID%type,
    application      IN sport_bet_reservations.application%type,
    bookID           IN sport_bet_reservations.ID%TYPE,
    unknown_ticketid OUT managment_types.yes%type,
    unknown_bookid   OUT managment_types.yes%type,
    unexpected_error OUT managment_types.yes%type);
    */
    public function ticketWon($ticket_id, $application, $bookID){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.ticketWon(:ticketID, :application_desc, :bookID, :unknown_ticketid, :unknown_bookid, :unexpected_error)');
            $stmt->bindParam(':ticketID', $ticket_id);
            $stmt->bindParam(':application_desc', $application);
            $stmt->bindParam(':bookID', $bookID);
            $unknown_ticketid = "";
            $stmt->bindParam(':unknown_ticketid', $unknown_ticketid, SQLT_CHR, 255);
            $unknown_bookid = "";
            $stmt->bindParam(':unknown_bookid', $unknown_bookid, SQLT_CHR, 255);
            $unexpected_error = "";
            $stmt->bindParam(':unexpected_error', $unexpected_error, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "ticketID"=>$ticket_id, "application"=>$application, "bookID"=>$bookID,
               "invalid_ticket_id"=>$unknown_ticketid, "invalid_book_id"=>$unknown_bookid, "unexpected_error"=>$unexpected_error);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.ticketWon(ticketID = {$ticket_id}, application = {$application}, bookID = {$bookID}, unknown_bookid, unexpected_error) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
        }
    }

    /*
        PROCEDURE rollbackTicketWon  (ticketID         IN sport_bet_reservations.ticket_ID%type,
        application      IN sport_bet_reservations.application%type,
        bookID           IN sport_bet_reservations.ID%TYPE,
        unknown_ticketid OUT managment_types.yes%type,
        unknown_bookid   OUT managment_types.yes%type,
        unexpected_error OUT managment_types.yes%type);
    */
    public function rollbackTicketWon($ticket_id, $application, $bookID){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.rollbackTicketWon(:ticketID, :application_desc, :bookID, :unknown_ticketid, :unknown_bookid, :unexpected_error)');
            $stmt->bindParam(':ticketID', $ticket_id);
            $stmt->bindParam(':application_desc', $application);
            $stmt->bindParam(':bookID', $bookID);
            $unknown_ticketid = "";
            $stmt->bindParam(':unknown_ticketid', $unknown_ticketid, SQLT_CHR, 255);
            $unknown_bookid = "";
            $stmt->bindParam(':unknown_bookid', $unknown_bookid, SQLT_CHR, 255);
            $unexpected_error = "";
            $stmt->bindParam(':unexpected_error', $unexpected_error, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "ticketID"=>$ticket_id, "application"=>$application, "bookID"=>$bookID,
                "invalid_ticket_id"=>$unknown_ticketid, "invalid_book_id"=>$unknown_bookid, "unexpected_error"=>$unexpected_error);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.rollbackTicketWon(ticketID = {$ticket_id}, application = {$application}, bookID = {$bookID}, unknown_ticketid, unknown_bookid, unexpected_error) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
        }
    }

    /*
    PROCEDURE ticketLost  (ticketID         IN sport_bet_reservations.ticket_ID%type,
    application      IN sport_bet_reservations.application%type,
    unknown_ticketid OUT managment_types.yes%type,
    unexpected_error OUT managment_types.yes%type);
    */
    public function ticketLost($ticket_id, $application){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.ticketLost(:ticketID, :application_desc, :unknown_ticketid, :unexpected_error)');
            $stmt->bindParam(':ticketID', $ticket_id);
            $stmt->bindParam(':application_desc', $application);
            $unknown_ticketid = "";
            $stmt->bindParam(':unknown_ticketid', $unknown_ticketid, SQLT_CHR, 255);
            $unexpected_error = "";
            $stmt->bindParam(':unexpected_error', $unexpected_error, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "ticketID"=>$ticket_id, "application"=>$application,
                "invalid_ticket_id"=>$unknown_ticketid, "unexpected_error"=>$unexpected_error);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.ticketLost(ticketID = {$ticket_id}, application = {$application}, unknown_ticketid, unexpected_error) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
        }
    }

    /*
    PROCEDURE rollbackTicketLost  (ticketID         IN sport_bet_reservations.ticket_ID%type,
    application      IN sport_bet_reservations.application%type,
    unknown_ticketid OUT managment_types.yes%type,
    unexpected_error OUT managment_types.yes%type);
    */
    public function rollbackTicketLost($ticket_id, $application){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL BETKIOSK.rollbackTicketLost(:ticketID, :application_desc, :unknown_ticketid, :unexpected_error)');
            $stmt->bindParam(':ticketID', $ticket_id);
            $stmt->bindParam(':application_desc', $application);
            $unknown_ticketid = "";
            $stmt->bindParam(':unknown_ticketid', $unknown_ticketid, SQLT_CHR, 255);
            $unexpected_error = "";
            $stmt->bindParam(':unexpected_error', $unexpected_error, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "ticketID"=>$ticket_id, "application"=>$application,
                "invalid_ticket_id"=>$unknown_ticketid, "unexpected_error"=>$unexpected_error);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SPORT BET INTEGRATION Error <br /> BETKIOSK.rollbackTicketLost(ticketID = {$ticket_id}, application = {$application}, unknown_ticketid, unexpected_error) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
        }
    }

    /*
        Checks and performes pending transactions on player for Malta
    */
	/*public function checkPendingTransactionsForPlayer($player_id, $pc_session_id){
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WETPUNKT_INTEGRATION.CHECK_PENDING_TRANS_FOR_PLAYER(:p_player_id_in, :p_session_id_in)');
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "player_id"=>$player_id, "pc_session_id"=>$pc_session_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "BETTING INTEGRATION: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->bettingIntegrationError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
		}
	}*/
}