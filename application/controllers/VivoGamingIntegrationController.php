<?php
/**
*	Web service for vivo gaming integration to our system
*/

require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once MODELS_DIR . DS . 'VivoGamingIntegrationModel.php';

/**
 * Class EnumErrorMessages
 */
class EnumErrorMessages {
    const INSUFFICIENT_FUNDS                    = 300;
    const OPERATION_FAILED  				    = 301;
    const UNKNOWN_TRANSACTION_ID_FOR_STATUS_API = 302;
    const TRANSACTION_ID_ALREADY_PROCESSED      = 302; //for bet, win, canceled_bet
    const UNKNOWN_USER_ID                       = 310;
    const INTERNAL_ERROR                        = 399;
    const INVALID_TOKEN						    = 400;
    const INVALID_HASH                          = 500;
}

/**
 * Class VivoGamingIntegrationController
 */
class VivoGamingIntegrationController extends Zend_Controller_Action{

    private $DEBUG = false;

    public function init(){
		    $this->_helper->viewRenderer->setNoRender(true);
		      $this->_helper->layout->disableLayout();
        header('Access-Control-Allow-Origin: *');
		    header('Content-Type: application/xml');
	}

    public function preDispatch(){
		$ip_address = IPHelper::getRealIPAddress();
		if(!$this->isWhiteListedIP($ip_address)){
            $errorHelper = new ErrorHelper();
			$message = "VivoGamingIntegrationController :: INVALID IP ADDRESS ACCESS FROM VIVO GAMING PROVIDER. <br /> IP Address = {$ip_address}";
			$errorHelper->vivoGamingIntegrationError($message, $message);
            //$this->returnError(EnumErrorMessages::PERMISSION_DENIED);
		}
		if(!in_array($_SERVER['REQUEST_METHOD'], array("POST", "GET"))) {
			$errorHelper = new ErrorHelper();
			$message = "VivoGamingIntegrationController :: INVALID GET/POST HTTP REQUEST METHOD. <br /> IP Address = {$ip_address}";
			$errorHelper->vivoGamingIntegrationError($message, $message);
            //$this->returnError(EnumErrorMessages::INVALID_HTTP_REQUEST);
		}
	}

    private function isWhiteListedIP($ip_address){
        
		$config = Zend_Registry::get('config');
		//check if configuration for integration casino exists
		if(strlen($config->vivoGamingIntegrationTestWhiteListIP) == 0){
            $errorHelper = new ErrorHelper();
			$message = "Vivo Gaming Integration Error: Configuration Settings Missing For Web Service. <br /> IP Address: " . $ip_address;
			$errorHelper->vivoGamingIntegrationError($message, $message);
			return false;
		}
		if($config->vivoGamingIntegrationTestWhiteListIP == "true"){
			//tests for white listed ip address from integration casino
			//location ex. /application/configs/whitelist/acceptor_integration_SECTION-NAME.ini
			$filePath = APP_DIR . DS . 'configs' . DS . 'whitelist' . DS . 'vivo_gaming_integration_' . $config->getSectionName() . '.ini';
			$lines = file($filePath);
			$flag = false;
			foreach($lines as $line){
				if(trim($line) == "*"){
					$flag = true;
					break;
				}
				if(trim($line) == trim($ip_address))$flag = true;
				if($flag)break;
			}
			if(!$flag){
				$errorHelper = new ErrorHelper();
				$message = "Vivo Gaming Integration Error: IP Address not in white list. <br /> IP Address: " . $ip_address;
				$errorHelper->vivoGamingIntegrationError($message, $message);
			}
			return $flag;
		}else{
			//does not test for white listed ip address from integration casino
			//allowed access for everyone
			return true;
		}
	}

    private function urldecodeparams($param){
        //return urlencode($param);
        return $param;
    }

    private function printInputResponse($message){
        if($this->DEBUG){
            $ip_address = IPHelper::getRealIPAddress();
            $message .= " Detected IP Address = {$ip_address}";
            $errorHelper = new ErrorHelper();
			$errorHelper->vivoGamingIntegrationAccessLog($message);
        }
    }

    private function printExitResponse($response){
        if($this->DEBUG){
            $ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $response_to_mail = ($response);
			$message = "Detected IP Address = {$ip_address} <br /> {$response_to_mail}";
			$errorHelper->vivoGamingIntegrationAccessLog($message);
        }
        exit($response);
    }

