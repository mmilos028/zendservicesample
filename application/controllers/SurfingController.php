<?php
require_once 'Zend/Amf/Server.php';
require_once 'Zend/Amf/Server/Exception.php';
require_once SERVICES_DIR . DS . 'WebSurfingManager.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once APP_DIR . DS . 'browser' . DS . 'ZendAmfServiceBrowser.php';
class SurfingController extends Zend_Controller_Action {
	public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
	}
	//this is web service for browser surfing client application
	//here are method calls for client browser for surfing
	
	public function indexAction(){
		header('Location: http://www.google.com/');
	}
	public function postDispatch(){
		$this->_response->setHeader('Content-Type', 'application/x-amf');
	}
	public function serviceAction(){
		try{
			$server = new Zend_Amf_Server();
			$server->setClass("WebSurfingManager");
			$config = Zend_Registry::get('config');
			if($config->surfingWSDLMode == "true"){
				$server->setClass("ZendAmfServiceBrowser");
				ZendAmfServiceBrowser::$ZEND_AMF_SERVER = $server;
			}				
			$server->setProduction(true);
			echo $server->handle();
		}catch(Zend_Amf_Exception $ex){			
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceError($message, $message);
			unset($errorHelper);			
		}
	}
}