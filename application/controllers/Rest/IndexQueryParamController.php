<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once "services" . DS . "WebSiteManager.php";
require_once "services" . DS . "WebSiteBonusManager.php";
require_once "services" . DS . "WebSiteReportsManager.php";
require_once "services" . DS . "WebSitePlayerAccountManager.php";
require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
require_once "services" . DS . "WebSitePlayerAccountMailsManager.php";
require_once "services" . DS . "WebSiteDocumentManagementManager.php";
require_once "services" . DS . "WebSiteSportIntegrationManager.php";
require_once "services" . DS . "WebSiteGglIntegrationManager.php";
require_once "services" . DS . "WebSiteVivoGamingIntegrationManager.php";
require_once "services" . DS . "WebSitePaysafecardDirectMerchantManager.php";
require_once "services" . DS . "WebSiteWirecardMerchantManager.php";
require_once "services" . DS . "WebSiteMerchantManager.php";
require_once "services" . DS . "WebSiteApcoMerchantManager.php";

/**
 *
 * Web site web service main calls ...
 *
 */

class Rest_IndexQueryParamController extends Zend_Controller_Action {


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
				"\n\n /onlinecasinoservice/rest/index-query-param " .
                "\n\n\n WEB SITE \n" .
				"\n\n set-time-modified(site_session_id, pc_session_id) " .
				"\n\n validate-site-session(site_session_id, pc_session_id) " .
				"\n\n list-anonymous-games(affiliate_name, ip_address) " .
				"\n\n open-anonymous-session(ip_address) " .
                "\n\n site-logout-pc(session_id) " .
                "\n\n site-logout(site_session_id, pc_session_id) " .
                "\n\n site-login-pc(username, password, mac_address, version, ip_address, country, city) " .
                "\n\n site-login-pc(site_name, username, password, mac_address, version, ip_address, country, city, device_aff_id, gp_mac_address) " .
                "\n\n site-login(site_name, username, password, mac_address, version, ip_address, country, city, device_aff_id, gp_mac_address) " .
                "\n\n list-countries() " .
                "\n\n list-countries-allowed-for-player(white_label_id) " .
                "\n\n currency-for-promo-code(affiliate_name) " .
                "\n\n login-external-integration(token, ip_address) " .
                "\n\n\n WEB SITE BONUS \n" .
                "\n\n cancel-bonus-transactions(pc_session_id) " .
                "\n\n check-bonus-code(pc_session_id, bonus_campaign_code, deposit_amount)" .
                "\n\n check-bonus-code-status(pc_session_id, bonus_campaign_code, deposit_amount)" .
                "\n\n list-player-bonus-history(session_id)" .
                "\n\n\n WEB SITE REPORTS \n" .
                "\n\n list-limits(session_id)" .
                "\n\n list-limits-new(session_id)" .
                "\n\n list-player-history(session_id, start_date, end_date, page_number, per_page, column, order)" .
                "\n\n list-player-history-details(session_id, page_number, per_page, column, order)" .
                "\n\n list-player-history-subdetails(session_id, page_number, per_page, column, order)" .
                "\n\n list-credit-transfers(session_id, start_date, end_date, page_number, per_page, column, order)" .
                "\n\n list-available-bonus-campaigns(affiliate_username, player_id)" .
                "\n\n list-player-available-bonus-campaigns(player_id)" .
                "\n\n list-player-active-bonuses-and-promotions(player_id)" .
                "\n\n list-top-won-jackpots(affiliate_name)" .
                "\n\n list-current-jackpot-levels(affiliate_name, currency)" .
                "\n\n list-high-wins(affiliate_name)" .
                "\n\n currency-list-for-new-player(affiliate_id, tid_code)" .
                "\n\n\n WEB SITE PLAYER ACCOUNT \n" .
                "\n\n currency-list-for-new-player(affiliate_id, tid_code)" .
                "\n\n insert-player(affiliate_id, username, password, email, first_name, last_name,
	                    birthday, country, zip, city, street_address1, street_address2, phone,
	                    bank_account, bank_country, swift, iban, receive_email, currency, ip_address,
                        registration_code, tid_code, language)" .
                "\n\n player-details(session_id, ip_address)" .
                "\n\n update-player(session_id, email, first_name, last_name,
	                    birthday, country, zip, city, street_address1, street_address2,
	                    phone_number, bank_account, bank_country, swift, iban, receive_email,
                        ip_address, language)" .
                "\n\n reset-password(session_id, password_old, password_new, ip_address)" .
                "\n\n validate-player(username)" .
                "\n\n validate-player-phone(player_id, player_phone, white_label_name)" .
                "\n\n validate-player-email(player_id, player_email, white_label_name)" .
                "\n\n validate-player-first-name-last-name-birthday(player_id, player_first_name, player_last_name, player_birthday, white_label_name)" .
                "\n\n validate-player-first-name-last-name-address(player_id, player_first_name, player_last_name, player_city,
                        player_address, player_address2, white_label_name)" .
                "\n\n validate-player-first-name-last-name-phone(player_id, player_first_name, player_last_name, player_phone, white_label_name)" .
                "\n\n player-credits(session_id, ip_address)" .
                "\n\n change-terms-and-conditions-for-cbc(session_id)" .
                "\n\n change-terms-and-conditions-for-ggl(session_id)" .
                "\n\n check-terms-and-conditions(session_id)" .
                "\n\n check-temporary-id(username, id)" .
                "\n\n change-password(username, id, password, question, answer)" .
                "\n\n set-security-question-answer(username, password, question, answer)" .
                "\n\n get-security-question(username)" .
                "\n\n responsible-gaming-setup(session_id,
		            monthly_deposit_limit, monthly_deposit_limit_start_date, monthly_deposit_limit_end_date,
		            weekly_deposit_limit, weekly_deposit_start_date, weekly_deposit_end_date,
		            daily_deposit_limit, daily_deposit_start_date, daily_deposit_end_date,
                    monthly_max_loss_limit, monthly_max_loss_start_date, monthly_max_loss_end_date,
		            weekly_max_loss_limit, weekly_max_loss_start_date, weekly_max_loss_end_date,
		            daily_max_loss_limit, daily_max_loss_start_date, daily_max_loss_end_date,
		            max_stake_start_date, max_stake_end_date, max_stake, time_limit_minutes,
		            banned_start_date, banned_end_date, banned_status)" .
                "\n\n responsible-gaming-setup-delay(session_id,
                        monthly_deposit_limit, monthly_deposit_limit_start_date, monthly_deposit_limit_end_date,
                        weekly_deposit_limit, weekly_deposit_start_date, weekly_deposit_end_date,
                        daily_deposit_limit, daily_deposit_start_date, daily_deposit_end_date,
                        monthly_max_loss_limit, monthly_max_loss_start_date, monthly_max_loss_end_date,
                        weekly_max_loss_limit, weekly_max_loss_start_date, weekly_max_loss_end_date,
                        daily_max_loss_limit, daily_max_loss_start_date, daily_max_loss_end_date,
                        max_stake_start_date, max_stake_end_date, max_stake, time_limit_minutes,
                        banned_start_date, banned_end_date, banned_status)" .
                "\n\n ban-player(username, password)" .
                "\n\n unlock-account(player_id)" .
                "\n\n\n WEB SITE PLAYER ACCOUNT MAILS \n" .
                "\n\n forgot-username(name, familyname, birthday, email)" .
                "\n\n send-player-activation-mail(player_id)" .
                "\n\n forgot-password-with-security-answer(username, answer)" .
                "\n\n forgot-password-with-personal-data(username, name, familyname, birthday, email)" .
                "\n\n player-registration-confirmation(hash_id, player_id, ip_address)" .
                "\n\n\n WEB SITE DOCUMENT MANAGEMENT MANAGER \n" .
                "\n\n upload-document(site_session_id, document_site, document_location, document_file_name)" .
                "\n\n list-players-documents(site_session_id, player_id)" .
                "\n\n\n WEB SITE SPORT INTEGRATION \n" .
                "\n\n open-cbc-session(pc_session_id)" .
                "\n\n open-bet-kiosk-session(pc_session_id, ip_address)" .
                "\n\n open-max-bet-session(pc_session_id, ip_address)" .
                "\n\n open-memo-bet-session(pc_session_id, ip_address)" .
                "\n\n close-sport-betting-window(pc_session_id)" .
                "\n\n check-betkiosk-game-status(pc_session_id)" .
                "\n\n\n WEB SITE LDC(GGL) INTEGRATION \n" .
                "\n\n get-encrypted-token(site_session_id)" .
                "\n\n check-ggl-game-status(pc_session_id)" .
                "\n\n\n WEB SITE VIVO GAMING INTEGRATION \n" .
                "\n\n get-vivo-gaming-token(pc_session_id, player_id, credits, provider_id)" .
                "\n\n close-vivo-gaming-integration-session(pc_session_id)" .
                "\n\n\n WEB SITE PAYSAFECARD DIRECT MERCHANT INTEGRATION \n" .
                "\n\n get-paysafecard-payment-purchase-message(site_session_id, pc_session_id, amount, payment_method, payment_method_id, 
                        ip_address, bonus_code, css_template)" .
                "\n\n get-paysafecard-payment-details(payment_id)" .
                "\n\n paysafecard-withdraw-request(pc_session_id, payment_method, payment_method_id, amount, paysafecard_email, 
                        paysafecard_date_of_birth, paysafecard_first_name, paysafecard_last_name, ip_address)" .
                "\n\n\n WEB SITE WIRECARD MERCHANT INTEGRATION \n" .
                "\n\n get-wirecard-payment-purchase-custom-card-message(site_session_id, pc_session_id, amount, payment_method,
                        payment_method_id, ip_address, bonus_code, css_template)" .
                "\n\n get-wirecard-payment-purchase-custom-card-with-token-message(site_session_id, pc_session_id, amount, payment_method,
                        payment_method_id, token_id, ip_address, bonus_code, css_template)" .
                "\n\n get-wirecard-payment-purchase-custom-payment-method-message(site_session_id, pc_session_id, amount, payment_method,
                        payment_method_id, ip_address, bonus_code, css_template)" .
                "\n\n get-wirecard-withdraw-request(pc_session_id, wirecard_transaction_id, token_id,
                        payment_method, payment_method_id, amount, ip_address)" .
                "\n\n\n WEB SITE MERCHANT INTEGRATION - GENERAL CALLS \n" .
                "\n\n get-all-payment-methods()" .
                "\n\n get-transaction-limit-purchase(site_session_id, amount, payment_method, ip_address)" .
                "\n\n get-transaction-limit-payout(site_session_id, amount, payment_method, ip_address)" .
                "\n\n pending-payout-status(site_session_id)" .
                "\n\n is-withdraw-possible(site_session_id, pc_session_id, expected_withdraw_amount, transaction_id)" .
                "\n\n cancel-withdraw(site_session_id, pc_session_id, withdraw_amount, transaction_id)" .
                "\n\n list-payment-limits-for-white-label(site_session_id, currency)" .
                "\n\n set-iban-swift(player_id, swift, iban)" .
                "\n\n get-promotion-code(pc_session_id, promotion_code)" .
                "\n\n\n WEB SITE APCO MERCHANT INTEGRATION \n" .
                "\n\n get-apco-payment-purchase-custom-card-message(site_session_id, pc_session_id, amount, payment_method, payment_method_id, 
                        is_3d_secure, ip_address, bonus_code, css_template)" .
                "\n\n get-apco-purchase-custom-payment-method-message(site_session_id, pc_session_id, amount, payment_method, payment_method_id, 
                            ip_address, bonus_code, css_template)" .
                "\n\n get-apco-last-card-numbers(site_session_id)" .
                "\n\n get-apco-register-credit-card-message(site_session_id, pc_session_id, ip_address)" .
                "\n\n apco-withdraw-request(pc_session_id, apco_transaction_id, payment_method, payment_method_id, amount, ip_address)"
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

            $site_session_id = $this->getRequest()->getParam('site_session_id', null);
            $pc_session_id = $this->getRequest()->getParam('pc_session_id', null);
            $affiliate_name = $this->getRequest()->getParam('affiliate_name', null);
            $affiliate_username = $this->getRequest()->getParam('affiliate_username', null);
            $affiliate_id = $this->getRequest()->getParam('affiliate_id', null);
            $ip_address = $this->getRequest()->getParam('ip_address', null);
            $session_id = $this->getRequest()->getParam('session_id', null);
            $username = $this->getRequest()->getParam('username', null);
            $password = $this->getRequest()->getParam('password', null);
            $mac_address = $this->getRequest()->getParam('mac_address', null);
            $version = $this->getRequest()->getParam('version', null);
            $country = $this->getRequest()->getParam('country', null);
            $city = $this->getRequest()->getParam('city', null);
            $site_name = $this->getRequest()->getParam('site_name', null);
            $device_aff_id = $this->getRequest()->getParam('device_aff_id', null);
            $gp_mac_address = $this->getRequest()->getParam('gp_mac_address', null);
            $white_label_id = $this->getRequest()->getParam('white_label_id', null);
            $white_label_name = $this->getRequest()->getParam('white_label_name', null);
            $token = $this->getRequest()->getParam('token', null);
            $bonus_campaign_code = $this->getRequest()->getParam('bonus_campaign_code', null);
            $deposit_amount = $this->getRequest()->getParam('deposit_amount', null);
            $start_date = $this->getRequest()->getParam('start_date', null);
            $end_date = $this->getRequest()->getParam('end_date', null);
            $page_number = $this->getRequest()->getParam('page_number', null);
            $per_page = $this->getRequest()->getParam('per_page', null);
            $column = $this->getRequest()->getParam('column', 1);
            $order = $this->getRequest()->getParam('order', 'asc');
            $player_id = $this->getRequest()->getParam('player_id', null);
            $currency = $this->getRequest()->getParam('currency', null);
            $tid_code = $this->getRequest()->getParam('tid_code', null);
            $email = $this->getRequest()->getParam('email', null);
            $first_name = $this->getRequest()->getParam('first_name', null);
            $name = $this->getRequest()->getParam('name', null);
            $last_name = $this->getRequest()->getParam('last_name', null);
            $familyname = $this->getRequest()->getParam('familyname', null);
            $birthday = $this->getRequest()->getParam('birthday', null);
            $zip = $this->getRequest()->getParam('zip', null);
            $street_address1 = $this->getRequest()->getParam('street_address1', null);
            $street_address2 = $this->getRequest()->getParam('street_address2', null);
            $phone = $this->getRequest()->getParam('phone', null);
            $bank_account = $this->getRequest()->getParam('bank_account', null);
            $bank_country = $this->getRequest()->getParam('bank_country', null);
            $swift = $this->getRequest()->getParam('swift', null);
            $iban = $this->getRequest()->getParam('iban', null);
            $receive_email = $this->getRequest()->getParam('receive_email', null);
            $registration_code = $this->getRequest()->getParam('registration_code', null);
            $language = $this->getRequest()->getParam('language', null);
            $phone_number = $this->getRequest()->getParam('phone_number', null);
            $password_old = $this->getRequest()->getParam('password_old', null);
            $password_new = $this->getRequest()->getParam('password_new', null);
            $player_phone = $this->getRequest()->getParam('player_phone', null);
            $player_email = $this->getRequest()->getParam('player_email', null);
            $player_first_name = $this->getRequest()->getParam('player_first_name', null);
            $player_last_name = $this->getRequest()->getParam('player_last_name', null);
            $player_birthday = $this->getRequest()->getParam('player_birthday', null);
            $player_city = $this->getRequest()->getParam('player_city', null);
            $player_address = $this->getRequest()->getParam('player_address', null);
            $player_address2 = $this->getRequest()->getParam('player_address2', null);
            $id = $this->getRequest()->getParam('id', null);
            $question = $this->getRequest()->getParam('question', null);
            $answer = $this->getRequest()->getParam('answer', null);
            $monthly_deposit_limit = $this->getRequest()->getParam('monthly_deposit_limit', null);
            $monthly_deposit_limit_start_date = $this->getRequest()->getParam('monthly_deposit_limit_start_date', null);
            $monthly_deposit_limit_end_date =  $this->getRequest()->getParam('monthly_deposit_limit_end_date', null);
		    $weekly_deposit_limit = $this->getRequest()->getParam('weekly_deposit_limit', null);
            $weekly_deposit_start_date = $this->getRequest()->getParam('weekly_deposit_start_date', null);
            $weekly_deposit_end_date = $this->getRequest()->getParam('weekly_deposit_end_date', null);
		    $daily_deposit_limit = $this->getRequest()->getParam('daily_deposit_limit', null);
            $daily_deposit_start_date = $this->getRequest()->getParam('monthly_deposit_start_date', null);
            $daily_deposit_end_date = $this->getRequest()->getParam('daily_deposit_end_date', null);
            $monthly_max_loss_limit = $this->getRequest()->getParam('monthly_max_loss_limit', null);
            $monthly_max_loss_start_date = $this->getRequest()->getParam('monthly_max_loss_start_date', null);
            $monthly_max_loss_end_date = $this->getRequest()->getParam('monthly_max_loss_end_date', null);
		    $weekly_max_loss_limit = $this->getRequest()->getParam('monthly_max_loss_limit', null);
            $weekly_max_loss_start_date = $this->getRequest()->getParam('weekly_max_loss_start_date', null);
            $weekly_max_loss_end_date = $this->getRequest()->getParam('weekly_max_loss_end_date', null);
		    $daily_max_loss_limit = $this->getRequest()->getParam('daily_max_loss_limit', null);
            $daily_max_loss_start_date = $this->getRequest()->getParam('daily_max_start_date', null);
            $daily_max_loss_end_date = $this->getRequest()->getParam('daily_max_loss_end_date', null);
		    $max_stake_start_date = $this->getRequest()->getParam('max_stake_start_date', null);
            $max_stake_end_date = $this->getRequest()->getParam('max_stake_end_date', null);
            $max_stake = $this->getRequest()->getParam('max_stake', null);
            $time_limit_minutes = $this->getRequest()->getParam('time_limit_minutes', null);
		    $banned_start_date = $this->getRequest()->getParam('banned_start_date', null);
            $banned_end_date = $this->getRequest()->getParam('banned_end_date', null);
            $banned_status = $this->getRequest()->getParam('banned_status', null);
            $hash_id = $this->getRequest()->getParam('hash_id', null);
            $document_site = $this->getRequest()->getParam('document_site', null);
            $document_location = $this->getRequest()->getParam('document_location', null);
            $document_file_name = $this->getRequest()->getParam('document_file_name', null);
            $credits = $this->getRequest()->getParam('credits', null);
            $provider_id = $this->getRequest()->getParam('provider_id', null);
            $amount = $this->getRequest()->getParam('amount', null);
            $payment_method = $this->getRequest()->getParam('payment_method', null);
            $payment_method_id = $this->getRequest()->getParam('payment_method_id', null);
            $bonus_code = $this->getRequest()->getParam('bonus_code', null);
            $css_template = $this->getRequest()->getParam('css_template', null);
            $payment_id = $this->getRequest()->getParam('payment_id', null);
            $paysafecard_email = $this->getRequest()->getParam('paysafecard_email', null);
            $paysafecard_date_of_birth = $this->getRequest()->getParam('paysafecard_date_of_birth', null);
            $paysafecard_first_name = $this->getRequest()->getParam('paysafecard_first_name', null);
            $paysafecard_last_name = $this->getRequest()->getParam('paysafecard_last_name', null);
            $token_id = $this->getRequest()->getParam('token_id', null);
            $wirecard_transaction_id = $this->getRequest()->getParam('wirecard_transaction_id', null);
            $expected_withdraw_amount = $this->getRequest()->getParam('expected_withdraw_amount', null);
            $transaction_id = $this->getRequest()->getParam('transaction_id', null);
            $withdraw_amount = $this->getRequest()->getParam('wirecard_amount', null);
            $promotion_code = $this->getRequest()->getParam('promotion_code', null);
            $is_3d_secure = $this->getRequest()->getParam('is_3d_secure', null);
            $apco_transaction_id = $this->getRequest()->getParam('apco_transaction_id', null);

            switch(strtolower($operation)){
                //WEB SITE PACKAGE
                case 'set-time-modified':

                    WebSiteManager::setTimeModified($site_session_id, $pc_session_id);
                    break;
                case 'validate-site-session':
                    WebSiteManager::validateSiteSession($site_session_id, $pc_session_id);
                    break;
                case 'list-anonymous-games':
                    WebSiteManager::listAnonymousGames($affiliate_name, $ip_address);
                    break;
                case 'open-anonymous-session':
                    WebSiteManager::openAnonymousSession($ip_address);
                    break;
                case 'site-logout-pc':
                    WebSiteManager::siteLogoutPC($session_id);
                    break;
                case 'site-logout':
                    WebSiteManager::siteLogout($site_session_id, $pc_session_id);
                    break;
                case 'site-login-pc':
                    WebSiteManager::siteLoginPC($username, $password, $mac_address, $version, $ip_address, $country, $city);
                    break;
                case 'site-login':
                    WebSiteManager::siteLogin($site_name, $username, $password, $mac_address, $version, $ip_address, $country, $city, $device_aff_id, $gp_mac_address);
                    break;
                case 'list-countries':
                    WebSiteManager::listCountries();
                    break;
                case 'list-countries-allowed-for-player':
                    WebSiteManager::listCountriesAllowedForPlayer($white_label_id);
                    break;
                case 'currency-for-promo-code':
                    WebSiteManager::currencyForPromoCode($affiliate_name);
                    break;
                case 'login-external-integration':
                    WebSiteManager::loginExternalIntegration($token, $ip_address);
                    break;

                //WEB SITE BONUS PACKAGE
                case 'cancel-bonus-transactions':
                    WebSiteBonusManager::cancelBonusTransactions($pc_session_id);
                    break;
                case 'check-bonus-code':
                    WebSiteBonusManager::checkBonusCode($pc_session_id, $bonus_campaign_code, $deposit_amount);
                    break;
                case 'check-bonus-code-status':
                    WebSiteBonusManager::checkBonusCodeStatus($pc_session_id, $bonus_campaign_code, $deposit_amount);
                    break;
                case 'list-player-bonus-history':
                    WebSiteBonusManager::listPlayerBonusHistory($session_id);
                    break;

                //WEB SITE REPORTS
                case 'list-limits':
                    WebSiteReportsManager::listLimits($session_id);
                    break;
                case 'list-limits-new':
                    WebSiteReportsManager::listLimitsNew($session_id);
                    break;
                case 'list-player-history':
                    WebSiteReportsManager::listPlayerHistory($session_id, $start_date, $end_date, $page_number, $per_page, $column, $order);
                    break;
                case 'list-player-history-details':
                    WebSiteReportsManager::listPlayerHistoryDetails($session_id, $page_number, $per_page, $column, $order);
                    break;
                case 'list-player-history-subdetails':
                    WebSiteReportsManager::listPlayerHistorySubdetails($session_id, $page_number, $per_page, $column, $order);
                    break;
                case 'list-credit-transfers':
                    WebSiteReportsManager::listCreditTransfers($session_id, $start_date, $end_date, $page_number, $per_page, $column, $order);
                    break;
                case 'list-available-bonus-campaigns':
                    WebSiteReportsManager::listAvailableBonusCampaigns($affiliate_username, $player_id);
                    break;
                case 'list-player-available-bonus-campaigns':
                    WebSiteReportsManager::listPlayerAvailableBonusCampaigns($player_id);
                    break;
                case 'list-player-active-bonuses-and-promotions':
                    WebSiteReportsManager::listPlayerActiveBonusesAndPromotions($player_id);
                    break;
                case 'list-top-won-jackpots':
                    WebSiteReportsManager::listTopWonJackpots($affiliate_name);
                    break;
                case 'list-current-jackpot-level':
                    WebSiteReportsManager::listCurrentJackpotLevels($affiliate_name, $currency);
                    break;
                case 'list-high-wins':
                    WebSiteReportsManager::listHighWins($affiliate_name);
                    break;

                //WEB SITE PLAYER ACCOUNT
                case 'currency-list-for-new-player':
                    WebSitePlayerAccountManager::currencyListForNewPlayer($affiliate_id, $tid_code);
                    break;
                case 'insert-player':
                    WebSitePlayerAccountManager::insertPlayer($affiliate_id, $username, $password, $email, $first_name, $last_name,
	                    $birthday, $country, $zip, $city, $street_address1, $street_address2, $phone,
	                    $bank_account, $bank_country, $swift, $iban, $receive_email, $currency, $ip_address,
                        $registration_code, $tid_code, $language);
                    break;
                case 'player-details':
                    WebSitePlayerAccountManager::playerDetails($session_id, $ip_address);
                    break;
                case 'update-player':
                    WebSitePlayerAccountManager::updatePlayer($session_id, $email, $first_name, $last_name,
	                    $birthday, $country, $zip, $city, $street_address1, $street_address2,
	                    $phone_number, $bank_account, $bank_country, $swift, $iban, $receive_email,
                        $ip_address, $language);
                    break;
                case 'reset-password':
                    WebSitePlayerAccountManager::resetPassword($session_id, $password_old, $password_new, $ip_address);
                    break;
                case 'validate-player':
                    WebSitePlayerAccountManager::validatePlayer($username);
                    break;
                case 'validate-player-phone':
                    WebSitePlayerAccountManager::validatePlayerPhone($player_id, $player_phone, $white_label_name);
                    break;
                case 'validate-player-email':
                    WebSitePlayerAccountManager::validatePlayerEmail($player_id, $player_email, $white_label_name);
                    break;
                case 'validate-player-first-name-last-name-birthday':
                    WebSitePlayerAccountManager::validatePlayerFirstNameLastNameBirthday($player_id, $player_first_name, $player_last_name, $player_birthday, $white_label_name);
                    break;
                case 'validate-player-first-name-last-name-address':
                    WebSitePlayerAccountManager::validatePlayerFirstNameLastNameAddress($player_id, $player_first_name, $player_last_name, $player_city,
                        $player_address, $player_address2, $white_label_name);
                    break;
                case 'validate-player-first-name-last-name-phone':
                    WebSitePlayerAccountManager::validatePlayerFirstNameLastNamePhone($player_id, $player_first_name, $player_last_name, $player_phone, $white_label_name);
                    break;
                case 'player-credits':
                    WebSitePlayerAccountManager::playerCredits($session_id, $ip_address);
                    break;
                //WEB SITE PLAYER ACCOUNT SETUP
                case 'change-terms-and-conditions-for-cbc':
                    WebSitePlayerAccountSetupManager::ChangeTermsAndConditionsForCBC($session_id);
                    break;
                case 'change-terms-and-conditions-for-ggl':
                    WebSitePlayerAccountSetupManager::ChangeTermsAndConditionsForGGL($session_id);
                    break;
                case 'check-terms-and-conditions':
                    WebSitePlayerAccountSetupManager::CheckTermsAndConditions($session_id);
                    break;
                case 'check-temporary-id':
                    WebSitePlayerAccountSetupManager::CheckTemporaryID($username, $id);
                    break;
                case 'change-password':
                    WebSitePlayerAccountSetupManager::ChangePassword($username, $id, $password, $question, $answer);
                    break;
                case 'set-security-question-answer':
                    WebSitePlayerAccountSetupManager::SetSecurityQuestionAnswer($username, $password, $question, $answer);
                    break;
                case 'get-security-question':
                    WebSitePlayerAccountSetupManager::GetSecurityQuestion($username);
                    break;
                case 'responsible-gaming-setup':
                    WebSitePlayerAccountSetupManager::responsibleGamingSetup($session_id,
		            $monthly_deposit_limit, $monthly_deposit_limit_start_date, $monthly_deposit_limit_end_date,
		            $weekly_deposit_limit, $weekly_deposit_start_date, $weekly_deposit_end_date,
		            $daily_deposit_limit, $daily_deposit_start_date, $daily_deposit_end_date,
                    $monthly_max_loss_limit, $monthly_max_loss_start_date, $monthly_max_loss_end_date,
		            $weekly_max_loss_limit, $weekly_max_loss_start_date, $weekly_max_loss_end_date,
		            $daily_max_loss_limit, $daily_max_loss_start_date, $daily_max_loss_end_date,
		            $max_stake_start_date, $max_stake_end_date, $max_stake, $time_limit_minutes,
		            $banned_start_date, $banned_end_date, $banned_status);
                    break;
                case 'responsible-gaming-setup-delay':
                    WebSitePlayerAccountSetupManager::responsibleGamingSetupDelay($session_id,
                        $monthly_deposit_limit, $monthly_deposit_limit_start_date, $monthly_deposit_limit_end_date,
                        $weekly_deposit_limit, $weekly_deposit_start_date, $weekly_deposit_end_date,
                        $daily_deposit_limit, $daily_deposit_start_date, $daily_deposit_end_date,
                        $monthly_max_loss_limit, $monthly_max_loss_start_date, $monthly_max_loss_end_date,
                        $weekly_max_loss_limit, $weekly_max_loss_start_date, $weekly_max_loss_end_date,
                        $daily_max_loss_limit, $daily_max_loss_start_date, $daily_max_loss_end_date,
                        $max_stake_start_date, $max_stake_end_date, $max_stake, $time_limit_minutes,
                        $banned_start_date, $banned_end_date, $banned_status);
                    break;
                case 'ban-player':
                    WebSitePlayerAccountSetupManager::banPlayer($username, $password);
                    break;
                case 'unlock-account':
                    WebSitePlayerAccountSetupManager::unlockAccount($player_id);
                    break;
                //WEB SITE PLAYER ACCOUNT MAILS
                case 'forgot-username':
                    WebSitePlayerAccountMailsManager::ForgotUsername($name, $familyname, $birthday, $email);
                    break;
                case 'send-player-activation-mail':
                    WebSitePlayerAccountMailsManager::sendPlayerActivationMail($player_id);
                    break;
                case 'forgot-password-with-security-answer':
                    WebSitePlayerAccountMailsManager::ForgotPasswordWithSecurityAnswer($username, $answer);
                    break;
                case 'forgot-password-with-personal-data':
                    WebSitePlayerAccountMailsManager::ForgotPasswordWithPersonalData($username, $name, $familyname, $birthday, $email);
                    break;
                case 'player-registration-confirmation':
                    WebSitePlayerAccountMailsManager::playerRegistrationConfirmation($hash_id, $player_id, $ip_address);
                    break;

                //WEB SITE DOCUMENT MANAGEMENT MANAGER
                case 'upload-document':
                    WebSiteDocumentManagementManager::uploadDocument($site_session_id, $document_site, $document_location, $document_file_name);
                    break;
                case 'list-player-documents':
                    WebSiteDocumentManagementManager::listPlayersDocuments($site_session_id, $player_id);
                    break;

                //WEB SITE SPORT INTEGRATION
                case 'open-cbc-session':
                    WebSiteSportIntegrationManager::openCbcSession($pc_session_id);
                    break;
                case 'open-betkiosk-session':
                    WebSiteSportIntegrationManager::openBetKioskSession($pc_session_id, $ip_address);
                    break;
                case 'open-maxbet-session':
                    WebSiteSportIntegrationManager::openMaxBetSession($pc_session_id, $ip_address);
                    break;
                case 'open-memobet-session':
                    WebSiteSportIntegrationManager::openMemoBetSession($pc_session_id, $ip_address);
                    break;
                case 'close-sport-betting-window':
                    WebSiteSportIntegrationManager::closeSportBettingWindow($pc_session_id);
                    break;
                case 'check-betkiosk-game-status':
                    WebSiteSportIntegrationManager::checkBetkioskGameStatus($pc_session_id);
                    break;

                //WEB SITE LDC(GGL) INTEGRATION
                case 'get-encrypted-token':
                    WebSiteGglIntegrationManager::getEncryptedToken($site_session_id);
                    break;
                case 'check-ggl-game-status':
                    WebSiteGglIntegrationManager::checkGGLGameStatus($pc_session_id);
                    break;

                //WEB SITE VIVO GAMING INTEGRATION
                case 'get-vivo-gaming-token':
                    WebSiteVivoGamingIntegrationManager::getVivoGamingToken($pc_session_id, $player_id, $credits, $provider_id);
                    break;
                case 'close-vivo-gaming-integration-session':
                    WebSiteVivoGamingIntegrationManager::closeVivoGamingIntegrationSession($pc_session_id);
                    break;

                //WEB SITE PAYSAFECARD DIRECT MERCHANT INTEGRATION
                case 'get-paysafecard-payment-purchase-message':
                    WebSitePaysafecardDirectMerchantManager::getPaysafecardPaymentPurchaseMessage($site_session_id, $pc_session_id,
                        $amount, $payment_method, $payment_method_id, $ip_address, $bonus_code, $css_template
                    );
                    break;
                case 'get-paysafecard-payment-details':
                    WebSitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails($payment_id);
                    break;
                case 'get-paysafecard-withdraw-request':
                    WebSitePaysafecardDirectMerchantManager::paysafecardWithdrawRequest($pc_session_id, $payment_method, $payment_method_id, $amount,
                        $paysafecard_email, $paysafecard_date_of_birth, $paysafecard_first_name, $paysafecard_last_name,
                        $ip_address);
                    break;

                //WEB SITE WIRECARD MERCHANT INTEGRATION
                case 'get-wirecard-payment-purchase-custom-card-message':
                    WebSiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage($site_session_id, $pc_session_id, $amount, $payment_method,
                        $payment_method_id, $ip_address, $bonus_code, $css_template);
                    break;
                case 'get-wirecard-payment-purchase-custom-card-with-token-message':
                    WebSiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardWithTokenMessage($site_session_id, $pc_session_id, $amount,
                        $payment_method, $payment_method_id, $token_id, $ip_address, $bonus_code, $css_template);
                    break;

                case 'get-wirecard-payment-purchase-custom-payment-method-message':
                    WebSiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomPaymentMethodMessage($site_session_id, $pc_session_id,
                        $amount, $payment_method,
                        $payment_method_id, $ip_address, $bonus_code, $css_template);
                    break;
                case 'get-wirecard-withdraw-request':
                    WebSiteWirecardMerchantManager::wirecardWithdrawRequest($pc_session_id, $wirecard_transaction_id, $token_id,
                        $payment_method, $payment_method_id, $amount, $ip_address);
                    break;

                //WEB SITE MERCHANT INTEGRATION - GENERAL CALLS
                case 'get-all-payment-methods':
		            WebSiteMerchantManager::getAllPaymentMethods();
                    break;
                case 'get-transaction-limit-purchase':
                    WebSiteMerchantManager::getTransactionLimitPurchase($site_session_id, $amount, $payment_method, $ip_address);
                    break;
                case 'get-transaction-limit-payout':
                    WebSiteMerchantManager::getTransactionLimitPayout($site_session_id, $amount, $payment_method, $ip_address);
                    break;
                case 'pending-payout-status':
                    WebSiteMerchantManager::pendingPayoutStatus($site_session_id);
                    break;
                case 'is-withdraw-possible':
                    WebSiteMerchantManager::isWithdrawPossible($site_session_id, $pc_session_id, $expected_withdraw_amount, $transaction_id);
                    break;
                case 'cancel-withdraw':
                    WebSiteMerchantManager::cancelWithdraw($site_session_id, $pc_session_id, $withdraw_amount, $transaction_id);
                    break;
                case 'list-payment-limits-for-white-label':
                    WebSiteMerchantManager::listPaymentLimitsForWhiteLabel($site_session_id, $currency);
                    break;
                case 'set-iban-swift':
                    WebSiteMerchantManager::setIbanSwift($player_id, $swift, $iban);
                    break;
                case 'get-promotion-code':
                    WebSiteMerchantManager::getPromotionCode($pc_session_id, $promotion_code);
                    break;

                //WEB SITE MERCHANT INTEGRATION - APCO INTEGRATION
                case 'get-apco-payment-purchase-custom-card-message':
                    WebSiteApcoMerchantManager::getApcoPaymentPurchaseCustomCardMessage($site_session_id, $pc_session_id, $amount, $payment_method,
                        $payment_method_id, $is_3d_secure, $ip_address, $bonus_code, $css_template);
                    break;
                case 'get-apco-purchase-custom-payment-method-message':
                    WebSiteApcoMerchantManager::getApcoPurchaseCustomPaymentMethodMessage($site_session_id, $pc_session_id,
                        $amount, $payment_method, $payment_method_id, $ip_address, $bonus_code, $css_template);
                    break;
                case 'get-apco-last-card-numbers':
                    WebSiteApcoMerchantManager::getLastCardNumbers($site_session_id);
                    break;
                case 'get-apco-register-credit-card-message':
                    WebSiteApcoMerchantManager::getApcoRegisterCreditCardMessage($site_session_id, $pc_session_id, $ip_address);
                    break;
                case 'apco-withdraw-request':
                    WebSiteApcoMerchantManager::apcoWithdrawRequest($pc_session_id, $apco_transaction_id, $payment_method, $payment_method_id,
                        $amount, $ip_address);
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