    //http://192.168.3.63/onlinecasinoservice/vivo-gaming-integration/authenticate?token=123&hash=4297f44b13955235245b2497399d7a93
    public function authenticateAction(){
        //set all parameter names from get/post to lowercase to be case insensitive
        $_REQUEST = array_change_key_case($_REQUEST, CASE_LOWER);
        $token = isset($_REQUEST['token']) ? $this->urldecodeparams($_REQUEST['token']) : '';
        $hash = isset($_REQUEST['hash']) ? $this->urldecodeparams($_REQUEST['hash']) : '';
        /*
        $token = $this->getRequest()->getParam('token', '');
        $hash = $this->getRequest()->getParam('hash', '');
        */

        $this->printInputResponse("VivoGamingIntegrationController::authenticate(token = {$token}, hash = {$hash})");

        $config = Zend_Registry::get('config');
        //test sent message for hash received and hash generated here
        $pass_key = $config->vivoGamingPassKey;
        $testHash = md5($token . $pass_key);
        if($testHash != $hash){
            $date_response = date("d M Y H:i:s");
			$response = "<?xml version='1.0' encoding='UTF-8'?>";
			$response .= "<VGSSYSTEM>";
			    $response .= "<REQUEST>";
                    $response .= "<TOKEN>{$token}</TOKEN>";
                    $response .= "<HASH>{$hash}</HASH>";
                $response .= "</REQUEST>";
                $response .= "<TIME>{$date_response}</TIME>";
                $response .= "<RESPONSE>";
                    $response .= "<RESULT>FAILED</RESULT>";
                    $response .= "<CODE>" . EnumErrorMessages::INVALID_HASH . "</CODE>";
                $response .= "</RESPONSE>";
			$response .= "</VGSSYSTEM>";
            $this->printExitResponse($response);
        }
        try{
            $modelVivoGamingIntegration = new VivoGamingIntegrationModel();
            $result = $modelVivoGamingIntegration->authenticate($token);
            if($result['status'] == OK){
                $user_id = $result['subject_id'];
                $username = $result['username'];
                $first_name = $result['first_name'];
                $last_name = $result['last_name'];
                $email = $result['email'];
                $currency = $result['currency'];
                $balance = NumberHelper::format_english_double($result['credits']);
                $ec_game_session_id = $result['game_session_id'];
                //CORRECT RESPONSE
                $date_response = date("d M Y H:i:s");
                $response = "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<VGSSYSTEM>";
                    $response .= "<REQUEST>";
                        $response .= "<TOKEN>{$token}</TOKEN>";
                        $response .= "<HASH>{$hash}</HASH>";
                    $response .= "</REQUEST>";
                    $response .= "<TIME>{$date_response}</TIME>";
                    $response .= "<RESPONSE>";
                        $response .= "<RESULT>OK</RESULT>";
                        $response .= "<USERID>{$user_id}</USERID>";
                        $response .= "<USERNAME>{$username}</USERNAME>";
                        $response .= "<FIRSTNAME>{$first_name}</FIRSTNAME>";
                        $response .= "<LASTNAME>{$last_name}</LASTNAME>";
                        $response .= "<EMAIL>{$email}</EMAIL>";
                        $response .= "<CURRENCY>{$currency}</CURRENCY>";
                        $response .= "<BALANCE>{$balance}</BALANCE>";
                        $response .= "<GAMESESSIONID>{$ec_game_session_id}</GAMESESSIONID>";
                    $response .= "</RESPONSE>";
                $response .= "</VGSSYSTEM>";
                $this->printExitResponse($response);
            }else if($result['status'] == NOK && $result['error_code'] == "-1"){
                //ERROR RESPONSE - INVALID TOKEN 400
                $date_response = date("d M Y H:i:s");
                $response = "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<VGSSYSTEM>";
                    $response .= "<REQUEST>";
                        $response .= "<TOKEN>{$token}</TOKEN>";
                        $response .= "<HASH>{$hash}</HASH>";
                    $response .= "</REQUEST>";
                    $response .= "<TIME>{$date_response}</TIME>";
                    $response .= "<RESPONSE>";
                        $response .= "<RESULT>FAILED</RESULT>";
                        $response .= "<CODE>" . EnumErrorMessages::INVALID_TOKEN . "</CODE>";
                    $response .= "</RESPONSE>";
                $response .= "</VGSSYSTEM>";
                $this->printExitResponse($response);
            }else{
                //ERROR RESPONSE - INTERNAL ERROR 399
                $date_response = date("d M Y H:i:s");
                $response = "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<VGSSYSTEM>";
                    $response .= "<REQUEST>";
                        $response .= "<TOKEN>{$token}</TOKEN>";
                        $response .= "<HASH>{$hash}</HASH>";
                    $response .= "</REQUEST>";
                    $response .= "<TIME>{$date_response}</TIME>";
                    $response .= "<RESPONSE>";
                        $response .= "<RESULT>FAILED</RESULT>";
                        $response .= "<CODE>" . EnumErrorMessages::INTERNAL_ERROR . "</CODE>";
                    $response .= "</RESPONSE>";
                $response .= "</VGSSYSTEM>";
                $this->printExitResponse($response);
            }

        }catch(Zend_Exception $ex){
            //ERROR RESPONSE - INTERNAL ERROR 399
            $date_response = date("d M Y H:i:s");
            $response = "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<VGSSYSTEM>";
                $response .= "<REQUEST>";
                    $response .= "<TOKEN>{$token}</TOKEN>";
                    $response .= "<HASH>{$hash}</HASH>";
                $response .= "</REQUEST>";
                $response .= "<TIME>{$date_response}</TIME>";
                $response .= "<RESPONSE>";
                    $response .= "<RESULT>FAILED</RESULT>";
                    $response .= "<CODE>" . EnumErrorMessages::INTERNAL_ERROR . "</CODE>";
                $response .= "</RESPONSE>";
            $response .= "</VGSSYSTEM>";
            $this->printExitResponse($response);
        }
    }

