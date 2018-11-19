<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class Rest_WebSiteGglIntegrationController extends Zend_Controller_Action {


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
				"\n\n /onlinecasinoservice/rest/web-site-ggl-integration ";
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
	public function getEncryptedTokenAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        require_once "services" . DS . "WebSiteGglIntegrationManager.php";
		WebSiteGglIntegrationManager::getEncryptedToken($site_session_id);
	}

    /**
	 * @return mixed
	 */
	public function checkGglGameStatusAction(){
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        require_once "services" . DS . "WebSiteGglIntegrationManager.php";
		WebSiteGglIntegrationManager::checkGGLGameStatus($pc_session_id);
	}
}