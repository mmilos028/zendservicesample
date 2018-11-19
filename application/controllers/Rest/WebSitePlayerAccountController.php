<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 * 
 * Web site web service main calls ...
 *
 */

class Rest_WebSitePlayerAccountController extends Zend_Controller_Action {


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
				"\n\n /onlinecasinoservice/rest/web-site-player-account ";
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
	public function currencyListForNewPlayerAction(){
        $affiliate_id = strip_tags($this->getRequest()->getParam('affiliate_id', null));
        $tid_code = strip_tags($this->getRequest()->getParam('tid_code', null));
        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::currencyListForNewPlayer($affiliate_id, $tid_code);
	}

    /**
	 * @return mixed
	 */
	public function insertPlayerAction(){
        $affiliate_id = strip_tags($this->getRequest()->getParam('affiliate_id', null));
        $username = strip_tags($this->getRequest()->getParam('username', null));
        $password = strip_tags($this->getRequest()->getParam('password', null));
        $email = strip_tags($this->getRequest()->getParam('email', null));
        $first_name = strip_tags($this->getRequest()->getParam('first_name', null));
        $last_name = strip_tags($this->getRequest()->getParam('last_name', null));
        $birthday = strip_tags($this->getRequest()->getParam('birthday', null));
        $country = strip_tags($this->getRequest()->getParam('country', null));
        $zip = strip_tags($this->getRequest()->getParam('zip', null));
        $city = strip_tags($this->getRequest()->getParam('city', null));
        $street_address1 = strip_tags($this->getRequest()->getParam('street_address1', null));
        $street_address2 = strip_tags($this->getRequest()->getParam('street_address2', null));
        $phone = strip_tags($this->getRequest()->getParam('phone', null));
        $bank_account = strip_tags($this->getRequest()->getParam('bank_account', null));
        $bank_country = strip_tags($this->getRequest()->getParam('bank_country', null));
        $swift = strip_tags($this->getRequest()->getParam('swift', null));
        $iban = strip_tags($this->getRequest()->getParam('iban', null));
        $receive_email = strip_tags($this->getRequest()->getParam('receive_email', null));
        $currency = strip_tags($this->getRequest()->getParam('currency', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));
        $registration_code = strip_tags($this->getRequest()->getParam('registration_code', null));
        $tid_code = strip_tags($this->getRequest()->getParam('tid_code', null));
        $language = strip_tags($this->getRequest()->getParam('language', null));

        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::insertPlayer($affiliate_id, $username, $password, $email, $first_name, $last_name,
	        $birthday, $country, $zip, $city, $street_address1, $street_address2, $phone,
	        $bank_account, $bank_country, $swift, $iban, $receive_email, $currency, $ip_address, $registration_code, $tid_code, $language);
	}