    /////http://192.168.3.63/onlinecasinoservice/vivo-gaming-integration/change-balance?userId=abc&Amount=75.00&TransactionID=abc&TrnType=BET&TrnDescription=GameRound:TableID=2&roundId=12345&gameId=5&History=127,15;129,50&isRoundFinished=false&hash=3678fb70838010e67347ca67f0a26760&sessionId=785df376123dfg
    public function changeBalanceAction(){
        $_REQUEST = array_change_key_case($_REQUEST, CASE_LOWER);
        $user_id = isset($_REQUEST['userid']) ? $this->urldecodeparams($_REQUEST['userid']) : '';
        $amount = isset($_REQUEST['amount']) ? $this->urldecodeparams($_REQUEST['amount']) : '';
        $vivo_transaction_id = isset($_REQUEST['transactionid']) ? $this->urldecodeparams($_REQUEST['transactionid']) : '';
        $transaction_type = isset($_REQUEST['trntype']) ? $this->urldecodeparams($_REQUEST['trntype']) : '';
        $transaction_description = isset($_REQUEST['trndescription']) ? $this->urldecodeparams($_REQUEST['trndescription']) : '';
        $round_id = isset($_REQUEST['roundid']) ? $this->urldecodeparams($_REQUEST['roundid']) : '';
        $game_id = isset($_REQUEST['gameid']) ? $this->urldecodeparams($_REQUEST['gameid']) : '';
        $session_id = isset($_REQUEST['sessionid']) ? $this->urldecodeparams($_REQUEST['sessionid']) : '';
        $history = isset($_REQUEST['history']) ? $this->urldecodeparams($_REQUEST['history']) : '';
        $is_round_finished = isset($_REQUEST['isroundfinished']) ? $this->urldecodeparams($_REQUEST['isroundfinished']) : '';
        $hash = isset($_REQUEST['hash']) ? $this->urldecodeparams($_REQUEST['hash']) : '';

        /*
        $user_id = $this->getRequest()->getParam('userId','');
        $amount = $this->getRequest()->getParam('Amount', '');
        $transaction_id = $this->getRequest()->getParam('TransactionID','');
        $transaction_type = $this->getRequest()->getParam('TrnType', '');
        $transaction_description = $this->getRequest()->getParam('TrnDescription', '');
        $round_id = $this->getRequest()->getParam('roundId', '');
        $game_id = $this->getRequest()->getParam('gameId', '');
        $session_id = $this->getRequest()->getParam('sessionId', '');
        $history = $this->getRequest()->getParam('History', '');
        $is_round_finished = $this->getRequest()->getParam('isRoundFinished', '');
        $hash = $this->getRequest()->getParam('hash', '');
        */

        $this->printInputResponse("VivoGamingIntegrationController::changeBalance(user_id = {$user_id}, amount = {$amount}, vivo_transaction_id = {$vivo_transaction_id}, transaction_type = {$transaction_type},
			transaction_description = {$transaction_description}, round_id = {$round_id}, game_id = {$game_id}, session_id = {$session_id}, history = {$history}, is_round_finished = {$is_round_finished},
			hash = {$hash})");

        if(!is_numeric($session_id) || strtoupper($session_id) == strtoupper('No_Session_Provided')){
            $session_id = -1;
        }

        $config = Zend_Registry::get('config');
        //test sent message for hash received and hash generated here
        $pass_key = $config->vivoGamingPassKey;
        $testHash = md5($user_id . $amount . $transaction_type . $transaction_description . $round_id . $game_id . $history . $pass_key);
        //exit($user_id . $amount . $transaction_type . $transaction_description . $round_id . $game_id . $history . $pass_key);
        if($testHash != $hash){
            $date_response = date("d M Y H:i:s");
			$response = "<?xml version='1.0' encoding='UTF-8'?>";
			$response .= "<VGSSYSTEM>";
			    $response .= "<REQUEST>";
                    $response .= "<USERID>{$user_id}</USERID>";
                    $response .= "<AMOUNT>{$amount}</AMOUNT>";
                    $response .= "<TRANSACTIONID>{$vivo_transaction_id}</TRANSACTIONID>";
                    $response .= "<TRNTYPE>{$transaction_type}</TRNTYPE>";
                    $response .= "<GAMEID>{$game_id}</GAMEID>";
                    $response .= "<ROUNDID>{$round_id}</ROUNDID>";
                    $response .= "<TRNDESCRIPTION>{$transaction_description}</TRNDESCRIPTION>";
                    $response .= "<ISROUNDFINISH>{$is_round_finished}</ISROUNDFINISH>";
                    $response .= "<HASH>{$hash}</HASH>";
                $response .= "</REQUEST>";
                $response .= "<TIME>{$date_response}</TIME>";
                $response .= "<RESPONSE>";
                    $response .= "<RESULT>FAILED</RESULT>";
                    $response .= "<CODE>" . EnumErrorMessages::INVALID_HASH . "</CODE>";
                $response .= "</RESPONSE>";
			$response .= "</VGSSYSTEM>";
            $this->printExitResponse($response);
        }

        if(!is_numeric($user_id) || !is_numeric($amount)){
            $date_response = date("d M Y H:i:s");
            $response = "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<VGSSYSTEM>";
                $response .= "<REQUEST>";
                    $response .= "<USERID>{$user_id}</USERID>";
                    $response .= "<AMOUNT>{$amount}</AMOUNT>";
                    $response .= "<TRANSACTIONID>{$vivo_transaction_id}</TRANSACTIONID>";
                    $response .= "<TRNTYPE>{$transaction_type}</TRNTYPE>";
                    $response .= "<GAMEID>{$game_id}</GAMEID>";
                    $response .= "<ROUNDID>{$round_id}</ROUNDID>";
                    $response .= "<TRNDESCRIPTION>{$transaction_description}</TRNDESCRIPTION>";
                    $response .= "<ISROUNDFINISH>{$is_round_finished}</ISROUNDFINISH>";
                    $response .= "<HASH>{$hash}</HASH>";
                $response .= "</REQUEST>";
                $response .= "<TIME>{$date_response}</TIME>";
                $response .= "<RESPONSE>";
                    $response .= "<RESULT>FAILED</RESULT>";
                    $response .= "<CODE>" . EnumErrorMessages::INTERNAL_ERROR . "</CODE>";
                $response .= "</RESPONSE>";
            $response .= "</VGSSYSTEM>";
            $this->printExitResponse($response);
        }

        $is_round_finished = ($is_round_finished == 'true') ? 1 : 0;

        try{
            $modelVivoGamingIntegration = new VivoGamingIntegrationModel();
            $transaction_type_int = $transaction_type;
            switch(strtoupper($transaction_type)){
                case 'BET':
                    $transaction_type_int = 1;
                    break;
                case 'WIN':
                    $transaction_type_int = 2;
                    break;
                case 'CANCELED_BET':
                    $transaction_type_int = 3;
                    break;
                case 'TIP':
                    $transaction_type_int = 4;
                    break;
                case 'COMPANSATION':
                case 'COMPENSATION':
                    $transaction_type_int = 5;
                    break;
                case 'BONUS':
                    $transaction_type_int = 6;
                    break;
                case 'DEPOSIT':
                    $transaction_type_int = 7;
                    break;
                case 'WITHDRAWN':
                    $transaction_type_int = 8;
                    break;
                default:
                    $transaction_type_int = $transaction_type;
            }

            $result = $modelVivoGamingIntegration->changeBalance($user_id, $amount, $vivo_transaction_id, $transaction_type_int,
                $transaction_description, $round_id, $game_id, $session_id, $history, $is_round_finished);
            if($result['status'] == OK){
                $ec_system_transaction_id = $result['ec_system_transaction_id'];
                $balance = NumberHelper::format_english_double($result['balance']);
                //CORRECT RESPONSE
                $date_response = date("d M Y H:i:s");
                $response = "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<VGSSYSTEM>";
                    $response .= "<REQUEST>";
                        $response .= "<USERID>{$user_id}</USERID>";
                        $response .= "<AMOUNT>{$amount}</AMOUNT>";
                        $response .= "<TRANSACTIONID>{$vivo_transaction_id}</TRANSACTIONID>";
                        $response .= "<TRNTYPE>{$transaction_type}</TRNTYPE>";
                        $response .= "<GAMEID>{$game_id}</GAMEID>";
                        $response .= "<ROUNDID>{$round_id}</ROUNDID>";
                        $response .= "<TRNDESCRIPTION>{$transaction_description}</TRNDESCRIPTION>";
                        $response .= "<ISROUNDFINISH>{$is_round_finished}</ISROUNDFINISH>";
                        $response .= "<HASH>{$hash}</HASH>";
                    $response .= "</REQUEST>";
                    $response .= "<TIME>{$date_response}</TIME>";
                    $response .= "<RESPONSE>";
                        $response .= "<RESULT>OK</RESULT>";
                        $response .= "<ECSYSTEMTRANSACTIONID>{$ec_system_transaction_id}</ECSYSTEMTRANSACTIONID>";
                        $response .= "<BALANCE>{$balance}</BALANCE>";
                    $response .= "</RESPONSE>";
                $response .= "</VGSSYSTEM>";
                $this->printExitResponse($response);
            }
            else if($result['status'] == NOK && $result['error_code'] == 20399){
                //ERROR RESPONSE - INTERNAL ERROR 399
                $date_response = date("d M Y H:i:s");
                $response = "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<VGSSYSTEM>";
                    $response .= "<REQUEST>";
                        $response .= "<USERID>{$user_id}</USERID>";
                        $response .= "<AMOUNT>{$amount}</AMOUNT>";
                        $response .= "<TRANSACTIONID>{$vivo_transaction_id}</TRANSACTIONID>";
                        $response .= "<TRNTYPE>{$transaction_type}</TRNTYPE>";
                        $response .= "<GAMEID>{$game_id}</GAMEID>";
                        $response .= "<ROUNDID>{$round_id}</ROUNDID>";
                        $response .= "<TRNDESCRIPTION>{$transaction_description}</TRNDESCRIPTION>";
                        $response .= "<ISROUNDFINISH>{$is_round_finished}</ISROUNDFINISH>";
                        $response .= "<HASH>{$hash}</HASH>";
                    $response .= "</REQUEST>";
                    $response .= "<TIME>{$date_response}</TIME>";
                    $response .= "<RESPONSE>";
                        $response .= "<RESULT>FAILED</RESULT>";
                        $response .= "<CODE>" . EnumErrorMessages::INTERNAL_ERROR . "</CODE>";
                    $response .= "</RESPONSE>";
                $response .= "</VGSSYSTEM>";
                $this->printExitResponse($response);
            }
            else if($result['status'] == NOK && $result['error_code'] == 20301){
                //ERROR RESPONSE - INTERNAL ERROR 399
                $date_response = date("d M Y H:i:s");
                $response = "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<VGSSYSTEM>";
                    $response .= "<REQUEST>";
                        $response .= "<USERID>{$user_id}</USERID>";
                        $response .= "<AMOUNT>{$amount}</AMOUNT>";
                        $response .= "<TRANSACTIONID>{$vivo_transaction_id}</TRANSACTIONID>";
                        $response .= "<TRNTYPE>{$transaction_type}</TRNTYPE>";
                        $response .= "<GAMEID>{$game_id}</GAMEID>";
                        $response .= "<ROUNDID>{$round_id}</ROUNDID>";
                        $response .= "<TRNDESCRIPTION>{$transaction_description}</TRNDESCRIPTION>";
                        $response .= "<ISROUNDFINISH>{$is_round_finished}</ISROUNDFINISH>";
                        $response .= "<HASH>{$hash}</HASH>";
                    $response .= "</REQUEST>";
                    $response .= "<TIME>{$date_response}</TIME>";
                    $response .= "<RESPONSE>";
                        $response .= "<RESULT>FAILED</RESULT>";
                        $response .= "<CODE>" . EnumErrorMessages::INTERNAL_ERROR . "</CODE>";
                    $response .= "</RESPONSE>";
                $response .= "</VGSSYSTEM>";
                $this->printExitResponse($response);
            }
            else if($result['status'] == NOK && $result['error_code'] == 20302){
                 //ERROR RESPONSE - INSUFFICIENT FUNDS 300
                $date_response = date("d M Y H:i:s");
                $response = "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<VGSSYSTEM>";
                    $response .= "<REQUEST>";
                        $response .= "<USERID>{$user_id}</USERID>";
                        $response .= "<AMOUNT>{$amount}</AMOUNT>";
                        $response .= "<TRANSACTIONID>{$vivo_transaction_id}</TRANSACTIONID>";
                        $response .= "<TRNTYPE>{$transaction_type}</TRNTYPE>";
                        $response .= "<GAMEID>{$game_id}</GAMEID>";
                        $response .= "<ROUNDID>{$round_id}</ROUNDID>";
                        $response .= "<TRNDESCRIPTION>{$transaction_description}</TRNDESCRIPTION>";
                        $response .= "<ISROUNDFINISH>{$is_round_finished}</ISROUNDFINISH>";
                        $response .= "<HASH>{$hash}</HASH>";
                    $response .= "</REQUEST>";
                    $response .= "<TIME>{$date_response}</TIME>";
                    $response .= "<RESPONSE>";
                        $response .= "<RESULT>FAILED</RESULT>";
                        $response .= "<CODE>" . EnumErrorMessages::INSUFFICIENT_FUNDS . "</CODE>";
                    $response .= "</RESPONSE>";
                $response .= "</VGSSYSTEM>";
                $this->printExitResponse($response);
            }
            else{
                //ERROR RESPONSE - INTERNAL ERROR 399
                $date_response = date("d M Y H:i:s");
                $response = "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<VGSSYSTEM>";
                    $response .= "<REQUEST>";
                        $response .= "<USERID>{$user_id}</USERID>";
                        $response .= "<AMOUNT>{$amount}</AMOUNT>";
                        $response .= "<TRANSACTIONID>{$vivo_transaction_id}</TRANSACTIONID>";
                        $response .= "<TRNTYPE>{$transaction_type}</TRNTYPE>";
                        $response .= "<GAMEID>{$game_id}</GAMEID>";
                        $response .= "<ROUNDID>{$round_id}</ROUNDID>";
                        $response .= "<TRNDESCRIPTION>{$transaction_description}</TRNDESCRIPTION>";
                        $response .= "<ISROUNDFINISH>{$is_round_finished}</ISROUNDFINISH>";
                        $response .= "<HASH>{$hash}</HASH>";
                    $response .= "</REQUEST>";
                    $response .= "<TIME>{$date_response}</TIME>";
                    $response .= "<RESPONSE>";
                        $response .= "<RESULT>FAILED</RESULT>";
                        $response .= "<CODE>" . EnumErrorMessages::INTERNAL_ERROR . "</CODE>";
                    $response .= "</RESPONSE>";
                $response .= "</VGSSYSTEM>";
                $this->printExitResponse($response);
            }
        }catch(Zend_Exception $ex){
            //ERROR RESPONSE - INTERNAL ERROR 399
            $date_response = date("d M Y H:i:s");
            $response = "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<VGSSYSTEM>";
                $response .= "<REQUEST>";
                    $response .= "<USERID>{$user_id}</USERID>";
                    $response .= "<AMOUNT>{$amount}</AMOUNT>";
                    $response .= "<TRANSACTIONID>{$vivo_transaction_id}</TRANSACTIONID>";
                    $response .= "<TRNTYPE>{$transaction_type}</TRNTYPE>";
                    $response .= "<GAMEID>{$game_id}</GAMEID>";
                    $response .= "<ROUNDID>{$round_id}</ROUNDID>";
                    $response .= "<TRNDESCRIPTION>{$transaction_description}</TRNDESCRIPTION>";
                    $response .= "<ISROUNDFINISH>{$is_round_finished}</ISROUNDFINISH>";
                    $response .= "<HASH>{$hash}</HASH>";
                $response .= "</REQUEST>";
                $response .= "<TIME>{$date_response}</TIME>";
                $response .= "<RESPONSE>";
                    $response .= "<RESULT>FAILED</RESULT>";
                    $response .= "<CODE>" . EnumErrorMessages::INTERNAL_ERROR . "</CODE>";
                $response .= "</RESPONSE>";
            $response .= "</VGSSYSTEM>";
            $this->printExitResponse($response);
        }
    }

