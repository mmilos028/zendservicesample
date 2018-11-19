<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once "services" . DS . "AuthorizationManager.php";
require_once "services" . DS . "CashierManager.php";
require_once "services" . DS . "AccountManager.php";
require_once "services" . DS . "ReportManager.php";
require_once "services" . DS . "TicketTerminalCashierManager.php";
//require_once "services" . DS . "outcomebet_integration" . DS . "OutcomebetManager.php";
require_once "services" . DS . "HighScoreManager.php";

/**
 *
 * Web site web service main calls ...
 *
 */

class TerminalIntegrationRest_IndexQueryParamController extends Zend_Controller_Action {

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
				"\n\n /onlinecasinoservice/terminal-integration-rest/index-query-param " .
                "\n\n\n AUTHORIZATION \n" .
				"\n\n test " .
				"\n\n test-ip-address " .
				"\n\n login-with-web-site(web_site_session_id, version, ip_address) " .
				"\n\n login-old(username, password, barcode, mac_address, version,
                        ip_address, game_client_type, device_affiliate_id, general_purpose_mac_address,
                        registered_affiliate) " .
                "\n\n login(username, password, barcode, mac_address, version,
                        ip_address, game_client_type, device_affiliate_id,
                        general_purpose_mac_address, registered_affiliate) " .
                "\n\n login-external-integration(token, ip_address) " .
                "\n\n logout(session_id) " .
                "\n\n logout-bo(session_id) " .
                "\n\n games(game_client_type, mac_address) " .
                "\n\n countries(ip_address) " .
                "\n\n currency-affiliate(affiliate_name) " .
                "\n\n login-cashier(session_id, access_code, mac_address, general_purpose) " .
                "\n\n login-web-cashier(session_id, access_code) " .
                "\n\n game-move(transaction_id, session_id, terminal_name) " .
                "\n\n enable-terminal(pin_code, status, mac_address) " .
                "\n\n connect-vlt-and-io-card(pin_code, serial_number) " .
                "\n\n member-card-login(username, password, barcode, ip_address, version,
                        mac_address, general_purpose_mac_address, device_affiliate_id) " .
                "\n\n get-ggl-encrypted-token(player_id) " .
                "\n\n open-betting-session(pc_session_id) " .
                "\n\n login-fun(browser_session) " .
                "\n\n logout-fun(browser_session) " .
                "\n\n open-session-by-browser(session_id) " .
                "\n\n open-sport-bet-session(pc_session_id, ip_address) " .
                "\n\n open-bet-kiosk-session(pc_session_id, ip_address) " .
                "\n\n open-memo-bet-session(pc_session_id, ip_address) " .
                "\n\n close-sport-betting-window(pc_session_id) " .
                "\n\n set-terminal-for-affiliate(affiliate_name, mac_address) " .
                "\n\n open-anonymous-session(ip_address) " .
                "\n\n check-terminal-date-code(mac_address) " .
                "\n\n get-vivo-gaming-token(session_id, player_id, credits) " .
                "\n\n open-shop-integration-session(pc_session_id, ip_address) " .
                "\n\n close-shop-integration-session(session_id) " .
                "\n\n get-shop-integration-balance(subject_id) " .
                "\n\n\n CASHIER MANAGER \n" .
                "\n\n reset(session_id) " .
                "\n\n usb-credit-transfer(session_id, credits) " .
                "\n\n\n ACCOUNT MANAGER \n" .
                "\n\n new-account(session_id, affiliate_id, username, password, email,
	                    first_name, last_name, birthday, zip, phone, city,
                        address, country, currency) " .
                "\n\n update-player(backoffice_session_id, player_id, email, banned,
                        zip, phone, address, birthday, first_name,
                        last_name, city, country) " .
                "\n\n validate-player(username) " .
                "\n\n reset-player-password(backoffice_session_id, player_id, old_password, new_password) " .
                "\n\n player-details(backoffice_session_id, player_id) " .
                "\n\n player-sessions(player_username) " .
                "\n\n recycler-balance-report(serial_number, mac_address) " .
                "\n\n recycler-balance-top-transactions-report(recycler_name, mac_address) " .
                "\n\n\n TICKET TERMINAL CASHIER MANAGER \n" .
                "\n\n set-shop-cart(xml_content, terminal_mac, buyer_username) " .
                "\n\n list-categories(affiliate_id) " .
                "\n\n list-products-for-affiliate(affiliate_id, mac_address) " .
                "\n\n get-player-allowed-withdrawal(player_id) " .
                "\n\n get-affiliate-parameters(affiliate_id) " .
                "\n\n\n OUTCOMEBET MANAGER \n" .
                /*
                "\n\n outcomebet-list-games() " .
                "\n\n outcomebet-start-game(game_id, affiliate_id, player_id, player_username, currency) " .
                "\n\n outcomebet-create-bank-group(affiliate_id, currency) " .
                "\n\n outcomebet-apply-settings-template(affiliate_id, currency) " .
                "\n\n outcomebet-create-player(player_id, player_username, affiliate_id) " .
                "\n\n outcomebet-create-game-session(game_id, player_id, restore_policy, static_host) " .
                "\n\n outcomebet-create-game-demo-session(game_id, bank_group_id, start_balance, static_host) " .
                "\n\n outcomebet-close-session(session_id, player_id) " .
                "\n\n outcomebet-get-session(session_id) " .
                */
                "\n\n\n HIGHSCORE MANAGER \n" .
                "\n\n list-high-score(session_id, sort_method, page_number, hits_per_page) " .
                "\n\n add-high-score(subject_id, name, score, like_flag) " .
                "\n\n check-score(subject_id) " .
                "\n\n check-score-position(session_id, score) "
                ;
				exit($message);
			}
		}
	}

    public function indexAction(){
        try {

            if(!$this->hasParam('operation')){
                $response = array(
					"status"=>NOK,
					"message"=>NOK_INVALID_DATA
				);
				exit(Zend_Json::encode($response));
            }

            $operation = $this->getRequest()->getParam('operation', null);

            $web_site_session_id = $this->getRequest()->getParam('web_site_session_id', null);
            $pc_session_id = $this->getRequest()->getParam('pc_session_id', null);
            $affiliate_name = $this->getRequest()->getParam('affiliate_name', null);
            $barcode = $this->getRequest()->getParam('barcode', null);
            $affiliate_id = $this->getRequest()->getParam('affiliate_id', null);
            $ip_address = $this->getRequest()->getParam('ip_address', null);
            $session_id = $this->getRequest()->getParam('session_id', null);
            $username = $this->getRequest()->getParam('username', null);
            $password = $this->getRequest()->getParam('password', null);
            $mac_address = $this->getRequest()->getParam('mac_address', null);
            $version = $this->getRequest()->getParam('version', null);
            $country = $this->getRequest()->getParam('country', null);
            $city = $this->getRequest()->getParam('city', null);
            $game_client_type = $this->getRequest()->getParam('game_client_type', null);
            $device_affiliate_id = $this->getRequest()->getParam('device_affiliate_id', null);
            $general_purpose_mac_address = $this->getRequest()->getParam('general_purpose_mac_address', null);
            $registered_affiliate = $this->getRequest()->getParam('registered_affiliate', null);
            $access_code = $this->getRequest()->getParam('access_code', null);
            $token = $this->getRequest()->getParam('token', null);
            $general_purpose = $this->getRequest()->getParam('general_purpose', null);
            $terminal_name = $this->getRequest()->getParam('terminal_name', null);
            $pin_code = $this->getRequest()->getParam('pin_code', null);
            $player_username = $this->getRequest()->getParam('player_username', null);
            $page_number = $this->getRequest()->getParam('page_number', null);
            $player_id = $this->getRequest()->getParam('player_id', null);
            $currency = $this->getRequest()->getParam('currency', null);
            $email = $this->getRequest()->getParam('email', null);
            $first_name = $this->getRequest()->getParam('first_name', null);
            $name = $this->getRequest()->getParam('name', null);
            $last_name = $this->getRequest()->getParam('last_name', null);
            $status = $this->getRequest()->getParam('status', null);
            $birthday = $this->getRequest()->getParam('birthday', null);
            $zip = $this->getRequest()->getParam('zip', null);
            $serial_number = $this->getRequest()->getParam('serial_number', null);
            $browser_session = $this->getRequest()->getParam('browser_session', null);
            $phone = $this->getRequest()->getParam('phone', null);
            $subject_id = $this->getRequest()->getParam('subject_id', null);
            $address = $this->getRequest()->getParam('address', null);
            $backoffice_session_id = $this->getRequest()->getParam('backoffice_session_id', null);
            $banned = $this->getRequest()->getParam('banned', null);
            $old_password = $this->getRequest()->getParam('old_password', null);
            $new_password = $this->getRequest()->getParam('new_password', null);
            $recycler_name = $this->getRequest()->getParam('recycler_name', null);
            $xml_content = $this->getRequest()->getParam('xml_content', null);
            $terminal_mac = $this->getRequest()->getParam('terminal_mac', null);
            $buyer_username = $this->getRequest()->getParam('buyer_username', null);
            $game_id = $this->getRequest()->getParam('game_id', null);
            $restore_policy = $this->getRequest()->getParam('restore_policy', null);
            $static_host = $this->getRequest()->getParam('static_host', null);
            $bank_group_id = $this->getRequest()->getParam('bank_group_id', null);
            $start_balance = $this->getRequest()->getParam('start_balance', null);
            $sort_method = $this->getRequest()->getParam('sort_method', null);
            $hits_per_page = $this->getRequest()->getParam('hits_per_page', null);
            $score = $this->getRequest()->getParam('score', null);
            $like_flag = $this->getRequest()->getParam('like_flag', null);
            $credits = $this->getRequest()->getParam('credits', null);
            $transaction_id = $this->getRequest()->getParam('transaction_id', null);

            switch(strtolower($operation)){
                //AUTHORIZATION
                case 'test':
                    AuthorizationManager::test();
                    break;
                case 'test-ip-address':
                    AuthorizationManager::testIpAddress();
                    break;
                case 'login-with-web-site':
                    AuthorizationManager::loginWithWebSite($web_site_session_id, $version, $ip_address);
                    break;
                case 'login-old':
                    AuthorizationManager::loginOld($username, $password, $barcode, $mac_address, $version,
                        $ip_address, $game_client_type, $device_affiliate_id, $general_purpose_mac_address,
                        $registered_affiliate);
                    break;
                case 'login':
                    AuthorizationManager::login($username, $password, $barcode, $mac_address, $version,
                        $ip_address, $game_client_type, $device_affiliate_id,
                        $general_purpose_mac_address, $registered_affiliate);
                    break;
                case 'login-external-integration':
                    AuthorizationManager::loginExternalIntegration($token, $ip_address);
                    break;
                case 'logout':
                    AuthorizationManager::logout($session_id);
                    break;
                case 'logout-bo':
                    AuthorizationManager::logoutBo($session_id);
                    break;
                case 'games':
                    AuthorizationManager::games($game_client_type, $mac_address);
                    break;
                case 'countries':
                    AuthorizationManager::countries($ip_address);
                    break;
                case 'currency-affiliate':
                    AuthorizationManager::currency_affiliate($affiliate_name);
                    break;
                case 'login-cashier':
                    AuthorizationManager::loginCashier($session_id, $access_code, $mac_address, $general_purpose);
                    break;
                case 'login-web-cashier':
                    AuthorizationManager::loginWebCashier($session_id, $access_code);
                    break;
                case 'game-move':
                    AuthorizationManager::gameMove($transaction_id, $session_id, $terminal_name);
                    break;
                case 'enable-terminal':
                    AuthorizationManager::enableTerminal($pin_code, $status, $mac_address);
                    break;
                case 'connect-vlt-and-io-card':
                    AuthorizationManager::connectVltAndIoCard($pin_code, $serial_number);
                    break;
                case 'member-card-login':
                    AuthorizationManager::memberCardLogin($username, $password, $barcode, $ip_address, $version,
                        $mac_address, $general_purpose_mac_address, $device_affiliate_id);
                    break;
                case 'get-ggl-encrypted-token':
                    AuthorizationManager::getGglEncryptedToken($player_id);
                    break;
                case 'open-betting-session':
                    AuthorizationManager::openBettingSession($pc_session_id);
                    break;
                case 'login-fun':
                    AuthorizationManager::loginFun($browser_session);
                    break;
                case 'logout-fun':
                    AuthorizationManager::logoutFun($browser_session);
                    break;
                case 'open-session-by-browser':
                    AuthorizationManager::openSessionByBrowser($session_id);
                    break;
                case 'open-sport-bet-session':
                    AuthorizationManager::openSportBetSession($pc_session_id, $ip_address);
                    break;
                case 'open-bet-kiosk-session':
                    AuthorizationManager::openBetKioskSession($pc_session_id, $ip_address);
                    break;
                case 'open-memo-bet-session':
                    AuthorizationManager::openMemoBetSession($pc_session_id, $ip_address);
                    break;
                case 'close-sport-betting-window':
                    AuthorizationManager::closeSportBettingWindow($pc_session_id);
                    break;
                case 'set-terminal-for-affiliate':
                    AuthorizationManager::setTerminalForAffiliate($affiliate_name, $mac_address);
                    break;
                case 'open-anonymous-session':
                    AuthorizationManager::openAnonymousSession($ip_address);
                    break;
                case 'check-terminal-date-code':
                    AuthorizationManager::checkTerminalDateCode($mac_address);
                    break;
                case 'get-vivo-gaming-token':
                    AuthorizationManager::getVivoGamingToken($session_id, $player_id, $credits);
                    break;
                case 'open-shop-integration-session':
                    AuthorizationManager::openShopIntegrationSession($pc_session_id, $ip_address);
                    break;
                case 'close-shop-integration-session':
                    AuthorizationManager::closeShopIntegrationSession($session_id);
                    break;
                case 'get-shop-integration-balance':
                    AuthorizationManager::getShopIntegrationBalance($subject_id);
                    break;
                //CASHIER
                case 'reset':
                    CashierManager::reset($session_id);
                    break;
                case 'usb-credit-transfer':
                    CashierManager::usbCreditTransfer($session_id, $credits);
                    break;
                //ACCOUNT
                case 'new-account':
                    AccountManager::newAccount($session_id, $affiliate_id, $username, $password, $email,
	                    $first_name, $last_name, $birthday, $zip, $phone, $city,
                        $address, $country, $currency);
                    break;
                case 'update-account':
                    AccountManager::updatePlayer($backoffice_session_id, $player_id, $email, $banned,
                        $zip, $phone, $address, $birthday, $first_name,
                        $last_name, $city, $country);
                    break;
                case 'validate-player':
                    AccountManager::validatePlayer($username);
                    break;
                case 'reset-player-password':
                    AccountManager::resetPlayerPassword($backoffice_session_id, $player_id, $old_password, $new_password);
                    break;
                case 'player-details':
                    AccountManager::playerDetails($backoffice_session_id, $player_id);
                    break;
                //REPORT
                case 'player-sessions':
                    ReportManager::playerSessions($player_username);
                    break;
                case 'recycler-balance-report':
                    ReportManager::recyclerBalanceReport($serial_number, $mac_address);
                    break;
                case 'recycler-balance-top-transactions-report':
                    ReportManager::recyclerBalanceTopTransactionsReport($recycler_name, $mac_address);
                    break;
                //TICKET TERMINAL CASHIER
                case 'set-shop-cart':
                    TicketTerminalCashierManager::setShopCart($xml_content, $terminal_mac, $buyer_username);
                    break;
                case 'list-categories':
                    TicketTerminalCashierManager::listCategories($affiliate_id);
                    break;
                case 'list-products-for-affiliate':
                    TicketTerminalCashierManager::listProductsForAffiliate($affiliate_id, $mac_address);
                    break;
                case 'get-player-allowed-withdrawal':
                    TicketTerminalCashierManager::getPlayerAllowedWithdrawal($player_id);
                    break;
                case 'get-affiliate-parameters':
                    TicketTerminalCashierManager::getAffiliateParameters($affiliate_id);
                    break;
                //OUTCOMEBET
                /*
                case 'outcomebet-list-games':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->listGames();
                    break;
                case 'outcomebet-start-game':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->startGame($game_id, $affiliate_id, $player_id, $player_username, $currency);
                    break;
                case 'outcomebet-create-bank-group':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->createBankGroup($affiliate_id, $currency);
                    break;
                case 'outcomebet-apply-settings-template':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->applySettingsTemplate($affiliate_id, $currency);
                    break;
                case 'outcomebet-create-player':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->createPlayer($player_id, $player_username, $affiliate_id);
                    break;
                case 'outcomebet-create-game-session':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->createGameSession($game_id, $player_id, $restore_policy, $static_host);
                    break;
                case 'outcomebet-create-game-demo-session':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->createGameDemoSession($game_id, $bank_group_id, $start_balance, $static_host);
                    break;
                case 'outcomebet-close-session':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->closeSession($session_id, $player_id);
                    break;
                case 'outcomebet-get-session':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->getSession($session_id);
                    break;
                */
                //HIGH SCORE
                case 'list-high-score':
                    HighScoreManager::listHighScore($session_id, $sort_method, $page_number, $hits_per_page);
                    break;
                case 'add-high-score':
                    HighScoreManager::addHighScore($subject_id, $name, $score, $like_flag);
                    break;
                case 'check-score':
                    HighScoreManager::checkScore($subject_id);
                    break;
                case 'check-score-position':
                    HighScoreManager::checkScorePosition($session_id, $score);
                    break;
                default:
                    $response = array(
                        "status"=>NOK,
                        "message"=>INTERNAL_ERROR_MESSAGE
                    );
                    exit(Zend_Json::encode($response));
            }

        }catch(Zend_Exception $ex1){
            $response = array(
                "status"=>NOK,
                "message"=>$ex1->getMessage()
            );
			exit(Zend_Json::encode($response));
        }
    }
}