    /**
	 * @return mixed
	 */
	public function playerDetailsAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::playerDetails($session_id, $ip_address);
	}

    /**
	 * @return mixed
	 */
	public function updatePlayerAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));
        $email = strip_tags($this->getRequest()->getParam('email', null));
        $first_name = strip_tags($this->getRequest()->getParam('first_name', null));
        $last_name = strip_tags($this->getRequest()->getParam('last_name', null));
        $birthday = strip_tags($this->getRequest()->getParam('birthday', null));
        $country = strip_tags($this->getRequest()->getParam('country', null));
        $zip = strip_tags($this->getRequest()->getParam('zip', null));
        $city = strip_tags($this->getRequest()->getParam('city', null));
        $street_address1 = strip_tags($this->getRequest()->getParam('street_address1', null));
        $street_address2 = strip_tags($this->getRequest()->getParam('street_address2', null));
        $phone_number = strip_tags($this->getRequest()->getParam('phone', null));
        $bank_account = strip_tags($this->getRequest()->getParam('bank_account', null));
        $bank_country = strip_tags($this->getRequest()->getParam('bank_country', null));
        $swift = strip_tags($this->getRequest()->getParam('swift', null));
        $iban = strip_tags($this->getRequest()->getParam('iban', null));
        $receive_email = strip_tags($this->getRequest()->getParam('receive_email', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));
        $language = strip_tags($this->getRequest()->getParam('language', null));

        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::updatePlayer($session_id, $email, $first_name, $last_name,
	        $birthday, $country, $zip, $city, $street_address1, $street_address2,
	        $phone_number, $bank_account, $bank_country, $swift, $iban, $receive_email, $ip_address, $language);
	}

	/**
	 * @return mixed
	 */
	public function resetPasswordAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));
        $password_old = strip_tags($this->getRequest()->getParam('password_old', null));
        $password_new = strip_tags($this->getRequest()->getParam('password_new', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::resetPassword($session_id, $password_old, $password_new, $ip_address);
	}

    /**
	 * @return mixed
	 */
	public function validatePlayerAction(){
        $username = strip_tags($this->getRequest()->getParam('username', null));

        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::validatePlayer($username);
	}

    /**
	 * @return mixed
	 */
	public function validatePlayerPhoneAction(){
        $player_id = strip_tags($this->getRequest()->getParam('player_id', null));
        $phone = strip_tags($this->getRequest()->getParam('phone', null));
        $white_label_name = strip_tags($this->getRequest()->getParam('white_label_name', null));

        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::validatePlayerPhone($player_id, $phone, $white_label_name);
	}

    /**
	 * @return mixed
	 */
	public function validatePlayerEmailAction(){
        $player_id = strip_tags($this->getRequest()->getParam('player_id', null));
        $player_email = strip_tags($this->getRequest()->getParam('player_email', null));
        $white_label_name = strip_tags($this->getRequest()->getParam('white_label_name', null));

        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::validatePlayerEmail($player_id, $player_email, $white_label_name);
	}

    /**
	 * @return mixed
	 */
	public function validatePlayerFirstNameLastNameBirthdayAction(){
        $player_id = strip_tags($this->getRequest()->getParam('player_id', null));
        $player_first_name = strip_tags($this->getRequest()->getParam('player_first_name', null));
        $player_last_name = strip_tags($this->getRequest()->getParam('player_last_name', null));
        $player_birthday = strip_tags($this->getRequest()->getParam('player_birthday', null));
        $white_label_name = strip_tags($this->getRequest()->getParam('white_label_name', null));

        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::validatePlayerFirstNameLastNameBirthday($player_id, $player_first_name, $player_last_name, $player_birthday, $white_label_name);
	}

    /**
	 * @return mixed
	 */
	public function validatePlayerFirstNameLastNameAddressAction(){
        $player_id = strip_tags($this->getRequest()->getParam('player_id', null));
        $player_first_name = strip_tags($this->getRequest()->getParam('player_first_name', null));
        $player_last_name = strip_tags($this->getRequest()->getParam('player_last_name', null));
        $player_city = strip_tags($this->getRequest()->getParam('player_city', null));
        $player_address = strip_tags($this->getRequest()->getParam('player_address', null));
        $player_address2 = strip_tags($this->getRequest()->getParam('player_address2', null));
        $white_label_name = strip_tags($this->getRequest()->getParam('white_label_name', null));

        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::validatePlayerFirstNameLastNameAddress($player_id, $player_first_name, $player_last_name, $player_city, $player_address, $player_address2, $white_label_name);
	}

    /**
	 * @return mixed
	 */
	public function validatePlayerFirstNameLastNamePhoneAction(){
        $player_id = strip_tags($this->getRequest()->getParam('player_id', null));
        $player_first_name = strip_tags($this->getRequest()->getParam('player_first_name', null));
        $player_last_name = strip_tags($this->getRequest()->getParam('player_last_name', null));
        $player_phone = strip_tags($this->getRequest()->getParam('player_phone', null));
        $white_label_name = strip_tags($this->getRequest()->getParam('white_label_name', null));

        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::validatePlayerFirstNameLastNamePhone($player_id, $player_first_name, $player_last_name, $player_phone, $white_label_name);
	}

    /**
	 * @return mixed
	 */
	public function playerCreditsAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSitePlayerAccountManager.php";
		WebSitePlayerAccountManager::playerCredits($session_id, $ip_address);
	}
}