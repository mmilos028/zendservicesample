<?php
require_once MODELS_DIR . DS . 'CashierModel.php';
require_once MODELS_DIR . DS . 'SessionModel.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';

/**
 * 
 * CashierManager class implements all of commands of Cashier web service
 *
 */
class CashierManager {
	/**
	 * 
	 * Enter description here ...
	 * @param int $session_id
	 * @return mixed
	 */
	public function reset($session_id){
		//returns status (success reset, not success), new credit status (0 - reset is success)
		if(!isset($session_id)){
			return null;
		}		
		try{
			$session_id = strip_tags($session_id);
			$modelSession = new SessionModel();
			$modelSession->resetPackages();
			$modelCashier = new CashierModel();
			$arrData = $modelCashier->resetTerminal($session_id);
			unset($modelSession);
			unset($modelCashier);
			return $arrData;
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return null;
		}
	}
	
	/**
	 * 
	 * usb credit transfer for cashier in game client
	 * @param int $session_id
	 * @param float $credits
	 * @return mixed
	 */
	public function usbCreditTransfer($session_id, $credits){
		if(!isset($session_id) || !isset($credits)){
			return null;
		}		
		try{
			$session_id = strip_tags($session_id);
			$credits = strip_tags($credits);
			$modelCashier = new CashierModel();
			$arrData = $modelCashier->usbCreditTransfer($session_id, $credits);
			unset($modelCashier);
			if(is_null($arrData)){
				//sends email when usb credit transaction fails
				$errorHelper = new ErrorHelper();				
				$mail_message = $log_message = "USB credit transaction with Session_id = {$session_id} has failed!!!";
				$errorHelper->serviceError($mail_message, $log_message);
				return null;
			}
			return $arrData;
		}catch(Zend_Exception $ex){
			//sends email when usb credit transaction fails
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return null;
		}
	}
}