<?php
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'AuthorizationModel.php';
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'GameModel.php';
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'SessionModel.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once 'ErrorConstants.php';

/**
 * 
 * Performes authorization process through web service
 *
 */
class AuthorizationManager {
	
	/**
	* @return mixed
	*/
	public static function test(){
		$json_message = Zend_Json::encode(array("status"=>OK));
        exit($json_message);
	}

    /**
     * @return mixed
     */
    public static function testIpAddress(){
        $ip_address = IPHelper::getRealIPAddress();
        $message = "AuthorizationManager::testIpAddress() <br /> Detected IP Address = {$ip_address}";
        $errorHelper = new ErrorHelper();
        $errorHelper->serviceAccess($message, $message);

        $json_message = Zend_Json::encode(array("status"=>OK));
        exit($json_message);
    }

    /**
     * @param $web_site_session_id
     * @param $version
     * @param $ipaddress
     * @return mixed|string
     * @throws Zend_Exception
     */
	public static function loginWithWebSite($web_site_session_id, $version, $ipaddress){
		if(!isset($web_site_session_id)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
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
		$web_site_session_id = strip_tags($web_site_session_id);
		$version = strip_tags($version);
		$ip_address = strip_tags($ipaddress);
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
        //test if ip address not sent by game client then autodetect it and sent autodetected ip address
        if(strlen(trim($ip_address)) == 0){
            $ip_address = IPHelper::getRealIPAddress();
        }
		require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'WebSiteModel.php';
		$modelWebSite = new WebSiteModel();
		$arrData = $modelWebSite->sessionIdToPlayerId((int)$web_site_session_id);
		if($arrData == false){
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
		$username = $arrData['player_name'];
		$password = $arrData['player_password'];
		$mac_address = null;
		$gctype = PLAYER;
		return self::login($username, $password, "", $mac_address, $version, $ip_address, $gctype);
	}	
	
	/**
	 * 
	 * Opens terminal session - old for terminals and players without barcode
	 * Returns list of games and parameters
	 * Game (param1, param2, ... paramN) and so on
	 * @param string $username
	 * @param string $password
	 * @param string $barcode
	 * @param string $mac_address
	 * @param string $version
	 * @param string $ipaddress
	 * @param string $gctype
	 * @param string $device_aff_id
	 * @param string $gp_mac_address
	 * @param string $registred_aff
	 * @return mixed
	 */
	public static function loginOld($username, $password, $barcode, $mac_address, $version, $ipaddress, $gctype,
	$device_aff_id, $gp_mac_address, $registred_aff = ""){
        if(!isset($username) || !isset($password)){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
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
        $username = trim(strip_tags($username));
        $password = trim(strip_tags($password));
        $mac_address = trim(strip_tags($mac_address));
        $version = trim(strip_tags($version));
        $ip_address = trim(strip_tags($ipaddress));
        $gctype = trim(strip_tags($gctype));
        $device_aff_id = trim(strip_tags($device_aff_id));
        $gp_mac_address = trim(strip_tags($gp_mac_address));
        $registred_aff = trim(strip_tags($registred_aff));
        $country = "";
        $city = "";
        try{
			if(strlen($device_aff_id) == 0 || $device_aff_id == " "){
				$device_aff_id = null;
			}
			if(strlen($gp_mac_address) == 0 || $gp_mac_address == " "){
				$gp_mac_address = null;
			}
			if(!isset($registred_aff) || strlen($registred_aff) == 0 || $registred_aff == " "){
				$registred_aff = null;
			}
            $modelSession = new SessionModel();
            $modelSession->resetPackages();
            $modelAuthorization = new AuthorizationModel();
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
            //test if ip address not sent by game client then autodetect it and sent autodetected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
			//DEBUG THIS PART OF CODE
			/*
				$errorHelper = new ErrorHelper();
				$message = "login (username = $username, password = $password, mac_address = $mac_address, version = $version, ip_address = $ip_address, gc_type = $gctype, device_aff_id = $device_aff_id,
					gp_mac_address = $gp_mac_address, registred_aff = $registred_aff";
				$errorHelper->sendMail($message);
				$errorHelper->serviceAccessLog($message);
			*/
			//if game client is player type then send username insted of mac address
			$status = NO;
			if($gctype == PLAYER){
				$resArr = $modelAuthorization->checkAffiliateForTerminal($username, $gctype);
				//DEBUG THIS PART OF CODE
				/*
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("Poziv za playera managment_core.m checkAffiliateForTerminal: username= " . $username . " gctype= " . $gctype);
				$errorHelper->sendMail("Poziv za playera managment_core.m checkAffiliateForTerminal: username= " . $username . " gctype= " . $gctype);
				*/
			}
			//if game client is terminal type then send mac address
			else{
				$resArr = $modelAuthorization->checkAffiliateForTerminal($mac_address, $gctype);
				//DEBUG THIS PART OF CODE
				/*
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("Poziv za terminala managment_core.m checkAffiliateForTerminal: mac address= " . $username . " gctype= " . $gctype);
				$errorHelper->sendMail("Poziv za terminala managment_core.m checkAffiliateForTerminal: mac address= " . $username . " gctype= " . $gctype);
				*/
			}
			// returns if access is banned
			// 1 - affiliate is banned -1 - affiliate is self banned
			$status = $resArr[0];
			//returns reason as number why is banned (if banned)
			$reason = $resArr[1];
			//DEBUG THIS PART OF CODE
			/*
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceAccessLog("Player banned status : " . $status);
			$errorHelper->sendMail("Player banned status : " . $status);
			*/
			//this terminal's affiliate is banned don't open terminal session
			if($status == YES && $reason == 1){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$AFFILIATE_BANNED);
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
			//this terminal's affiliate is self banned don't open terminal session
			if($status == YES && $reason == -1){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$PLAYER_SELF_BANNED);
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
            //DEBUG THIS PART OF CODE
            /*
            $errorHelper = new ErrorHelper();
            $errorHelper->serviceAccessLog("User is trying to open terminal session! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ip_address . " Country: " . $country . " City: " . $city . " Device Aff Id: " . $device_aff_id . " General Purpose Mac Address: " . $gp_mac_address);
            $errorHelper->sendMail("User is trying to open terminal session! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ip_address . " Country: " . $country . " City: " . $city . " Device Aff Id: " . $device_aff_id . " General Purpose Mac Address: " . $gp_mac_address);
            */
            $config = Zend_Registry::get('config');
            $res = $modelAuthorization->openTerminalSession($username, $password, $mac_address, $version, $ip_address, $country, $city, $device_aff_id, $gp_mac_address, $registred_aff);
            //DEBUG THIS PART OF CODE
            /*
            $errorHelper = new ErrorHelper();
            $errorHelper->serviceAccessLog("User is trying to open terminal session! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ip_address . " Country: " . $country . " City: " . $city . " Device Aff Id: " . $device_aff_id . " General Purpose Mac Address: " . $gp_mac_address . "Result = " . $res);
            $errorHelper->sendMail("User is trying to open terminal session! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ip_address . " Country: " . $country . " City: " . $city . " Device Aff Id: " . $device_aff_id . " General Purpose Mac Address: " . $gp_mac_address . "Result = " . $res);
            */
            if($res == INTERNAL_ERROR){
                $detected_ip_address = IPHelper::getRealIPAddress();
                $message = "TerminalIntegration\AuthorizationManager::loginOld (username = {$username}, password=, barcode = {$barcode}, mac_address = {$mac_address}, version = {$version}, ipaddress = {$ip_address}, gctype = {$gctype}, device_aff_id = {$device_aff_id}, gp_mac_address={$gp_mac_address}, registred_aff={$registred_aff}) <br /> Detected IP Address: {$detected_ip_address} <br /> Open terminal session procedure in database failed.";
                $errorHelper = new ErrorHelper();
                $errorHelper->serviceError($message, $message);

                $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
            if($res == WRONG_USERNAME_PASSWORD){
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$WRONG_USERNAME_OR_PASSWORD);
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
            if($res == WRONG_PHYSICAL_ADDRESS){
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$WRONG_PHYSICAL_ADDRESS);
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
            if($res == PLAYER_BANNED_LIMIT){
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$PLAYER_BANNED_LIMIT);
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
            if($res == LOGIN_TOO_MANY_TIMES){
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$LOGIN_TOO_MANY_TIMES);
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
            $modelSession->resetPackages();
            //returns enabled games for this user
            $res1 = array();
            $gamesArr = array();
            //if has session valid
            if(!is_null($res['session_id'])){
                $i = 0;

                foreach ($res['list_games'] as $g) {
                    // Za sada nemam jackpot ali cemo i to kasnije dodati u storku tj. bice upisan u kursor za game order.
                    $gamesArr[$i++] = //new GameGroupOrder($g['game_group'], $g['group_order'], $g['game'], $g['game_id'], $g['page'], $g['game_order'], null);
                    array(
                        "game_group" => $g['game_group'],
                        "group_order" => $g['group_order'],
                        "game" => $g["game"],
                        "game_id" => $g['game_id'],
                        "page" => $g['page'],
                        "game_order" => $g['game_order'],
                        "pot_name" => null
                    );
                }
            }else {
                $detected_ip_address = IPHelper::getRealIPAddress();
                $message = "TerminalIntegration\AuthorizationManager::loginOld (username = {$username}, password=, barcode = {$barcode}, mac_address = {$mac_address}, version = {$version}, ipaddress = {$ip_address}, gctype = {$gctype}, device_aff_id = {$device_aff_id}, gp_mac_address={$gp_mac_address}, registred_aff={$registred_aff}) <br /> Detected IP Address: {$detected_ip_address} <br /> Result while user was logging in is null, session id is null.";
                $errorHelper = new ErrorHelper();
                $errorHelper->serviceError($message, $message);

                $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
            //check if any invalid results from database if status is not returned
            if($res['session_id'] == "123456789" || $res['credits'] == "123456789" || $res['currency'] == "123456789"){
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>NOK,
                        "error_message"=> $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }else{
                //if all results are in place and player has successfully loged in
                //DEBUG THIS PART OF CODE
                /*
                $errorHelper = new ErrorHelper();
                $errorHelper->serviceAccessLog("User is successfully logged in! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ip_address . " Country: " . $country . " City: " . $city);
                $errorHelper->sendMail("User is successfully logged in! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ip_address . " Country: " . $country . " City: " . $city);
                */
                if(count($gamesArr)==0){
                    $detected_ip_address = IPHelper::getRealIPAddress();
                    $message = "TerminalIntegration\AuthorizationManager::loginOld(username = {$username}, password=, barcode = {$barcode}, mac_address = {$mac_address}, version = {$version}, ipaddress = {$ip_address}, gctype = {$gctype}, device_aff_id = {$device_aff_id}, gp_mac_address={$gp_mac_address}, registred_aff={$registred_aff}) <br /> Detected IP Address: {$detected_ip_address} <br /> Error: Array of games and parameters is empty.";
                    $errorHelper = new ErrorHelper();
                    $errorHelper->serviceError($message, $message);

                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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

                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                        "session_id"=> $res['session_id'],
                        "games" => $gamesArr,
                        "credits" => $res['credits'],
                        "currency" => $res['currency'],
                        "page_number" => $res['one_page'],
                        "terminal_status" => $res['device'],
                        "player_id" => $res['player_id'],
                        "username" => $res['username'],
                    )
                );
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::loginOld(username = $username, password =, barcode={$barcode}, mac_address = {$mac_address}, version = {$version}, ip_address = {$ip_address}, gameClientType = {$gctype},
	            DeviceAffiliateID: {$device_aff_id}, GeneralPurposeMacAddress = {$gp_mac_address}, Registred affiliate = {$registred_aff}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
	 * 
	 * Opens terminal session, membership card login and expend credits from member card identified with barcode number
	 * Returns list of games and parameters
	 * Game (param1, param2, ... paramN) and so on
	 * @param string $username
	 * @param string $password
	 * @param string $barcode
	 * @param string $mac_address
	 * @param string $version
	 * @param string $ipaddress
	 * @param string $gctype
	 * @param string $device_aff_id
	 * @param string $gp_mac_address
	 * @param string $registred_aff
	 * @return mixed
	 */
	public static function login($username, $password, $barcode, $mac_address, $version, $ipaddress, $gctype, $device_aff_id = "", $gp_mac_address = "", $registred_aff = ""){
        if(!isset($username) || !isset($password)){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
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
        $username = trim(strip_tags($username));
        $password = trim(strip_tags($password));
        $barcode = trim(strip_tags($barcode));
        $mac_address = trim(strip_tags($mac_address));
        $version = trim(strip_tags($version));
        $ip_address = trim(strip_tags($ipaddress));
        $gctype = trim(strip_tags($gctype));
        $device_aff_id = trim(strip_tags($device_aff_id));
        $gp_mac_address = trim(strip_tags($gp_mac_address));
        $registred_aff = trim(strip_tags($registred_aff));
        $country = "";
        $city = "";
        try{
			$modelSession = new SessionModel();
			$modelSession->resetPackages();
			$modelAuthorization = new AuthorizationModel();
			if(strlen($device_aff_id) == 0 || $device_aff_id == " "){
				$device_aff_id = null;
			}
			if(strlen($gp_mac_address) == 0 || $gp_mac_address == " "){
				$gp_mac_address = null;
			}
			if(!isset($registred_aff) || strlen($registred_aff) == 0 || $registred_aff == " "){
				$registred_aff = null;
			}
			if(!isset($barcode) || strlen($barcode) == 0 || $barcode == " "){
				$barcode = null;
			}
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
			//DEBUG THIS PART OF CODE
			/*
				$errorHelper = new ErrorHelper();
				$message = "login (username = $username, password = $password, barcode = $barcode, mac_address = $mac_address, version = $version, ip_address = $ip_address, gc_type = $gctype, device_aff_id = $device_aff_id,
					gp_mac_address = $gp_mac_address, registred_aff = $registred_aff)";
				$errorHelper->sendMail($message);
				$errorHelper->serviceAccessLog($message);
			*/
			//if game client is player type then send username insted of mac address
			$status = NO;
			if($gctype == PLAYER){
				$resArr = $modelAuthorization->checkAffiliateForTerminal($username, $gctype);
				//DEBUG THIS PART OF CODE
				/*
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("Poziv za playera managment_core.m checkAffiliateForTerminal: username= " . $username . " gctype= " . $gctype);
				$errorHelper->sendMail("Poziv za playera managment_core.m checkAffiliateForTerminal: username= " . $username . " gctype= " . $gctype);
				*/
			}
			//if game client is terminal type then send mac address
			else{
				$resArr = $modelAuthorization->checkAffiliateForTerminal($mac_address, $gctype);
				//DEBUG THIS PART OF CODE
				/*
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("Poziv za terminala managment_core.m checkAffiliateForTerminal: mac address= " . $username . " gctype= " . $gctype);
				$errorHelper->sendMail("Poziv za terminala managment_core.m checkAffiliateForTerminal: mac address= " . $username . " gctype= " . $gctype);
				*/
			}
			// returns if access is banned
			// 1 - affiliate is banned -1 - affiliate is self banned
			$status = $resArr[0];
			//returns reason as number why is banned (if banned)
			$reason = $resArr[1];
			//DEBUG THIS PART OF CODE
			/*
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceAccessLog("Player banned status : " . $status);
			$errorHelper->sendMail("Player banned status : " . $status);
			*/
			//this terminal's affiliate is banned don't open terminal session
			if($status == YES && $reason == 1){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$AFFILIATE_BANNED);
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
			//this terminal's affiliate is self banned don't open terminal session
			if($status == YES && $reason == -1){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$PLAYER_SELF_BANNED);
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

            //DEBUG THIS PART OF CODE
            /*
            $errorHelper = new ErrorHelper();
            $errorHelper->serviceAccessLog("User is trying to open terminal session! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ip_address . " Country: " . $country . " City: " . $city . " Device Aff Id: " . $device_aff_id . " General Purpose Mac Address: " . $gp_mac_address);
            $errorHelper->sendMail("User is trying to open terminal session! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ip_address . " Country: " . $country . " City: " . $city . " Device Aff Id: " . $device_aff_id . " General Purpose Mac Address: " . $gp_mac_address);
            */
            $res = self::memberCardLogin($username, $password, $barcode, $ip_address, $version, $mac_address, $gp_mac_address, $device_aff_id);
            return $res;
            //check if any invalid results from database if status is not returned
            /*if($res['session_id'] == "123456789" || $res['credits'] == "123456789" || $res['currency'] == "123456789"){
                return null;
            }else{
                //if all results are in place and player has successfully loged in
                //DEBUG THIS PART OF CODE

                $errorHelper = new ErrorHelper();
                $errorHelper->serviceAccessLog("User is successfully logged in! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ip_address . " Country: " . $country . " City: " . $city);
                $errorHelper->sendMail("User is successfully logged in! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ip_address . " Country: " . $country . " City: " . $city);

                if(count($gamesArr)==0){
                    $message = "AuthorizationManager::login (username = {$username} password= barcode = {$barcode} mac_address = {$mac_address} version = {$version} ipaddress = {$ip_address} gctype = {$gctype} device_aff_id = {$device_aff_id}, gp_mac_address={$gp_mac_address}, registred_aff={$registred_aff} ) <br /> call error: Array of games and parameters is empty.";
                    $errorHelper = new ErrorHelper();
                    $errorHelper->serviceError($message, $message);
                    return INTERNAL_ERROR;
                }
                //terminal status - kandidat, rejected, accepted
                //subject status - role of player or cashier that is loging in
                $result = array("session"=>$res['session_id'], "games"=>$gamesArr, "credits"=>$res['credits'],	"currency"=>$res['currency'], "page_no"=>$res1['one_page'], "terminal_status"=>$res['device'], "player_id"=>$res['player_id'], "subject_status"=>$res['subject_status']);
                return $result;
            }*/
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$message = "TerminalIntegration\AuthorizationManager::login(username = {$username}, password =, Barcode = {$barcode}, Mac Address = {$mac_address}, Version = {$version}, IP Address = {$ip_address}, Game Client = {$gctype},
	            Device Affiliate ID = {$device_aff_id}, General Purpose Mac Address = {$gp_mac_address}, Registred Affiliate = {$registred_aff} <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
	 * 
	 * Opens player session for players coming from external client integration to our system
	 * Returns list of games and parameters
	 * Game (param1, param2, ... paramN) and so on
	 * @param string $token
	 * @param string $ip_address
	 * @return mixed
	 */
	public static function loginExternalIntegration($token, $ip_address){
		try{
			if(!isset($token) || !isset($ip_address)){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
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
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
            //test if ip address not sent by game client then autodetect it and sent autodetected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
			$modelSession = new SessionModel();
			$modelSession->resetPackages();
			$modelAuthorization = new AuthorizationModel();
			//DEBUG THIS PART OF CODE
			/*
				$errorHelper = new ErrorHelper();
				$message = "loginExternalIntegration (token = {$token}, ip_address = {$ip_address})";
				$errorHelper->sendMail($message);
				$errorHelper->serviceAccessLog($message);
			*/
			$res = $modelAuthorization->loginPlayerByToken($token, NO, $ip_address);
			if($res['status'] == OK){
				if(strlen($res['message']) != 0){
					$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                    $json_message = Zend_Json::encode(
                        array(
                            "status"=>NOK,
                            "error_message"=> $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
				}else{
					//return complete result to game client
					$list_games = array();
					foreach($res['list_games'] as $game){
						$list_games[] = array(
							"game_group"=>$game['game_group'],
							"group_order"=>$game['group_order'],
							"game"=>$game['game'],
							"game_id"=>$game['game_id'],
							"page"=>$game['page'],
							"game_order"=>$game['game_order']
						);
					}

					$json_message = Zend_Json::encode(
                        array(
                            "status"=>OK,
                            "token"=> $res['token'],
                            "ip_address" => $res['ip_address'],
                            "session_id" => $res['session_id'],
                            "player_id" => $res['player_id'],
                            "credits" => $res['credits'],
                            "currency" => $res['currency'],
                            "games" => $list_games
                        )
                    );
                    exit($json_message);
				}
			}else{
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$message = "TerminalIntegration\AuthorizationManager::loginExternalIntegration(token = {$token}, ip_address = {$ip_address}) <br /> Detected IP address = {$detected_ip_address} <br /> Exception message: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
	 * 
	 * logout user from system
	 * @param int $session_id
	 * @return mixed
	 */
	public static function logout($session_id){
		try{
			if(!isset($session_id)){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
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
			$session_id = strip_tags($session_id);
			$modelSession = new SessionModel();
			$modelSession->resetPackages();
			$modelAuthorization = new AuthorizationModel();
			$modelAuthorization->closeTerminalSession($session_id);
			unset($modelSession);
			unset($modelAuthorization);

			$json_message = Zend_Json::encode(
                array(
                    "status"=>OK,
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			/*if there was unknown exception type*/
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
			$message = "TerminalIntegration\AuthorizationManager::logout(session_id = {$session_id}) <br /> Detected IP address = {$detected_ip_address} <br /> Exception Error: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
	 * 
	 * close fictive bo session
	 * @param int $session_id
	 * @return mixed
	 */
	public static function logoutBo($session_id){
		if(!isset($session_id)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
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
		$session_id = strip_tags($session_id);
		try{
			$modelAuthorization = new AuthorizationModel();
			$modelAuthorization->closeBoSession($session_id);
			unset($modelAuthorization);

			$json_message = Zend_Json::encode(
                array(
                    "status"=>OK
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			/*if there was unknown exception type*/
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::logoutBo(session_id = {$session_id}) <br /> Detected IP address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceErrorLog($message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
	 * 
	 * returns list of games on system before there logging user
	 * @param string $gctype
	 * @param string $mac_address
	 * @return mixed
	 */
	public static function games($gctype, $mac_address){
		try{			
			if(!isset($gctype)){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
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
			$gctype = strip_tags($gctype);
			$mac_address = strip_tags($mac_address);
			$modelSession = new SessionModel();
			$modelSession->resetPackages();
			unset($modelSession);
			$modelGames = new GameModel();
			$res = $modelGames->getGames(ENABLED, DISABLED, $gctype, $mac_address);
			unset($modelGames);
			$res2 = $res;
			$gamesArr = array();
			/*
			$parameters = array();
			$parametersGameOnOff = array();
			foreach($res['list_games'] as $g){
				if($g['p_name'] == GAME_ON_OFF){ //gomila parametre game on off u posebno
					//$parametersGameOnOff[$g['id']] = new Parameter($g['p_name'], $g['p_value']);
			        $parametersGameOnOff[$g['id']] = array(
			            "name" => $g['p_name'],
			            "value" => $g['p_value']
			        );
				}else{
					//$parameter = new Parameter($g['p_name'], $g['p_value']);
			        $parameter = array(
			            "name" => $g['p_name'],
			            "value" => $g['p_value']
			        );
					$parameters[$g['id']][] = $parameter;
					unset($parameter);
				}
			}
			foreach($res2['list_games'] as $g){
				if(isset($parametersGameOnOff[$g['id']])){
					//$g['game_on_off'] = $parametersGameOnOff[$g['id']]->value;
			        $g['game_on_off'] = $parametersGameOnOff[$g['id']]['value'];
				}
				else{
					$g['game_on_off'] = null;
				}				
				$named_pot = "";
				foreach($res['list_pots'] as $pot){
					if($pot['id'] == $g['id']){
						$named_pot = $pot['p_name'];
						break;
					}
				}
				if($g['name'] != "dummy"){
					//$gamesArr[$g['id']] = new Game($g['id'], $g['name'], $g['status'], $g['game_on_off'], $parameters[$g['id']], $named_pot);
			        $gamesArr[$g['id']] = array(
			            "id" => $g['id'],
                        "name" => $g['name'],
                        "status" => $g['status'],
			            "game_on_off" => $g['game_on_off'],
                        "parameters" => $parameters[g['id']],
                        "pot_name" => $named_pot
			        );
				}
			}
			*/
			$i = 0;
			
			foreach ($res2['list_games'] as $g) {
			    // Za sada nemam jackpot ali cemo i to kasnije dodati u storku tj. bice upisan u kursor za game order.
			    $gamesArr[$i++] = //new GameGroupOrder($g['game_group'], $g['group_order'], $g['game'], $g['game_id'], $g['page'], $g['game_order'], null);
                    array(
                        "game_group" => $g['game_group'],
                        "group_order" => $g['group_order'],
                        "game" => $g["game"],
                        "game_id" => $g['game_id'],
                        "page" => $g['page'],
                        "game_order" => $g['game_order'],
                        "pot_name" => null
                    );
			}
			
			if(count($gamesArr) == 0){
				//return INTERNAL_ERROR;
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOT_DEFINED_GAMES_OR_PARAMETERS);
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

			$json_message = Zend_Json::encode(
                array(
                    "status"=>OK,
                    "games"=> $gamesArr,
                    "terminal_type" => $res['terminal_type'],
                    "skin" => $res['skin'],
                    "key_exit" => $res['key_exit'],
                    "enter_password" => $res['enter_password'],
                    "mouse_on_off" => $res['mouse_on_off'],
                    "one_page" => $res['one_page'],
                    "port" => $res['port'],
                    "general_purpose" => $res['general_purpose'],
                    "affiliate_id" => $res['affiliate_id']
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			$code = $ex->getCode();
			if($code != "20122"){ 
				// if it is unknown error
				$errorHelper = new ErrorHelper();
                $detected_ip_address = IPHelper::getRealIPAddress();
				$message = "TerminalIntegration\AuthorizationManager::games(Game Client type={$gctype}, Mac address = {$mac_address}) <br /> Detected IP Address: {$detected_ip_address} <br /> Exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);

				$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
			if($code == "20122"){ 
				//if user is using unknown mac physical address				
				$errorHelper = new ErrorHelper();
                $detected_ip_address = IPHelper::getRealIPAddress();
				$message = "TerminalIntegration\AuthorizationManager::games(Game Client type={$gctype}, Mac address = {$mac_address}) <br /> Detected IP Address: {$detected_ip_address} <br /> User has tried to login with wrong physical address! <br /> Exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);

				$error = ErrorConstants::getErrorMessage(ErrorConstants::$WRONG_PHYSICAL_ADDRESS);
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
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
	 * 
	 * returns list of countres and opens fictive backoffice session
	 * @param string $ip_address
	 * @return mixed
	 */
	public static function countries($ip_address){
		try{
			if(!isset($ip_address)){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
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
			$ip_address = strip_tags($ip_address);
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
            //test if ip address not sent by game client then autodetect it and sent autodetected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
			$modelAuthorization = new AuthorizationModel();
			$list_countries = array();
			$cursor_countries = $modelAuthorization->listCountries(0);
			foreach($cursor_countries as $country){
				$list_countries[] = array(
				    "id" => $country["id"],
                    "name" => $country["name"]
                );
			}

			$json_message = Zend_Json::encode(
                array(
                    "status" => OK,
                    "list_countries" => $list_countries,
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			$code = $ex->getCode();
			if($code != "20122"){
				//if there was exception with unknown error
				$errorHelper = new ErrorHelper();
                $detected_ip_address = IPHelper::getRealIPAddress();
                $message = "TerminalIntegration\AuthorizationManager::countries(IP Address = {$ip_address}) <br /> Detected IP Address: {$detected_ip_address} <br /> Exception message: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);

				$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
			if($code == "20122"){
				//if user is using wrong physical address
				$errorHelper = new ErrorHelper();
                $detected_ip_address = IPHelper::getRealIPAddress();
				$message = "TerminalIntegration\AuthorizationManager::countries(IP Address = {$ip_address}) <br /> Detected IP Address: {$detected_ip_address} <br /> User has tried to login with wrong physical address! <br /> Exception message: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);

				$error = ErrorConstants::getErrorMessage(ErrorConstants::$WRONG_PHYSICAL_ADDRESS);
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
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
	 * 
	 * returns currency from entered promo code
	 * @param string $aff_name
	 * @return mixed
	 */
	public static function currency_affiliate($aff_name){
		try{
			$modelAuthorization = new AuthorizationModel();
			$arrData = $modelAuthorization->validateAffName($aff_name);

			$json_message = Zend_Json::encode(
                array(
                    "status"=>NOK,
                    "currency"=> $arrData[0],
                    "affiliate_id" => $arrData[1]
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
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
	 * 
	 * Enter description here ...
	 * @param string $ip_address
	 * @return mixed
	 */
	private static function geolocation($ip_address){
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
        //test if ip address not sent by game client then autodetect it and sent autodetected ip address
        if(strlen(trim($ip_address)) == 0){
            $ip_address = IPHelper::getRealIPAddress();
        }
		if(IPHelper::testPrivateIP($ip_address)){
			//if it is private ip address
			return array("city"=>"PRIVATE IP ADDRESS", "country"=>"PRIVATE IP ADDRESS",	"countrycode"=>"PRIVATE IP ADDRESS");
		}else{ //ip it is public ip address call web service
			$jsonurl = "http://freegeoip.net/json/" . $ip_address;
			try{
				$json = @file_get_contents($jsonurl, 0, null, null);
				if($json == false){
					return array("city"=>"", "country"=>"", "countrycode"=>"");
				}
				else{
					$json_output = @json_decode($json);
					return array("city"=>$json_output->city, "country"=>$json_output->country_name, "countrycode"=>$json_output->country_code);
				}
			}catch(Zend_Exception $ex){
				return array("city"=>"", "country"=>"", "countrycode"=>"");
			}
		}
	}

	/**
	 * 
	 * login cashier
	 * general_purpose always Y | N
	 * @param int $session_id
	 * @param string $access_code
	 * @param string $mac_address
	 * @param string $general_purpose
	 * @return mixed
	 */
	public static function loginCashier($session_id, $access_code, $mac_address, $general_purpose){
		if((!isset($session_id) || !isset($access_code) || !isset($mac_address) || 
		!isset($general_purpose)) || ($general_purpose != YES && $general_purpose != NO)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
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
		$session_id = strip_tags($session_id);
		$access_code = strip_tags($access_code);
		$mac_address = strip_tags($mac_address);
		$general_purpose = strip_tags($general_purpose);		
		$modelSession = new SessionModel();
		$modelSession->resetPackages();
		$modelAuthorization = new AuthorizationModel();
		$arrData = $modelAuthorization->loginCashier($session_id, $access_code, $mac_address, $general_purpose);
		if($arrData != null){
		    $json_message = Zend_Json::encode(
                array(
                    "status"=>OK,
                    "status_out" => $arrData['status'],
                    "credits_in"=> $arrData['credits_in'],
                    "no_games" => $arrData['no_games'],
                    "credits_out" => $arrData['credits_out']
                )
            );
            exit($json_message);
        }else {
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
	}
	
	/**
	 * 
	 * login cashier
	 * @param int $session_id
	 * @param string $access_code
	 * @return mixed
	 */
	public static function loginWebCashier($session_id, $access_code){
		if(!isset($session_id) || !isset($access_code)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		$session_id = strip_tags($session_id);
		$access_code = strip_tags($access_code);
		$modelSession = new SessionModel();
		$modelSession->resetPackages();
		$modelAuthorization = new AuthorizationModel();
		$arrData = $modelAuthorization->loginWebCashier($session_id, $access_code);

		if($arrData != INTERNAL_ERROR){
            $json_message = Zend_Json::encode(
                array(
                    "status"=>OK,
                    "status_out" => $arrData['status'],
                    "credits_out"=> $arrData['credits_out'],
                    "affiliate_name" => $arrData['affiliate_name'],
                )
            );
            exit($json_message);
        }else{
		    $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
	}
	
	/**
	 * 
	 * get game move
	 * @param int $transaction_id
	 * @param int $session_id
	 * @param string $terminal_name
	 * @return mixed
	 */
	public static function gameMove($transaction_id, $session_id, $terminal_name){
		if(!isset($session_id) || !isset($transaction_id) || !isset($terminal_name)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		try{
			$transaction_id = strip_tags($transaction_id);
			$session_id = strip_tags($session_id);
			$terminal_name = strip_tags($terminal_name);
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'GameModel.php';
			$modelGame = new GameModel();
			$arrData = $modelGame->listGameMove($transaction_id, $session_id, $terminal_name);
			unset($modelGame);
			$json_message = Zend_Json::encode(
                array(
                    "status" => OK,
                    "active_lines" => $arrData['active_lines'],
                    "bet" => $arrData['bet'],
                    "bonus_games" => $arrData['bonus_games'],
                    "credit_amount" => $arrData['credit_amount'],
                    "card_to_beat" => $arrData['card_to_beat'],
                    "decimal_counter" => $arrData['decimal_counter'],
                    "game_state" => $arrData['game_state'],
					"gamble_type" => $arrData['gamble_type'],
                    "gamble_level" => $arrData['gamble_level'],
                    "gamble_card_history" => $arrData['gamble_card_history'],
                    "gamble_win" => $arrData['gamble_win'],
                    "last_win" => $arrData['last_win'],
                    "reels_position" => $arrData['reels_position'],
                    "total_bet" => $arrData['total_bet'],
                    "total_win" => $arrData['total_win'],
					"used_bonus_games" => $arrData['used_bonus_games'],
                    "win" => $arrData['win'],
                    "expanding_symbol_id" => $arrData['expanding_symbol_id'],
                    "c4bonus_win" => $arrData['c4bonus_win'],
                    "c4bonus" => $arrData['c4bonus'],
                    "scavenger_bonus" => $arrData['scavenger_bonus'],
                    "min_bet" => $arrData['min_bet'],
                    "bet_level_threshold" => $arrData['bet_level_threshold'],
                    "reel0" => $arrData['reel0'],
                    "reel1" => $arrData['reel1'],
                    "reel2" => $arrData['reel2'],
                    "reel3" => $arrData['reel3'],
                    "reel4" => $arrData['reel4'],
                    "game_id" => $arrData['game_id']
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::gameMove(transaction_id = {$transaction_id}, session_id = {$session_id}, terminal_name = {$terminal_name} ) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
	}
	
	/**
	 * 
	 * enable terminal with pin code
	 * @param string $pin_code
	 * @param string $status C | R
	 * @param string $mac_address
	 * @return mixed
	 */
	public static function enableTerminal($pin_code, $status, $mac_address){
		$pin_code = strip_tags($pin_code);
		$status = strip_tags($status);
		$mac_address = strip_tags($mac_address);
		if(!isset($pin_code) || !isset($status) || !isset($mac_address)){
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
			$message = "TerminalIntegration\AuthorizationManager::enableTerminal(pin code = {$pin_code}, status = {$status}, mac_address = {$mac_address}) <br /> Detected IP Address = {$detected_ip_address} <br /> Parameters for enable terminal are not sent!!!";
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		if($status != "C" && $status != "R"){
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::enableTerminal(pin code = {$pin_code}, status = {$status}, mac_address = {$mac_address}) <br /> Detected IP Address = {$detected_ip_address} <br /> Parameter status is not correct for enable terminal!!!";
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		try{	
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$result = $modelAuthorization->enableTerminal($pin_code, $status, $mac_address);

			if($result == "1"){
			    $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK
                    )
                );
                exit($json_message);
            }else if($result == WRONG_PIN_CODE){
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$WRONG_PIN_CODE);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }else {
			    $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
		}catch(Zend_Exception $ex){		
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::enableTerminal(pin code = {$pin_code}, status = {$status}, mac_address = {$mac_address}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
	}

    /**
     *
     * connect VLT and IO card
     * @param string $pin_code
     * @param string $serial_number
     * @return mixed
     */
    public static function connectVltAndIoCard($pin_code, $serial_number){
        $pin_code = strip_tags($pin_code);
        $serial_number = strip_tags($serial_number);
        if(strlen($pin_code) == 0 || strlen($serial_number) == 0){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::connectVltAndIoCard(pin code = {$pin_code}, serial_number = {$serial_number}) <br /> Detected IP Address = {$detected_ip_address} <br /> Parameters for connect VLT and IO card are not sent!!!";
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
        try{
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'AuthorizationModel.php';
            $modelAuthorization = new AuthorizationModel();
            $result = $modelAuthorization->connectVltAndIoCard($pin_code, $serial_number);
            if($result['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "recycler_status" => $result['recycler_status'],
                        "cr_status" => $result['cr_status'],
                        "ca_status" => $result['ca_status'],
                        "ba_status" => $result['ba_status'],
                        "status_out" => $result['status_out'],
                    )
                );
                exit($json_message);
            }else{
                if($result['message'] != INTERNAL_ERROR){
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                }else {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                }
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::connectVltAndIoCard(pin code = {$pin_code}, serial_number = {$serial_number}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
    }
	
	/**
	 * login for created player through their member cards
	 * @param string $username
	 * @param string $password
	 * @param string $barcode
	 * @param string $ip_address
	 * @param string $version
	 * @param string $mac_address
	 * @param string $gp_mac_address
	 * @param string $device_aff_id	 
	 * @return mixed
	*/
	private static function memberCardLogin($username, $password, $barcode, $ip_address, $version, $mac_address, $gp_mac_address, $device_aff_id){
		$username = strip_tags($username);
		$password = strip_tags($password);
		$barcode = strip_tags($barcode);
		$ip_address = strip_tags($ip_address);
		$version = strip_tags($version);
		$mac_address = strip_tags($mac_address);
		$gp_mac_address = strip_tags($gp_mac_address);
		$device_aff_id = strip_tags($device_aff_id);
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
        //test if ip address not sent by game client then autodetect it and sent autodetected ip address
        if(strlen(trim($ip_address)) == 0){
            $ip_address = IPHelper::getRealIPAddress();
        }
        if(strlen($barcode) > 0 && !is_numeric($barcode)){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
		try{	
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$res = $modelAuthorization->memberCardLogin($username, $password, $barcode, $ip_address, $version, $mac_address, $gp_mac_address, $device_aff_id);
			if($res['status'] == OK){
				if(strlen($res['message']) != 0){
					if($res['message'] == CASHIER_WRONG_MAC_ADDRESS_ERROR){
					    $error = ErrorConstants::getErrorMessage(ErrorConstants::$CASHIER_WRONG_MAC_ADDRESS_ERROR);
                        $json_message = Zend_Json::encode(
                            array(
                                "status" => NOK,
                                "error_message" => $error['message_text'],
                                "error_code" => $error['message_no'],
                                "error_description" => $error['message_description']
                            )
                        );
                        exit($json_message);
                    }else if($res['message'] == CARD_EXPIRED_ERROR){
                        $error = ErrorConstants::getErrorMessage(ErrorConstants::$CARD_EXPIRED_ERROR);
                        $json_message = Zend_Json::encode(
                            array(
                                "status" => NOK,
                                "error_message" => $error['message_text'],
                                "error_code" => $error['message_no'],
                                "error_description" => $error['message_description']
                            )
                        );
                        exit($json_message);
                    }else if($res['message'] == INTERNAL_ERROR){
                        $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                        $json_message = Zend_Json::encode(
                            array(
                                "status" => NOK,
                                "error_message" => $error['message_text'],
                                "error_code" => $error['message_no'],
                                "error_description" => $error['message_description']
                            )
                        );
                        exit($json_message);
                    }else{
                        $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                        $json_message = Zend_Json::encode(
                            array(
                                "status" => NOK,
                                "error_message" => $error['message_text'],
                                "error_code" => $error['message_no'],
                                "error_description" => $error['message_description']
                            )
                        );
                        exit($json_message);
                    }
				}else{
					//return complete result to game client
					$list_games = array();
					foreach($res['list_games'] as $game){
						$list_games[] = array(
							"game_group"=>$game['game_group'],
							"group_order"=>$game['group_order'],
							"game"=>$game['game'],
							"game_id"=>$game['game_id'],
							"page"=>$game['page'],
							"game_order"=>$game['game_order']
						);
					}
					$json_message = Zend_Json::encode(
                        array(
                            "status" => OK,
                            "session_id" => $res['session_id'],
                            "player_id" => $res['player_id'],
                            "credits" => $res['credits'],
                            "currency" => $res['currency'],
                            "terminal_status" => $res['device'],
                            "games" => $list_games,
                            "subject_status" => $res['subject_status'],
                            "username" => $res['username'],
                            "affiliate_id" => $res['affiliate_id']
                        )
                    );
                    exit($json_message);
				}
			}else{
				if(strlen($res['message']) != 0){
					if($res['message'] == CASHIER_WRONG_MAC_ADDRESS_ERROR){
					    $error = ErrorConstants::getErrorMessage(ErrorConstants::$CASHIER_WRONG_MAC_ADDRESS_ERROR);
                        $json_message = Zend_Json::encode(
                            array(
                                "status" => NOK,
                                "error_message" => $error['message_text'],
                                "error_code" => $error['message_no'],
                                "error_description" => $error['message_description']
                            )
                        );
                        exit($json_message);
                    }else if($res['message'] == CARD_EXPIRED_ERROR){
                        $error = ErrorConstants::getErrorMessage(ErrorConstants::$CARD_EXPIRED_ERROR);
                        $json_message = Zend_Json::encode(
                            array(
                                "status" => NOK,
                                "error_message" => $error['message_text'],
                                "error_code" => $error['message_no'],
                                "error_description" => $error['message_description']
                            )
                        );
                        exit($json_message);
                    }else if($res['message'] == INTERNAL_ERROR){
                        $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                        $json_message = Zend_Json::encode(
                            array(
                                "status" => NOK,
                                "error_message" => $error['message_text'],
                                "error_code" => $error['message_no'],
                                "error_description" => $error['message_description']
                            )
                        );
                        exit($json_message);
                    }else{
                        $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                        $json_message = Zend_Json::encode(
                            array(
                                "status" => NOK,
                                "error_message" => $error['message_text'],
                                "error_code" => $error['message_no'],
                                "error_description" => $error['message_description']
                            )
                        );
                        exit($json_message);
                    }
				}else{
					$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
				}
			}
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
			$message = "Error TerminalIntegration\AuthorizationManager::memberCardLogin(username = {$username}, password=, barcode={$barcode}, ip_address={$ip_address}, version={$version}, mac_address={$mac_address},
                gp_mac_address={$gp_mac_address}, device_aff_id={$device_aff_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
	}
	
	/**
	 * 
	 * Get encrypted token from GGL casino here for opening GGL lobby ...
	 * @param int $player_id
	 * @return mixed
	 */
	public static function getGglEncryptedToken($player_id){
		if(!isset($player_id)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		//DEBUG MESSAGES
		/*
		$errorHelper = new ErrorHelper();
		$mail_message = $log_message = "getEncryptedToken player_id = {$player_id}";
		$errorHelper->serviceError($mail_message, $log_message);
		*/
		try{
			$player_id = intval(strip_tags($player_id));
			$config = Zend_Registry::get('config');			
			$gglMasterClientWebServiceURL = $config->gglMasterClientWebServiceURL;
			$client = new Zend_Soap_Client($gglMasterClientWebServiceURL);
			$gglUser = $config->gglUser;
			$gglPassword = $config->gglPassword;
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$hashed_password = md5(md5(FICTIVE_BO_PASSWORD));
            //autodetect ip address from client
            $ip_address = IPHelper::getRealIPAddress();
			$bo_session_id = $modelAuthorization->openBoSession(FICTIVE_BO_USERNAME, $hashed_password, $ip_address);
			if($bo_session_id == 0){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
			}
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$details = $modelPlayer->getPlayerDetails($bo_session_id, $player_id);
			//check currency of player if supported by GGL
			if($details['currency'] == "ZWD"){
				$player_currency = "NONE";
			}else{
				$player_currency = $details['currency'];
			}
			if(!in_array($player_currency, array(
				"USD", "EUR", "RUB", "NONE", "CAD", "GBP", "TRY", "MXN", "MYR", "THB", "ARS", "VEF",
				"AUD", "NZD", "SGD", "DKK", "NOK", "CNY", "SEK", "HKD", "PLZ", "CZK",
				"TWD", "PLN", "JPY", "KRW", "IDR", "CHF", "IRR")
			)){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$INVALID_CURRENCY);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
			}
			$modelAuthorization->closeBoSession($bo_session_id);
			/* get encrypted token to open GGL (LDC) lobby for player */
			$paramsGetEncryptedToken = array(
				"clientUser" => $gglUser,
				"clientPassword" => $gglPassword,
				"customerID" => $player_id,
				"agentID" => $details['aff_id'], 
				"nickname" => $details['user_name']
			);
			$gglLobbyURL = $config->gglLobbyURL;
			//returns encrypted token string as result
			$responseGetEncryptedToken = (array)$client->getEncryptedToken($paramsGetEncryptedToken);
			//set currency to GGL (LDC) for this player
			$paramsSetPlayerCurrency = array(
				"clientUser" => $gglUser,
				"clientPassword" => $gglPassword,
				"customerID" => $player_id,
				"currency" => $player_currency
			);			
			//returns int 1 as accepted result
			$responseSetPlayerCurrencyCode = $client->setPlayerCurrencyCode($paramsSetPlayerCurrency);			
			//$responseSetPlayerCurrencyCode['setPlayerCurrencyCodeResult'] //should always return 1
			$token = $responseGetEncryptedToken['getEncryptedTokenResult'];			

			$json_message = Zend_Json::encode(
                array(
                    "status" => OK,
                    "affiliate_id" => $details['aff_id'],
                    "player_id" => $player_id,
                    "player_username" => $details['user_name'],
                    "player_currency" => $player_currency,
                    "url" => $gglLobbyURL,
                    "data" => $token
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
			$message = "Error TerminalIntegration\AuthorizationManager::getGglEncryptedToken(player_id = {$player_id}) web service. <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}	
	}
	
	/**
	 * 
	 * Get Betting Session Opened
	 * @param int $pc_session_id
	 * @return mixed
	 */
	public static function openBettingSession($pc_session_id){
		if(!isset($pc_session_id)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		//DEBUG MESSAGES
		/*
		$errorHelper = new ErrorHelper();
		$mail_message = $log_message = "openBettingSession pc_session_id = {$pc_session_id}";
		$errorHelper->serviceError($mail_message, $log_message);
		*/
		try{
			$pc_session_id = intval(strip_tags($pc_session_id));
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'BettingIntegrationModel.php';
			$modelBettingIntegration = new BettingIntegrationModel();
			$result = $modelBettingIntegration->openBettingSession($pc_session_id);
			if($result['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "pc_session_id" => $result['pc_session_id']
                    )
                );
                exit($json_message);
            }else{
			    $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
			$message = "Error TerminalIntegration\AuthorizationManager::openBettingSession(pc_session_id = {$pc_session_id}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}	
	}
	
	/**
	 * 
	 * Login Fun games
	 * @param int $browser_session
	 * @return mixed
	 */
	public static function loginFun($browser_session){
		if(!isset($browser_session)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		$browser_session = strip_tags($browser_session);
		$gamesArr = array();
		$i = 0;
		try{
			$modelAuthorization = new AuthorizationModel();
			$result_arr = $modelAuthorization->loginFunGames($browser_session);
			foreach ($result_arr['list_games'] as $g) {
				// Za sada nemam jackpot ali cemo i to kasnije dodati u storku tj. bice upisan u kursor za game order.
				$gamesArr[$i++] = //new GameGroupOrder($g['game_group'], $g['group_order'], $g['game'], $g['game_id'], $g['page'], $g['game_order'], null);
                    array(
                        "game_group" => $g['game_group'],
                        "group_order" => $g['group_order'],
                        "game" => $g["game"],
                        "game_id" => $g['game_id'],
                        "page" => $g['page'],
                        "game_order" => $g['game_order'],
                        "pot_name" => null
                    );
			}
			if(count($gamesArr)==0){
                $detected_ip_address = IPHelper::getRealIPAddress();
				$message = "TerminalIntegration\AuthorizationManager::loginFun(browser_session = {$browser_session}) <br /> Detected IP Address = {$detected_ip_address} <br /> Error: Array of games and parameters is empty.";
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceError($message, $message);

				$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
			}

			$json_message = Zend_Json::encode(
                array(
                    "status" => OK,
                    "session_id" => $result_arr['session_id'],
                    "games" => $gamesArr,
                    "credits" => $result_arr['credits'],
                    "fun_status" => $result_arr['status']
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			/*if there was unknown exception type*/
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::loginFun(browser_session = {$browser_session}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceError($message, $message);
			//$errorHelper->serviceErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
	}
	
	
	/**
	 * 
	 * Logout Fun games
	 * @param int $browser_session
	 * @return mixed
	 */
	public static function logoutFun($browser_session){
		if(!isset($browser_session)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		$browser_session = strip_tags($browser_session);
		try{
			$modelAuthorization = new AuthorizationModel();
			$modelAuthorization->logoutFunGames($browser_session);
			unset($modelAuthorization);
			$json_message = Zend_Json::encode(
                array(
                    "status" => OK,
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			/*if there was unknown exception type*/
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::logoutFun(browser_session = {$browser_session}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceErrorLog($message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
	}
	
	/**
	 * 
	 * Login Browser Session
	 * @param int $session_id
	 * @return mixed
	 */
	public static function openSessionByBrowser($session_id){
		if(!isset($session_id)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		$session_id = strip_tags($session_id);
		$gamesArr = array();
		$i = 0;
		try{	
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$res = $modelAuthorization->openSessionByBrowser($session_id);
			if($res['status'] == OK){
				if(strlen($res['message']) != 0){
					$json_message = Zend_Json::encode(
					    array("status"=>NOK, "message"=>$res['message'])
                    );
					exit($json_message);
				}else{
					foreach ($res['games'] as $g) {
						// Za sada nemam jackpot ali cemo i to kasnije dodati u storku tj. bice upisan u kursor za game order.
						$gamesArr[$i++] = //new GameGroupOrder($g['game_group'], $g['group_order'], $g['game'], $g['game_id'], $g['page'], $g['game_order'], null);
                            array(
                                "game_group" => $g['game_group'],
                                "group_order" => $g['group_order'],
                                "game" => $g["game"],
                                "game_id" => $g['game_id'],
                                "page" => $g['page'],
                                "game_order" => $g['game_order'],
                                "pot_name" => null
                            );
					}
					unset($res['games']);
					$res['games'] = $gamesArr;

					$json_message = Zend_Json::encode(
                        array(
                            "status" => OK,
                            "games" => $res['games'],
                            "subject_id" => $res['subject_id'],
                            "player_id" => $res['player_id'],
                            "terminal_status" => $res['device_out'],
                            "credits" => $res['credits'],
                            "currency" => $res['currency'],
                            "session_id" => $res['session'],
                        )
                    );
                    exit($json_message);
				}
			}else{
				if(strlen($res['message']) != 0){
					$json_message = Zend_Json::encode(
					    array("status"=>NOK, "message"=>$res['message'])
                    );
					exit($json_message);
				}else{ 
					$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
				}
			}
		}catch(Zend_Exception $ex){		
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::openSessionByBrowser(session_id = {$session_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
	}

    /**
     *
     * Get Sport Betting Session Opened - MAXBET integration
     * @param int $pc_session_id
     * @param string $ip_address
     * @return mixed
     */
    public static function openSportBetSession($pc_session_id, $ip_address){
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "openSportBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address} )";
        $errorHelper->serviceError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            $ip_address = strip_tags($ip_address);
            //test if ip address not sent by game client then autodetect it and sent autodetected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->openMaxBetSession($pc_session_id, $ip_address);
            if($result['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "pc_session_id" => $result['pc_session_id'],
                        "session_id_out" => $result['session_id_out']
                    )
                );
                exit($json_message);
            }else{
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error TerminalIntegration\AuthorizationManager::openSportBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
    }

    /**
     *
     * Get Sport Betting Session Opened - BETKIOSK integration
     * @param int $pc_session_id
     * @param string $ip_address
     * @return mixed
     */
    public static function openBetKioskSession($pc_session_id, $ip_address){
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "openBetKioskSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address} )";
        $errorHelper->serviceError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            $ip_address = strip_tags($ip_address);
            //test if ip address not sent by game client then autodetect it and sent autodetected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->openBetKioskSession($pc_session_id, $ip_address);

            if($result['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "pc_session_id" => $result['pc_session_id'],
                        "session_id_out" => $result['session_id_out']
                    )
                );
                exit($json_message);
            }else{
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error TerminalIntegration\AuthorizationManager::openBetKioskSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
    }

    /**
     *
     * Get Sport Betting Session Opened - MEMOBET integration
     * @param int $pc_session_id
     * @param string $ip_address
     * @return mixed
     */
    public static function openMemoBetSession($pc_session_id, $ip_address){
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "openMemoBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address} )";
        $errorHelper->serviceError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            $ip_address = strip_tags($ip_address);
            //test if ip address not sent by game client then autodetect it and sent autodetected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->openMemoBetGameSession($pc_session_id, $ip_address);

            if($result['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "pc_session_id" => $result['pc_session_id'],
                        "session_id_out" => $result['session_id_out']
                    )
                );
                exit($json_message);
            }else{
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error TerminalIntegration\AuthorizationManager::openMemoBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
    }

    /**
     * Close sport betting window - X button event for closing window of sport betting game
     * @param $pc_session_id
     * @return array
     */
    public static function closeSportBettingWindow($pc_session_id){
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "closeSportBettingWindow(pc_session_id = {$pc_session_id} )";
        $errorHelper->serviceError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->closeSportBettingGameWindow($pc_session_id);
            $json_message = Zend_Json::encode(
                array(
                    "status" => OK,
                    "session_id" => $result['session_id'],
                    "status_out" => $result['status_out']
                )
            );
            exit($json_message);
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error TerminalIntegration\AuthorizationManager::closeSportBettingWindow(pc_session_id = {$pc_session_id}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
    }

    /**
	 *
	 * set terminal for affiliate
	 * @param string $affiliate_name
	 * @param string $mac_address
	 * @return mixed
	 */
	public static function setTerminalForAffiliate($affiliate_name, $mac_address){
		$affiliate_name = strip_tags($affiliate_name);
		$mac_address = strip_tags($mac_address);
		if(strlen($mac_address) == 0){
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
			$message = "TerminalIntegration\AuthorizationManager::setTerminalForAffiliate(affiliate_name = {$affiliate_name}, mac_address = {$mac_address}) <br /> Detected IP Address = {$detected_ip_address} <br /> Parameters to set affiliate for terminal are not sent!!!";
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		try{
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$result = $modelAuthorization->setTerminalForAffiliate($affiliate_name, $mac_address);
			if($result['status'] == OK){
			    $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "status_out" => $result['status_out']
                    )
                );
                exit($json_message);
            }else {
			    if($result['code'] == 20300){
			       $error = ErrorConstants::getErrorMessage(ErrorConstants::$AFFILIATE_NAME_WRONG);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                }
                if($result['code'] == 20301){
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$MAC_ADDRESS_ALREADY_USED_FOR_OTHER_AFFILIATE);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                }
                if($result['code'] == 20302){
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$MAC_ADDRESS_ALREADY_USED);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                }
                if($result['code'] == 20399){
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                }
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::setTerminalForAffiliate(affiliate_name = {$affiliate_name}, mac_address = {$mac_address}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
	}

    /**
     *
     * Anonymous session
     * @param string $ip_address
     * @return mixed
     */
    public static function openAnonymousSession($ip_address){
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $message = "openAnonymousSession(ip_address = {$ip_address} )";
        $errorHelper->serviceError($message, $message);
        */
        try{
            $ip_address = strip_tags($ip_address);
            //test if ip address not sent by game client then autodetect it and sent autodetected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'AuthorizationModel.php';
            $modelAuthorization = new AuthorizationModel();
            $result = $modelAuthorization->openAnonymousSession($ip_address);

            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "ip_address" => $ip_address,
                    "session_id_out" => $result['session_id']
                )
            );
            exit($json_message);
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "Error TerminalIntegration\AuthorizationManager::openAnonymousSession(ip_address = {$ip_address}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
    }

    /**
     * check terminal date code from his affiliate
     * @param $mac_address
     * @return array
     */
	public static function checkTerminalDateCode($mac_address){
		$mac_address = strip_tags($mac_address);
		if(strlen($mac_address) == 0){
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
			//$message = "AuthorizationManager::checkTerminalDateCode(mac_address = {$mac_address}) <br /> Detected IP Address = {$detected_ip_address} <br /> Parameters to check date code from terminals's affiliate are not sent!!!";
			//$errorHelper->serviceError($message, $message);
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		try{
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$result = $modelAuthorization->checkTerminalDateCode($mac_address);

			if($result['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "mac_address" => $result['mac_address'],
                        "date_code" => $result['date_code']
                    )
                );
                exit($json_message);
            }else{
			    $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "TerminalIntegration\AuthorizationManager::checkTerminalDateCode(mac_address = {$mac_address}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
	}

    /**
	 *
	 * Get token for VIVO Gaming casino here to open vivogaming games
     * @param $session_id
	 * @param $player_id
     * @param $credits
	 * @return mixed
	 */
	public static function getVivoGamingToken($session_id, $player_id, $credits){
		if(strlen($player_id) == 0 || strlen($session_id) == 0 || strlen($credits) == 0){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		//DEBUG MESSAGES
		/*
		$errorHelper = new ErrorHelper();
		$mail_message = $log_message = "AuthorizationManager::getVivoGamingToken(session_id = {$session_id}, player_id = {$player_id}, credits = {$credits})";
		$errorHelper->serviceError($mail_message, $log_message);
		*/
		try{
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'VivoGamingIntegrationModel.php';
			$modelVivoGaming = new VivoGamingIntegrationModel();
            $result = $modelVivoGaming->getVivoGamingIntegrationToken($session_id, $player_id, $credits);
            if($result['status'] != OK){
                $errorHelper = new ErrorHelper();
                $detected_ip_address = IPHelper::getRealIPAddress();
			    $message = "Error AuthorizationManager::getVivoGamingToken(session_id = {$session_id}, player_id = {$player_id}, credits = {$credits}) web service. <br /> Detected IP Address = {$detected_ip_address}";
			    $errorHelper->serviceError($message, $message);

			    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }

            $config = Zend_Registry::get('config');
            $bet_soft_operator_id = $config->vivoGamingBetSoftOperatorId;

            $live_game_unified_operator_id = $config->vivoGamingLiveGameUnifiedOperatorId;

            $spinomenal_operator_id = $config->vivoGamingSpinomenalOperatorId;
            $spinomenal_partner_id = $config->vivoGamingSpinomenalPartnerId;

            $tom_horn_operator_id = $config->vivoGamingTomHornOperatorId;
            $tom_horn_partner_id = $config->vivoGamingTomHornPartnerId;

            $json_message = Zend_Json::encode(
                array("status"=>OK,
                    "token"=>$result['token'],
                    "bet_soft_operator_id"=>$bet_soft_operator_id,
                    "bet_soft_partner_id"=>"",
                    "live_game_unified_operator_id"=>$live_game_unified_operator_id,
                    "live_game_unified_partner_id"=>"",
                    "spinomenal_operator_id"=>$spinomenal_operator_id,
                    "spinomenal_partner_id"=>$spinomenal_partner_id,
                    "tom_horn_operator_id"=>$tom_horn_operator_id,
                    "tom_horn_partner_id"=>$tom_horn_partner_id
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
			$message = "Error TerminalIntegration\AuthorizationManager::getVivoGamingToken(session_id = {$session_id}, player_id = {$player_id}, credits = {$credits}) web service. <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
	}

	/**
     *
     * Get Shopping Integration - Shop Integration integration
     * @param int $pc_session_id
     * @param string $ip_address
     * @return mixed
     */
    public static function openShopIntegrationSession($pc_session_id, $ip_address){
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "openShopIntegrationSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address} )";
        $errorHelper->shopIntegrationError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            $ip_address = strip_tags($ip_address);
            //test if ip address not sent by game client then autodetect it and sent auto-detected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'ShopIntegrationModel.php';
            $modelShopIntegration = new ShopIntegrationModel();
            $result = $modelShopIntegration->openShopIntegrationSession($pc_session_id, $ip_address);

            if($result['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "pc_session_id" => $result['pc_session_id'],
                        "session_id_out" => $result['session_id_out']
                    )
                );
                exit($json_message);
            }else{
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "Shop Integration Error <br />Error TerminalIntegration\AuthorizationManager::openShopIntegrationSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
    }

    /**
     * @param $session_id
     * @return array
     */
    public static function closeShopIntegrationSession($session_id){
        if(!isset($session_id) || strlen($session_id) == 0){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "closeShopIntegrationSession(session_id = {$session_id})";
        $errorHelper->shopIntegrationError($mail_message, $log_message);
        */
        try{
            $session_id = intval(strip_tags($session_id));
            //test if ip address not sent by game client then autodetect it and sent auto-detected ip address
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'ShopIntegrationModel.php';
            $modelShopIntegration = new ShopIntegrationModel();
            $result = $modelShopIntegration->closeShopIntegrationWindow($session_id);

            $json_message = Zend_Json::encode(
                array(
                    "status" => OK,
                    "session_id" => $result['session_id'],
                    "status_out" => $result['status_out']
                )
            );
            exit($json_message);
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "Shop Integration Error <br />Error TerminalIntegration\AuthorizationManager::closeShopIntegrationSession(session_id = {$session_id}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
    }

    public static function getShopIntegrationBalance($subject_id){
        if(!isset($subject_id) || strlen($subject_id) == 0){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "getShopIntegrationBalance(subject_id = {$subject_id})";
        $errorHelper->shopIntegrationError($mail_message, $log_message);
        */
        try{
            $subject_id = intval(strip_tags($subject_id));
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'ShopIntegrationModel.php';
            $modelShopIntegration = new ShopIntegrationModel();
            $result = $modelShopIntegration->getShopIntegrationBalance($subject_id);

            if($result['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                        "subject_id"=>$subject_id, "credits"=>$result['credits'], "credits_formatted"=>$result['credits_formatted'])
                );
                exit($json_message);
            }else{
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "Shop Integration Error <br />Error TerminalIntegration\AuthorizationManager::getShopIntegrationBalance(subject_id = {$subject_id}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
    }

    /**
	* currency list for new player method by using affiliate_id
	* @param int $affiliate_id
    * @param string $tid_code
	* @return mixed
	*/
	public static function currencyListForNewPlayer($affiliate_id, $tid_code = ""){
		if(!isset($affiliate_id) || $affiliate_id == 0){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		$affiliate_id = strip_tags($affiliate_id);
        $tid_code = strip_tags($tid_code);
		try{
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$result = $modelWebSite->currencyListNewPlayer($affiliate_id, $tid_code);
			unset($modelWebSite);
			if($result['status'] == NOK){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
			}
			$currency_list = array();
			if($result['status'] == OK){
				foreach($result['result'] as $res){
					$currency_list[] = array(
						'currency' => $res['ics'] . ' - ' . $res['description'],
						'id' => $res['id']
					);
				}

                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "currency_list" => $currency_list
                    )
                );
                exit($json_message);
			}else{
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "Error in receiving currency list for new player from web site. <br /> Affiliate id: {$affiliate_id} <br /> Currency list for new player on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
	}

	/**
	 *
	 * insert new player method new with fixed response...
	 * @param int $affiliate_id
	 * @param string $username
	 * @param string $password
	 * @param string $email
	 * @param string $first_name
	 * @param string $last_name
	 * @param string $birthday
	 * @param int $country
	 * @param string $zip
	 * @param string $city
	 * @param string $street_address1
	 * @param string $street_address2
	 * @param string $phone
	 * @param string $bank_account
	 * @param int $bank_country
	 * @param string $swift
	 * @param string $iban
	 * @param int $receive_email
	 * @param string $currency
	 * @param string $ip_address
	 * @param string $registration_code
     * @param string $tid_code
     * @param string $language
	 * @return mixed
	 */
	public static function insertPlayer($affiliate_id, $username, $password, $email, $first_name, $last_name,
	$birthday, $country, $zip, $city, $street_address1, $street_address2 = '', $phone,
	$bank_account, $bank_country, $swift, $iban, $receive_email = 1, $currency, $ip_address, $registration_code, $tid_code = '', $language = 'en_GB'){
		if(strlen($username) == 0 || strlen($password) == 0 || strlen($email) == 0){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
		}
		$affiliate_id = strip_tags($affiliate_id);
		$username = strip_tags($username);
		$password = strip_tags($password);
		$email = strip_tags($email);
		$first_name = strip_tags($first_name);
		$last_name = strip_tags($last_name);
		$birthday = strip_tags($birthday);
		$country = strip_tags($country);
		$zip = strip_tags($zip);
		$city = strip_tags($city);
		$street_address1 = strip_tags($street_address1);
		$street_address2 = strip_tags($street_address2);
		$phone = strip_tags($phone);
		$bank_account = strip_tags($bank_account);
		$bank_country = strip_tags($bank_country);
		$swift = strip_tags($swift);
		$iban = strip_tags($iban);
		$receive_email = strip_tags($receive_email);
		$currency = strip_tags($currency);
		$ip_address = strip_tags($ip_address);
		$registration_code = strip_tags($registration_code);
        $tid_code = strip_tags($tid_code);
        $language = strip_tags($language);
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
        $res = array();
		try{
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'AuthorizationModel.php';
            $modelAuthorization = new AuthorizationModel();
            $hashed_password = md5(md5(FICTIVE_BO_PASSWORD));
            $bo_session_id = $modelAuthorization->openBoSession(FICTIVE_BO_USERNAME, $hashed_password, $ip_address);
            if($bo_session_id == 0){
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'PlayerModel.php';
            $modelPlayer = new PlayerModel();
            //returns false if not successfully or true if successfull inserted player
            $player_type = $modelPlayer->getPlayerTypeID(null, ROLA_PL_PC_PLAYER_INTERNET);
            $password = md5(md5($password));
            $action = INSERT;
            $aff_for = $affiliate_id;
            $subrole = $player_type;
            $mac_address = null;
            $banned = NO; //player is not banned
            $subject_id = null;
            $multicurrency = null;
            $autoincrement = null;
            $game_payback = null;
            $key_exit = null;
            $enter_password = null;
            if(strlen($language) == 0){
                $language = "en_GB";
            }
			//DEBUG BEFORE PLAYER REGISTRATION TO DATABASE
            /*
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("TerminalIntegration/AuthorizationManager::insertPlayer CALL FOR MANAGE_SUBJECTS.MANAGE_PLAYERS_ON_WEB_CHECK
			<br /> Session id: {$bo_session_id} <br /> Action: {$action}
			<br /> Aff for: {$aff_for} <br /> Username: {$username}
			<br /> Password: {$password} <br /> Subrole: {$subrole}
			<br /> Mac address: {$mac_address} <br /> Email: {$email}
			<br /> Country: {$country} <br /> Currency: {$currency}
			<br /> Banned: {$banned} <br /> Zip: {$zip}
			<br /> Phone: {$phone} <br /> Address: {$street_address1}
			<br /> Birthday: {$birthday} <br /> First name: {$first_name}
			<br /> Last name: {$last_name} <br /> City: {$city}
			<br /> Subject id: {$subject_id} <br /> Multicurrency: {$multicurrency}
			<br /> Autoincrement: {$autoincrement} <br /> Game payback: {$game_payback}
			<br /> Key exit: {$key_exit} <br /> Enter password: {$enter_password}
			<br /> Street address 2: {$street_address2} <br /> Bank account: {$bank_account}
			<br /> Bank country: {$bank_country} <br /> Swift: {$swift}
			<br /> Iban: {$iban} <br /> Receive mail: {$receive_email}
			<br /> Checks list: 'ALL' <br />
			<br /> Registration code: {$registration_code}
			<br /> Tid code: {$tid_code}
			<br /> Lanugage: {$language} ");
            */
			$res = $modelPlayer->managePlayerOnWebSite($bo_session_id, $action, $aff_for, $username,
				$password, $subrole, $mac_address, $email,
				$country, $currency, $banned, $zip, $phone,
				$street_address1, $birthday, $first_name,
				$last_name, $city, $subject_id, $multicurrency, $autoincrement,	$game_payback, $key_exit,
				$enter_password, $street_address2, $bank_account, $bank_country,
				$swift, $iban, $receive_email, 'ALL', $registration_code, $tid_code, $language);

            if($res['status'] != OK){
                /*
                $errorHelper = new ErrorHelper();
                $message = "WebSitePlayerAccountManager::insertPlayer Result = {$res['status']}";
			    $errorHelper->serviceError($message, $message);
                */
                $modelAuthorization->closeBoSession($bo_session_id);
                if ($res['error_code'] == 20713) {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_WRONG_FNAME_LNAME_BIRTHDAY);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                } else if ($res['error_code'] == 20714) {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_WRONG_EMAIL);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                } else if ($res['error_code'] == 20715) {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_WRONG_FNAME_LNAME_ADDRESS);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                } else if ($res['error_code'] == 20721) {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_WRONG_FNAME_LNAME_PHONE);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                } else if ($res['error_code'] == 20729) {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_INVALID_REGISTRATION_CODE);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                } else {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description']
                        )
                    );
                    exit($json_message);
                }
            }

            if(!isset($res['player_id'])){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
			}
			if($res['status'] != OK){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
			}

            if($res['status'] == OK && isset($res['player_id'])){
                require_once MODELS_DIR . DS . 'DocumentManagmentModel.php';
			    $modelDocumentManagment = new DocumentManagmentModel();
                $modelDocumentManagment->setPlayerVerificationStatus($bo_session_id, $res['player_id'], NOT_VERIFIED);
                //$modelDocumentManagment->setPlayerVerificationStatus($bo_session_id, $res['player_id'], KNOW_YOUR_CUSTOMER);
                //$modelDocumentManagment->setPlayerVerificationStatus($bo_session_id, $res['player_id'], EMAIL_VERIFIED);
                //DEBUG THIS PART OF CODE
                /*
                $errorHelper = new ErrorHelper();
                $errorHelper->sendMail("WebSitePlayerAccountManager::insertPlayer Set Player Verification Status <br />Bo session id: {$bo_session_id} <br />Player ID: {$res['player_id']}");
                */
                $modelAuthorization->closeBoSession($bo_session_id);
                require_once MODELS_DIR . DS . 'WebSiteModel.php';
                $modelWebSite = new WebSiteModel();
                $validationHash = $modelWebSite->getPlayerValidationHash($res['player_id']) . '_' . $res['player_id'];
                if($validationHash != null){
                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $site_settings = $modelMerchant->findSiteSettings($res['player_id']);
                    if($site_settings['status'] != OK){
                        $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                        $json_message = Zend_Json::encode(
                            array(
                                "status" => NOK,
                                "error_message" => $error['message_text'],
                                "error_code" => $error['message_no'],
                                "error_description" => $error['message_description']
                            )
                        );
                        exit($json_message);
                    }
                    //procedure to send mail to player after his activation
                    $playerMailSendFrom = $site_settings['mail_address_from'];
                    $playerMailAddress = $email;
                    $playerSmtpServer = $site_settings['smtp_server_ip'];
                    $casinoName = $site_settings['casino_name'];
                    $siteImagesLocation = $site_settings['site_image_location'];
                    $siteLink = $site_settings['site_link'];
                    $activationLink = $site_settings['player_activation_link'];
                    $playerActivationLink = $activationLink . '?activation_key=' . $validationHash;
                    $playerName = $first_name . " " . $last_name;
                    $playerUsername = $username;
                    $supportLink = $site_settings['support_url_link'];
                    $termsLink = $site_settings['terms_url_link'];
                    $contactLink = $site_settings['contact_url_link'];
                    $languageSettings = $site_settings['language_settings'];
                    $playerMailRes = WebSiteEmailHelper::getActivationEmailToPlayerContent($playerName, $playerUsername,
                    $siteImagesLocation, $casinoName, $siteLink, $playerActivationLink, $supportLink, $termsLink, $contactLink, $languageSettings);
                    $title = $playerMailRes['mail_title'];
                    $content = $playerMailRes['mail_message'];
                    $loggerMessage = "TerminalIntegration\AuthorizationManager::insertPlayer. Player with player username: {$playerUsername} on mail address: {$playerMailAddress}
                    has not received mail that his account is activated.";
                    WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
                    $playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);

                    $json_message = Zend_Json::encode(
                        array(
                            "status" => OK
                        )
                    );
                    exit($json_message);
                }else{
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description'],
                            "details" => "Error receiving inserted player validation hash for his activation mail"
                        )
                    );
                    exit($json_message);
                }
            }else{
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description'],
                        "details" => "Error receiving inserted player validation hash for his activation mail"
                    )
                );
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "TerminalIntegration\AuthorizationManager::insertPlayer. Error while creating new player in web site. <br /> Player username: {$username} <br /> Player email adddress: {$email} <br /> Player registration on web site exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description'],
                    "details" => "Error inserting player or seting player verification status"
                )
            );
            exit($json_message);
		}
	}

	/**
	 *
	 * player details method
	 * @param int $session_id
	 * @param string $ip_address
	 * @return mixed
	 */
	public static function playerDetails($session_id, $ip_address){
		if(!isset($session_id) || !isset($ip_address)){
			$error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description'],
                    "details" => "Error inserting player or seting player verification status"
                )
            );
            exit($json_message);
		}
		try{
			$session_id = strip_tags($session_id);
			$ip_address = strip_tags($ip_address);
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($session_id);
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($session_id, $arrPlayer['player_id']);
			if($playerDetails['status'] != OK){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description'],
                        "details" => "Error inserting player or seting player verification status"
                    )
                );
                exit($json_message);
			}
			$details = $playerDetails['details'];
			$bonus_status = $playerDetails['bonus_status'];

            $json_message = Zend_Json::encode(
                array(
                    "status" => OK,
                    "user_name"=>$details['user_name'],
                    "first_name"=>StringHelper::filterString($details['first_name']),
                    "last_name"=>StringHelper::filterString($details['last_name']),
                    "email"=>$details['email'],
                    "birthday"=>$details['birthday'],
                    "country_name"=>StringHelper::filterCountry($details['country_name']),
                    "country_id"=>$details['country_id'],
                    "city"=>StringHelper::filterString($details['city']),
                    "zip_code"=>$details['zip_code'],
                    "address"=>StringHelper::filterString($details['address']),
                    "phone"=>$details['phone'],
                    "rola"=>$details['rola'],
                    "super_rola"=>$details['super_rola'],
                    "path"=>$details['path'],
                    "banned"=>$details['banned'],
                    "currency"=>$details['currency'],
                    "start_time"=>$details['start_time'],
                    "ip_address"=>$details['ip_address'],
                    "address2"=>StringHelper::filterString($details['address2']),
                    "bank_account"=>$details['bank_account'],
                    "bank_country"=>$details['bank_country'],
                    "swift"=>$details['swift'],
                    "iban"=>$details['iban'],
                    "receive_mail"=>$details['send_mail'],
                    "credit_status"=>$details['credit_status'],
                    "total_credits"=>$details['credit_status'],
                    "credits"=>$details['credits'],
                    "free_credits"=>$details['credits'],
                    "credits_restricted"=>$details['credits_restricted'],
                    "bonus_restricted"=>$details['bonus_restricted'],
                    "bonus_win_restricted"=>$details['bonus_win_restricted'],
                    "promotion"=>$details['promotion'],
                    "bonus_status"=>$bonus_status,
                    "language"=>$details['bo_default_language'],
                    "bank_country_name" => $details['bank_country_name'],
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "TerminalIntegration/AuthorizationManager::playerDetails. Error while getting player details for web site. <br /> Session id: {$session_id} <br /> IP address: {$ip_address} <br /> Player details on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description'],
                    "details" => "Error inserting player or seting player verification status"
                )
            );
            exit($json_message);
		}
	}

	/**
	 *
	 * update player details ...
	 * @param int $session_id
	 * @param string $email
	 * @param string $first_name
	 * @param string $last_name
	 * @param string $birthday
	 * @param int $country
	 * @param string $zip
	 * @param string $city
	 * @param string $street_address1
	 * @param string $street_address2
	 * @param string $phone_number
	 * @param string $bank_account
	 * @param int $bank_country
	 * @param string $swift
	 * @param string $iban
	 * @param int $receive_email
	 * @param string $ip_address
     * @param string $language
	 * @return mixed
	 */
	public static function updatePlayer($session_id, $email = "", $first_name = "", $last_name = "",
	$birthday, $country, $zip = "", $city = "", $street_address1 = "", $street_address2 = "",
	$phone_number = "", $bank_account, $bank_country, $swift, $iban, $receive_email = 1, $ip_address, $language = "en_GB"){
		$session_id = intval(strip_tags($session_id));
		$email = strip_tags($email);
		$first_name = strip_tags($first_name);
		$last_name = strip_tags($last_name);
		$birthday = strip_tags($birthday);
		$country = strip_tags($country);
		$zip = strip_tags($zip);
		$city = strip_tags($city);
		$street_address1 = strip_tags($street_address1);
		$street_address2 = strip_tags($street_address2);
		$phone_number = strip_tags($phone_number);
		$bank_account = strip_tags($bank_account);
		$bank_country = strip_tags($bank_country);
		$swift = strip_tags($swift);
		$iban = strip_tags($iban);
		$receive_email = strip_tags($receive_email);
		$ip_address = strip_tags($ip_address);
        $language = strip_tags($language);
		try{
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$hashed_password = md5(md5(FICTIVE_BO_PASSWORD));
			$bo_session_id = $modelAuthorization->openBoSession(FICTIVE_BO_USERNAME , $hashed_password, $ip_address);
			if($bo_session_id == 0){
				$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description'],
                        "details" => "Invalid BO Session"
                    )
                );
                exit($json_message);
			}
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($session_id);
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$subrole = $modelPlayer->getPlayerTypeID(null, ROLA_PL_PC_PLAYER_INTERNET);
			$subject_id = $arrPlayer['player_id'];
			$action = UPDATE;
			$aff_for = null;
			$username = null;
			$password = null;
			$mac_address = null;
			$currency = null;
			$banned = NO;
			$multicurrency = null;
			$autoincrement = null;
			$game_payback = null;
			$key_exit = null;
			$enter_password = null;
            $registration_code = null;
            $tid_code = null;
            if(strlen($language) == 0){
                $language = "en_GB";
            }
			try{
				$res = $modelPlayer->managePlayerOnWebSite($bo_session_id, $action, $aff_for, $username,
				$password, $subrole, $mac_address, $email,
				$country, $currency, $banned, $zip, $phone_number,
				$street_address1, $birthday, $first_name,
				$last_name, $city, $subject_id, $multicurrency, $autoincrement,	$game_payback, $key_exit,
				$enter_password, $street_address2, $bank_account, $bank_country,
				$swift, $iban, $receive_email, 'ALL', $registration_code, $tid_code, $language);
			}catch(Zend_Exception $ex){
				$errorHelper = new ErrorHelper();
				$mail_message = "TerminalIntegration/AuthorizationManager::updatePlayer. Error while trying to update player details for web site. <br /> Session id: {$session_id} <br /> Player email address: {$email} <br /> Player details on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($mail_message, $log_message);

				$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description'],
                        "details" => "Error updating player information"
                    )
                );
                exit($json_message);
			}
			$modelAuthorization->closeBoSession($bo_session_id);
			if($res['status'] == OK){
                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                    )
                );
                exit($json_message);
			}
			else{
                if ($res['error_code'] == 20713) {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_WRONG_FNAME_LNAME_BIRTHDAY);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description'],
                        )
                    );
                    exit($json_message);
                } else if ($res['error_code'] == 20714) {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_WRONG_EMAIL);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description'],
                        )
                    );
                    exit($json_message);
                } else if ($res['error_code'] == 20715) {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_WRONG_FNAME_LNAME_ADDRESS);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description'],
                        )
                    );
                    exit($json_message);
                } else if ($res['error_code'] == 20721) {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_WRONG_FNAME_LNAME_PHONE);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description'],
                        )
                    );
                    exit($json_message);
                } else if ($res['error_code'] == 20729) {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_INVALID_REGISTRATION_CODE);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description'],
                        )
                    );
                    exit($json_message);
                } else {
                    $error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
                    $json_message = Zend_Json::encode(
                        array(
                            "status" => NOK,
                            "error_message" => $error['message_text'],
                            "error_code" => $error['message_no'],
                            "error_description" => $error['message_description'],
                        )
                    );
                    exit($json_message);
                }
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "TerminalIntegration/AuthorizationManager::updatePlayer. Error while trying to update player details for web site. <br /> Session id: {$session_id} <br /> Player email address: {$email} <br /> Player details on web site exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);

			$error = ErrorConstants::getErrorMessage(ErrorConstants::$GENERAL_ERROR);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description'],
                    "details" => "Error updating player information"
                )
            );
            exit($json_message);
		}
	}

	/**
     *
     * Get Lucky Game Integration Session
     * @param int $pc_session_id
     * @param string $game_id
     * @return mixed
     */
    public static function openLuckyGameIntegrationSession($pc_session_id, $game_id){
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0 || !isset($game_id) || strlen($game_id) == 0){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "openMemoBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address} )";
        $errorHelper->sportBettingIntegrationError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            $game_id = strip_tags($game_id);
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'LuckyGameIntegrationModel.php';
            $modelLuckyGameIntegration = new LuckyGameIntegrationModel();
            $result = $modelLuckyGameIntegration->getLuckyIntegrationToken($pc_session_id, $game_id);
            if($result['status'] == OK) {
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                        "game_session_id_out"=>$result['game_session_id_out'],
                        "path" => $result['path']
                    )
                );
                exit($json_message);
            }else{
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "Luck Game Integration Error <br />Error AuthorizationManager::openLuckyGameIntegrationSession(pc_session_id = {$pc_session_id}, game_id = {$game_id}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->sportBettingIntegrationError($message, $message);

            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
    }

    /**
     *
     * Get Lucky Game Integration Session
     * @param int $pc_session_id
     * @return mixed
     */
    public function closeLuckyGameIntegrationSession($pc_session_id){
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0){
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$MISSING_PARAMETERS);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "closeLuckyGameIntegrationSession(pc_session_id = {$pc_session_id})";
        $errorHelper->serviceError($mail_message, $log_message);
        */
        try{
            $pc_session_id = intval(strip_tags($pc_session_id));
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'LuckyGameIntegrationModel.php';
            $modelLuckyGameIntegration = new LuckyGameIntegrationModel();
            $result = $modelLuckyGameIntegration->closeLuckyGameIntegrationSession($pc_session_id);
            if($result['status'] == OK) {
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                        "status_out"=>$result['status_out']
                    )
                );
                exit($json_message);
            }else{
                $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
                $json_message = Zend_Json::encode(
                    array(
                        "status" => NOK,
                        "error_message" => $error['message_text'],
                        "error_code" => $error['message_no'],
                        "error_description" => $error['message_description']
                    )
                );
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "Luck Game Integration Error <br />Error AuthorizationManager::closeLuckyGameIntegrationSession(pc_session_id = {$pc_session_id}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            $error = ErrorConstants::getErrorMessage(ErrorConstants::$NOK_EXCEPTION);
            $json_message = Zend_Json::encode(
                array(
                    "status" => NOK,
                    "error_message" => $error['message_text'],
                    "error_code" => $error['message_no'],
                    "error_description" => $error['message_description']
                )
            );
            exit($json_message);
        }
    }


}