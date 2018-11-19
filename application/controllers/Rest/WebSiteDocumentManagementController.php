<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class Rest_WebSiteDocumentManagementController extends Zend_Controller_Action {


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
				"\n\n /onlinecasinoservice/rest/web-site-document-management ";
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
	 *
	 * save document information, from player uploaded file
	 * @return mixed
	 */
	public function uploadDocumentAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $document_site = strip_tags($this->getRequest()->getParam('document_site', null));
        $document_location = strip_tags($this->getRequest()->getParam('document_location', null));
        $document_file_name = strip_tags($this->getRequest()->getParam('document_file_name', null));
        require_once "services" . DS . "WebSiteDocumentManagementManager.php";
		WebSiteDocumentManagementManager::uploadDocument($site_session_id, $document_site, $document_location, $document_file_name);
	}

    /**
	 *
	 * list player documents
	 * @return mixed
	 */
	public function listPlayerDocumentsAction(){
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $player_id = strip_tags($this->getRequest()->getParam('player_id', null));
        require_once "services" . DS . "WebSiteDocumentManagementManager.php";
		WebSiteDocumentManagementManager::listPlayersDocuments($site_session_id, $player_id);
	}

}