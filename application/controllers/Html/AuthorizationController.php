<?php
/**
	Game client HTML5 implementation of Authorization controller
*/
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';

/**
	Game client HTML5 implementation of Authorization controller
*/

class Html_AuthorizationController extends Zend_Controller_Action{

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
				"\n\n /onlinecasinoservice/html_authorization " .
                "\n\n login(site_name, username, password) " .
                "\n\n logout(session_id) " .
				"\n\n login-external-integration (token)" .
				"\n\n list-anonymous-games (affiliate_name)" .
				"\n\n open-anonymous-session()" .
                "\n\n check-active-mobile-session(pc_session_id)" .
                "\n\n open-sport-bet-session(pc_session_id)" .
                "\n\n open-bet-kiosk-session(pc_session_id)" .
                "\n\n close-sport-betting-window(pc_session_id)" .
                "\n\n open-memo-bet-session(pc_session_id)" .
                "\n\n list-player-favourite-games(player_id)"
				;
				exit($message);
			}
		}
	}

	public function indexAction(){
		$config = Zend_Registry::get('config');
		if($config->onlinecasinoserviceWSDLMode == "false"){ // not in wsdl mode
			header('Location: http://www.google.com/');
		}
	}

    /**
	 *
	 * Opens player session for players coming from external client integration to our system
	 * Returns list of games and parameters
	 * Game (param1, param2, ... paramN) and so on
	 * @param string $token
	 * @return mixed
	 */
	public function loginExternalIntegrationAction()
    {
        $token = trim(strip_tags($this->getRequest()->getParam('token', null)));
        $ip_address = IPHelper::getRealIPAddress();
        try {
            if (!isset($token)) {
                $message = array(
                    "status" => NOK,
                    "message" => NOK_INVALID_DATA
                );
                exit(Zend_Json::encode($message));
            }
            //if there are ip addresses with , separated as CSV string
            $ip_addresses = explode(",", $ip_address);
            $ip_address = $ip_addresses[0];
            require_once MODELS_DIR . DS . 'ExternalIntegrationModel.php';
            $modelExternalIntegration = new ExternalIntegrationModel();
            //DEBUG THIS PART OF CODE
            /*
                $errorHelper = new ErrorHelper();
                $message = "loginExternalIntegration (token = {$token}, ip_address = {$ip_address})";
                $errorHelper->serviceError($message, $message);
            */
            $res = $modelExternalIntegration->loginPlayerByToken($token, $ip_address, YES);
            if($res['status'] == OK){
				if(strlen($res['message']) != 0){
					return array("status"=>NOK, "message"=>$res['message']);
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
							"game_order"=>$game['game_order'],
                            "pot_name"=> null,
                            "has_jackpot_enabled"=>$game['jp_on_off'] == '1' ? YES : NO
						);
					}
                    require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
                    $helperCurrencyList = new CurrencyListHelper();
			        $currency_code = $helperCurrencyList->getCurrencyCode($res['currency']);
					$new_res = array("status"=>OK, "token"=>$res['token'], "ip_address"=>$res['ip_address'], "username"=>$res['username'],
                        "player_id"=>$res['player_id'], "credits"=>$res['credits'],	"currency"=>$res['currency'], "currency_code"=>$currency_code,
                        "site_session_id"=>$res['site_session_id'], "pc_session_id"=>$res['pc_session_id'],
                        //empty parameters
                        "device"=>"", "aff_id_out"=>"", "player_verif_status"=>"", "language_settings"=>"", "terms_and_conditions"=>array(),
                        //list of games
                        "games"=>$list_games
                    );
                    exit(Zend_Json::encode($new_res));
				}
			}else{
                $message = array(
                    "status" => NOK,
                    "message" => INTERNAL_ERROR_MESSAGE
                );
                exit(Zend_Json::encode($message));
			}
        } catch (Zend_Exception $ex) {
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "HTML_AuthorizationController::loginExternalIntegration(token = {$token}, ip_address = {$ip_address}) <br /> Detected IP address = {$detected_ip_address} <br /> Exception message: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper = new ErrorHelper();
            $errorHelper->siteError($message, $message);
            $message = array(
                "status" => NOK,
                "message" => NOK_INVALID_DATA
            );
            exit(Zend_Json::encode($message));
        }
    }

    /**
	 * siteLogin method
	 * @param string $site_name
	 * @param string $username
	 * @param string $password
     * @param string $version
	 * @return mixed
	 */
	public function loginAction()
    {
        $site_name = trim(strip_tags($this->getRequest()->getParam('site_name', null)));
        $username = trim(strip_tags($this->getRequest()->getParam('username', null)));
        $password = trim(strip_tags($this->getRequest()->getParam('password', null)));
        $version = trim(strip_tags($this->getRequest()->getParam('version', 'MOBILE')));
        $ip_address = IPHelper::getRealIPAddress();
		if( ( !isset($site_name) || (!isset($username) || !isset($password)) && !isset($ip_address) ) ){
			$message = array(
                "status" => NOK,
                "message" => NOK_INVALID_DATA
            );
            exit(Zend_Json::encode($message));
		}
		try{
			$site_name = strip_tags($site_name);
			$username = strip_tags($username);
			$password = strip_tags($password);
			$ip_address = strip_tags($ip_address);
            if(IPHelper::testPrivateIP($ip_address)){
                $ip_address = "212.200.99.50";
            }
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrData = $modelWebSite->siteLogin($site_name, $username, $password, "", $version, $ip_address,
			"", "", "", "");

            $gameArrData = $modelWebSite->checkPlayerCountryLimits($arrData['player_id'], null, $ip_address);
            if ($gameArrData['status'] != OK) {
                $message = array(
                    "status" => NOK,
                    "message" => NOK_EXCEPTION
                );
                exit(Zend_Json::encode($message));
            }
            $deposit_limits = array();
            foreach ($gameArrData['deposit_limit_cursor'] as $deposit_limit) {
                $deposit_limits[] = array(
                    'deposit_type' => $deposit_limit['deposit_type'],
                    'deposit_limit' => $deposit_limit['deposit_limit']
                );
            }

            require_once MODELS_DIR . DS . 'PlayerModel.php';
            $modelPlayer = new PlayerModel();
            //get player details
            $playerDetails = $modelPlayer->getPlayerDetailsMalta(null, $arrData['player_id']);
            if ($playerDetails['status'] != OK) {
                $message = array(
                    "status" => NOK,
                    "message" => NOK_EXCEPTION
                );
                exit(Zend_Json::encode($message));
            }
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            $site_settings = $modelMerchant->findSiteSettings($arrData['player_id']);
            if ($site_settings['status'] != OK) {
                $message = array(
                    "status" => NOK,
                    "message" => NOK_EXCEPTION
                );
                exit(Zend_Json::encode($message));
            }
            $details = $playerDetails['details'];
            $playerUsername = $details['user_name'];
            $playerEmail = $details['email'];
            //$arrData['language_settings'] = $site_settings['language_settings'];

			if($arrData['status'] == NOK){
				if($arrData['message'] == NOK_EXCEPTION){
					$message = array(
                        "status" => NOK,
                        "message" => NOK_EXCEPTION
                    );
                    exit(Zend_Json::encode($message));
				}
				else if($arrData['message'] == WRONG_USERNAME_PASSWORD){
                    $message = array(
                        "status" => NOK,
                        "message" => WRONG_USERNAME_PASSWORD
                    );
                    exit(Zend_Json::encode($message));
				}
				else if($arrData['message'] == WRONG_PHYSICAL_ADDRESS){
                    $message = array(
                        "status" => NOK,
                        "message" => WRONG_PHYSICAL_ADDRESS
                    );
                    exit(Zend_Json::encode($message));
				}
				else if($arrData['message'] == PLAYER_BANNED_LIMIT){
                    $message = array(
                        "status" => NOK,
                        "message" => PLAYER_BANNED_LIMIT
                    );
                    exit(Zend_Json::encode($message));
				}
				else if($arrData['message'] == LOGIN_TOO_MANY_TIMES){
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
					$playerUnlockLink, $supportLink, $termsLink, $contactLink, $arrData['language_settings']);
                    $title = $playerMailRes['mail_title'];
                    $content = $playerMailRes['mail_message'];
					$loggerMessage =  "Player with player_id: {$arrData['player_id']} and player username: {$playerUsername} on mail address: {$playerMailAddress} has not received mail with his unlock account link.";
					WebSiteEmailHelper::sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
					$playerSmtpServer, $title, $content, $title, $title, $loggerMessage, $siteImagesLocation);
                    $message = array(
                        "status" => NOK,
                        "message" => LOGIN_TOO_MANY_TIMES
                    );
                    exit(Zend_Json::encode($message));
				}
				else{
					$message = array(
                        "status" => NOK,
                        "message" => NOK_EXCEPTION
                    );
                    exit(Zend_Json::encode($message));
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
          $gamesArr[$i++] = array(
              "game_group"=>$g['game_group'],
              "group_order"=>$g['group_order'],
              "game"=>$g['game'],
              "game_id"=>$g['game_id'],
              "page"=>$g['page'],
              "game_order"=>$g['game_order'],
              "has_jackpot_enabled"=>$g['jp_on_off'] == '1' ? YES : NO,
							"game_provider_name" => $g['game_provider_name'],
							"game_provider_id" => $g['game_provider_id']
          );
			}

            //check terms and conditions (CBC and GGL)
			$t_and_c_status = $modelWebSite->checkTermsAndConditions($arrData['site_session_id']);
			$terms_and_conditions = $t_and_c_status;

			//get currency code, find currency text and put to output currency code
            require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
			$helperCurrencyList = new CurrencyListHelper();
			$currency_code = $helperCurrencyList->getCurrencyCode($arrData['currency']);
			////
            $message = array(
                "status" => OK,
                "username" => $details['user_name'],
                "email" => $details['email'],
                "site_session_id" => $arrData['site_session_id'],
                "session" => $arrData['pc_session_id'],
                "credits" => NumberHelper::format_double($arrData['credits']),
                "player_id" => $arrData['player_id'],
                "affiliate_id" => $arrData['aff_id_out'],
                "language_settings" => $details['bo_default_language'],
                "currency_code"=> $currency_code,
                "currency" => $arrData['currency'],
                "terms_and_conditions"=>$terms_and_conditions,
                //
                "device" => $arrData['device'],
                "player_verif_status" => $arrData['player_verif_status'],
                "games"=>$gamesArr,

                "game_limits"=>array(
                    "status"=>$gameArrData['status'],
                    "reg_limit"=>$gameArrData['reg_limit'],
                    "game_limit"=>$gameArrData['game_limit'],
                    "bonus_limit"=>$gameArrData['bonus_limit'],
                    "status_out"=>$gameArrData['status_out'],
                    "country_code"=>$gameArrData['country_code'],
                    "affiliate_id"=>$gameArrData['affiliate_id'],
                ),
                "deposit_limits"=>$deposit_limits
            );
            exit(Zend_Json::encode($message));
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "Error while player login to his account in web site. <br /> Player username: {$username} <br /> Player site login in web site exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$message = array(
                "status" => NOK,
                "message" => NOK_EXCEPTION
            );
            exit(Zend_Json::encode($message));
		}
	}

    /**
	 * logout user from games
     * @param string $session_id
	 * @return mixed
	 */
	public function logoutAction(){
		$session_id = trim(strip_tags($this->getRequest()->getParam('session_id', null)));
		try{
			if(strlen($session_id) == 0 || !isset($session_id) || $session_id == 'null'){
				$message = array(
					"status"=>NOK,
					"message"=>PARAMETER_MISSING_MESSAGE,
					"session_id"=>$session_id
				);
				exit(Zend_Json::encode($message));
			}
			$session_id = strip_tags($session_id);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$status = $modelWebSite->siteLogoutPC($session_id);
            $message = array(
                "status" => OK,
                "result" => "1"
            );
			exit(Zend_Json::encode($message));
		}catch(Zend_Exception $ex){
			/*if there was unknown exception type*/
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
            $mail_message = $log_message = "Html_AuthorizationController::logoutAction(session_id = {$session_id}) <br /> Detected IP Address = {$detected_ip_address} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
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
     * list all games for anonymous session
     * @param string $affiliate_name
     * @return mixed
     */
    public function listAnonymousGamesAction(){
        $affiliate_name = trim(strip_tags($this->getRequest()->getParam('affiliate_name', null)));
        $ip_address = IPHelper::getRealIPAddress();
        try{
            if (!isset($affiliate_name) || strlen($affiliate_name) == 0 || $affiliate_name == 'null') {
                $message = array(
                    "status" => NOK,
                    "message" => NOK_INVALID_DATA
                );
                exit(Zend_Json::encode($message));
            }
            $ip_address = strip_tags($ip_address);
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
            $i = 0;
            foreach($res['list_games'] as $g){
								$gamesArr[$i] = array(
									"game_group"=>$g['game_group'],
									"group_order"=>$g['group_order'],
									"game"=>$g['game'],
									"game_id"=>$g['game_id'],
									"page"=>$g['page'],
									"game_order"=>$g['game_order'],
									"has_jackpot_enabled"=>$g['jp_on_off'] == '1' ? YES : NO,
									"game_provider_name" => $g['game_provider_name'],
									"game_provider_id" => $g['game_provider_id']
								);
                $i++;
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
                        'deposit_limit' => $deposit_limit['deposit_limit']
                    );
                }
            }

            $message = array(
                "status"=>OK,
                "games"=>$gamesArr,
                "game_limits"=>array(
                    "status"=>$gameArrData['status'],
                    "reg_limit"=>$gameArrData['reg_limit'],
                    "game_limit"=>$gameArrData['game_limit'],
                    "bonus_limit"=>$gameArrData['bonus_limit'],
                    "status_out"=>$gameArrData['status_out'],
                    "country_code"=>$gameArrData['country_code'],
                    "affiliate_id"=>$gameArrData['affiliate_id'],
                ),
                "deposit_limits"=>$deposit_limits
            );
            exit(Zend_Json::encode($message));
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $mail_message = 'Error while listing anonymous games on web site exception: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->siteError($mail_message, $log_message);
            $message = array(
                "status" => NOK,
                "message" => NOK_EXCEPTION
            );
            exit(Zend_Json::encode($message));
        }
    }

    /**
	 * list all games for anonymous session
	 * @param string $player_id
     * @param string $ip_address
	 * @return mixed
	 */
	public function listPlayerFavouriteGamesAction(){
		try{
            $player_id = trim(strip_tags($this->getRequest()->getParam('player_id', null)));
            $ip_address = IPHelper::getRealIPAddress();
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
                        "has_jackpot_enabled" => $g['jp_on_off'] == '1' ? YES : NO
                    );
                }
                $message = array(
                    "status" => OK,
                    "games" => $gamesArr
                );
                exit(Zend_Json::encode($message));
            }else{
                $message = array("status"=>NOK, "message"=>NOK_EXCEPTION);
                exit(Zend_Json::encode($message));
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error while listing player favourite games on web site exception: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$message = array("status"=>NOK, "message"=>NOK_EXCEPTION);
            exit(Zend_Json::encode($message));
		}
	}

    /**
	 * open anonymous session
	 * @return mixed
	 */
	public function openAnonymousSessionAction(){
        $ip_address = IPHelper::getRealIPAddress();
		try{
			$ip_address = strip_tags($ip_address);
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$session_id = $modelWebSite->openAnonymousSession($ip_address);
			unset($modelWebSite);
            $message = array(
                "status"=>OK,
                "session_id"=>$session_id
            );
            exit(Zend_Json::encode($message));
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error while opening anonymous session on web site <br /> Player site login in web site exception: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$message = array(
                "status" => NOK,
                "message" => NOK_EXCEPTION
            );
            exit(Zend_Json::encode($message));
		}
	}

    /**
     * check active mobile session for HTML5
     * @param string $pc_session_id
     * @return mixed
     */
    public function checkActiveMobileSessionAction(){
        $pc_session_id = trim(strip_tags($this->getRequest()->getParam('pc_session_id', null)));
        try{
            require_once MODELS_DIR . DS . 'AuthorizationModel.php';
            $modelAuthorization = new AuthorizationModel();
            $result = $modelAuthorization->checkActiveMobileSession($pc_session_id);
            if($result['status'] == OK){
                $message = array(
                    "status"=>OK,
                    "result"=>$result['status_out']
                );
            }else{
                $message = array(
                    "status"=>NOK,
                    "message"=>$result['error_message']
                );
            }
            exit(Zend_Json::encode($message));
        }catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
            $mail_message = "Error Html_AuthorizationController::checkActiveMobileSessionAction(session_id = {$pc_session_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $log_message = "Error Html_AuthorizationController::checkActiveMobileSessionAction(session_id = {$pc_session_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper = new ErrorHelper();
            $errorHelper->siteError($mail_message, $log_message);
            $message = array(
                "status"=>NOK,
                "message"=>INTERNAL_ERROR_MESSAGE
            );
            exit(Zend_Json::encode($message));
        }
    }

    /**
     *
     * Get Sport Betting Session Opened - MAXBET integration
     * @param int $pc_session_id
     * @return mixed
     */
    public function openSportBetSessionAction(){
        $pc_session_id = trim(strip_tags($this->getRequest()->getParam('pc_session_id', null)));
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0 || $pc_session_id == 'null'){
            $message = array(
				"status"=>NOK,
				"message"=>PARAMETER_MISSING_MESSAGE,
				"pc_session_id"=>$pc_session_id
			);
			exit(Zend_Json::encode($message));
        }
        $ip_address = IPHelper::getRealIPAddress();
        //if there are ip addresses with , separated as CSV string
        $ip_addresses = explode(",", $ip_address);
        $ip_address = $ip_addresses[0];
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "openSportBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address} )";
        $errorHelper->sportBettingIntegrationError($mail_message, $log_message);
        */
        try{
            $ip_address = strip_tags($ip_address);
            require_once MODELS_DIR . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->openMaxBetSession($pc_session_id, $ip_address);
            if($result['status'] == NOK){
                $message = array(
                    "status"=>NOK,
                    "message"=>INTERNAL_ERROR_MESSAGE
                );
                exit(Zend_Json::encode($message));
            }else{
                $message = array(
                    "status"=>OK,
                    "pc_session_id"=>$result['pc_session_id'],
                    "session_id_out"=>$result['session_id_out']
                );
                exit(Zend_Json::encode($message));
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error Html_AuthorizationController::openSportBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->sportBettingIntegrationError($message, $message);
            $message = array(
				"status"=>NOK,
				"message"=>INTERNAL_ERROR_MESSAGE
			);
			exit(Zend_Json::encode($message));
        }
    }

    /**
     *
     * Get Sport Betting Session Opened - BETKIOSK integration
     * @param int $pc_session_id
     * @return mixed
     */
    public function openBetKioskSessionAction(){
        $pc_session_id = trim(strip_tags($this->getRequest()->getParam('pc_session_id', null)));
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0 || $pc_session_id == 'null'){
            $message = array(
				"status"=>NOK,
				"message"=>PARAMETER_MISSING_MESSAGE,
				"pc_session_id"=>$pc_session_id
			);
			exit(Zend_Json::encode($message));
        }
        $ip_address = IPHelper::getRealIPAddress();
        //if there are ip addresses with , separated as CSV string
        $ip_addresses = explode(",", $ip_address);
        $ip_address = $ip_addresses[0];
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "openBetKioskSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address} )";
        $errorHelper->sportBettingIntegrationError($mail_message, $log_message);
        */
        try{
            $ip_address = strip_tags($ip_address);
            require_once MODELS_DIR . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->openBetKioskSession($pc_session_id, $ip_address);
            if($result['status'] == NOK){
                $message = array(
                    "status"=>NOK,
                    "message"=>INTERNAL_ERROR_MESSAGE
                );
                exit(Zend_Json::encode($message));
            }else{
                $message = array(
                    "status"=>OK,
                    "pc_session_id"=>$result['pc_session_id'],
                    "session_id_out"=>$result['session_id_out']
                );
                exit(Zend_Json::encode($message));
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error Html_AuthorizationController::openBetKioskSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->sportBettingIntegrationError($message, $message);
            $message = array(
				"status"=>NOK,
				"message"=>INTERNAL_ERROR_MESSAGE
			);
			exit(Zend_Json::encode($message));
        }
    }

    /**
     * Close sport betting window - X button event for closing window of sport betting game
     * @param $pc_session_id
     * @return array
     */
    public function closeSportBettingWindowAction(){
        $pc_session_id = trim(strip_tags($this->getRequest()->getParam('pc_session_id', null)));
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0 || $pc_session_id == 'null'){
            $message = array(
				"status"=>NOK,
				"message"=>PARAMETER_MISSING_MESSAGE,
				"pc_session_id"=>$pc_session_id
			);
			exit(Zend_Json::encode($message));
        }
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "closeSportBettingWindow(pc_session_id = {$pc_session_id} )";
        $errorHelper->sportBettingIntegrationError($mail_message, $log_message);
        */
        try{
            require_once MODELS_DIR . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->closeSportBettingGameWindowForMobilePlatform($pc_session_id);
            if($result['status'] == NOK){
                $message = array(
                    "status"=>NOK,
                    "message"=>INTERNAL_ERROR_MESSAGE
                );
                exit(Zend_Json::encode($message));
            }else{
                $listGamesArray = array();
                foreach($result['list_games'] as $game){
                    $listGamesArray[] = array(
                        "game_group"=>$game['game_group'],
                        "group_order"=>$game['group_order'],
                        "game"=>$game['game'],
                        "game_id"=>$game['game_id'],
                        "page"=>$game['page'],
                        "game_order"=>$game['game_order']
                    );
                }
                $message = array(
                    "status"=>OK,
                    "session_id"=>$result['session_id'],
                    "status_out"=>$result['status_out'],
                    "credits"=>$result['credits'],
                    "currency"=>$result['currency'],
                    "username"=>$result['username'],
                    "list_games"=>$listGamesArray
                );
                exit(Zend_Json::encode($message));
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error Html_AuthorizationController::closeSportBettingWindow(pc_session_id = {$pc_session_id}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->sportBettingIntegrationError($message, $message);
            $message = array(
				"status"=>NOK,
				"message"=>INTERNAL_ERROR_MESSAGE
			);
			exit(Zend_Json::encode($message));
        }
    }

    /**
     *
     * Get Sport Betting Session Opened - MemoBet integration
     * @param int $pc_session_id
     * @return mixed
     */
    public function openMemoBetSessionAction(){
        $pc_session_id = trim(strip_tags($this->getRequest()->getParam('pc_session_id', null)));
        if(!isset($pc_session_id) || strlen($pc_session_id) == 0 || $pc_session_id == 'null'){
            $message = array(
				"status"=>NOK,
				"message"=>PARAMETER_MISSING_MESSAGE,
				"pc_session_id"=>$pc_session_id
			);
			exit(Zend_Json::encode($message));
        }
        $ip_address = IPHelper::getRealIPAddress();
        //if there are ip addresses with , separated as CSV string
        $ip_addresses = explode(",", $ip_address);
        $ip_address = $ip_addresses[0];
        //DEBUG MESSAGES
        /*
        $errorHelper = new ErrorHelper();
        $mail_message = $log_message = "openMemoBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address} )";
        $errorHelper->sportBettingIntegrationError($mail_message, $log_message);
        */
        try{
            $ip_address = strip_tags($ip_address);
            require_once MODELS_DIR . DS . 'SportBettingIntegrationModel.php';
            $modelSportBettingIntegration = new SportBettingIntegrationModel();
            $result = $modelSportBettingIntegration->openMemoBetGameSession($pc_session_id, $ip_address);
            if($result['status'] == NOK){
                $message = array(
                    "status"=>NOK,
                    "message"=>INTERNAL_ERROR_MESSAGE
                );
                exit(Zend_Json::encode($message));
            }else{
                $message = array(
                    "status"=>OK,
                    "pc_session_id"=>$result['pc_session_id'],
                    "session_id_out"=>$result['session_id_out']
                );
                exit(Zend_Json::encode($message));
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "SPORT BET Integration Error <br />Error Html_AuthorizationController::openMemoBetSession(pc_session_id = {$pc_session_id}, ip_address = {$ip_address}) web service <br /> Detected IP Address: {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->sportBettingIntegrationError($message, $message);
            $message = array(
				"status"=>NOK,
				"message"=>INTERNAL_ERROR_MESSAGE
			);
			exit(Zend_Json::encode($message));
        }
    }
}