    //http://192.168.3.63/onlinecasinoservice/vivo-gaming-integration/status?userID=12345&casinoTransactionID=12344546&hash=1f2bdcd90f13b00b40d502c530d5efd6
    public function statusAction(){
        $_REQUEST = array_change_key_case($_REQUEST, CASE_LOWER);
        $user_id = isset($_REQUEST['userid']) ? $this->urldecodeparams($_REQUEST['userid']) : '';
        $vivo_casino_transaction_id = isset($_REQUEST['casinotransactionid']) ? $this->urldecodeparams($_REQUEST['casinotransactionid']) : '';
        $hash = isset($_REQUEST['hash']) ? $this->urldecodeparams($_REQUEST['hash']) : '';
        /*
        $user_id = $this->getRequest()->getParam('userId', null);
        $casino_transaction_id = $this->getRequest()->getParam('casinoTransactionID', null);
        $hash = $this->getRequest()->getParam('hash', null);
        */

        $this->printInputResponse("VivoGamingIntegrationController::status(user_id = {$user_id}, vivo_casino_transaction_id = {$vivo_casino_transaction_id}, hash = {$hash})");

        $config = Zend_Registry::get('config');
        //test sent message for hash received and hash generated here
        $pass_key = $config->vivoGamingPassKey;
        $testHash = md5($user_id . $vivo_casino_transaction_id . $pass_key);
        if($testHash != $hash){
            $date_response = date("d M Y H:i:s");
			$response = "<?xml version='1.0' encoding='UTF-8'?>";
			$response .= "<VGSSYSTEM>";
			    $response .= "<REQUEST>";
                    $response .= "<USERID>{$user_id}</USERID>";
                    $response .= "<CASINOTRANSACTIONID>{$vivo_casino_transaction_id}</CASINOTRANSACTIONID>";
                    $response .= "<HASH>{$hash}</HASH>";
                $response .= "</REQUEST>";
                $response .= "<TIME>{$date_response}</TIME>";
                $response .= "<RESPONSE>";
                    $response .= "<RESULT>FAILED</RESULT>";
                    $response .= "<CODE>" . EnumErrorMessages::INVALID_HASH . "</CODE>";
                $response .= "</RESPONSE>";
			$response .= "</VGSSYSTEM>";
            $this->printExitResponse($response);
        }
        try{
            $modelVivoGamingIntegration = new VivoGamingIntegrationModel();
            $result = $modelVivoGamingIntegration->checkTransactionStatus($user_id, $vivo_casino_transaction_id);
            if($result['status'] == OK){
                if($result['status_out'] == "1") {
                    $ec_system_transaction_id = $result['ec_system_transaction_id'];
                    //CORRECT RESPONSE
                    $date_response = date("d M Y H:i:s");
                    $response = "<?xml version='1.0' encoding='UTF-8'?>";
                    $response .= "<VGSSYSTEM>";
                        $response .= "<REQUEST>";
                            $response .= "<USERID>{$user_id}</USERID>";
                            $response .= "<CASINOTRANSACTIONID>{$vivo_casino_transaction_id}</CASINOTRANSACTIONID>";
                            $response .= "<HASH>{$hash}</HASH>";
                        $response .= "</REQUEST>";
                        $response .= "<TIME>{$date_response}</TIME>";
                        $response .= "<RESPONSE>";
                            $response .= "<RESULT>OK</RESULT>";
                            $response .= "<ECSYSTEMTRANSACTIONID>{$ec_system_transaction_id}</ECSYSTEMTRANSACTIONID>";
                        $response .= "</RESPONSE>";
                    $response .= "</VGSSYSTEM>";
                    $this->printExitResponse($response);
                }else if($result['status_out'] == "-1"){
                    //ERROR RESPONSE - UNKNOWN_TRANSACTION_ID 302
                    $date_response = date("d M Y H:i:s");
                    $response = "<?xml version='1.0' encoding='UTF-8'?>";
                    $response .= "<VGSSYSTEM>";
                        $response .= "<REQUEST>";
                            $response .= "<USERID>{$user_id}</USERID>";
                            $response .= "<CASINOTRANSACTIONID>{$vivo_casino_transaction_id}</CASINOTRANSACTIONID>";
                            $response .= "<HASH>{$hash}</HASH>";
                        $response .= "</REQUEST>";
                        $response .= "<TIME>{$date_response}</TIME>";
                        $response .= "<RESPONSE>";
                            $response .= "<RESULT>FAILED</RESULT>";
                            $response .= "<CODE>" . EnumErrorMessages::UNKNOWN_TRANSACTION_ID_FOR_STATUS_API . "</CODE>";
                        $response .= "</RESPONSE>";
                    $response .= "</VGSSYSTEM>";
                    $this->printExitResponse($response);
                }else{
                    //ERROR RESPONSE - INTERNAL ERROR 399
                    $date_response = date("d M Y H:i:s");
                    $response = "<?xml version='1.0' encoding='UTF-8'?>";
                    $response .= "<VGSSYSTEM>";
                        $response .= "<REQUEST>";
                            $response .= "<USERID>{$user_id}</USERID>";
                            $response .= "<CASINOTRANSACTIONID>{$vivo_casino_transaction_id}</CASINOTRANSACTIONID>";
                            $response .= "<HASH>{$hash}</HASH>";
                        $response .= "</REQUEST>";
                        $response .= "<TIME>{$date_response}</TIME>";
                        $response .= "<RESPONSE>";
                            $response .= "<RESULT>FAILED</RESULT>";
                            $response .= "<CODE>" . EnumErrorMessages::INTERNAL_ERROR . "</CODE>";
                        $response .= "</RESPONSE>";
                    $response .= "</VGSSYSTEM>";
                    $this->printExitResponse($response);
                }
            }else{
                //ERROR RESPONSE - INTERNAL ERROR 399
                $date_response = date("d M Y H:i:s");
                $response = "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<VGSSYSTEM>";
                    $response .= "<REQUEST>";
                        $response .= "<USERID>{$user_id}</USERID>";
                        $response .= "<CASINOTRANSACTIONID>{$vivo_casino_transaction_id}</CASINOTRANSACTIONID>";
                        $response .= "<HASH>{$hash}</HASH>";
                    $response .= "</REQUEST>";
                    $response .= "<TIME>{$date_response}</TIME>";
                    $response .= "<RESPONSE>";
                        $response .= "<RESULT>FAILED</RESULT>";
                        $response .= "<CODE>" . EnumErrorMessages::INTERNAL_ERROR . "</CODE>";
                    $response .= "</RESPONSE>";
                $response .= "</VGSSYSTEM>";
                $this->printExitResponse($response);
            }
        }catch(Zend_Exception $ex){
            //ERROR RESPONSE - INTERNAL ERROR 399
            $date_response = date("d M Y H:i:s");
            $response = "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<VGSSYSTEM>";
                $response .= "<REQUEST>";
                    $response .= "<USERID>{$user_id}</USERID>";
                    $response .= "<CASINOTRANSACTIONID>{$vivo_casino_transaction_id}</CASINOTRANSACTIONID>";
                    $response .= "<HASH>{$hash}</HASH>";
                $response .= "</REQUEST>";
                $response .= "<TIME>{$date_response}</TIME>";
                $response .= "<RESPONSE>";
                    $response .= "<RESULT>FAILED</RESULT>";
                    $response .= "<CODE>" . EnumErrorMessages::INTERNAL_ERROR . "</CODE>";
                $response .= "</RESPONSE>";
            $response .= "</VGSSYSTEM>";
            $this->printExitResponse($response);
        }
    }

