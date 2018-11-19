<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class Rest_WebSitePaysafecardDirectMerchantController extends Zend_Controller_Action {


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
				"\n\n /onlinecasinoservice/rest/web-site-paysafecard-direct-merchant ";
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
	public function getPaysafecardPaymentPurchaseMessageAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $amount = strip_tags($this->getRequest()->getParam('amount', null));
        $payment_method = strip_tags($this->getRequest()->getParam('payment_method', null));
        $payment_method_id = strip_tags($this->getRequest()->getParam('payment_method_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));
        $bonus_code = strip_tags($this->getRequest()->getParam('bonus_code', null));
        $css_template = strip_tags($this->getRequest()->getParam('css_template', 'Default'));

        require_once "services" . DS . "WebSitePaysafecardDirectMerchantManager.php";
		WebSitePaysafecardDirectMerchantManager::getPaysafecardPaymentPurchaseMessage($site_session_id, $pc_session_id, $amount, $payment_method, $payment_method_id, $ip_address, $bonus_code, $css_template);
	}

    /**
	 * @return mixed
	 */
	public function getPaysafecardPaymentDetailsAction(){
        $payment_id = strip_tags($this->getRequest()->getParam('payment_id', null));

        require_once "services" . DS . "WebSitePaysafecardDirectMerchantManager.php";
		WebSitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails($payment_id);
	}

    public function getPaysafecardWithdrawRequestAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $payment_method = strip_tags($this->getRequest()->getParam('payment_method', null));
        $payment_method_id = strip_tags($this->getRequest()->getParam('payment_method_id', null));
        $amount = strip_tags($this->getRequest()->getParam('amount', null));
        $paysafecard_email = strip_tags($this->getRequest()->getParam('paysafecard_email', null));
        $paysafecard_date_of_birth = strip_tags($this->getRequest()->getParam('paysafecard_date_of_birth', null));
        $paysafecard_first_name = strip_tags($this->getRequest()->getParam('paysafecard_first_name', null));
        $paysafecard_last_name = strip_tags($this->getRequest()->getParam('paysafecard_last_name', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSitePaysafecardDirectMerchantManager.php";
		WebSitePaysafecardDirectMerchantManager::paysafecardWithdrawRequest($pc_session_id, $payment_method, $payment_method_id, $amount,
        $paysafecard_email, $paysafecard_date_of_birth, $paysafecard_first_name, $paysafecard_last_name,
        $ip_address);
    }
}
