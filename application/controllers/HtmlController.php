<?php
/**
  * Web service pure JSON - for HTML5 Game Client 
*/
require_once MODELS_DIR . DS . 'AuthorizationModel.php';
require_once MODELS_DIR . DS . 'GameModel.php';
require_once MODELS_DIR . DS . 'SessionModel.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

class HtmlController extends Zend_Controller_Action{
	public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		header('Content-Type: application/json');
	}
	
	public function preDispatch(){
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			$config = Zend_Registry::get('config');
			if($config->onlinecasinoserviceWSDLMode == "false"){ // not in wsdl mode
				$response = array(
					"status"=>NOK,
					"message"=>NOK_POST_METHOD_MESSAGE
				);
				exit(Zend_Json::encode($response));
			}else{
				$message = 
				"\n\n /onlinecasinoservice/html_account " .
				"\n\n /onlinecasinoservice/html_authorization " .
				"\n\n /onlinecasinoservice/html_report " .
				"\n\n /onlinecasinoservice/html_cashier ";
				exit($message);
			}
		}
	}
	
	public function indexAction(){
		$config = Zend_Registry::get('config');
		if($config->onlinecasinoserviceWSDLMode == "false"){ // not in wsdl mode
			header('Location: http://www.google.com/');
		}
	}
	
}