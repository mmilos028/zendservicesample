<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
use outcomebet\casino25\api\client\Client;
require_once 'casino25api' . DS . 'vendor' . DS . 'autoload.php';
require_once __DIR__ . '/../ErrorConstants.php';

/**
 *
 * web service for outcomebet integration
 *
 */
class OutcomebetManager {

    private $_client = null;

    public function __construct(){
        $client = new \outcomebet\casino25\api\client\Client(
            array(
                "ssl_verification" => false,
                "sslKeyPath" => "casino25api" . DS . "apikey8.pem",
                "url" => "https://api.gamingsystem.org:8443/"
            )
        );
        $this->_client = $client;
    }

    /**
     * @return mixed
     */
    public function listGames(){
        try {
            $this->_client->listGames();

            $json_message = Zend_Json::encode(
			    array(
			        "status"=>OK,
                )
            );
			exit($json_message);
        }catch(Exception $ex){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
        }
    }

    /**
     * @param $game_id
     * @param $affiliate_id
     * @param $player_id
     * @param $player_username
     * @param $currency
     * @return array
     */
    public function startGame($game_id, $affiliate_id, $player_id, $player_username, $currency){
        try {
            $bankGroup = array(
                "Id" => $affiliate_id,
                "Currency" => $currency,
            );
            $this->_client->createBankGroup($bankGroup);
			try{
                $templateSettings = array(
                    "BankGroupId" => $affiliate_id,
                    "SettingsTemplateId" => "T" . $currency,
                );
                $this->_client->applySettingsTemplate($templateSettings);
            }catch(Exception $ex){
                $errorHelper = new ErrorHelper();
                $message = "OUTCOMEBET INTEGRATION Error <br /> OutcomebetManager::applySettingsTemplate (BankGroupId = {$affiliate_id}, SettingsTemplateId = T{$currency}) <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceError($message, $message);
            }
        }catch(Exception $ex){
			$exception_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            if((trim($ex->getMessage()) != "bankgroup_exists") || (trim($ex->getMessage()) != "ERROR: INSERT has more target columns than expressions (SQLSTATE 42601)")) {
                $errorHelper = new ErrorHelper();
                $message = "OUTCOMEBET INTEGRATION Error <br /> OutcomebetManager::createBankGroup (Id = {$affiliate_id}, Currency = {$currency}) <br /> Exception: " . $exception_message;
                $errorHelper->serviceError($message, $message);
            }
        }
        try {
            $player = array(
                "Id" => $player_id,
                "Nick" => $player_username,
                "BankGroupId" => $affiliate_id
            );
            $this->_client->createPlayer($player);
        }catch(Exception $ex){
			$exception_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            if(trim($ex->getMessage()) != "player_exists") {
                $errorHelper = new ErrorHelper();
                $message = "OUTCOMEBET INTEGRATION Error <br /> OutcomebetManager::createPlayer (Id = {$player_id}, Nick = {$player_username}, BankGroupId = {$affiliate_id}) <br /> Exception: " . $exception_message;
                $errorHelper->serviceError($message, $message);
            }
        }
        if(strlen($player_id) == 0 && strlen($game_id) == 0){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
        }
        try {
            $session = array(
                "PlayerId" => $player_id,
                "GameId" => $game_id
            );
            $this->_client->createSession($session);

            $json_message = Zend_Json::encode(
			    array(
			        "status"=>OK,
                )
            );
			exit($json_message);
        }catch(Exception $ex){
			$errorHelper = new ErrorHelper();
            $message = "OUTCOMEBET INTEGRATION Error <br /> OutcomebetManager::createSession (PlayerId = {$player_id}, GameId = {$game_id}) <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
        }
    }

    /**
     * @param $affiliate_id
     * @param $currency
     * @return mixed
     */
    public function createBankGroup($affiliate_id, $currency)
	{
        try {
            /*
            require_once MODELS_DIR . DS . 'AuthorizationModel.php';
            $modelAuthorization = new AuthorizationModel();
			$hashed_password = md5(md5(FICTIVE_BO_PASSWORD));
            //autodetect ip address from client
            $ip_address = IPHelper::getRealIPAddress();
			$bo_session_id = $modelAuthorization->openBoSession(FICTIVE_BO_USERNAME, $hashed_password, $ip_address);
			if($bo_session_id == 0){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			require_once MODELS_DIR . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$affiliateDetails = $modelAuthorization->getAffiliateDetails($bo_session_id, $affiliate_id);
            if($affiliateDetails['status'] == NOK){
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
            $affiliate_username = $affiliateDetails['cursor']['user_name'];
            $affiliate_path = $affiliateDetails['cursor']['path'] . $affiliate_username; //path
            */
            $bankGroup = array(
                "Id" => $affiliate_id,
                "Currency" => $currency,
            );
            $this->_client->createBankGroup($bankGroup);

            $json_message = Zend_Json::encode(
			    array(
			        "status"=>OK,
                )
            );
			exit($json_message);
        }catch(Exception $ex){
			$exception_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            if((trim($ex->getMessage()) != "bankgroup_exists") || (trim($ex->getMessage()) != "ERROR: INSERT has more target columns than expressions (SQLSTATE 42601)")) {
                $errorHelper = new ErrorHelper();
                $message = "OUTCOMEBET INTEGRATION Error <br /> OutcomebetManager::createBankGroup (Id = {$affiliate_id}, Currency = {$currency}) <br /> Exception: " . $exception_message;
                $errorHelper->serviceError($message, $message);
            }

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
        }
	}
	
