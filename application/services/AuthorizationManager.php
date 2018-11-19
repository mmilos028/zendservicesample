<?php
require_once 'Zend/Registry.php';
require_once MODELS_DIR . DS . 'AuthorizationModel.php';
require_once MODELS_DIR . DS . 'GameModel.php';
require_once MODELS_DIR . DS . 'SessionModel.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
/** Creates object Country and its data */
class Country{
	public $id;
	public $name;
	public function __construct($id, $name){
		$this->id = $id;
		$this->name = $name;
	}
}
/** Creates Object Game and its data */
class Game{
	public $id;
	public $name;
	public $status;
	public $parameters;
	public $game_on_off;
	public $pot_name;
	public function __construct($id, $name, $status, $game_on_off = null, $parameters, $pot_name){
		$this->id = $id;
		$this->name = $name;
		$this->status = $status;
		$this->game_on_off = $game_on_off;
		$this->parameters = $parameters;
		$this->pot_name = $pot_name;
	}
}
/** Creates object Parameter and holds parameter data */
class Parameter{
	public $name;
	public $value;
	public function __construct($name, $value){
		$this->name = $name;
		$this->value = $value;
	}
}

class GameGroupOrder
{
    public $game_group;
    public $group_order;
    public $game;
    public $game_id;
    public $page;
    public $game_order;
    public $pot_name;
    public function __construct($game_group, $group_order, $game, $game_id, $page, $game_order, $pot_name)
    {
	$this->game_group = $game_group;
	$this->group_order = $group_order;
	$this->game = $game;
	$this->game_id = $game_id;
	$this->page = $page;
	$this->game_order = $game_order;
	$this->pot_name = $pot_name;
    }
}

/**
 * 
 * Performes authorization process through web service
 *
 */
