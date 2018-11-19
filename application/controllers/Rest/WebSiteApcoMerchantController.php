<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class Rest_WebSiteApcoMerchantController extends Zend_Controller_Action {


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
				"\n\n /onlinecasinoservice/rest/web-site-apco-merchant ";
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
	 * @return mixed
	 */
	public function getApcoPaymentPurchaseCustomCardMessageAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $amount = strip_tags($this->getRequest()->getParam('amount', null));
        $payment_method = strip_tags($this->getRequest()->getParam('payment_method', null));
        $payment_method_id = strip_tags($this->getRequest()->getParam('payment_method_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));
        $bonus_code = strip_tags($this->getRequest()->getParam('bonus_code', null));
        $css_template = strip_tags($this->getRequest()->getParam('css_template', 'Default'));
        $is_3d_secure = strip_tags($this->getRequest()->getParam('is_3_secure', null));

        require_once "services" . DS . "WebSiteApcoMerchantManager.php";
		WebSiteApcoMerchantManager::getApcoPaymentPurchaseCustomCardMessage($site_session_id, $pc_session_id, $amount, $payment_method, $payment_method_id, $is_3d_secure, $ip_address, $bonus_code, $css_template);
	}

    /**
	 * @return mixed
	 */
	public function getApcoPurchaseCustomPaymentMethodMessageAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $amount = strip_tags($this->getRequest()->getParam('amount', null));
        $payment_method = strip_tags($this->getRequest()->getParam('payment_method', null));
        $payment_method_id = strip_tags($this->getRequest()->getParam('payment_method_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));
        $bonus_code = strip_tags($this->getRequest()->getParam('bonus_code', null));
        $css_template = strip_tags($this->getRequest()->getParam('css_template', 'Default'));

        require_once "services" . DS . "WebSiteApcoMerchantManager.php";
		WebSiteApcoMerchantManager::getApcoPurchaseCustomPaymentMethodMessage($site_session_id, $pc_session_id, $amount, $payment_method, $payment_method_id, $ip_address, $bonus_code, $css_template);
	}

    /**
	 * @return mixed
	 */
	public function getLastCardNumbersAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));

        require_once "services" . DS . "WebSiteApcoMerchantManager.php";
		WebSiteApcoMerchantManager::getLastCardNumbers($site_session_id);
	}

    /**
	 * @return mixed
	 */
	public function getApcoRegisterCreditCardMessageAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site__session_id', null));
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteApcoMerchantManager.php";
		WebSiteApcoMerchantManager::getApcoRegisterCreditCardMessage($site_session_id, $pc_session_id, $ip_address);
	}

    public function getApcoWithdrawRequestAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $apco_transaction_id = strip_tags($this->getRequest()->getParam('apco_transaction_id', null));
        $payment_method = strip_tags($this->getRequest()->getParam('payment_method', null));
        $payment_method_id = strip_tags($this->getRequest()->getParam('payment_method_id', null));
        $amount = strip_tags($this->getRequest()->getParam('amount', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteApcoMerchantManager.php";
		WebSiteApcoMerchantManager::apcoWithdrawRequest($pc_session_id, $apco_transaction_id, $payment_method, $payment_method_id, $amount, $ip_address);
	}

}
