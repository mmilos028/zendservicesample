<?php
require_once SERVICES_DIR . DS . 'AffiliateTrackingSystemManager.php';
require_once("Zend/Soap/Server.php");
require_once("Zend/Soap/Wsdl.php");
require_once("Zend/Soap/Wsdl/Strategy/ArrayOfTypeComplex.php");
require_once("Zend/Soap/Wsdl/Strategy/ArrayOfTypeSequence.php");
require_once("Zend/Soap/AutoDiscover.php");
require_once("Zend/Soap/Server.php");

class AffiliateTrackingSystemController extends Zend_Controller_Action
{
    private $serviceURL = "";
	public function init(){
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
		$serverURL = new Zend_View_Helper_ServerUrl();
		$baseURL = Zend_Controller_Front::getInstance()->getBaseUrl();
		//$scheme = $serverURL->getScheme(); //http or https
		$scheme = "http";
		$url = $scheme . '://' . $serverURL->getHost();
		$url .= $this->view->url(array('controller'=>'affiliate-tracking-system', 'action'=>'index'), null, true);
		$this->serviceURL = $url;
	}
	
	public function indexAction(){
		//error_reporting(E_ALL|E_STRICT);
		$config = Zend_Registry::get('config');
		if($config->atsWSDLMode == "true"){
			/*if(isset($_GET['api']) && $_GET['api']=='C456yM7NG1838M3wIwGJo1ean517MB'){*/
				if(isset($_GET['wsdl'])) {
					$autodiscover = new Zend_Soap_AutoDiscover();
					$autodiscover->setClass('AffiliateTrackingSystemManager');
					$autodiscover->setUri($this->serviceURL . '?soap');
					$autodiscover->handle();
				} else{
					$server = new Zend_Soap_Server();
					$server->setUri($this->serviceURL . "?wsdl");
					$server->setClass('AffiliateTrackingSystemManager');
					$server->handle();
				}
			//}
		}
		else{
			/*if(isset($_GET['api']) && $_GET['api']=='C456yM7NG1838M3wIwGJo1ean517MB'){*/
				$server = new Zend_Soap_Server();
				$server->setUri($this->serviceURL);
				$server->setClass('AffiliateTrackingSystemManager');
				$server->handle();
			/*}*/
		}
	}
}