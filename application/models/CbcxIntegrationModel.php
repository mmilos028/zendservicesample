<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class CbcxIntegrationModel{
	public function __construct(){
	}
	
	/** 
		Returns Player information based on PC Session ID from web site
		for CBC (DOROBET) integration system on Malta
	*/
    /**
     * @param $pc_session_id
     * @return array
     * @throws Zend_Exception
     */
	public function getUserInfo($pc_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL CBC_INTEGRATION.getUserInfo(:p_session_id_in, :userID, :firstName, :lastName, :title, :languageID, :balance, :currencyID, :EXPIRED_SESSIONID, :LOCKED_USER, :UNEXPECTED_ERROR)');
			$stmt = $dbAdapter->prepare('CALL CBC_INTEGRATION.GET_USER_INFO_NEW(:p_session_id_in, :userID, :firstName, :lastName, :title, :languageID, :balance, :currencyID, :EXPIRED_SESSIONID, :LOCKED_USER, :UNEXPECTED_ERROR)');
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
			$stmt->bindParam(':EXPIRED_SESSIONID', $expired_session_id, SQLT_CHR, 255);
			$locked_user = "";
			$stmt->bindParam(':LOCKED_USER', $locked_user, SQLT_CHR, 255);
			$unexpected_error = "";
			$stmt->bindParam(':UNEXPECTED_ERROR', $unexpected_error, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "session_id"=>$pc_session_id, "user_id"=>$user_id, "first_name"=>$first_name,
			"last_name"=>$last_name, "title"=>$title, "languageID"=>$languageID, "balance"=>$balance,
			"currency_id"=>$currency_id, "expired_session_id"=>$expired_session_id, "locked_user"=>$locked_user, 
			"unexpected_error"=>$unexpected_error);
		}catch(Zend_Exception $ex){
			//RAISE_APPLICATION_ERROR(-20232,'Currency is not valid !!!!!');
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "CBC INTEGRATION: CBC_INTEGRATION.GET_USER_INFO_NEW >" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->cbcxError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
		}
	}
	
	/**
		Returns player information based on UserID (player id)
		for Malta web site
	*/
    /**
     * @param $user_id
     * @return array
     * @throws Zend_Exception
     */
	public function getUserInfoByUserID($user_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL CBC_INTEGRATION.getUserInfoByUserID(:userID, :firstName, :lastName, :title, :languageID, :balance, :currencyID, :UNKNOWN_USERID, :UNEXPECTED_ERROR)');
			$stmt = $dbAdapter->prepare('CALL CBC_INTEGRATION.GET_USER_INFO_BY_USER_ID(:userID, :firstName, :lastName, :title, :languageID, :balance, :currencyID, :UNKNOWN_USERID, :UNEXPECTED_ERROR)');
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
			$unknown_userid = "";
			$stmt->bindParam(':UNKNOWN_USERID', $unknown_userid, SQLT_CHR, 255);
			$unexpected_error = "";
			$stmt->bindParam(':UNEXPECTED_ERROR', $unexpected_error, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "user_id"=>$user_id, "first_name"=>$first_name,
					"last_name"=>$last_name, "title"=>$title, "languageID"=>$languageID, "balance"=>$balance,
					"currency_id"=>$currency_id, "unknown_userid"=>$unknown_userid, "unexpected_error"=>$unexpected_error);
		}catch(Zend_Exception $ex){
			//RAISE_APPLICATION_ERROR(-20232,'Currency is not valid !!!!!');
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "CBC INTEGRATION: CBC_INTEGRATION.GET_USER_INFO_BY_USER_ID >" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->cbcxError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
		}
	}

    /**
     * @param $pc_session_id
     * @return array
     * @throws Zend_Exception
     */
	public function openCbcSession($pc_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL CBC_INTEGRATION.M$OPEN_CBC_SESSION(:p_session_id_in)');
			$stmt = $dbAdapter->prepare('CALL CBC_INTEGRATION.OPEN_CBC_SESSION(:p_session_id_in)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "pc_session_id"=>$pc_session_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "CBC INTEGRATION: CBC_INTEGRATION.OPEN_CBC_SESSION > " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->cbcxError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
     * @param $user_id
     * @param $amount
     * @param $book_type
     * @return array
     * @throws Zend_Exception
     */
	public function book($user_id, $amount, $book_type){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL CBC_INTEGRATION.book(:userID, :amount, :BookType, :BookID, :INSUFFICIENT_CREDIT, :UNEXPECTED_ERROR, :UNKNOWN_USERID)');
			$stmt = $dbAdapter->prepare('CALL CBC_INTEGRATION.BOOK_NEW(:userID, :amount, :BookType, :BookID, :INSUFFICIENT_CREDIT, :UNEXPECTED_ERROR, :UNKNOWN_USERID)');
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
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "book_id"=>$book_id,
			"insufficient_credit"=>$insufficient_credit, "unexpected_error"=>$unexpected_error,
			"unknown_userid"=>$unknown_userid);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "CBC INTEGRATION: CBC_INTEGRATION.BOOK_NEW >" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->cbcxError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
		}
	}

    /**
     * @param $book_id
     * @return array
     * @throws Zend_Exception
     */
	public function rollbackBook($book_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CBC_INTEGRATION.rollbackBook(:bookID, :p_invalid_book_id, :p_others)');
			$stmt->bindParam(':bookID', $book_id);
			$invalid_book_id = "";
			$stmt->bindParam(':p_invalid_book_id', $invalid_book_id);
			$others = "";
			$stmt->bindParam(':p_others', $others);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "book_id"=>$book_id, 
			"invalid_book_id"=>$invalid_book_id, "others"=>$others);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "CBC INTEGRATION: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->cbcxError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "database_message"=>$ex->getMessage());
		}
	}
}