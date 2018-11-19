<?php
require_once SERVICES_DIR . DS . 'wirecard_merchant' . DS . 'SiteWirecardMerchantManager.php';
class SiteWirecardMerchantController extends Zend_Controller_Action{
	public function init(){
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
	}
	//this is web service for web site 
	//web site calls for payment services (APCO) are separated to this class calls
	public function indexAction(){
		$server = new Zend_Json_Server();
		$server->setClass('SiteWirecardMerchantManager');
		$config = Zend_Registry::get('config');
		if($config->siteMerchantWSDLMode == "true"){
			if('GET' == $_SERVER['REQUEST_METHOD']) {
				// Indicate the URL endpoint, and the JSON-RPC version used:
				$server->setTarget('/json-rpc.php')->setEnvelope(Zend_Json_Server_Smd::ENV_JSONRPC_2);
				// Grab the SMD
				$smd = $server->getServiceMap();
				// Return the SMD to the client
				header('Content-Type: application/json');
				echo $smd;
				return;
			}
		}
		$server->handle();
	}
}