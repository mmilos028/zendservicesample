<?php
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'CashierModel.php';
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'SessionModel.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

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
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "CashierManager::reset(session_id = {$session_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceError($message, $message);
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
                $detected_ip_address = IPHelper::getRealIPAddress();
				//sends email when usb credit transaction fails
				$errorHelper = new ErrorHelper();				
				$mail_message = $log_message = "CashierManager::usbCreditTransfer(session_id = {$session_id}, credits = {$credits}) <br /> USB credit transaction has failed!!! <br /> Detected IP Address = {$detected_ip_address}";
				$errorHelper->serviceError($mail_message, $log_message);
				return null;
			}
			return $arrData;
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			//sends email when usb credit transaction fails
			$errorHelper = new ErrorHelper();
			$message = "CashierManager::usbCreditTransfer(session_id = {$session_id}, credits = {$credits}) <br /> USB credit transaction has failed!!! <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return null;
		}
	}
}