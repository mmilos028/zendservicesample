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

class TerminalIntegrationRest_IndexController extends Zend_Controller_Action {


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
				"\n /onlinecasinoservice/terminal-integration-rest " .
                "\n /onlinecasinoservice/terminal-integration-rest/index " .
                "\n /onlinecasinoservice/terminal-integration-rest/index-query-param " .
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

            $json_message = file_get_contents('php://input');

            $json_object = json_decode($json_message);

            if(!isset($json_object->operation)){
                $response = array(
					"status"=>NOK,
					"message"=>NOK_INVALID_DATA
				);
				exit(Zend_Json::encode($response));
            }

            switch(strtolower($json_object->operation)){
                // AUTHORIZATION
                case 'test':
                    AuthorizationManager::test();
                    break;
                case 'test-ip-address':
                    AuthorizationManager::testIpAddress();
                    break;
                case 'login-with-web-site':
                    AuthorizationManager::loginWithWebSite($json_object->web_site_session_id, $json_object->version, $json_object->ip_address);
                    break;
                case 'login-old':
                    AuthorizationManager::loginOld($json_object->username, $json_object->password, $json_object->barcode, $json_object->mac_address, $json_object->version,
                        $json_object->ip_address, $json_object->game_client_type, $json_object->device_affiliate_id, $json_object->general_purpose_mac_address,
                        $json_object->registered_affiliate);
                    break;
                case 'login':
                    AuthorizationManager::login($json_object->username, $json_object->password, $json_object->barcode, $json_object->mac_address, $json_object->version,
                        $json_object->ip_address, $json_object->game_client_type, $json_object->device_affiliate_id,
                        $json_object->general_purpose_mac_address, $json_object->registered_affiliate);
                    break;
                case 'login-external-integration':
                    AuthorizationManager::loginExternalIntegration($json_object->token, $json_object->ip_address);
                    break;
                case 'logout':
                    AuthorizationManager::logout($json_object->session_id);
                    break;
                case 'logout-bo':
                    AuthorizationManager::logoutBo($json_object->session_id);
                    break;
                case 'games':
                    AuthorizationManager::games($json_object->game_client_type, $json_object->mac_address);
                    break;
                case 'countries':
                    AuthorizationManager::countries($json_object->ip_address);
                    break;
                case 'currency-affiliate':
                    AuthorizationManager::currency_affiliate($json_object->affiliate_name);
                    break;
                case 'login-cashier':
                    AuthorizationManager::loginCashier($json_object->session_id, $json_object->access_code, $json_object->mac_address, $json_object->general_purpose);
                    break;
                case 'login-web-cashier':
                    AuthorizationManager::loginWebCashier($json_object->session_id, $json_object->access_code);
                    break;
                case 'game-move':
                    AuthorizationManager::gameMove($json_object->transaction_id, $json_object->session_id, $json_object->terminal_name);
                    break;
                case 'enable-terminal':
                    AuthorizationManager::enableTerminal($json_object->pin_code, $json_object->status, $json_object->mac_address);
                    break;
                case 'connect-vlt-and-io-card':
                    AuthorizationManager::connectVltAndIoCard($json_object->pin_code, $json_object->serial_number);
                    break;
                case 'member-card-login':
                    AuthorizationManager::memberCardLogin($json_object->username, $json_object->password, $json_object->barcode, $json_object->ip_address, $json_object->version,
                        $json_object->mac_address, $json_object->general_purpose_mac_address, $json_object->device_affiliate_id);
                    break;
                case 'get-ggl-encrypted-token':
                    AuthorizationManager::getGglEncryptedToken($json_object->player_id);
                    break;
                case 'open-betting-session':
                    AuthorizationManager::openBettingSession($json_object->pc_session_id);
                    break;
                case 'login-fun':
                    AuthorizationManager::loginFun($json_object->browser_session);
                    break;
                case 'logout-fun':
                    AuthorizationManager::logoutFun($json_object->browser_session);
                    break;
                case 'open-session-by-browser':
                    AuthorizationManager::openSessionByBrowser($json_object->session_id);
                    break;
                case 'open-sport-bet-session':
                    AuthorizationManager::openSportBetSession($json_object->pc_session_id, $json_object->ip_address);
                    break;
                case 'open-bet-kiosk-session':
                    AuthorizationManager::openBetKioskSession($json_object->pc_session_id, $json_object->ip_address);
                    break;
                case 'open-memo-bet-session':
                    AuthorizationManager::openMemoBetSession($json_object->pc_session_id, $json_object->ip_address);
                    break;
                case 'close-sport-betting-window':
                    AuthorizationManager::closeSportBettingWindow($json_object->pc_session_id);
                    break;
                case 'set-terminal-for-affiliate':
                    AuthorizationManager::setTerminalForAffiliate($json_object->affiliate_name, $json_object->mac_address);
                    break;
                case 'open-anonymous-session':
                    AuthorizationManager::openAnonymousSession($json_object->ip_address);
                    break;
                case 'check-terminal-date-code':
                    AuthorizationManager::checkTerminalDateCode($json_object->mac_address);
                    break;
                case 'get-vivo-gaming-token':
                    AuthorizationManager::getVivoGamingToken($json_object->session_id, $json_object->player_id, $json_object->credits);
                    break;
                case 'open-shop-integration-session':
                    AuthorizationManager::openShopIntegrationSession($json_object->pc_session_id, $json_object->ip_address);
                    break;
                case 'close-shop-integration-session':
                    AuthorizationManager::closeShopIntegrationSession($json_object->session_id);
                    break;
                case 'get-shop-integration-balance':
                    AuthorizationManager::getShopIntegrationBalance($json_object->subject_id);
                    break;
                // CASHIER
                case 'reset':
                    CashierManager::reset($json_object->session_id);
                    break;
                case 'usb-credit-transfer':
                    CashierManager::usbCreditTransfer($json_object->session_id, $json_object->credits);
                    break;
                // ACCOUNT
                case 'new-account':
                    AccountManager::newAccount($json_object->session_id, $json_object->affiliate_id, $json_object->username, $json_object->password, $json_object->email,
	                    $json_object->first_name, $json_object->last_name, $json_object->birthday, $json_object->zip, $json_object->phone, $json_object->city,
                        $json_object->address, $json_object->country, $json_object->currency);
                    break;
                case 'update-account':
                    AccountManager::updatePlayer($json_object->backoffice_session_id, $json_object->player_id, $json_object->email, $json_object->banned,
                        $json_object->zip, $json_object->phone, $json_object->address, $json_object->birthday, $json_object->first_name,
                        $json_object->last_name, $json_object->city, $json_object->country);
                    break;
                case 'validate-player':
                    AccountManager::validatePlayer($json_object->username);
                    break;
                case 'reset-player-password':
                    AccountManager::resetPlayerPassword($json_object->backoffice_session_id, $json_object->player_id, $json_object->old_password, $json_object->new_password);
                    break;
                case 'player-details':
                    AccountManager::playerDetails($json_object->backoffice_session_id, $json_object->player_id);
                    break;
                // REPORT
                case 'player-sessions':
                    ReportManager::playerSessions($json_object->player_username);
                    break;
                case 'recycler-balance-report':
                    ReportManager::recyclerBalanceReport($json_object->serial_number, $json_object->mac_address);
                    break;
                case 'recycler-balance-top-transactions-report':
                    ReportManager::recyclerBalanceTopTransactionsReport($json_object->recycler_name, $json_object->mac_address);
                    break;
                // TICKET TERMINAL CASHIER
                case 'set-shop-cart':
                    TicketTerminalCashierManager::setShopCart($json_object->xml_content, $json_object->terminal_mac, $json_object->buyer_username);
                    break;
                case 'list-categories':
                    TicketTerminalCashierManager::listCategories($json_object->affiliate_id);
                    break;
                case 'list-products-for-affiliate':
                    TicketTerminalCashierManager::listProductsForAffiliate($json_object->affiliate_id, $json_object->mac_address);
                    break;
                case 'get-player-allowed-withdrawal':
                    TicketTerminalCashierManager::getPlayerAllowedWithdrawal($json_object->player_id);
                    break;
                case 'get-affiliate-parameters':
                    TicketTerminalCashierManager::getAffiliateParameters($json_object->affiliate_id);
                    break;
                // OUTCOMEBET
                /*
                case 'outcomebet-list-games':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->listGames();
                    break;
                case 'outcomebet-start-game':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->startGame($json_object->game_id, $json_object->affiliate_id, $json_object->player_id, $json_object->player_username, $json_object->currency);
                    break;
                case 'outcomebet-create-bank-group':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->createBankGroup($json_object->affiliate_id, $json_object->currency);
                    break;
                case 'outcomebet-apply-settings-template':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->applySettingsTemplate($json_object->affiliate_id, $json_object->currency);
                    break;
                case 'outcomebet-create-player':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->createPlayer($json_object->player_id, $json_object->player_username, $json_object->affiliate_id);
                    break;
                case 'outcomebet-create-game-session':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->createGameSession($json_object->game_id, $json_object->player_id, $json_object->restore_policy, $json_object->static_host);
                    break;
                case 'outcomebet-create-game-demo-session':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->createGameDemoSession($json_object->game_id, $json_object->bank_group_id, $json_object->start_balance, $json_object->static_host);
                    break;
                case 'outcomebet-close-session':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->closeSession($json_object->session_id, $json_object->player_id);
                    break;
                case 'outcomebet-get-session':
                    $outcomebetManager = new OutcomebetManager();
                    $outcomebetManager->getSession($json_object->session_id);
                    break;
                */
                // HIGH SCORE
                case 'list-high-score':
                    HighScoreManager::listHighScore($json_object->ession_id, $json_object->sort_method, $json_object->page_number, $json_object->hits_per_page);
                    break;
                case 'add-high-score':
                    HighScoreManager::addHighScore($json_object->subject_id, $json_object->name, $json_object->score, $json_object->like_flag);
                    break;
                case 'check-score':
                    HighScoreManager::checkScore($json_object->subject_id);
                    break;
                case 'check-score-position':
                    HighScoreManager::checkScorePosition($json_object->session_id, $json_object->score);
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