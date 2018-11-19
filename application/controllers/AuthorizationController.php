<?php
require_once 'Zend/Amf/Server.php';
require_once 'Zend/Amf/Server/Exception.php';
require_once SERVICES_DIR . DS . 'AuthorizationManager.php';
require_once SERVICES_DIR . DS . 'AccountManager.php';
require_once SERVICES_DIR . DS . 'CashierManager.php';
require_once SERVICES_DIR . DS . 'ReportManager.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once APP_DIR . DS . 'browser' . DS . 'ZendAmfServiceBrowser.php';
class AuthorizationController extends Zend_Controller_Action{
	public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
	}
	public function postDispatch(){
		$this->_response->setHeader('Content-Type', 'application/x-amf');
	}
	//used by game clients to connect to public AMF web service
	public function serviceAction(){
		try{
			$server = new Zend_Amf_Server();
			$server->setClass("AuthorizationManager")->setClass("CashierManager")->setClass("AccountManager")->setClass("ReportManager");
			$config = Zend_Registry::get('config');
			$onlinecasinoserviceWSDLMode = $config->onlinecasinoserviceWSDLMode;
			if($onlinecasinoserviceWSDLMode == "true"){
				$server->setClass("ZendAmfServiceBrowser");
				ZendAmfServiceBrowser::$ZEND_AMF_SERVER = $server;
			}
			$server->setProduction(true);
			echo $server->handle();
		}catch(Zend_Amf_Exception $ex){
			$errorHelper = new ErrorHelper();
			require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
		}
	}
}