    //http://192.168.3.63/onlinecasinoservice/vivo-gaming-integration/get-balance?userID=12345&hash=109889f941630d269546335f728f3558
    public function getBalanceAction(){
        $_REQUEST = array_change_key_case($_REQUEST, CASE_LOWER);
        $user_id = isset($_REQUEST['userid']) ? $this->urldecodeparams($_REQUEST['userid']) : '';
        $hash = isset($_REQUEST['hash']) ? $this->urldecodeparams($_REQUEST['hash']) : '';
        /*
        $user_id = $this->getRequest()->getParam('userId', null);
        $hash = $this->getRequest()->getParam('hash', null);
        */

        $this->printInputResponse("VivoGamingIntegrationController::getBalance(user_id = {$user_id}, hash = {$hash})");

        $config = Zend_Registry::get('config');
        //test sent message for hash received and hash generated here
        $pass_key = $config->vivoGamingPassKey;
        $testHash = md5($user_id . $pass_key);
        if($testHash != $hash){
            $date_response = date("d M Y H:i:s");
			$response = "<?xml version='1.0' encoding='UTF-8'?>";
			$response .= "<VGSSYSTEM>";
			    $response .= "<REQUEST>";
                    $response .= "<USERID>{$user_id}</USERID>";
                    $response .= "<HASH>{$hash}</HASH>";
                $response .= "</REQUEST>";
                $response .= "<TIME>{$date_response}</TIME>";
                $response .= "<RESPONSE>";
                    $response .= "<RESULT>FAILED</RESULT>";
                    $response .= "<CODE>" . EnumErrorMessages::INVALID_HASH . "</CODE>";
                $response .= "</RESPONSE>";
			$response .= "</VGSSYSTEM>";
            $this->printExitResponse($response);
        }

        try{

            $modelVivoGamingIntegration = new VivoGamingIntegrationModel();
            $result = $modelVivoGamingIntegration->getBalance($user_id);

            if($result['status'] == OK){
                $balance = NumberHelper::format_english_double($result['credits']);
                //CORRECT RESPONSE
                $date_response = date("d M Y H:i:s");
                $response = "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<VGSSYSTEM>";
                    $response .= "<REQUEST>";
                        $response .= "<USERID>{$user_id}</USERID>";
                        $response .= "<HASH>{$hash}</HASH>";
                    $response .= "</REQUEST>";
                    $response .= "<TIME>{$date_response}</TIME>";
                    $response .= "<RESPONSE>";
                        $response .= "<RESULT>OK</RESULT>";
                        $response .= "<BALANCE>{$balance}</BALANCE>";
                    $response .= "</RESPONSE>";
                $response .= "</VGSSYSTEM>";
                $this->printExitResponse($response);
            }else{
                //ERROR RESPONSE - INTERNAL ERROR 399
                $date_response = date("d M Y H:i:s");
                $response = "<?xml version='1.0' encoding='UTF-8'?>";
                $response .= "<VGSSYSTEM>";
                    $response .= "<REQUEST>";
                        $response .= "<USERID>{$user_id}</USERID>";
                        $response .= "<HASH>{$hash}</HASH>";
                    $response .= "</REQUEST>";
                    $response .= "<TIME>{$date_response}</TIME>";
                    $response .= "<RESPONSE>";
                        $response .= "<RESULT>FAILED</RESULT>";
                        $response .= "<CODE>" . EnumErrorMessages::UNKNOWN_TRANSACTION_ID_FOR_STATUS_API . "</CODE>";
                    $response .= "</RESPONSE>";
                $response .= "</VGSSYSTEM>";
                $this->printExitResponse($response);
            }
        }catch(Zend_Exception $ex){
            //ERROR RESPONSE - INTERNAL ERROR 399
            $date_response = date("d M Y H:i:s");
            $response = "<?xml version='1.0' encoding='UTF-8'?>";
            $response .= "<VGSSYSTEM>";
                $response .= "<REQUEST>";
                    $response .= "<USERID>{$user_id}</USERID>";
                    $response .= "<HASH>{$hash}</HASH>";
                $response .= "</REQUEST>";
                $response .= "<TIME>{$date_response}</TIME>";
                $response .= "<RESPONSE>";
                    $response .= "<RESULT>FAILED</RESULT>";
                    $response .= "<CODE>" . EnumErrorMessages::INTERNAL_ERROR . "</CODE>";
                $response .= "</RESPONSE>";
            $response .= "</VGSSYSTEM>";
            $this->printExitResponse($response);
        }
    }

