<?php
/**
	Game client HTML5 implementation of Cashier controller
*/
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 * Performes player account actions trough web service
 */
class Html_CashierController extends Zend_Controller_Action {

	public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		//set output header Content-Type to application/json
		header('Content-Type: application/json');
	}
	
	public function preDispatch(){
        header("Access-Control-Allow-Origin: *");
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			$config = Zend_Registry::get('config');
			if($config->onlinecasinoserviceWSDLMode == "false" || $config->onlinecasinoserviceWSDLMode == false){ // not in wsdl mode
				$response = array(
					"status"=>NOK,
					"message"=>NOK_POST_METHOD_MESSAGE
				);
				exit(Zend_Json::encode($response));
			}else{
				$message = 
				"\n\n /onlinecasinoservice/html_cashier " .
				"\n\n reset(session_id)" .
				"\n\n usb-credit-transfer(session_id, credits)";
				exit($message);
			}
		}
	}
	
	public function indexAction(){
		$config = Zend_Registry::get('config');
		if($config->onlinecasinoserviceWSDLMode == "false" || $config->onlinecasinoserviceWSDLMode == false){ // not in wsdl mode
			header('Location: http://www.google.com/');
		}
	}
	
	/**
	 * 
	 * Reset terminal credits ...
	 * @return mixed
	 */
	public function resetAction(){
		//returns status (success reset, not success), new credit status (0 - reset is success)
		$session_id = strip_tags($this->getRequest()->getParam('session_id', null));
		if(strlen($session_id) == 0){
			$message = array(
				"status"=>NOK,
				"message"=>PARAMETER_MISSING_MESSAGE,
				"session_id"=>$session_id
			);
			exit(Zend_Json::encode($message));
		}
		try{
			$modelSession = new SessionModel();
			$modelSession->resetPackages();
			$modelCashier = new CashierModel();
			$arrData = $modelCashier->resetTerminal($session_id);
			unset($modelSession);
			unset($modelCashier);
			$message = array(
				"status"=>OK,
				"result"=>$arrData
			);
			exit(Zend_Json::encode($message));
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			$message = array(
				"status"=>NOK,
				"message"=>INTERNAL_ERROR_MESSAGE
			);
			exit(Zend_Json::encode($message));
		}
	}
	
	/**
	 * 
	 * usb credit transfer for cashier in game client
	 * @return mixed
	 */
	public function usbCreditTransferAction(){
		$session_id = strip_tags($this->getRequest()->getParam('session_id', null));
		$credits = strip_tags($this->getRequest()->getParam('credits', null));
		if(strlen($session_id) == 0 || $session_id == 'null' || strlen($credits) == 0 || $credits == 'null'){
			$message = array(
				"status"=>NOK,
				"message"=>PARAMETER_MISSING_MESSAGE,
				"session_id"=>$session_id,
				"credits"=>$credits
			);
			exit(Zend_Json::encode($message));
		}	
		try{
			$modelCashier = new CashierModel();
			$arrData = $modelCashier->usbCreditTransfer($session_id, $credits);
			unset($modelCashier);
			if(is_null($arrData)){
				//sends email when usb credit transaction fails
				$errorHelper = new ErrorHelper();				
				$mail_message = $log_message = "USB credit transaction with Session_id = {$session_id} has failed!!!";
				$errorHelper->serviceError($mail_message, $log_message);
				$message = array(
					"status"=>NOK,
					"message"=>INTERNAL_ERROR_MESSAGE
				);
				exit(Zend_Json::encode($message));
			}
			$message = array(
				"status"=>OK,
				"result"=>array("status"=>$arrData[0], "credits"=>$arrData[1])
			);
			exit(Zend_Json::encode($message));
		}catch(Zend_Exception $ex){
			//sends email when usb credit transaction fails
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			$message = array(
				"status"=>NOK,
				"message"=>INTERNAL_ERROR_MESSAGE
			);
			exit(Zend_Json::encode($message));
		}
	}
	
}