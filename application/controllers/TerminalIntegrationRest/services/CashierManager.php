<?php
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'CashierModel.php';
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'SessionModel.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once 'ErrorConstants.php';

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
	public static function reset($session_id){
		//returns status (success reset, not success), new credit status (0 - reset is success)
		if(!isset($session_id)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
		}		
		try{
			$session_id = strip_tags($session_id);
			$modelSession = new SessionModel();
			$modelSession->resetPackages();
			$modelCashier = new CashierModel();
			$arrData = $modelCashier->resetTerminal($session_id);
			if($arrData != null){
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                        "status_out"=> $arrData['status'],
                        "credits" => $arrData['credits_out']
                    )
                );
                exit($json_message);
            }else{
			    $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>NOK,
                        "error_message"=> $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "CashierManager::reset(session_id = {$session_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
		}
	}
	
	/**
	 * 
	 * usb credit transfer for cashier in game client
	 * @param int $session_id
	 * @param float $credits
	 * @return mixed
	 */
	public static function usbCreditTransfer($session_id, $credits){
		if(!isset($session_id) || !isset($credits)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
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

				$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>NOK,
                        "error_message"=> $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
			}
			$json_message = Zend_Json::encode(
                array(
                    "status" => OK,
                    "status_out" => $arrData[0],
                    "credits" => $arrData[1]
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			//sends email when usb credit transaction fails
			$errorHelper = new ErrorHelper();
			$message = "CashierManager::usbCreditTransfer(session_id = {$session_id}, credits = {$credits}) <br /> USB credit transaction has failed!!! <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
	}
}