<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class Rest_WebSiteSportIntegrationController extends Zend_Controller_Action {


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
				"\n\n /onlinecasinoservice/rest/web-site-sport-integration ";
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
	public function openCbcSessionAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));

        require_once "services" . DS . "WebSiteSportIntegrationManager.php";
		WebSiteSportIntegrationManager::openCbcSession($pc_session_id);
	}

    /**
	 * @return mixed
	 */
	public function openBetKioskSessionAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteSportIntegrationManager.php";
		WebSiteSportIntegrationManager::openBetKioskSession($pc_session_id, $ip_address);
	}

    /**
	 * @return mixed
	 */
	public function openMaxBetSessionAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteSportIntegrationManager.php";
		WebSiteSportIntegrationManager::openMaxBetSession($pc_session_id, $ip_address);
	}

    /**
	 * @return mixed
	 */
	public function openMemoBetSessionAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteSportIntegrationManager.php";
		WebSiteSportIntegrationManager::openMemoBetSession($pc_session_id, $ip_address);
	}

    /**
	 * @return mixed
	 */
	public function closeSportBettingWindowAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));

        require_once "services" . DS . "WebSiteSportIntegrationManager.php";
		WebSiteSportIntegrationManager::closeSportBettingWindow($pc_session_id);
	}

    /**
	 * @return mixed
	 */
	public function checkBetkioskGameStatusAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));

        require_once "services" . DS . "WebSiteSportIntegrationManager.php";
		WebSiteSportIntegrationManager::checkBetkioskGameStatus($pc_session_id);
	}

}