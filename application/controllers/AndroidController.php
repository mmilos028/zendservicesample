<?php
require_once 'Zend/Amf/Server.php';
require_once 'Zend/Amf/Server/Exception.php';
require_once SERVICES_DIR . DS . 'AndroidCashierManager.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once APP_DIR . DS . 'browser' . DS . 'ZendAmfServiceBrowser.php';

class AndroidController extends Zend_Controller_Action{
	public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
	}
	public function postDispatch(){
		$this->_response->setHeader('Content-Type', 'application/x-amf');
	}
	//used by android interface to connect through AMF web service
	public function indexAction(){
		try{
			$server = new Zend_Amf_Server();
			$server->setClass("AndroidCashierManager");
			$config = Zend_Registry::get('config');
			$androidWSDLMode = $config->androidWSDLMode;
			if($androidWSDLMode == "true"){
				$server->setClass("ZendAmfServiceBrowser");
				ZendAmfServiceBrowser::$ZEND_AMF_SERVER = $server;
			}
			$server->setProduction(true);
			echo $server->handle();
		}catch(Zend_Amf_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
		}
	}
}