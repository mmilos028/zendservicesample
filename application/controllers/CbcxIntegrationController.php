<?php
require_once("Zend/Soap/AutoDiscover.php");
require_once("Zend/Soap/ServerCBCX.php");
require_once("Zend/Soap/Wsdl.php");
require_once("Zend/Soap/Wsdl/Strategy/ArrayOfTypeComplex.php");
require_once("Zend/Soap/Wsdl/Strategy/DefaultComplexType.php");
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once MODELS_DIR . DS . 'CbcxIntegrationModel.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'walletUserApplicationSettings.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'walletUser.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'WalletException.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'walletExceptionBean.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'bookType.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'setting.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'walletErrorCode.php';
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'WalletService.php';

/**
 * 
 * CBCX Casino web service wallet integration ... Sensitive to white spaces !!!!
 * @author Milos.Milosevic
 *
 */
class CbcxIntegrationController extends Zend_Controller_Action{

	//used for CBCX casino integration	
	private $serviceURL = "";
	private $classmap = array('walletUser' => 'walletUser', 'bookType' => 'bookType', 
		'WalletException'=>'WalletException', 'walletUserApplicationSettings' => 'walletUserApplicationSettings', 
		'walletExceptionBean' => 'walletExceptionBean', 'walletErrorCode' => 'walletErrorCode', 'setting' => 'setting');
	public function init(){
		if(!$this->isWhiteListedIP(IPHelper::getRealIPAddress())){
			header('Location: https://www.google.com/');
		}
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();		
		$serverURL = new Zend_View_Helper_ServerUrl();
		$baseURL = Zend_Controller_Front::getInstance()->getBaseUrl();
		//$scheme = $serverURL->getScheme(); //http or https
		$scheme = "https"; //https or http
		$url = $scheme . '://' . $serverURL->getHost();
		$url .= $this->view->url(array('controller'=>'cbcx-integration', 'action'=>'index'));
		$this->serviceURL = $url;
	}
	
	
	/**
	 * 
	 * this is wallet integration for integration with cbcx casino
	 */
	public function indexAction(){
		if(!$this->isWhiteListedIP(IPHelper::getRealIPAddress())){
			header('Location: https://www.google.com/');
		}
		$config = Zend_Registry::get('config');
		if($config->cbcxWSDLMode == "true"){
			if (isset($_GET['wsdl'])){
				// set up WSDL auto-discovery
				$autodiscover = new Zend_Soap_AutoDiscover('Zend_Soap_Wsdl_Strategy_ArrayOfTypeComplex');
				//set wsdl style				
				$type1 = "literal";
				$autodiscover->setOperationBodyStyle(array('use' => $type1, 'namespace' => $this->serviceURL));
				if(array_key_exists('encoded', $_GET)){
					//use encoded
					$type1 = "encoded";
					$autodiscover->setOperationBodyStyle(array('use' => $type1, 'encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/'));
				}
				$type2 = "rpc";
				$autodiscover->setBindingStyle(array('style' => $type2, 'transport' => 'http://schemas.xmlsoap.org/soap/http'));
				if(array_key_exists('document', $_GET)){
					//use rpc
					$type2 = "document";
					$autodiscover->setBindingStyle(array('style' => $type2, 'transport' => 'http://schemas.xmlsoap.org/soap/http'));
				}
				// attach SOAP service class
				$autodiscover->setClass('WalletService');
				// set SOAP action URI
				$autodiscover->setUri($this->serviceURL . "?soap");
				// handle request
				$autodiscover->handle();
			} else {
				//ovde je za testing
				// initialize server and set WSDL file location
				$wsdl_url = SERVICES_DIR . DS . 'cbcx_integration' . DS . 'cbcx_integration.wsdl';
				$options = array(
					'classmap'=>$this->classmap,
					'soap_version' => SOAP_1_2,
					'encoding'=>'UTF-8'
				);
				$server = new Zend_Soap_ServerCBCX($wsdl_url, $options);
				// set SOAP service class
				$server->setClass('WalletService');
				$server->setObject(new WalletService());
				// register exceptions that generate SOAP faults
				$server->registerFaultException(array('WalletException'));
				// handle request
				$server->handle();
			}
		}else{
			/* NON WSDL MODE*/
			// initialize server and set WSDL file location
			$server = new Zend_Soap_ServerCBCX();
			$options = array(
				'classmap'=>$this->classmap,
				'soap_version' => SOAP_1_2,
				'encoding'=>'UTF-8',
				'uri'=> $this->serviceURL . '?wsdl'
			);
			$server->setOptions($options);
			// set SOAP service class
			$server->setClass('WalletService');
			// register exceptions that generate SOAP faults
			$server->registerFaultException(array('WalletException'));
			// handle request
			$server->handle();
		}
	}
	
	//check if ip address is allowed
	//if returns true then allow access
	//if returns false then deny access
	private function isWhiteListedIP($clientIpAddress){
		$config = Zend_Registry::get('config');
		//check if configuration for cbcx casino exists
		if(strlen($config->cbcxTestWhiteListIP) == 0){
			return false;
		}
		if($config->cbcxTestWhiteListIP == "true"){
			//tests for white listed ip address from cbcx casino
			//location ex. /application/configs/whitelist/cbcx_whitelist_SECTION-NAME.ini
			$filePath = APP_DIR . DS . 'configs' . DS . 'whitelist' . DS . 'cbcx_whitelist_' . $config->getSectionName() . '.ini';
			$lines = file($filePath);
			$flag = false;
			foreach($lines as $line){
				if(trim($line) == trim($clientIpAddress))$flag = true;
				if($flag)break;
			}
			if(!$flag){
				$errorHelper = new ErrorHelper();
				$message = "CBCX Integration Error: IP Address not in white list. <br /> IP Address: " . $clientIpAddress;
				$errorHelper->cbcxError($message, $message);
			}
			return $flag;
		}else{
			//does not test for white listed ip address from cbcx casino
			//allowed access for everyone
			return true;
		}
	}
}