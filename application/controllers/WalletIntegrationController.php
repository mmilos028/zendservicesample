<?php
require_once SERVICES_DIR . DS . 'WalletManager.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once("Zend/Soap/Server.php");
require_once("Zend/Soap/Wsdl.php");
require_once("Zend/Soap/Wsdl/Strategy/ArrayOfTypeComplex.php");
require_once("Zend/Soap/Wsdl/Strategy/ArrayOfTypeSequence.php");
require_once("Zend/Soap/Wsdl/Strategy/Composite.php");
require_once("Zend/Soap/Wsdl/Strategy/DefaultComplexType.php");
require_once("Zend/Soap/Wsdl/Strategy/AnyType.php");
require_once("Zend/Soap/AutoDiscover.php");

class WalletIntegrationController extends Zend_Controller_Action{
	
	private $serviceURL = "";
	public function init(){
		if(!$this->isWhiteListedIP(IPHelper::getRealIPAddress())){
			header('Location: https://www.google.com/');
		}
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		$serverURL = new Zend_View_Helper_ServerUrl();
		$baseURL = Zend_Controller_Front::getInstance()->getBaseUrl();
		//$scheme = $serverURL->getScheme(); //http or https
		$scheme = "https";
		$url = $scheme . '://' . $serverURL->getHost();
		$url .= $this->view->url(array('controller'=>'wallet-integration', 'action'=>'index'), null, true);
		$this->serviceURL = $url;
	}
	/**
	 * General method when
	 * document-literal
	 * /onlinecasinoservice/wallet-integration?wsdl is called to generate wsdl
	 * document-literal style
	 * /onlinecasinoservice/wallet-integration?wsdl&document&literal
	 * document-encoded style
	 * /onlinecasinoservice/wallet-integration?wsdl&document&encoded
	 * rpc-literal style
	 * /onlinecasinoservice/wallet-integration?wsdl&rpc&literal
	 * rpc-encoded style
	 * /onlinecasinoservice/wallet-integration?wsdl&rpc&encoded
	 */
	public function indexAction(){
		if(!$this->isWhiteListedIP(IPHelper::getRealIPAddress())){
			header('Location: https://www.google.com/');
		}
		$config = Zend_Registry::get('config');
		if($config->ldcWSDLMode == "true"){
			if (isset($_GET['wsdl'])){
				// set up WSDL auto-discovery
				$autodiscover = new Zend_Soap_AutoDiscover('Zend_Soap_Wsdl_Strategy_ArrayOfTypeComplex');
                //$autodiscover = new Zend_Soap_AutoDiscover('Zend_Soap_Wsdl_Strategy_DefaultComplexType');
                //$autodiscover = new Zend_Soap_AutoDiscover('Zend_Soap_Wsdl_Strategy_AnyType');
				//set wsdl style 	
				$type1 = "literal";
				$autodiscover->setOperationBodyStyle(array('use' => $type1, 'namespace' => $this->serviceURL));			
				if(array_key_exists('encoded', $_GET)){
					//use encoded
					$type1 = "encoded";
					$autodiscover->setOperationBodyStyle(array('use' => $type1, 'encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/'));			
				}
				$type2 = "document";
				$autodiscover->setBindingStyle(array('style' => $type2, 'transport' => 'http://schemas.xmlsoap.org/soap/http'));
				if(array_key_exists('rpc', $_GET)){
					//use rpc
					$type2 = "rpc";
					$autodiscover->setBindingStyle(array('style' => $type2, 'transport' => 'http://schemas.xmlsoap.org/soap/http'));
				}
				// attach SOAP service class
				$autodiscover->setClass('WalletManager');
				// set SOAP action URI		
				$autodiscover->setUri($this->serviceURL . "?soap");
				// handle request
				$autodiscover->handle();
			} else {
				// initialize server and set WSDL file location
	    		$server = new Zend_Soap_Server();
                //$server->setUri($this->serviceURL . "?wsdl");
                $server->setUri($this->serviceURL . "?soap");
    			// set SOAP service class
    			$server->setClass('WalletManager');
	    		$server->setObject(new WalletManager());
    			// handle request
    			$server->handle();
			}
		}else{		
			/* NON WSDL MODE*/
			// initialize server and set WSDL file location
			$server = new Zend_Soap_Server();
			$server->setUri($this->serviceURL);
			// set SOAP service class
			$server->setClass('WalletManager');
			//$server->setObject(new WalletManager());
			// handle request
			$server->handle();
		}
	}

	//check if ip address is allowed
	private function isWhiteListedIP($clientIpAddress){
		$config = Zend_Registry::get('config');
		//check if configuration for ldc casino exists
		if(strlen($config->ldcTestWhiteListIP) == 0){
			return false;
		}
		if($config->ldcTestWhiteListIP == "true"){
			//tests for white listed ip address from ldc casino
			//location ex. /application/configs/whitelist/ldc_whitelist_SECTION-NAME.ini
			$filePath = APP_DIR . DS . 'configs' . DS . 'whitelist' . DS . 'ldc_whitelist_' . $config->getSectionName() . '.ini';
			$lines = file($filePath);
			$flag = false;
			foreach($lines as $line){
				if(trim($line) == trim($clientIpAddress))$flag = true;
				if($flag)break;
			}
			if(!$flag){
				$errorHelper = new ErrorHelper();
				$message = "WalletIntegrationController::isWhiteListedIP GGL Integration Error: IP Address not in white list for wallet integration. <br /> IP Address: {$clientIpAddress}";
				$errorHelper->ldcIntegrationError($message, $message);
			}
			return $flag;
		}else{
			//does not test for white listed ip address from ldc casino
			//allowed access for everyone
			return true;
		}
	}
}