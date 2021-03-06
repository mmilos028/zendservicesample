<?php
/**
*	Web service for external integration to our system
*/
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once MODELS_DIR . DS . 'ExternalIntegrationModel.php';

class Enum {
    const MISSING_PARAMETERS                    = 'Missing parameters';
	const MISSING_PARAMETERS_ID				    = '100';
	const UNKNOWN_ERROR                         = 'Unknown error';
	const UNKNOWN_ERROR_ID                      = '101';
	const INVALID_IP_ADDRESS                    = 'Invalid ip address';
	const INVALID_IP_ADDRESS_ID                 = '102';
	const INVALID_METHOD						= 'Not a post method';
	const INVALID_METHOD_ID						= '103';
}

class OperationName {
	const GET_TOKEN = 'get-token';
    const CLOSE_SESSION = 'close-session';
}

class ExternalIntegrationController extends Zend_Controller_Action {

	public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/xml');
	}

	public function indexAction(){
		header('Location: http://www.google.com/');
	}

	private function isWhiteListedIP($clientIpAddress){
		/*
		$config = Zend_Registry::get('config');
		//check if configuration for integration casino exists
		if(strlen($config->externalIntegrationTestWhiteListIP) == 0){
			return false;
		}
		if($config->externalIntegrationTestWhiteListIP == "true"){
			//tests for white listed ip address from integration casino
			//location ex. /application/configs/whitelist/external_integration_SECTION-NAME.ini
			$filePath = APP_DIR . DS . 'configs' . DS . 'whitelist' . DS . 'external_integration_' . $config->getSectionName() . '.ini';
			$lines = file($filePath);
			$flag = false;
			foreach($lines as $line){
				if(trim($line) == "*"){
					$flag = true;
					break;
				}
				if(trim($line) == trim($clientIpAddress))$flag = true;
				if($flag)break;
			}
			if(!$flag){
				$errorHelper = new ErrorHelper();
				$message = "External Integration Error: IP Address not in white list. <br /> IP Address: " . $clientIpAddress;
				$errorHelper->externalIntegrationError($message, $message);
			}
			return $flag;
		}else{
			//does not test for white listed ip address from integration casino
			//allowed access for everyone
			return true;
		}
		*/
		return true;
	}

	public function preDispatch(){
		$ip_address = IPHelper::getRealIPAddress();
		if(!$this->isWhiteListedIP($ip_address)){
        $response = "";
        $response .= "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<info>";
            $response .= "<status>NOK</status>";
            $response .= "<error_desc>" . Enum::INVALID_IP_ADDRESS . "</error_desc>";
            $response .= "<error_code>" . Enum::INVALID_IP_ADDRESS_ID . "</error_code>";
            $response .= "</info>";
        exit($response);
		}
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			$errorHelper = new ErrorHelper();
			$message = "External Integration Error: Invalid POST Method. <br /> IP Address: " . IPHelper::getRealIPAddress();
			$errorHelper->externalIntegrationError($message, $message);
      $response = "";
          $response .= "<?xml version='1.0' encoding='UTF-8'?>";
          $response .= "<info>";
          $response .= "<status>NOK</status>";
          $response .= "<error_desc>" . Enum::INVALID_METHOD . "</error_desc>";
          $response .= "<error_code>" . Enum::INVALID_METHOD_ID . "</error_code>";
          $response .= "</info>";
      exit($response);
		}
	}

	/**
	* Return token
	* @param string $username
	* @param string $password
	* @param string $player_id
	* @param string $player_currency
	* @param string $white_label_id
    * @param string $parent_affiliate_id
    * @param string $player_path
	* @return mixed
	*/
	public function getTokenAction(){
		$ip_address = IPHelper::getRealIPAddress();
		$username  = urldecode(strip_tags($_POST['username']));
		$password  = strip_tags($_POST['password']);
    $player_name = urldecode(strip_tags($_POST['player_name']));
		$player_id = strip_tags($_POST['player_id']);
		$player_currency = strip_tags($_POST['player_currency']);
		$white_label_id = strip_tags($_POST['white_label_id']);
    //player is created under parent_affiliate_id
    $parent_affiliate_id = strip_tags($_POST['parent_affiliate_id']);
    $player_path = urldecode(strip_tags($_POST['player_path']));
		//if parameters are missing
		if(strlen($username) == 0 || strlen($password) == 0 || strlen($player_id) == 0 || strlen($player_name) == 0 || strlen($player_currency) == 0 || strlen($parent_affiliate_id) == 0){
			$message = "External Integration: getToken <br /> Missing some input parameters <br /> IP Address = " . $ip_address .
			"<br /> getToken(username = {$username}, password = {$password}, player_id = {$player_id}, player_name =  {$player_name}, player_currency = {$player_currency},
			white_label_id = {$white_label_id}, parent_affiliate_id = {$parent_affiliate_id}, player_path = {$player_path})";
			$errorHelper = new ErrorHelper();
			$errorHelper->externalIntegrationError($message, $message);
            $response = "";
            $response .= "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<info>";
                $response .= "<status>NOK</status>";
                $response .= "<error_desc>" . Enum::MISSING_PARAMETERS . "</error_desc>";
                $response .= "<error_code>" . Enum::MISSING_PARAMETERS_ID . "</error_code>";
                $response .= "<operation>" . OperationName::GET_TOKEN . "</operation>";
                $response .= "<username>" . $username . "</username>";
                $response .= "<player_id>" . $player_id . "</player_id>";
                $response .= "<player_name>" . $player_name . "</player_name>";
                $response .= "<player_currency>" . $player_currency . "</player_currency>";
                $response .= "<white_label_id>" . $white_label_id . "</white_label_id>";
                $response .= "<parent_affiliate_id>" . $parent_affiliate_id . "</parent_affiliate_id>";
                $response .= "<player_path>" . $player_path . "</player_path>";
                $response .= "</info>";
            exit($response);
		}
		try{
			$modelExternalIntegration = new ExternalIntegrationModel();
			$result = $modelExternalIntegration->getToken($username, $password, $player_id, $player_name, $player_currency, $white_label_id, $parent_affiliate_id, $player_path);
			if($result['status'] == NOK){
				//THERE WAS AN UKNOWN ERROR IN DATABASE
        $response = "";
        $response .= "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<info>";
            $response .= "<status>NOK</status>";
            $response .= "<error_desc>" . Enum::UNKNOWN_ERROR . "</error_desc>";
            $response .= "<error_code>" . Enum::UNKNOWN_ERROR_ID . "</error_code>";
            $response .= "<operation>" . OperationName::GET_TOKEN . "</operation>";
            $response .= "<username>" . $username . "</username>";
            $response .= "<player_id>" . $player_id . "</player_id>";
            $response .= "<player_name>" . $player_name . "</player_name>";
            $response .= "<player_currency>" . $player_currency . "</player_currency>";
            $response .= "<white_label_id>" . $white_label_id . "</white_label_id>";
            $response .= "<parent_affiliate_id>" . $parent_affiliate_id . "</parent_affiliate_id>";
            $response .= "<player_path>" . $player_path . "</player_path>";
            $response .= "<detail_desc>" . $result['database_message'] . "</detail_desc>";
            $response .= "<detail_code>" . Enum::UNKNOWN_ERROR_ID . "</detail_code>";
            $response .= "</info>";
        exit($response);
			}else if($result['status'] == NOK || strlen($result['error_message']) != 0){
				//RESULT IS OK, BUT THERE IS ERROR MESSAGE
				//UNKNOWN ERROR IN DATABASE
				$message = "External Integration: getToken <br /> IP Address = {$ip_address} <br /> Unknown error in database <br /> {$result['error_message']}" .
				"<br /> getToken(username = {$username}, password = {$password}, player_id = {$player_id}, player_name = {$player_name}, player_currency = {$player_currency},
				white_label_id = {$white_label_id}, parent_affiliate_id = {$parent_affiliate_id}, player_path = {$player_path})";
				$errorHelper = new ErrorHelper();
				$errorHelper->externalIntegrationError($message, $message);
        $response = "";
        $response .= "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<info>";
            $response .= "<status>NOK</status>";
            $response .= "<error_desc>" . Enum::UNKNOWN_ERROR . "</error_desc>";
            $response .= "<error_code>" . Enum::UNKNOWN_ERROR_ID . "</error_code>";
            $response .= "<operation>" . OperationName::GET_TOKEN . "</operation>";
            $response .= "<username>" . $username . "</username>";
            $response .= "<player_id>" . $player_id . "</player_id>";
            $response .= "<player_name>" . $player_name . "</player_name>";
            $response .= "<player_currency>" . $player_currency . "</player_currency>";
            $response .= "<white_label_id>" . $white_label_id . "</white_label_id>";
            $response .= "<parent_affiliate_id>" . $parent_affiliate_id . "</parent_affiliate_id>";
            $response .= "<player_path>" . $player_path . "</player_path>";
            $response .= "<detail_desc>" . $result['error_message'] . "</detail_desc>";
            $response .= "<detail_code>" . Enum::UNKNOWN_ERROR_ID . "</detail_code>";
            $response .= "</info>";
        exit($response);
			}else if($result['status'] == OK){
				//RESULT is OK, return TOKEN
        $response = "";
        $response .= "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<info>";
            $response .= "<status>OK</status>";
            $response .= "<operation>" . OperationName::GET_TOKEN . "</operation>";
            $response .= "<username>" . $username . "</username>";
            $response .= "<player_id>" . $player_id . "</player_id>";
            $response .= "<player_name>" . $player_name . "</player_name>";
            $response .= "<player_currency>" . $player_currency . "</player_currency>";
            $response .= "<white_label_id>" . $white_label_id . "</white_label_id>";
            $response .= "<parent_affiliate_id>" . $parent_affiliate_id . "</parent_affiliate_id>";
            $response .= "<player_path>" . $player_path . "</player_path>";
            $response .= "<token>" . $result['token'] . "</token>";
            $response .= "</info>";
        exit($response);
			}else{
				//UNKNOWN ERROR IN DATABASE
				$message = "External Integration: getToken <br /> IP Address = {$ip_address} <br /> Unknown error in database <br /> {$result['database_code']} <br /> {$result['database_message']}" .
				"<br /> getToken(username = {$username}, password = {$password}, player_id = {$player_id}, player_name = {$player_name}, player_currency = {$player_currency},
				white_label_id = {$white_label_id}, parent_affiliate_id = {$parent_affiliate_id}, player_path = {$player_path})";
				$errorHelper = new ErrorHelper();
				$errorHelper->externalIntegrationError($message, $message);
        $response = "";
            $response .= "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<info>";
            $response .= "<status>NOK</status>";
            $response .= "<error_desc>" . Enum::UNKNOWN_ERROR . "</error_desc>";
            $response .= "<error_code>" . Enum::UNKNOWN_ERROR_ID . "</error_code>";
            $response .= "<operation>" . OperationName::GET_TOKEN . "</operation>";
            $response .= "<username>" . $username . "</username>";
            $response .= "<player_id>" . $player_id . "</player_id>";
            $response .= "<player_name>" . $player_name . "</player_name>";
            $response .= "<player_currency>" . $player_currency . "</player_currency>";
            $response .= "<white_label_id>" . $white_label_id . "</white_label_id>";
            $response .= "<parent_affiliate_id>" . $parent_affiliate_id . "</parent_affiliate_id>";
            $response .= "<player_path>" . $player_path . "</player_path>";
            $response .= "<detail_desc>" . $result['database_message'] . "</detail_desc>";
            $response .= "<detail_code>" . $result['database_code'] . "</detail_code>";
            $response .= "</info>";
        exit($response);
			}
		}catch(Zend_Exception $ex){
      $result = array(
          "database_code"=>$ex->getCode(),
          "database_message"=>$ex->getMessage()
      );
			$message = "External Integration: getToken <br /> IP Address = {$ip_address} <br /> Unknown error in database <br /> {$result['database_code']} <br /> {$result['database_message']}" .
			"<br /> getToken(username = {$username}, password = {$password}, player_id = {$player_id}, player_name = {$player_name}, player_currency = {$player_currency},
			white_label_id = {$white_label_id}, parent_affiliate_id = {$parent_affiliate_id}, player_path = {$player_path})";
			$errorHelper = new ErrorHelper();
			$errorHelper->externalIntegrationError($message, $message);
      $response = "";
          $response .= "<?xml version='1.0' encoding='UTF-8'?>";
          $response .= "<info>";
          $response .= "<status>NOK</status>";
          $response .= "<error_desc>" . Enum::UNKNOWN_ERROR . "</error_desc>";
          $response .= "<error_code>" . Enum::UNKNOWN_ERROR_ID . "</error_code>";
          $response .= "<operation>" . OperationName::GET_TOKEN . "</operation>";
          $response .= "<username>" . $username . "</username>";
          $response .= "<player_id>" . $player_id . "</player_id>";
          $response .= "<player_name>" . $player_name . "</player_name>";
          $response .= "<player_currency>" . $player_currency . "</player_currency>";
          $response .= "<white_label_id>" . $white_label_id . "</white_label_id>";
          $response .= "<parent_affiliate_id>" . $parent_affiliate_id . "</parent_affiliate_id>";
          $response .= "<player_path>" . $player_path . "</player_path>";
          $response .= "<detail_desc>" . $result['database_message'] . "</detail_desc>";
          $response .= "<detail_code>" . $result['database_code'] . "</detail_code>";
          $response .= "</info>";
      exit($response);
		}
	}

    /**
	* Notify player closed games
	* @param string $player_id
	* @return mixed
	*/
	public function closeSessionAction(){
		$ip_address = IPHelper::getRealIPAddress();
		$player_id = strip_tags($_POST['player_id']);
		//if parameters are missing
		if(strlen($player_id) == 0){
			$message = "External Integration: getToken <br /> Missing some input parameters <br /> IP Address = " . $ip_address .
			"<br /> closeSession(player_id = {$player_id})";
			$errorHelper = new ErrorHelper();
			$errorHelper->externalIntegrationError($message, $message);
      $response = "";
      $response .= "<?xml version='1.0' encoding='UTF-8'?>";
          $response .= "<info>";
          $response .= "<status>NOK</status>";
          $response .= "<error_desc>" . Enum::MISSING_PARAMETERS . "</error_desc>";
          $response .= "<error_code>" . Enum::MISSING_PARAMETERS_ID . "</error_code>";
          $response .= "<operation>" . OperationName::CLOSE_SESSION . "</operation>";
          $response .= "<player_id>" . $player_id . "</player_id>";
          $response .= "</info>";
      exit($response);
		}
		try{
			$modelExternalIntegration = new ExternalIntegrationModel();
			$result = $modelExternalIntegration->closePcAndSiteSession($player_id);
			if($result['status'] == OK){
				//RESULT is OK, close session message
        $response = "";
        $response .= "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<info>";
            $response .= "<status>OK</status>";
            $response .= "<operation>" . OperationName::CLOSE_SESSION . "</operation>";
            $response .= "<player_id>" . $player_id . "</player_id>";
            $response .= "</info>";
        exit($response);
			}else{
				//UNKNOWN ERROR IN DATABASE
				$message = "External Integration: closeSession <br /> IP Address = {$ip_address} <br /> Unknown error in database <br /> {$result['database_code']} <br /> {$result['database_message']}" .
				"<br /> closeSession(player_id = {$player_id})";
				$errorHelper = new ErrorHelper();
				$errorHelper->externalIntegrationError($message, $message);
        $response = "";
            $response .= "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<info>";
            $response .= "<status>NOK</status>";
            $response .= "<error_desc>" . Enum::UNKNOWN_ERROR . "</error_desc>";
            $response .= "<error_code>" . Enum::UNKNOWN_ERROR_ID . "</error_code>";
            $response .= "<operation>" . OperationName::CLOSE_SESSION . "</operation>";
            $response .= "<player_id>" . $player_id . "</player_id>";
            $response .= "<detail_desc>" . $result['database_message'] . "</detail_desc>";
            $response .= "<detail_code>" . $result['database_code'] . "</detail_code>";
            $response .= "</info>";
        exit($response);
			}
		}catch(Zend_Exception $ex){
      $result = array(
          "database_code"=>$ex->getCode(),
          "database_message"=>$ex->getMessage()
      );
			$message = "External Integration: closeSession <br /> IP Address = {$ip_address} <br /> Unknown error in database <br /> {$result['database_code']} <br /> {$result['database_message']}" .
			"<br /> closeSession(player_id = {$player_id})";
			$errorHelper = new ErrorHelper();
			$errorHelper->externalIntegrationError($message, $message);
      $response = "";
          $response .= "<?xml version='1.0' encoding='UTF-8'?>";
          $response .= "<info>";
          $response .= "<status>NOK</status>";
          $response .= "<error_desc>" . Enum::UNKNOWN_ERROR . "</error_desc>";
          $response .= "<error_code>" . Enum::UNKNOWN_ERROR_ID . "</error_code>";
          $response .= "<operation>" . OperationName::CLOSE_SESSION . "</operation>";
          $response .= "<player_id>" . $player_id . "</player_id>";
          $response .= "<detail_desc>" . $result['database_message'] . "</detail_desc>";
          $response .= "<detail_code>" . $result['database_code'] . "</detail_code>";
          $response .= "</info>";
      exit($response);
		}
	}
}