class AuthorizationManager {
	/**
	 * 
	 * Opens terminal session with web site session id
	 * @param int $web_site_session_id
	 * @param string $version
	 * @param string $ipaddress
	 * @return mixed
	 */
	public function loginWithWebSite($web_site_session_id, $version, $ipaddress){
		if(!isset($web_site_session_id)){
			return INTERNAL_ERROR;
		}
		$web_site_session_id = strip_tags($web_site_session_id);
		$version = strip_tags($version);
		$ipaddress = strip_tags($ipaddress);
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ipaddress);
		$ipaddress = $ip_addresses[0];		
		require_once MODELS_DIR . DS . 'WebSiteModel.php';
		$modelWebSite = new WebSiteModel();
		$arrData = $modelWebSite->sessionIdToPlayerId((int)$web_site_session_id);
		if($arrData == false){
			return INTERNAL_ERROR;
		}
		$username = $arrData['player_name'];
		$password = $arrData['player_password'];
		$mac_address = null;
		$gctype = PLAYER;
		return 
			$this->login($username, $password, $mac_address, $version, $ipaddress, $gctype);
	}	
	
	/**
	 * 
	 * Opens terminal session
	 * Returns list of games and parameters
	 * Game (param1, param2, ... paramN) and so on
	 * @param string $username
	 * @param string $password
	 * @param string $mac_address
	 * @param string $version
	 * @param string $ipaddress
	 * @param string $gctype
	 * @param string $device_aff_id
	 * @param string $gp_mac_address
	 * @param string $registred_aff
	 * @return mixed
	 */
	public function login($username, $password, $mac_address, $version, $ipaddress, $gctype, 
	$device_aff_id = "", $gp_mac_address = "", $registred_aff = ""){
		try{
			if(!isset($username) || !isset($password)){
				return null;
			}
			$username = trim(strip_tags($username));
			$password = trim(strip_tags($password));
			$mac_address = trim(strip_tags($mac_address));
			$version = trim(strip_tags($version));
			$ipaddress = trim(strip_tags($ipaddress));
			$gctype = trim(strip_tags($gctype));
			$device_aff_id = trim(strip_tags($device_aff_id));
			$gp_mac_address = trim(strip_tags($gp_mac_address));
			$registred_aff = trim(strip_tags($registred_aff));
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ipaddress);
			$ipaddress = $ip_addresses[0];
			$modelSession = new SessionModel();
			$modelSession->resetPackages();
			$modelAuthorization = new AuthorizationModel();
			$country = "";
			$city = "";
			if(strlen($device_aff_id) == 0 || $device_aff_id == " "){
				$device_aff_id = null;
			}
			if(strlen($gp_mac_address) == 0 || $gp_mac_address == " "){
				$gp_mac_address = null;
			}
			if(!isset($registred_aff) || strlen($registred_aff) == 0 || $registred_aff == " "){
				$registred_aff = null;
			}
			//DEBUG THIS PART OF CODE
			/*
				$errorHelper = new ErrorHelper();
				$message = "login (username = $username, password = $password, mac_address = $mac_address, version = $version, ip_address = $ipaddress, gc_type = $gctype, device_aff_id = $device_aff_id,
					gp_mac_address = $gp_mac_address, registred_aff = $registred_aff";
				$errorHelper->sendMail($message);
				$errorHelper->serviceAccessLog($message);
			*/
			if(strlen($mac_address) == 0){
				$panic = NO;
			}else{
				$panic = $modelAuthorization->checkPanic($mac_address);
				//DEBUG THIS PART OF CODE
				/*
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("Poziv player_core.m checkPanic: mac_address= " . $mac_address);
				$errorHelper->sendMail("Poziv player_core.m checkPanic: mac_address= " . $mac_address);
				*/
			}
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
				return AFFILIATE_BANNED;
			}
			//this terminal's affiliate is self banned don't open terminal session
			if($status == YES && $reason == -1){
				return PLAYER_SELF_BANNED;
			}
			if($panic == NO){
				//DEBUG THIS PART OF CODE
				/*
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("User is trying to open terminal session! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ipaddress . " Country: " . $country . " City: " . $city . " Device Aff Id: " . $device_aff_id . " General Purpose Mac Address: " . $gp_mac_address);
				$errorHelper->sendMail("User is trying to open terminal session! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ipaddress . " Country: " . $country . " City: " . $city . " Device Aff Id: " . $device_aff_id . " General Purpose Mac Address: " . $gp_mac_address);
				*/	
				$config = Zend_Registry::get('config');
				$res = $modelAuthorization->openTerminalSessionMalta($username, $password, $mac_address, $version, $ipaddress, $country, $city, $device_aff_id, $gp_mac_address, $registred_aff);
				//DEBUG THIS PART OF CODE
				/*
				$errorHelper = new ErrorHelper();
				$errorHelper->serviceAccessLog("User is trying to open terminal session! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ipaddress . " Country: " . $country . " City: " . $city . " Device Aff Id: " . $device_aff_id . " General Purpose Mac Address: " . $gp_mac_address . "Result = " . $res);
				$errorHelper->sendMail("User is trying to open terminal session! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ipaddress . " Country: " . $country . " City: " . $city . " Device Aff Id: " . $device_aff_id . " General Purpose Mac Address: " . $gp_mac_address . "Result = " . $res);
				*/
				if($res == INTERNAL_ERROR){
					$mail_message = "AuthorizationManager.games call error: Open terminal session failed.";
					$log_message = "AuthorizationManager.games call error: Open terminal session failed.";
					$errorHelper = new ErrorHelper();
					$errorHelper->serviceError($mail_message, $log_message);
					return INTERNAL_ERROR;
				}
				if($res == WRONG_USERNAME_PASSWORD){
					return WRONG_USERNAME_PASSWORD;
				}
				if($res == WRONG_PHYSICAL_ADDRESS){
					return WRONG_PHYSICAL_ADDRESS;
				}
				if($res == PLAYER_BANNED_LIMIT){
					return PLAYER_BANNED_LIMIT;
				}
				if($res == LOGIN_TOO_MANY_TIMES){
					return LOGIN_TOO_MANY_TIMES;
				}
				$modelSession->resetPackages();
				$modelGames = new GameModel();
				//returns enabled games for this user
				$res1 = array();
				$gamesArr = array();
				//if has session valid
				if(!is_null($res['session_id'])){
				    $i = 0;			
				    foreach ($res['list_games'] as $g) {
						// Za sada nemam jackpot ali cemo i to kasnije dodati u storku tj. bice upisan u kursor za game order.
						$gamesArr[$i++] = new GameGroupOrder($g['game_group'], $g['group_order'], $g['game'], $g['game_id'], $g['page'], $g['game_order'], null);
				    }
				}else {
					$mail_message = "Result while user was logging in is null. <br /> Username: " . $username . "<br /> Mac Address:" . $mac_address . "<br /> Version:" . $version . "<br /> IP Address: " . $ipaddress . "<br /> Country: " . $country . "<br /> City: " . $city;
					$log_message = "Result while user was logging in is null. Username: " . $username . " Mac Address:" . $mac_address . " Version:" . $version . " IP Address: " . $ipaddress . " Country: " . $country . " City: " . $city;
					$errorHelper = new ErrorHelper();
					$errorHelper->serviceError($mail_message, $log_message);
					return null;
				}
				//check if any invalid results from database if status is not returned
				if($res['session_id'] == "123456789" || $res['credits'] == "123456789" || $res['currency'] == "123456789"){
					return null;
				}else{
					//if all results are in place and player has successfully loged in
					//DEBUG THIS PART OF CODE
					/*
					$errorHelper = new ErrorHelper();
					$errorHelper->serviceAccessLog("User is successfully logged in! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ipaddress . " Country: " . $country . " City: " . $city);
					$errorHelper->sendMail("User is successfully logged in! Username: " . $username . " Mac Address: " . $mac_address . " Version: " . $version . " IP Address: " . $ipaddress . " Country: " . $country . " City: " . $city);
					*/
					if(count($gamesArr)==0){
						$mail_message = "AuthorizationManager.games call error: Array of games and parameters is empty.";
						$log_message = "AuthorizationManager.games call error: Array of games and parameters is empty.";
						$errorHelper = new ErrorHelper();
						$errorHelper->serviceError($mail_message, $log_message);
						return INTERNAL_ERROR;
					}
					$result = array("session"=>$res['session_id'], "games"=>$gamesArr, "credits"=>$res['credits'],	"currency"=>$res['currency'], "page_no"=>$res1['one_page'], "terminal_status"=>$res['device']);
					return $result;
				}
			}else { 
				//casino is in panic
				return PANIC_STATUS;
			}
		}catch(Zend_Exception $ex){
			$mail_message = "Username: " . $username . "<br />" . "Mac Address: " . $mac_address . "<br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "Username: " . $username . " Mac Address: " . $mac_address . " Exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceError($mail_message, $log_message);
			return INTERNAL_ERROR;
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
	public function loginExternalIntegration($token, $ip_address){
		try{
			if(!isset($token) || !isset($ip_address)){
				return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
			}
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
            //test if ip address not sent by game client then autodetect it and sent autodetected ip address
            if(strlen(trim($ip_address)) == 0){
                $ip_address = IPHelper::getRealIPAddress();
            }
            require_once MODELS_DIR . DS . 'ExternalIntegrationModel.php';
			$modelExternalIntegration = new ExternalIntegrationModel();
			//DEBUG THIS PART OF CODE
			/*
				$errorHelper = new ErrorHelper();
				$message = "loginExternalIntegration (token = {$token}, ip_address = {$ip_address})";
				$errorHelper->sendMail($message);
				$errorHelper->serviceAccessLog($message);
			*/
			$res = $modelExternalIntegration->loginPlayerByToken($token, $ip_address);
			if($res['status'] == OK){
				if(strlen($res['message']) != 0){
					return array("status"=>NOK, "message"=>INTERNAL_ERROR);
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
					$new_res = array("status"=>OK, "token"=>$res['token'], "ip_address"=>$res['ip_address'], "session"=>$res['session_id'], "player_id"=>$res['player_id'],
                        "credits"=>$res['credits'],	"currency"=>$res['currency'], "games"=>$list_games);
					return $new_res;
				}
			}else{
				return array("status"=>NOK, "message"=>INTERNAL_ERROR);
			}
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$message = "AuthorizationManager::loginExternalIntegration(token = {$token}, ip_address = {$ip_address}) <br /> Detected IP address = {$detected_ip_address} <br /> Exception message: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>INTERNAL_ERROR);
		}
	}
	
	/**
	 * 
	 * logout user from system
	 * @param int $session_id
	 * @return mixed
	 */
	public function logout($session_id){
		try{
			if(!isset($session_id)){
				return null;
			}
			$session_id = strip_tags($session_id);
			$modelSession = new SessionModel();
			$modelSession->resetPackages();
			$modelAuthorization = new AuthorizationModel();
			$modelAuthorization->closeTerminalSession($session_id);
			unset($modelSession);
			unset($modelAuthorization);
			return "1";
		}catch(Zend_Exception $ex){
			/*if there was unknown exception type*/
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = "AuthorizationManager - logout exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);			
			return "0";
		}
	}

	/**
	 * 
	 * close fictive bo session
	 * @param int $session_id
	 * @return mixed
	 */
	public function logoutBo($session_id){
		if(!isset($session_id)){
			return null;
		}
		$session_id = strip_tags($session_id);
		try{
			$modelAuthorization = new AuthorizationModel();
			$modelAuthorization->closeBoSession($session_id);
			unset($modelAuthorization);
			return "1";
		}catch(Zend_Exception $ex){
			/*if there was unknown exception type*/
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
			return null;
		}
	}
	
	/**
	 * 
	 * returns list of games on system before there logging user
	 * @param string $gctype
	 * @param string $mac_address
	 * @return mixed
	 */
	public function games($gctype, $mac_address){
		try{			
			if(!isset($gctype)){
				return null;
			}
			$gctype = strip_tags($gctype);
			$mac_address = strip_tags($mac_address);
			$modelSession = new SessionModel();
			$modelSession->resetPackages();
			$modelAuthorization = new AuthorizationModel();
			$panic = $modelAuthorization->checkPanic($mac_address);
			if($panic == YES){
				return PANIC_STATUS; //casino is in panic status
			}
			unset($modelSession);
			$modelGames = new GameModel();
			$res = $modelGames->getGames(ENABLED, DISABLED, $gctype, $mac_address);
			unset($modelGames);
			$res2 = array();
			$res2 = $res;
			$gamesArr = array();
			$i = 0;			
			foreach ($res2['list_games'] as $g) {
			    // Za sada nemam jackpot ali cemo i to kasnije dodati u storku tj. bice upisan u kursor za game order.
			    $gamesArr[$i++] = new GameGroupOrder($g['game_group'], $g['group_order'], $g['game'], $g['game_id'], $g['page'], $g['game_order'], null);
			}			
			if(count($gamesArr) == 0){
				return INTERNAL_ERROR;
			}
			$result = array($gamesArr, $res['terminal_type'], $res['skin'], $res['key_exit'], 
			$res['enter_password'], $res['mouse_on_off'], $res['one_page'], $res['port'], $res['general_purpose'], $res['affiliate_id']);
		}catch(Zend_Exception $ex){
			$code = $ex->getCode();
			if($code != "20122"){ 
				// if it is unknown error
				$errorHelper = new ErrorHelper();
				$mail_message = "GC Type: " . $gctype . "<br /> Mac Address: " . $mac_address . " <br /> Exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$log_message = "GC Type: " . $gctype . " Mac Address: " . $mac_address . " Exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($mail_message, $log_message);
				return INTERNAL_ERROR;
			}
			if($code == "20122"){ 
				//if user is using unknown mac physical address
				$errorHelper = new ErrorHelper();
				$mail_message = "User has tried to login with wrong physical address! <br /> GC Type: " . $gctype . "<br /> Mac Address: " . $mac_address . " <br /> Exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$log_message = "User has tried to login with wrong physical address! GC Type: " . $gctype . " Mac Address: " . $mac_address;
				$errorHelper->serviceError($mail_message, $log_message);
				return WRONG_PHYSICAL_ADDRESS;
			}
			return INTERNAL_ERROR;
		}
		return $result;
	}
	
	/**
	 * 
	 * returns list of countres and opens fictive backoffice session
	 * @param string $ip_address
	 * @return mixed
	 */
	public function countries($ip_address){
		try{
			if(!isset($ip_address)){
				return null;
			}
			$ip_address = strip_tags($ip_address);
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			$modelAuthorization = new AuthorizationModel();
			$hashed_password = md5(md5(FICTIVE_BO_PASSWORD));
			//DEBUG THIS PART OF CODE
			/*
			$errorHelper = new ErrorHelper();
			$errorHelper->serviceErrorLog('Username: ' . $this->fictive_bo_username . " password: " . $hashed_password . " Ip Address: " . $ip_address);
			$errorHelper->sendMail('Username: ' . $this->fictive_bo_username . " password: " . $hashed_password . " Ip Address: " . $ip_address);
			*/
			$res[1] = $modelAuthorization->openBoSession(FICTIVE_BO_USERNAME , $hashed_password, $ip_address);
			/*
			$errorHelper->serviceErrorLog("Fictive BO Session opened: " . $res[1]);
			$errorHelper->sendMail("Fictive BO Session opened: " . $res[1]);
			*/
			$res[2] = array();
			$cursor_countries = $modelAuthorization->listCountries($res[1]);
			$modelAuthorization->closeBoSession($res[1]);
			unset($modelAuthorization);
			foreach($cursor_countries as $country){
				$res[2][] = new Country($country["id"], $country["name"]);
			}
			$result = array($res[1], $res[2]);
		}catch(Zend_Exception $ex){
			$code = $ex->getCode();
			if($code != "20122"){ 
				//if there was exception with unknown error
				$errorHelper = new ErrorHelper();
				$mail_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($mail_message, $log_message);				
				return INTERNAL_ERROR; 
			}			
			if($code == "20122"){
				//if user is using wrong physical address
				$errorHelper = new ErrorHelper();
				$mail_message = "User has tried to login with wrong physical address! <br /> IP Address: " . $ip_address . " <br /> Exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($mail_message, $log_message);				
				return WRONG_PHYSICAL_ADDRESS;
			}
			return INTERNAL_ERROR;
		}
		return $result;
	}
	
	/**
	 * 
	 * returns currency from entered promo code
	 * @param string $aff_name
	 * @return mixed
	 */
	public function currency_affiliate($aff_name){
		try{
			$modelAuthorization = new AuthorizationModel();
			$arrData = $modelAuthorization->validateAffName($aff_name);
			unset($modelAuthorization);
			return array("currency" => $arrData["currency"], "affiliate_id"=> $arrData["affiliate_id"]);
		}catch(Zend_Exception $ex){
			return null;
		}
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $ip_address
	 * @return mixed
	 */
	private function geolocation($ip_address){
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
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
	public function loginCashier($session_id, $access_code, $mac_address, $general_purpose){
		if((!isset($session_id) || !isset($access_code) || !isset($mac_address) || 
		!isset($general_purpose)) || ($general_purpose != YES && $general_purpose != NO)){
			return null;
		}
		$session_id = strip_tags($session_id);
		$access_code = strip_tags($access_code);
		$mac_address = strip_tags($mac_address);
		$general_purpose = strip_tags($general_purpose);		
		$modelSession = new SessionModel();
		$modelSession->resetPackages();
		$modelAuthorization = new AuthorizationModel();
		$arrData = $modelAuthorization->loginCashier($session_id, $access_code, $mac_address, $general_purpose);
		unset($modelSession);
		unset($modelAuthorization);
		return $arrData;
	}
	
	/**
	 * 
	 * login cashier
	 * @param int $session_id
	 * @param string $access_code
	 * @return mixed
	 */
	public function loginWebCashier($session_id, $access_code){
		if(!isset($session_id) || !isset($access_code)){
			return null;
		}
		$session_id = strip_tags($session_id);
		$access_code = strip_tags($access_code);
		$modelSession = new SessionModel();
		$modelSession->resetPackages();
		$modelAuthorization = new AuthorizationModel();
		$arrData = $modelAuthorization->loginWebCashier($session_id, $access_code);
		unset($modelSession);
		unset($modelAuthorization);
		return $arrData;
	}
	
	/**
	 * 
	 * get game move
	 * @param int $transaction_id
	 * @param int $session_id
	 * @param string $terminal_name
	 * @return mixed
	 */
	public function gameMove($transaction_id, $session_id, $terminal_name){		
		if(!isset($session_id) || !isset($transaction_id) || !isset($terminal_name)){
			return null;
		}
		try{
			$transaction_id = strip_tags($transaction_id);
			$session_id = strip_tags($session_id);
			$terminal_name = strip_tags($terminal_name);
			require_once MODELS_DIR . DS . 'GameModel.php';
			$modelGame = new GameModel();
			$arrData = $modelGame->listGameMove($transaction_id, $session_id, $terminal_name);
			unset($modelGame);
			return $arrData;
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return null;
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
	public function enableTerminal($pin_code, $status, $mac_address){
		$pin_code = strip_tags($pin_code);
		$status = strip_tags($status);
		$mac_address = strip_tags($mac_address);
		if(!isset($pin_code) || !isset($status) || !isset($mac_address)){
			$errorHelper = new ErrorHelper();
			$mail_message = "Parameters for enable terminal are not sent!!! <br /> Pin code: " . $pin_code . " <br /> Status: " . $status . " <br /> Mac address: " . $mac_address;
			$log_message = "Parameters for enable terminal are not sent!!! Pin code: " . $pin_code . " Status: " . $status . " Mac address: " . $mac_address;
			$errorHelper->serviceError($mail_message, $log_message);
			return INTERNAL_ERROR;
		}
		if($status != "C" && $status != "R"){
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = "Parameter status is not correct for enable terminal!!! Status: " . $status;
			$errorHelper->serviceError($mail_message, $log_message);
			return INTERNAL_ERROR;
		}
		try{	
			require_once MODELS_DIR . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			return $modelAuthorization->enableTerminal($pin_code, $status, $mac_address);
		}catch(Zend_Exception $ex){		
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return INTERNAL_ERROR;
		}
	}
}