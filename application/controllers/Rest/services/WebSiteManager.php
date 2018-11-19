<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class WebSiteManager {

	/**
	 *
	 * setTimeModified on web site action (user changes page)
	 * @return mixed
	 */
	public static function setTimeModified($site_session_id, $pc_session_id){
		if(!isset($site_session_id) || !isset($pc_session_id)){
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			exit($json_message);
		}
		$site_session_id = strip_tags($site_session_id);
		$pc_session_id = strip_tags($pc_session_id);
		try{
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$result = $modelWebSite->setTimeModified($site_session_id, $pc_session_id);
			if($result['status'] == OK){
                $json_message = Zend_Json::encode(array("status"=>OK, "pc_session_id"=>$result['pc_session_id'], "site_session_id"=>$result['site_session_id']));
                exit($json_message);
            }else{
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>$result['message']));
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

	/**
	* validateSiteSession method by using site session id
	* @return mixed
	*/
	public static function validateSiteSession($site_session_id, $pc_session_id){
		if(!isset($site_session_id) || strlen($site_session_id) == 0 || $site_session_id == "null" || !isset($pc_session_id) || strlen($pc_session_id) == 0 || $pc_session_id == "null"){
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
			exit($json_message);
		}
		$site_session_id = strip_tags($site_session_id);
		$pc_session_id = strip_tags($pc_session_id);
		try{
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$result = $modelWebSite->validateSiteSession($site_session_id, $pc_session_id);
            if($result["status"] != OK){
                $json_message = Zend_Json::encode(array("status" => NOK, "message" => NOK_EXCEPTION));
                exit($json_message);
            }else {
                if ($result["yes_no_status"] == YES) {
                    $json_message = Zend_Json::encode(array("status" => OK, "result" => YES, "remaining_seconds" => $result['remaining_seconds']));
                    exit($json_message);
                } else {
                    $json_message = Zend_Json::encode(array("status" => OK, "result" => NO));
                    exit($json_message);
                }
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "Error while validating site session from web site. <br /> Site session id: {$site_session_id} <br /> Player validate site session on web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

	/**
	 * list all games for anonymous session
	 * @return mixed
	 */
	public static function listAnonymousGames($affiliate_name, $ip_address){
		try{
            if($ip_address == ""){
                $ip_address = IPHelper::getRealIPAddress();
            }
            if(IPHelper::testPrivateIP($ip_address)){
                $ip_address = "212.200.99.50";
            }
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once  MODELS_DIR . DS . 'GameModel.php';
			$modelGames = new GameModel();
			$res = $modelGames->getOrderedGames($affiliate_name);

            $gamesArr = array();
			foreach($res['list_games'] as $g){
                $gamesArr[] = array(
                    "game_group"=>$g['game_group'],
                    "group_order"=>$g['group_order'],
                    "game"=>$g['game'],
                    "game_id"=>$g['game_id'],
                    "page"=>$g['page'],
                    "game_order"=>$g['game_order'],
                    "pot_name"=> null,
                    "has_jackpot_enabled"=>$g['jp_on_off'] == '1' ? YES : NO,
                    "game_provider_name" => $g['game_provider_name'],
                    "game_provider_id" => $g['game_provider_id']
                );
			}

            require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
            $gameArrData = $modelWebSite->checkPlayerCountryLimits(null, $affiliate_name, $ip_address);
            $deposit_limits = array();
            if($gameArrData['status'] != OK){
                //return array("status"=>NOK, "message"=>NOK_EXCEPTION);
                $gameArrData = array(
                    "reg_limit" => "",
                    "game_limit" => "",
                    "bonus_limit" => "",
                    "status_out" => "",
                    "country_code" => "",
                    "affiliate_id" => "",
                );
                $deposit_limits = array();
            }else {
                $deposit_limits = array();
                foreach ($gameArrData['deposit_limit_cursor'] as $deposit_limit) {
                    $deposit_limits[] = array(
                        'deposit_type' => $deposit_limit['deposit_type'],
                        'deposit_limit' => NumberHelper::convert_double($deposit_limit['deposit_limit']),
                        'deposit_limit_formatted' => NumberHelper::format_double($deposit_limit['deposit_limit'])
                    );
                }
            }

            $json_message = Zend_Json::encode(
		        array(
                    "status"=>OK,
                    "games"=>$gamesArr,
                    "game_limits"=>array(
                        "status"=>$gameArrData['status'],
                        "reg_limit"=> NumberHelper::convert_double($gameArrData['reg_limit']),
                        "reg_limit_formatted"=> NumberHelper::format_double($gameArrData['reg_limit']),
                        "game_limit"=>NumberHelper::convert_double($gameArrData['game_limit']),
                        "game_limit_formatted"=>NumberHelper::format_double($gameArrData['game_limit']),
                        "bonus_limit"=>NumberHelper::convert_double($gameArrData['bonus_limit']),
                        "bonus_limit_formatted"=>NumberHelper::format_double($gameArrData['bonus_limit']),
                        "status_out"=>$gameArrData['status_out'],
                        "country_code"=>$gameArrData['country_code'],
                        "affiliate_id"=>$gameArrData['affiliate_id'],
                    ),
                    "deposit_limits"=>$deposit_limits
                )
            );
            exit($json_message);

		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error while listing anonymous games on web site exception: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

	/**
	 * list all games for anonymous session for mobile platform
	 * @return mixed
	 */
	public static function listAnonymousGamesForMobile($affiliate_name, $ip_address){
		try{
            if($ip_address == ""){
                $ip_address = IPHelper::getRealIPAddress();
            }
            if(IPHelper::testPrivateIP($ip_address)){
                $ip_address = "212.200.99.50";
            }
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once  MODELS_DIR . DS . 'GameModel.php';
			$modelGames = new GameModel();
			$res = $modelGames->getMobileOrderedGames($affiliate_name);

            $gamesArr = array();
			foreach($res['list_games'] as $g){
                $gamesArr[] = array(
                    "game_group"=>$g['game_group'],
                    "group_order"=>$g['group_order'],
                    "game"=>$g['game'],
                    "game_id"=>$g['game_id'],
                    "page"=>$g['page'],
                    "game_order"=>$g['game_order'],
                    "pot_name"=> null,
                    "has_jackpot_enabled"=>$g['jp_on_off'] == '1' ? YES : NO,
                    "game_provider_name" => $g['game_provider_name'],
                    "game_provider_id" => $g['game_provider_id']
                );
			}

            require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
            $gameArrData = $modelWebSite->checkPlayerCountryLimits(null, $affiliate_name, $ip_address);
            $deposit_limits = array();
            if($gameArrData['status'] != OK){
                //return array("status"=>NOK, "message"=>NOK_EXCEPTION);
                $gameArrData = array(
                    "reg_limit" => "",
                    "game_limit" => "",
                    "bonus_limit" => "",
                    "status_out" => "",
                    "country_code" => "",
                    "affiliate_id" => "",
                );
                $deposit_limits = array();
            }else {
                $deposit_limits = array();
                foreach ($gameArrData['deposit_limit_cursor'] as $deposit_limit) {
                    $deposit_limits[] = array(
                        'deposit_type' => $deposit_limit['deposit_type'],
                        'deposit_limit' => NumberHelper::convert_double($deposit_limit['deposit_limit']),
                        'deposit_limit_formatted' => NumberHelper::format_double($deposit_limit['deposit_limit']),
                    );
                }
            }

            $json_message = Zend_Json::encode(
		        array(
                    "status"=>OK,
                    "games"=>$gamesArr,
                    "game_limits"=>array(
                        "status"=>$gameArrData['status'],
                        "reg_limit"=> NumberHelper::convert_double($gameArrData['reg_limit']),
                        "reg_limit_formatted"=> NumberHelper::format_double($gameArrData['reg_limit']),
                        "game_limit"=>NumberHelper::convert_double($gameArrData['game_limit']),
                        "game_limit_formatted"=>NumberHelper::format_double($gameArrData['game_limit']),
                        "bonus_limit"=>NumberHelper::convert_double($gameArrData['bonus_limit']),
                        "bonus_limit_formatted"=>NumberHelper::format_double($gameArrData['bonus_limit']),
                        "status_out"=>$gameArrData['status_out'],
                        "country_code"=>$gameArrData['country_code'],
                        "affiliate_id"=>$gameArrData['affiliate_id'],
                    ),
                    "deposit_limits"=>$deposit_limits
                )
            );
            exit($json_message);

		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error while listing anonymous games on mobile web site exception: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

    /**
	 * list all games for anonymous session
     * @param $player_id
     * @param $ip_address
	 * @return mixed
	 */
	public static function listPlayerFavouriteGames($player_id, $ip_address){
		try{
            if($ip_address == ""){
                $ip_address = IPHelper::getRealIPAddress();
            }
            if(IPHelper::testPrivateIP($ip_address)){
                $ip_address = "212.200.99.50";
            }
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once  MODELS_DIR . DS . 'GameModel.php';
			$modelGames = new GameModel();
			$res = $modelGames->listMyFavouriteGames($player_id);
            if($res['status'] == OK) {
                $gamesArr = array();
                foreach ($res['list_games'] as $g) {
                    $gamesArr[] = array(
                        "game_id" => $g['game_id'],
                        "game_name" => $g['game_name'],
                        "page" => $g['page'],
                        "game_order" => $g['game_order'],
                        "has_jackpot_enabled" => $g['jp_on_off'] == '1' ? YES : NO,
						"integration_game_id" => $g['integration_game_id']
                    );
                }
                $json_message = Zend_Json::encode(
                    array(
                        "status" => OK,
                        "games" => $gamesArr
                    )
                );
                exit($json_message);
            }else{
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			    exit($json_message);
            }

		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error while listing player favourite games on web site exception: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

	/**
	 * open anonymous session
	 * @return mixed
	 */
	public static function openAnonymousSession($ip_address)
    {
		try{
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$session_id = $modelWebSite->openAnonymousSession($ip_address);
			$json_message = Zend_Json::encode(array("status"=>OK, "session_id"=>$session_id));
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error while opening anonymous session on web site <br /> Player site login in web site exception: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

	/**
	* siteLogoutPC method by using pc session
	* @return mixed
	*/
	public static function siteLogoutPC($session_id){
		//returns session_id or -1 or non-ssl-connection;
		if(!isset($session_id)){
            $json_message = Zend_Json::encode(array("status" => NOK, "message" => NOK_INVALID_DATA));
            exit($json_message);
		}
		try{
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$status = $modelWebSite->siteLogoutPC($session_id);
			if($status){
                $json_message = Zend_Json::encode(array("status" => OK, "status_out" => true));
                exit($json_message);
			}
			else{
				$json_message = Zend_Json::encode(array("status" => OK, "status_out" => false));
                exit($json_message);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "Error while player logout with pc session from his account in web site. <br /> Player session id: {$session_id} <br /> Player site login in web site exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

	/**
	* siteLogout method by using pc session or site session
	* @return mixed
	*/
	public static function siteLogout($site_session_id, $pc_session_id){
		if(!isset($site_session_id) || !isset($pc_session_id)){
			$json_message = Zend_Json::encode(array("status" => NOK, "message" => NOK_INVALID_DATA));
            exit($json_message);
		}
		try{
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$status = $modelWebSite->siteLogout($site_session_id, $pc_session_id);
			if($status){
                $json_message = Zend_Json::encode(array("status" => OK, "status_out" => true));
                exit($json_message);
			}
			else{
				$json_message = Zend_Json::encode(array("status" => OK, "status_out" => false));
                exit($json_message);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "Error while player logout from his account in web site. <br /> Site session id: {$site_session_id} <br /> PC session id: {$pc_session_id} <br /> Player site login in web site exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

	/**
	* siteLoginPC method by using pc session
	* @return mixed
	*/
	public static function siteLoginPC($username, $password, $mac_address, $version, $ip_address, $country, $city){
		if(!isset($username) || !isset($password)){
			$json_message = Zend_Json::encode(array("status" => NOK, "message" => NOK_INVALID_DATA));
            exit($json_message);
		}
		try{
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrData = $modelWebSite->siteLoginPC($username, $password, $mac_address, $version, $ip_address, $country, $city);
			if($arrData == INTERNAL_ERROR){
                $json_message = Zend_Json::encode(array("status" => OK, "status_out" => INTERNAL_ERROR));
                exit($json_message);
			}
			if($arrData == WRONG_USERNAME_PASSWORD){
                $json_message = Zend_Json::encode(array("status" => OK, "status_out" => WRONG_USERNAME_PASSWORD));
                exit($json_message);
			}
			if($arrData == WRONG_PHYSICAL_ADDRESS){
                $json_message = Zend_Json::encode(array("status" => OK, "status_out" => WRONG_PHYSICAL_ADDRESS));
                exit($json_message);
			}
			if($arrData == PLAYER_BANNED_LIMIT){
                $json_message = Zend_Json::encode(array("status" => OK, "status_out" => PLAYER_BANNED_LIMIT));
                exit($json_message);
			}
			if($arrData == LOGIN_TOO_MANY_TIMES){
                $json_message = Zend_Json::encode(array("status" => OK, "status_out" => LOGIN_TOO_MANY_TIMES));
                exit($json_message);
			}
			//cursor of games content: ID, NAME, p_name, p_value, STATUS
			$tempListGames = array();
			//load tempList with names of games
			foreach($arrData['list_of_games'] as $row){
				if($row['name'] != "dummy" || strlen($row['name']) != 0 || $row['name'] != null){
					$tempListGames[] = $row['name'];
				}
			}
			$tempListGames = array_unique($tempListGames);
			$listGames = array();
			for($i=0;$i<count($tempListGames);$i++){
				if($tempListGames[$i] != null || strlen($tempListGames[$i]) != 0)
					$listGames[] = $tempListGames[$i];
			}

            $json_message = Zend_Json::encode(array("status"=>OK,
                "result"=>
                array(
                    'pc_session_id'=>$arrData['session_id'],
                    'credits'=> NumberHelper::convert_double($arrData['credits']),
			        'credits_formatted'=> NumberHelper::format_double($arrData['credits']),
			        'currency'=>$arrData['currency'],
                    'player_id'=>$arrData['player_id'],
                    'list_of_games'=>$listGames
                )
            ));
			exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "Error while player login with pc session to his account in web site. <br /> Player username: {$username} <br /> Player mac address: {$mac_address} <br /> Player ip address: {$ip_address} <br /> Player site login in web site exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

	/**
	 * siteLogin method
	 * @return mixed
	 */
	public static function siteLogin($site_name, $username, $password, $mac_address, $version, $ip_address, $country, $city, $device_aff_id, $gp_mac_address){
		if( ( !isset($site_name) || (!isset($username) || !isset($password)) && !isset($mac_address) ) ){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_INVALID_DATA));
			exit($json_message);
		}
		try{
			$site_name = strip_tags($site_name);
			$username = strip_tags($username);
			$password = strip_tags($password);
			$mac_address = strip_tags($mac_address);
			$version = strip_tags($version);
			$ip_address = strip_tags($ip_address);
			$country = strip_tags($country);
			$city = strip_tags($city);
			$device_aff_id = strip_tags($device_aff_id);
			$gp_mac_address = strip_tags($gp_mac_address);
			//if there are ip addresses with , separated as CSV string
            if($ip_address == ""){
                $ip_address = IPHelper::getRealIPAddress();
            }
            if(IPHelper::testPrivateIP($ip_address)){
                $ip_address = "212.200.99.50";
            }
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrData = $modelWebSite->siteLogin($site_name, $username, $password, $mac_address, $version, $ip_address,
			$country, $city, $device_aff_id, $gp_mac_address);
            if($arrData['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
			    exit($json_message);
            }
            if($arrData['status'] == OK) {
                $gameArrData = $modelWebSite->checkPlayerCountryLimits($arrData['player_id'], null, $ip_address);
                if ($gameArrData['status'] != OK) {
                    $json_message = Zend_Json::encode(array("status" => NOK, "message" => NOK_EXCEPTION));
                    exit($json_message);
                }
                $deposit_limits = array();
                foreach ($gameArrData['deposit_limit_cursor'] as $deposit_limit) {
                    $deposit_limits[] = array(
                        'deposit_type' => $deposit_limit['deposit_type'],
                        'deposit_limit' => NumberHelper::convert_double($deposit_limit['deposit_limit']),
                        'deposit_limit_formatted' => NumberHelper::format_double($deposit_limit['deposit_limit']),
                    );
                }
                require_once MODELS_DIR . DS . 'PlayerModel.php';
                $modelPlayer = new PlayerModel();
                //get player details
                $playerDetails = $modelPlayer->getPlayerDetailsMalta(null, $arrData['player_id']);
                if ($playerDetails['status'] != OK) {
                    $json_message = Zend_Json::encode(array("status" => NOK, "message" => NOK_EXCEPTION));
                    exit($json_message);
                }
                require_once MODELS_DIR . DS . 'MerchantModel.php';
                $modelMerchant = new MerchantModel();
                $site_settings = $modelMerchant->findSiteSettings($arrData['player_id']);
                if ($site_settings['status'] != OK) {
                    $json_message = Zend_Json::encode(array("status" => NOK, "message" => NOK_EXCEPTION));
                    exit($json_message);
                }
                $details = $playerDetails['details'];
                $playerUsername = $details['user_name'];
                $playerEmail = $details['email'];
                //$arrData['language_settings'] = $site_settings['language_settings'];
                $language_settings = $details['bo_default_language'];
            }else{
				if($arrData['message'] == NOK_EXCEPTION){
					$json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
			        exit($json_message);
				}
				else if($arrData['message'] == WRONG_USERNAME_PASSWORD){
                    $json_message = Zend_Json::encode(array("status"=>OK, "status_out"=> WRONG_USERNAME_PASSWORD));
			        exit($json_message);
				}
				else if($arrData['message'] == WRONG_PHYSICAL_ADDRESS){
                    $json_message = Zend_Json::encode(array("status"=>OK, "status_out"=> WRONG_PHYSICAL_ADDRESS));
			        exit($json_message);
				}
				else if($arrData['message'] == PLAYER_BANNED_LIMIT){
                    $json_message = Zend_Json::encode(array("status"=>OK, "status_out"=> PLAYER_BANNED_LIMIT));
			        exit($json_message);
				}
				else if($arrData['message'] == PLAYER_COUNTRY_PROHIBITED){
                    $json_message = Zend_Json::encode(array("status"=>OK, "status_out"=> PLAYER_COUNTRY_PROHIBITED));
			        exit($json_message);
				}
				else if($arrData['message'] == LOGIN_TOO_MANY_TIMES){
                     require_once MODELS_DIR . DS . 'PlayerModel.php';
                    $modelPlayer = new PlayerModel();
                    //get player details
                    $playerDetails = $modelPlayer->getPlayerDetailsMalta(null, $arrData['player_id']);
                    if ($playerDetails['status'] != OK) {
                        return NOK_EXCEPTION;
                    }
                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $site_settings = $modelMerchant->findSiteSettings($arrData['player_id']);
                    if ($site_settings['status'] != OK) {
                        return NOK_EXCEPTION;
                    }
                    $details = $playerDetails['details'];
                    $playerUsername = $details['user_name'];
                    $playerEmail = $details['email'];
                    $language_settings = $details['bo_default_language'];

					//sends mail to player with link to unlock his account
					//get site settings for player with player_id
					//send mail to player that his account is unlocked procedure
					$playerMailSendFrom = $site_settings['mail_address_from'];
					$playerMailAddress = $playerEmail;
					$playerSmtpServer = $site_settings['smtp_server_ip'];
					$siteImagesLocation = $site_settings['site_image_location'];
					$casinoName = $site_settings['casino_name'];
					$siteLink = $site_settings['site_link'];
					$playerUnlockLink = $site_settings['unlock_url_link'] . "?id=" . $arrData['player_id'];
					$contactLink = $site_settings['contact_url_link'];
					$supportLink = $site_settings['support_url_link'];
					$termsLink = $site_settings['terms_url_link'];
					$playerMailRes = WebSiteEmailHelper::getUnlockPlayerEmailToPlayerContent(
					$playerUsername, $siteImagesLocation, $casinoName, $siteLink,
					$playerUnlockLink, $supportLink, $termsLink, $contactLink, $language_settings);
                    $title = $playerMailRes['mail_title'];
                    $content = $playerMailRes['mail_message'];
					$loggerMessage =  "Player with player_id: {$arrData['player_id']} and player username: {$playerUsername} on mail address: {$playerMailAddress} has not received mail with his unlock account link.";
					WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
					$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
                    $json_message = Zend_Json::encode(array("status"=>OK, "status_out"=> LOGIN_TOO_MANY_TIMES));
			        exit($json_message);
				}
				else{
					$json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
			        exit($json_message);
				}
			}

			/*
			$player_id = $arrData['player_id'];
			require_once  MODELS_DIR . DS . 'GameModel.php';
			$modelGames = new GameModel();
			$res1 = $modelGames->getGames(ENABLED, null, PLAYER, $mac_address, $player_id);
			$res2 = $res1;
			$i = 0;
			$gamesNames = array();
			foreach($arrData['list_games'] as $g){
				if(!in_array($g['name'], $gamesNames)){
					$gamesNames[] = $g['name'];
					$named_pot = "";
					foreach($res1['list_pots'] as $pot){
						if($pot['id'] == $g['id']){
							$named_pot = $pot['p_name'];
							break;
						}
					}
					if($g['name'] != "dummy"){
						$gamesArr[$i++] = new Game($g['id'], $g['name'], $named_pot);
					}
				}
			}
			*/
			$gamesArr = array();
			$i = 0;
			// Za sada zanemarujem jack pot informaciju.
			foreach ($arrData['list_games'] as $g)
			{
			    //$gamesArr[$i++] = new GameGroupOrder($g['game_group'], $g['group_order'], $g['game'], $g['game_id'], $g['page'], $g['game_order'], null);
                $gamesArr[$i++] = array(
                    "game_group"=>$g['game_group'],
                    "group_order"=>$g['group_order'],
                    "game"=>$g['game'],
                    "game_id"=>$g['game_id'],
                    "page"=>$g['page'],
                    "game_order"=>$g['game_order'],
                    "pot_name"=>null,
                    "has_jackpot_enabled"=>$g['jp_on_off'] == '1' ? YES : NO,
                    "game_provider_name" => $g['game_provider_name'],
                    "game_provider_id" => $g['game_provider_id']
                );
			}

            //check terms and conditions (CBC and GGL)
			$t_and_c_status = $modelWebSite->checkTermsAndConditions($arrData['site_session_id']);

			//get currency code, find currency text and put to output currency code
			$helperCurrencyList = new CurrencyListHelper();
			$currency_code = $helperCurrencyList->getCurrencyCode($arrData['currency']);
			////

            $json_message = Zend_Json::encode(array(
                "status" => OK,
                "status_out"=>$arrData['status'],
                "site_session_id"=>$arrData['site_session_id'],
                "pc_session_id"=>$arrData['pc_session_id'],
                "player_username"=>$playerUsername,
                "player_email"=>$playerEmail,
                "credits"=> NumberHelper::convert_double($arrData['credits']),
                "credits_formatted"=> NumberHelper::format_double($arrData['credits']),
                "player_id"=>$arrData['player_id'],
                "list_games"=>$gamesArr,
                "terms_and_conditions"=>$t_and_c_status,
                "currency_code"=>$currency_code,
                "currency"=>$arrData['currency'],
                "language_settings"=>$language_settings,
                "device"=>$arrData['device'],
                "aff_id_out"=>$arrData['aff_id_out'],
                "player_verif_status"=>$arrData['player_verif_status'],
                "game_limits"=>array(
                    "status"=>$gameArrData['status'],
                    "reg_limit"=> NumberHelper::convert_double($gameArrData['reg_limit']),
                    "reg_limit_formatted"=> NumberHelper::format_double($gameArrData['reg_limit']),
                    "game_limit"=>NumberHelper::convert_double($gameArrData['game_limit']),
                    "game_limit_formatted"=>NumberHelper::format_double($gameArrData['game_limit']),
                    "bonus_limit"=>NumberHelper::convert_double($gameArrData['bonus_limit']),
                    "bonus_limit_formatted"=>NumberHelper::format_double($gameArrData['bonus_limit']),
                    "status_out"=>$gameArrData['status_out'],
                    "country_code"=>$gameArrData['country_code'],
                    "affiliate_id"=>$gameArrData['affiliate_id'],
                ),
                "deposit_limits"=>$deposit_limits
            ));
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "Error while player login to his account in web site. <br /> Player username: {$username} <br /> Player site login in web site exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
            exit($json_message);
		}
	}

	/**
	 * listCountries method
	 * @return mixed
	 */
	public static function listCountries(){
		try{
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$cursorData = $modelWebSite->listCountries();
			$arrData = array();
			foreach($cursorData as $row){
                $arrData[] = array(
                    'id' => $row['id'],
                    'name' => StringHelper::filterCountry($row['name'])
                );
			}
            $json_message = Zend_Json::encode(array("status"=>OK, "result"=> $arrData));
			exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error while listing countries in web site. <br /> Player registration on web site exception: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
            exit($json_message);
		}
	}

    /**
     * listCountries method
     * @param $white_label_id
     * @return mixed
     */
    public static function listCountriesAllowedForPlayer($white_label_id){
        try{
            if(strlen($white_label_id) == 0){
                $config = Zend_Registry::get('config');
                $white_label_id = $config->casino_user_id;
            }
            require_once MODELS_DIR . DS . 'WebSiteModel.php';
            $modelWebSite = new WebSiteModel();

            $arrData = $modelWebSite->listRegistrationAllowedCountries($white_label_id);
            if( $arrData['status'] != OK ){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
                exit($json_message);
            }
            $inherited = $arrData['inherited'];
            $i = 0;
            $enabledList = array();
            $disabledList = array();
            foreach($arrData['cursorE'] as $data){
                $enabledList[] = array(
                    'name' => StringHelper::filterCountry($data['name']),
                    'id' => $data['country_code']
                );
                $enabledList[$i]['status'] = 'E';
                $i++;
            }
            $i = 0;
            foreach($arrData['cursorD'] as $data){
                $disabledList[] = array(
                    'name' => StringHelper::filterCountry($data['name']),
                    'id' => $data['country_code']
                );
                $disabledList[$i]['status'] = 'D';
                $i++;
            }
            $response = array(
                "status" => OK,
                "inherited" => $inherited,
                "enabled_countries"=> $enabledList,
                "disabled_countries"=> $disabledList,
            );
            $json_message = Zend_Json::encode($response);
            exit($json_message);
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $mail_message = 'Error while listing countries in web site. <br /> Player registration on web site exception: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->siteError($mail_message, $log_message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
        }
    }


	/**
	 * Currency for promo code
	 * @return mixed
	 */
	public static function currencyForPromoCode($affiliate_name){
		if(!isset($affiliate_name)){
            $json_message = Zend_Json::encode(array("status"=>OK, "status_out"=> false));
            exit($json_message);
		}
		try{
			require_once MODELS_DIR . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$arrData = $modelAuthorization->validateAffName($affiliate_name);
            $json_message = Zend_Json::encode(array("status"=>OK, "result"=> array("currency"=>$arrData["currency"], "affiliate_id"=>$arrData["affiliate_id"])));
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = "<br /> Currency For Promo Code Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
            exit($json_message);
		}
	}

    /**
	 *
	 * Opens player session for players coming from external client integration to our system
	 * Returns list of games and parameters
	 * Game (param1, param2, ... paramN) and so on
	 * @return mixed
	 */
	public static function loginExternalIntegration($token, $ip_address){
		try{
			if(!isset($token) || !isset($ip_address)){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_INVALID_DATA));
                exit($json_message);
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
				$errorHelper->siteAccessLog($message);
			*/
			$res = $modelExternalIntegration->loginPlayerByToken($token, $ip_address, NO);
			if($res['status'] == OK){
				if(strlen($res['message']) != 0){
	        $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>$res['message']));
	        exit($json_message);
				}else{
					$res2 = $modelExternalIntegration->getInternalPlayerId($res['player_id']);
					if($res2['status'] == OK || $res2['internal_player_id'] != "-1") {
						$internal_player_id = $res2['internal_player_id'];
					}else{
						$internal_player_id = "-1";
					}
					//return complete result to game client
					$list_games = array();
					foreach($res['list_games'] as $game){
						$list_games[] = array(
							"game_group"=>$game['game_group'],
							"group_order"=>$game['group_order'],
							"game"=>$game['game'],
							"game_id"=>$game['game_id'],
							"page"=>$game['page'],
							"game_order"=>$game['game_order'],
							"pot_name" => "",
							"has_jackpot_enabled"=>$game['jp_on_off'] == '1' ? YES : NO,
							"game_provider_name" => $game['game_provider_name'],
							"game_provider_id" => $game['game_provider_id']
						);
					}
          $helperCurrencyList = new CurrencyListHelper();
	        $currency_code = $helperCurrencyList->getCurrencyCode($res['currency']);
          $json_message = Zend_Json::encode(array("status"=>OK, "result"=>
          array(
              "status"=>OK,
              "token"=>$res['token'],
              "ip_address"=>$res['ip_address'],
              "username"=>$res['username'],
              "player_id"=>$res['player_id'],
              "credits"=> NumberHelper::convert_double($res['credits']),
              "credits_formatted" => NumberHelper::format_double($res['credits']),
              "currency"=>$res['currency'],
              "currency_code"=>$currency_code,
              "site_session_id"=>$res['site_session_id'],
              "pc_session_id"=>$res['pc_session_id'],
              //empty parameters
              "device"=>"",
              "aff_id_out"=>"",
              "player_verif_status"=>"",
              "language_settings"=>"",
              "terms_and_conditions"=>array(),
              //list of games
              "list_games"=>$list_games,
              "internal_player_id" => $internal_player_id
            )
          ));
          exit($json_message);
				}
			}else{
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>INTERNAL_ERROR));
				exit($json_message);
			}
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$message = "AuthorizationManager::loginExternalIntegration(token = {$token}, ip_address = {$ip_address}) <br /> Detected IP address = {$detected_ip_address} <br /> Exception message: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->siteError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>INTERNAL_ERROR));
			exit($json_message);
		}
	}

	/**
	 * returns number of games per page
	 * @param string $white_label_name
	 * @return mixed
	 */
	public static function getNumberOfGamesPerPage($white_label_name){
		try{
			if(strlen($white_label_name) == 0){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_INVALID_DATA));
		        exit($json_message);
			}
			require_once MODELS_DIR . DS . 'GameModel.php';
			$modelGame = new GameModel();
			$result = $modelGame->getNumberOfGamesPerPage($white_label_name);
			if($result['status'] == OK){
				if($result['game_per_page'] > 0){
					$json_message = Zend_Json::encode(array('status' => OK, "game_per_page"=>NumberHelper::format_integer($result['game_per_page'])));
			        exit($json_message);
				}else{
					$game_per_page = 0;
					$json_message = Zend_Json::encode(array('status' => OK, "game_per_page"=>$game_per_page));
                    exit($json_message);
				}
			}else{
				$json_message = Zend_Json::encode(array('status' => NOK, 'message'=>NOK_EXCEPTION));
				exit($json_message);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error on WebSiteManager::getNumberOfGamesPerPage on web site <br /> Exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}
	
	/**
	 * check if country is prohibited
	 * @param string $affiliate_id
	 * @param string $ip_address
	 * @return mixed
	 */
	public function checkIfCountryIsProhibited($affiliate_id, $ip_address){
		try{
			if(strlen($affiliate_id) == 0 || strlen($ip_address) == 0){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_INVALID_DATA));
				exit($json_message);
			}
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$result = $modelWebSite->checkIfCountryIsProhibited($affiliate_id, $ip_address);
			if($result['status'] == OK){
				$is_country_prohibited = $result['country_is_prohibited'];
				if($is_country_prohibited == "1"){
					//country is not prohibited
					$json_message = Zend_Json::encode(array("status"=>NOK, "country_is_prohibited"=>NO));
					exit($json_message);
				}else {
					//country is prohibited, no registration allowed
					$json_message = Zend_Json::encode(array("status"=>NOK, "country_is_prohibited"=>YES));
					exit($json_message);
				}
			}else{
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
				exit($json_message);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error on WebSiteManager::checkIfCountryIsProhibited(affiliate_id = {$affiliate_id}, ip_address = {$ip_address}) on web site <br /> Exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}
}
