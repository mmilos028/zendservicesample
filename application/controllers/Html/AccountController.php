<?php
/**
	Game client HTML5 implementation of Account controller
*/
require_once MODELS_DIR . DS . 'PlayerModel.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';

/**
 * Performes player account actions trough web service
 */
class Html_AccountController extends Zend_Controller_Action {

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
				"\n\n /onlinecasinoservice/html_account " .
				"\n\n new-account (session_id, affiliate_id, username, password, email, first_name, last_name, birthday, zip, phone, city, address, country, currency)" .
				"\n\n validate-player (username)" .
                "\n\n player-credits (session_id)";
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
	 * Create new account for player ...
	 * @return string
	 */
	public function newAccountAction(){
		try{
			$session_id = strip_tags($this->getRequest()->getParam('session_id', null));
			$affiliate_id = strip_tags($this->getRequest()->getParam('affiliate_id', null));
			$username = strip_tags($this->getRequest()->getParam('username', null));
			$password = strip_tags($this->getRequest()->getParam('password', null));
			$email = strip_tags($this->getRequest()->getParam('email', null));
			$first_name = strip_tags($this->getRequest()->getParam('first_name', null));
			$last_name = strip_tags($this->getRequest()->getParam('last_name', null));
			$birthday = strip_tags($this->getRequest()->getParam('birthday', null));
			$zip = strip_tags($this->getRequest()->getParam('zip', null));
			$phone = strip_tags($this->getRequest()->getParam('phone', null));
			$city = strip_tags($this->getRequest()->getParam('city', null));
			$address = strip_tags($this->getRequest()->getParam('address', null));
			$country = strip_tags($this->getRequest()->getParam('country', null));
			$currency = strip_tags($this->getRequest()->getParam('currency', null));
			$modelPlayer = new PlayerModel();
			$player_type_id = $modelPlayer->getPlayerTypeID($session_id, ROLA_PL_PC_PLAYER_INTERNET);
			$password = md5(md5($password));
			$res = $modelPlayer->manageUser($session_id, INSERT, $affiliate_id, $username, 
			$password, $player_type_id, null, $email, $country, $currency, NO, $zip, $phone, 
			$address, $birthday, $first_name, $last_name, $city, null, null, null, null, null, 
			null, null, null, null, null, null, null);
	    	unset($modelPlayer);
	    	$message = array(
				"status"=>OK,
				"result"=>"1"
			);
			exit(Zend_Json::encode($message));
		}catch(Zend_Exception $ex){			
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);			
			$message = array(
				"status"=>NOK,
				"message"=>INTERNAL_ERROR_MESSAGE,
				"result"=>"0"
			);
			exit(Zend_Json::encode($message));
		}
	}
	/**
	 * validates if new player username exists in our database ...
	 * @return mixed
	 */
	public function validatePlayerAction(){
		$username = strip_tags($this->getRequest()->getParam('username', null));
		try{
			if(!isset($username) || strlen($username) == 0 || $username == 'null'){
				$message = array(
					"status"=>NOK,
					"message"=>PARAMETER_MISSING_MESSAGE,
					"username"=>$username
				);
				exit(Zend_Json::encode($message));
			}
			$modelPlayer = new PlayerModel();
			$exist = $modelPlayer->validatePlayerName($username);
			unset($modelPlayer);
			$message = array(
				"status"=>OK,
				"result"=>$exist
			);
			exit(Zend_Json::encode($message));
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			$message = array(
				"status"=>NOK,
				"message"=>INTERNAL_ERROR_MESSAGE
			);
			exit(Zend_Json::encode($message));
		}
	}

    /**
	 *
	 * player credits method
	 * @return mixed
	 */
	public function playerCreditsAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));
		if(!isset($session_id) || strlen($session_id) == 0 || $session_id == 'null'){
			$message = array(
                "status"=>NOK,
                "message"=>PARAMETER_MISSING_MESSAGE
            );
            exit(Zend_Json::encode($message));
		}
        $ip_address = IPHelper::getRealIPAddress();
        //if there are ip addresses with , separated as CSV string
        $ip_addresses = explode(",", $ip_address);
        $ip_address = $ip_addresses[0];
		try{
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
            $result = $modelPlayer->getPlayerCredits($session_id);
			if($result['status'] != OK){
				$message = array(
                    "status"=>NOK,
                    "message"=>INTERNAL_ERROR_MESSAGE
                );
                exit(Zend_Json::encode($message));
			}
            $message = array(
				"status"=>OK,
				"credits"=>NumberHelper::convert_double($result['credits']),
                "credits_formatted"=>NumberHelper::format_double($result['credits'])
			);
			exit(Zend_Json::encode($message));
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "AccountController::playerCredits. Error while getting player credits for web site. <br /> Session id: {$session_id} <br /> IP address: {$ip_address} <br /> Player credits on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$message = array(
                "status"=>NOK,
                "message"=>INTERNAL_ERROR_MESSAGE
            );
            exit(Zend_Json::encode($message));
		}
	}
}