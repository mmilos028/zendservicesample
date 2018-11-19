<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class CashierModel{
	public function __construct(){
	}
	/** Performes cashiers payin credits to terminal */
	public function usbCreditTransfer($session_id, $credits){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$PAY_IN_PAY_OUT_USB(:p_session_id_in, :p_credits_in, :p_status_out, :p_credits_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_credits_in', $credits); // 0 - kada je isplata ili ako je vece od 0 onda je uplata
			$status = NO; 
			$stmt->bindParam(':p_status_out', $status, SQLT_CHR, 255); // Y or N - done or not done credit transaction
			$credits_out = "0.00";
			$stmt->bindParam(':p_credits_out', $credits_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array($status, $credits_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}		
	}
	/** Performes cashier reset terminal */
	public function resetTerminal($session_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$RESET_TERMINAL(:p_session_id_in, :p_status_out, :p_credits_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$status_out = "-1000000000000";
			$stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
			$credits_out = "-1000000000000";
			$stmt->bindParam(':p_credits_out', $credits_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>$status_out, "credits_out"=>$credits_out);
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
            return null;
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex2);
			$errorHelper->serviceError($message, $message);
            return null;
		}		
	}
}