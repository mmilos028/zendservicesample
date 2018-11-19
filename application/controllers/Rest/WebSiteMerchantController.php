<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class Rest_WebSiteMerchantController extends Zend_Controller_Action {


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
				"\n\n /onlinecasinoservice/rest/web-site-merchant-merchant ";
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
	public function getAllPaymentMethodsAction(){
        require_once "services" . DS . "WebSiteMerchantManager.php";
		WebSiteMerchantManager::getAllPaymentMethods();
	}

    /**
	 * @return mixed
	 */
	public function getTransactionLimitPurchaseAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $amount = strip_tags($this->getRequest()->getParam('amount', null));
        $payment_method = strip_tags($this->getRequest()->getParam('payment_method', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteMerchantManager.php";
		WebSiteMerchantManager::getTransactionLimitPurchase($site_session_id, $amount, $payment_method, $ip_address);
	}

    public function getTransactionLimitPayoutAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $amount = strip_tags($this->getRequest()->getParam('amount', null));
        $payment_method = strip_tags($this->getRequest()->getParam('payment_method', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteMerchantManager.php";
		WebSiteMerchantManager::getTransactionLimitPayout($site_session_id, $amount, $payment_method, $ip_address);
    }

    public function pendingPayoutStatusAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));

        require_once "services" . DS . "WebSiteMerchantManager.php";
		WebSiteMerchantManager::pendingPayoutStatus($site_session_id);
    }

    public function isWithdrawPossibleAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $expected_withdraw_amount = strip_tags($this->getRequest()->getParam('expected_withdraw_amount', null));
        $transaction_id = strip_tags($this->getRequest()->getParam('transaction_id', null));

        require_once "services" . DS . "WebSiteMerchantManager.php";
		WebSiteMerchantManager::isWithdrawPossible($site_session_id, $pc_session_id, $expected_withdraw_amount, $transaction_id);
    }

    public function cancelWithdrawAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $withdraw_amount = strip_tags($this->getRequest()->getParam('withdraw_amount', null));
        $transaction_id = strip_tags($this->getRequest()->getParam('transaction_id', null));

        require_once "services" . DS . "WebSiteMerchantManager.php";
		WebSiteMerchantManager::cancelWithdraw($site_session_id, $pc_session_id, $withdraw_amount, $transaction_id);
    }

    public function listPaymentLimitsForWhiteLabelAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $currency = strip_tags($this->getRequest()->getParam('currency', null));

        require_once "services" . DS . "WebSiteMerchantManager.php";
		WebSiteMerchantManager::listPaymentLimitsForWhiteLabel($site_session_id, $currency);
    }

    public function setIbanSwiftAction(){
        $player_id = strip_tags($this->getRequest()->getParam('player_id', null));
        $swift = strip_tags($this->getRequest()->getParam('swift', null));
        $iban = strip_tags($this->getRequest()->getParam('iban', null));

        require_once "services" . DS . "WebSiteMerchantManager.php";
		WebSiteMerchantManager::setIbanSwift($player_id, $swift, $iban);
    }

    public function getPromotionCodeAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $promotion_code = strip_tags($this->getRequest()->getParam('promotion_code', null));

        require_once "services" . DS . "WebSiteMerchantManager.php";
		WebSiteMerchantManager::getPromotionCode($pc_session_id, $promotion_code);
    }
}