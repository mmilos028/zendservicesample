<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 * 
 * Web site web service BONUS setup, reports ...
 *
 */

class Rest_WebSiteBonusController extends Zend_Controller_Action {

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
				"\n\n /onlinecasinoservice/rest/web-site-bonus";
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
	* cancel bonus transactions
	* @return mixed
	*/
	public function cancelBonusTransactionsAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
		if(!isset($pc_session_id) || $pc_session_id == 0){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			exit($json_message);
		}
		$pc_session_id = strip_tags($pc_session_id);
		require_once "services" . DS . "WebSiteBonusManager.php";
		WebSiteBonusManager::cancelBonusTransactions($pc_session_id);
	}
	
	/**
	*
	* @return mixed
	*/
	public function checkBonusCodeAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $bonus_campaign_code = strip_tags($this->getRequest()->getParam('bonus_campaign_code', null));
        $deposit_amount = strip_tags($this->getRequest()->getParam('deposit_amount', null));

		require_once "services" . DS . "WebSiteBonusManager.php";
		WebSiteBonusManager::checkBonusCode($pc_session_id, $bonus_campaign_code, $deposit_amount);
	}
	
	/**
	*
	* @return mixed
	*/
	public function checkBonusCodeStatusAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $bonus_campaign_code = strip_tags($this->getRequest()->getParam('bonus_campaign_code', null));
        $deposit_amount = strip_tags($this->getRequest()->getParam('deposit_amount', null));

        require_once "services" . DS . "WebSiteBonusManager.php";
		WebSiteBonusManager::checkBonusCodeStatus($pc_session_id, $bonus_campaign_code, $deposit_amount);
	}
	
	/**
	 * 
	 * List player bonus history method ...
	 * @return mixed
	 */
	public function listPlayerBonusHistoryAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));

        require_once "services" . DS . "WebSiteBonusManager.php";
		WebSiteBonusManager::listPlayerBonusHistory($session_id);
	}
	
	/**
	*
	* @return mixed
	*/
	public function checkBonusAvailableForCountryAction(){
        $affiliate_name = strip_tags($this->getRequest()->getParam('affiliate_name', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteBonusManager.php";
		WebSiteBonusManager::checkBonusAvailableForCountry($affiliate_name, $ip_address);
	}
}