    public function loginAndGenerateTokenAction(){
        $username = $this->getRequest()->getParam('username', null);
        $password = $this->getRequest()->getParam('password', null);
        $version = 1;
        $ip_address = IPHelper::getRealIPAddress();
        require_once MODELS_DIR . DS . 'WebSiteModel.php';
        $modelWebSite = new WebSiteModel();
        $resultSession = $modelWebSite->siteLogin("Casino400.com", $username, $password, "", "", $ip_address, "", "", "", "");

        $session_id = $resultSession['pc_session_id'];
        $pc_session_id = $resultSession['pc_session_id'];
        $site_session_id = $resultSession['site_session_id'];

        $arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
        $player_id = $arrPlayer['player_id'];

        $modelVivoGamingIntegration = new VivoGamingIntegrationModel();
        $result = $modelVivoGamingIntegration->getBalance($player_id);

        $resultToken = array();
        $session_id = "";
        $credits = "";
        $currency = "";
        $player_id = "";
        $game_id = 2012;

        if($resultSession['status'] == OK){
            $session_id = $resultSession['pc_session_id'];
            $credits = $result['credits'];
            $currency = $resultSession['currency'];
            $player_id = $resultSession['player_id'];

            if($session_id != "-1") {
                require_once MODELS_DIR . DS . 'VivoGamingIntegrationModel.php';
                $modelVivoGamingIntegration = new VivoGamingIntegrationModel();
                $resultToken = $modelVivoGamingIntegration->getVivoGamingIntegrationToken($session_id, $player_id, $credits, $game_id);
            }else{
                $resultToken = array(
                    "token"=>""
                );
            }

            exit (json_encode(array(
                "session_id"=>$session_id,
                "credits"=>$credits,
                "currency"=>$currency,
                "player_id"=>$player_id,
                "token"=>$resultToken['token'],
            )));
        }
    }

    public function testAction(){
      header('Access-Control-Allow-Origin: *');
      header('Content-Type: application/json');
      exit (json_encode(array(
          "success"=>true
      )));
    }

    public function indexAction(){
      header('Access-Control-Allow-Origin: *');
      header('Content-Type: application/json');
      exit (json_encode(array(
          "success"=>true
      )));
    }


}
