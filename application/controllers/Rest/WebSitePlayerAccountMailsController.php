<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 * 
 * Web site web service Player Account Mail sending ...
 *
 */

class Rest_WebSitePlayerAccountMailsController extends Zend_Controller_Action {

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
				"\n\n /onlinecasinoservice/rest/web-site-player-account-mails";
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
	public function forgotUsernameAction(){
        $name = strip_tags($this->getRequest()->getParam('name', null));
        $familyname = strip_tags($this->getRequest()->getParam('familyname', null));
        $birthday = strip_tags($this->getRequest()->getParam('birthday', null));
        $email = strip_tags($this->getRequest()->getParam('email', null));
		require_once "services" . DS . "WebSitePlayerAccountMailsManager.php";
		WebSitePlayerAccountMailsManager::ForgotUsername($name, $familyname, $birthday, $email);
	}
	
	/**
	*
	* @return mixed
	*/
	public function sendPlayerActivationEmailAction(){
        $player_id = strip_tags($this->getRequest()->getParam('player_id', null));
		require_once "services" . DS . "WebSitePlayerAccountMailsManager.php";
		WebSitePlayerAccountMailsManager::sendPlayerActivationMail($player_id);
	}
	
	/**
	*
	* @return mixed
	*/
	public function forgotPasswordWithSecurityAnswerAction(){
        $username = strip_tags($this->getRequest()->getParam('username', null));
        $answer = strip_tags($this->getRequest()->getParam('answer', null));
        require_once "services" . DS . "WebSitePlayerAccountMailsManager.php";
		WebSitePlayerAccountMailsManager::ForgotPasswordWithSecurityAnswer($username, $answer);
	}
	
	/**
	*
	* @return mixed
	*/
	public function forgotPasswordWithPersonalDataAction(){
        $username = strip_tags($this->getRequest()->getParam('username', null));
        $name = strip_tags($this->getRequest()->getParam('name', null));
        $familyname = strip_tags($this->getRequest()->getParam('familyname', null));
        $birthday = strip_tags($this->getRequest()->getParam('birthday', null));
        $email = strip_tags($this->getRequest()->getParam('email', null));
        require_once "services" . DS . "WebSitePlayerAccountMailsManager.php";
		WebSitePlayerAccountMailsManager::ForgotPasswordWithPersonalData($username, $name, $familyname, $birthday, $email);
	}

    /**
	*
	* @return mixed
	*/
	public function playerRegistrationConfirmationAction(){
        $hash_id = strip_tags($this->getRequest()->getParam('hash_id', null));
        $player_id = strip_tags($this->getRequest()->getParam('player_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));
        require_once "services" . DS . "WebSitePlayerAccountMailsManager.php";
		WebSitePlayerAccountMailsManager::playerRegistrationConfirmation($hash_id, $player_id, $ip_address);
	}

}