<?php
require_once SERVICES_DIR . DS . 'web_site' . DS . 'WebSiteManager.php';
require_once SERVICES_DIR . DS . 'web_site' . DS . 'WebSiteReportsManager.php';
require_once SERVICES_DIR . DS . 'web_site' . DS . 'WebSitePlayerAccountManager.php';
require_once SERVICES_DIR . DS . 'web_site' . DS . 'WebSitePlayerAccountMailsManager.php';
require_once SERVICES_DIR . DS . 'web_site' . DS . 'WebSitePlayerAccountSetupManager.php';
require_once SERVICES_DIR . DS . 'web_site' . DS . 'WebSiteBonusManager.php';
require_once SERVICES_DIR . DS . 'web_site' . DS . 'WebSiteSportIntegrationManager.php';
require_once SERVICES_DIR . DS . 'web_site' . DS . 'DocumentManagmentManager.php';
require_once SERVICES_DIR . DS . 'web_site' . DS . 'LdcIntegrationManager.php';
require_once SERVICES_DIR . DS . 'web_site' . DS . 'VivoGamingIntegrationManager.php';

class WebSiteController extends Zend_Controller_Action{
	public function init(){
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
	}
	//this is web service for client web sites
	public function indexAction(){
		$server = new Zend_Json_Server();
		$server
		->setClass('WebSiteManager')
		->setClass('WebSiteReportsManager')
		->setClass('WebSitePlayerAccountManager')
		->setClass('WebSitePlayerAccountMailsManager')
		->setClass('WebSitePlayerAccountSetupManager')
		->setClass('WebSiteBonusManager')
    ->setClass('WebSiteSportIntegrationManager')
		->setClass('DocumentManagmentManager')
		->setClass('LdcIntegrationManager')
    ->setClass('VivoGamingIntegrationManager');
		$config = Zend_Registry::get('config');
		$webSiteWsdlMode = $config->webSiteWSDLMode;
		if($webSiteWsdlMode == "true"){
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