	/**
     * @param $affiliate_id
     * @param $currency
     * @return mixed
     */
    public function applySettingsTemplate($affiliate_id, $currency){
        try {
            $settings_template_id = "T" . $currency;
            $bankGroup = array(
                "BankGroupId"=>$affiliate_id,
                "SettingsTemplateId"=>$settings_template_id,
            );
            $this->_client->applySettingsTemplate($bankGroup);

            $json_message = Zend_Json::encode(
			    array(
			        "status"=>OK
                )
            );
			exit($json_message);
        }catch(Exception $ex){
			$errorHelper = new ErrorHelper();
            $message = "OUTCOMEBET INTEGRATION Error <br /> OutcomebetManager::applySettingsTemplate (BankGroupId = {$affiliate_id}, SettingsTemplateId = T{$currency}) <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
        }
    }

    /**
     * @param $player_id
     * @param $player_username
     * @param $affiliate_id
     * @return mixed
     */
    public function createPlayer($player_id, $player_username = null, $affiliate_id = null){
        try {
            /*require_once MODELS_DIR . DS . 'AuthorizationModel.php';
            $modelAuthorization = new AuthorizationModel();
			$hashed_password = md5(md5(FICTIVE_BO_PASSWORD));
            //autodetect ip address from client
            $ip_address = IPHelper::getRealIPAddress();
			$bo_session_id = $modelAuthorization->openBoSession(FICTIVE_BO_USERNAME, $hashed_password, $ip_address);
			if($bo_session_id == 0){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			require_once MODELS_DIR . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$affiliateDetails = $modelAuthorization->getAffiliateDetails($bo_session_id, $affiliate_id);
            if($affiliateDetails['status'] == NOK){
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
            $affiliate_username = $affiliateDetails['cursor']['user_name'];
            $affiliate_path = $affiliateDetails['cursor']['path'] . $affiliate_username; //path
            */
            $player = array(
                "Id"=>$player_id,
                "Nick"=>$player_username,
                "BankGroupId"=>$affiliate_id
            );
            $this->_client->createPlayer($player);

            $json_message = Zend_Json::encode(
			    array(
			        "status"=>OK,
                )
            );
			exit($json_message);
        }catch(Exception $ex){
			$exception_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            if(trim($ex->getMessage()) != "player_exists") {
                $errorHelper = new ErrorHelper();
                $message = "OUTCOMEBET INTEGRATION Error <br /> OutcomebetManager::createPlayer (Id = {$player_id}, Nick = {$player_username}, BankGroupId = {$affiliate_id}) <br /> Exception: " . $exception_message;
                $errorHelper->serviceError($message, $message);
            }
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
        }
    }

    /**
     * @param $game_id
     * @param $player_id
     * @param $restore_policy
     * @param $static_host
     * @return mixed
     */
    public function createGameSession($game_id, $player_id, $restore_policy = 'Create', $static_host = null){
        try {
            $session = array(
                "PlayerId" => $player_id,
                "GameId" => $game_id
            );
            $this->_client->createSession($session);

            $json_message = Zend_Json::encode(
			    array(
			        "status"=>OK,
                )
            );
			exit($json_message);
        }catch(Exception $ex){
			$errorHelper = new ErrorHelper();
            $message = "OUTCOMEBET INTEGRATION Error <br /> OutcomebetManager::createSession (PlayerId = {$player_id}, GameId = {$game_id}) <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
        }
    }

    /**
     * @param $game_id
     * @param $bank_group_id
     * @param $start_balance
     * @param $static_host
     * @return array
     */
    public function createGameDemoSession($game_id, $bank_group_id, $start_balance = 1000, $static_host = null){
        try {
            $demoSession = array(
                "GameId" => $game_id,
                "BankGroupId" => $bank_group_id,
                "StartBalance" => $start_balance
            );
            $this->_client->createDemoSession($demoSession);

            $json_message = Zend_Json::encode(
			    array(
			        "status"=>OK,
                )
            );
			exit($json_message);
        }catch(Exception $ex){
			$errorHelper = new ErrorHelper();
            $message = "OUTCOMEBET INTEGRATION Error <br /> OutcomebetManager::createGameDemoSession (GameId = {$game_id}, BankGroupId = {$bank_group_id}, StartBalance = {$start_balance}) <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
        }
    }

    /**
     * @param $session_id
	 * @param $player_id
     * @return mixed
     */
    public function closeSession($session_id, $player_id = null){
        try {
            $session = array(
                "SessionId" => $session_id
            );
            $res1 = $this->_client->closeSession($session);
			if(!is_null($player_id)){
				require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'OutcomebetModel.php';
				$modelOutcomebet = new OutcomebetModel();
				$res2 = $modelOutcomebet->closeOutcomebetSession($player_id);
			}
            $json_message = Zend_Json::encode(
			    array(
			        "status"=>OK,
                )
            );
			exit($json_message);
        }catch(Exception $ex){
			$errorHelper = new ErrorHelper();
            $message = "OUTCOMEBET INTEGRATION Error <br /> OutcomebetManager::closeSession (SessionId = {$session_id}, PlayerId = {$player_id}) <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
        }
    }

    /**
     * @param $session_id
     * @return mixed
     */
    public function getSession($session_id){
        try {
            $session = array(
                "SessionId" => $session_id
            );
            $this->_client->getSession($session);

            $json_message = Zend_Json::encode(
			    array(
			        "status"=>OK,
                )
            );
			exit($json_message);
        }catch (Exception $ex){
			$errorHelper = new ErrorHelper();
            $message = "OUTCOMEBET INTEGRATION Error <br /> OutcomebetManager::getSession (SessionId = {$session_id}) <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
			$json_message = Zend_Json::encode(
			    array(
			        "status"=>NOK,
                    "error_message"=> $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
			exit($json_message);
        }
    }

}