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

class Rest_IndexController extends Zend_Controller_Action {


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
				"\n /onlinecasinoservice/rest " .
                "\n /onlinecasinoservice/rest/index " .
                "\n /onlinecasinoservice/rest/index-query-param " .
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
                //WEB SITE PACKAGE
                case 'set-time-modified':
                    WebSiteManager::setTimeModified($json_object->site_session_id, $json_object->pc_session_id);
                    break;
                case 'validate-site-session':
                    WebSiteManager::validateSiteSession($json_object->site_session_id, $json_object->pc_session_id);
                    break;
                case 'list-anonymous-games':
                    WebSiteManager::listAnonymousGames($json_object->affiliate_name, $json_object->ip_address);
                    break;
                case 'open-anonymous-session':
                    WebSiteManager::openAnonymousSession($json_object->ip_address);
                    break;
                case 'site-logout-pc':
                    WebSiteManager::siteLogoutPC($json_object->session_id);
                    break;
                case 'site-logout':
                    WebSiteManager::siteLogout($json_object->site_session_id, $json_object->pc_session_id);
                    break;
                case 'site-login-pc':
                    WebSiteManager::siteLoginPC($json_object->username, $json_object->password, $json_object->mac_address, $json_object->version, $json_object->ip_address, $json_object->country, $json_object->city);
                    break;
                case 'site-login':
                    WebSiteManager::siteLogin($json_object->site_name, $json_object->username, $json_object->password, $json_object->mac_address, $json_object->version, $json_object->ip_address, $json_object->country, $json_object->city, $json_object->device_aff_id, $json_object->gp_mac_address);
                    break;
                case 'list-countries':
                    WebSiteManager::listCountries();
                    break;
                case 'list-countries-allowed-for-player':
                    WebSiteManager::listCountriesAllowedForPlayer($json_object->white_label_id);
                    break;
                case 'currency-for-promo-code':
                    WebSiteManager::currencyForPromoCode($json_object->affiliate_name);
                    break;
                case 'login-external-integration':
                    WebSiteManager::loginExternalIntegration($json_object->token, $json_object->ip_address);
                    break;

                //WEB SITE BONUS PACKAGE
                case 'cancel-bonus-transactions':
                    WebSiteBonusManager::cancelBonusTransactions($json_object->pc_session_id);
                    break;
                case 'check-bonus-code':
                    WebSiteBonusManager::checkBonusCode($json_object->pc_session_id, $json_object->bonus_campaign_code, $json_object->deposit_amount);
                    break;
                case 'check-bonus-code-status':
                    WebSiteBonusManager::checkBonusCodeStatus($json_object->pc_session_id, $json_object->bonus_campaign_code, $json_object->deposit_amount);
                    break;
                case 'list-player-bonus-history':
                    WebSiteBonusManager::listPlayerBonusHistory($json_object->session_id);
                    break;

                //WEB SITE REPORTS
                case 'list-limits':
                    WebSiteReportsManager::listLimits($json_object->session_id);
                    break;
                case 'list-limits-new':
                    WebSiteReportsManager::listLimitsNew($json_object->session_id);
                    break;
                case 'list-player-history':
                    WebSiteReportsManager::listPlayerHistory($json_object->session_id, $json_object->start_date, $json_object->end_date, $json_object->page_number, $json_object->per_page, $json_object->column, $json_object->order);
                    break;
                case 'list-player-history-details':
                    WebSiteReportsManager::listPlayerHistoryDetails($json_object->session_id, $json_object->page_number, $json_object->per_page, $json_object->column, $json_object->order);
                    break;
                case 'list-player-history-subdetails':
                    WebSiteReportsManager::listPlayerHistorySubdetails($json_object->session_id, $json_object->page_number, $json_object->per_page, $json_object->column, $json_object->order);
                    break;
                case 'list-credit-transfers':
                    WebSiteReportsManager::listCreditTransfers($json_object->session_id, $json_object->start_date, $json_object->end_date, $json_object->page_number, $json_object->per_page, $json_object->column, $json_object->order);
                    break;
                case 'list-available-bonus-campaigns':
                    WebSiteReportsManager::listAvailableBonusCampaigns($json_object->affiliate_username, $json_object->player_id);
                    break;
                case 'list-player-available-bonus-campaigns':
                    WebSiteReportsManager::listPlayerAvailableBonusCampaigns($json_object->player_id);
                    break;
                case 'list-player-active-bonuses-and-promotions':
                    WebSiteReportsManager::listPlayerActiveBonusesAndPromotions($json_object->player_id);
                    break;
                case 'list-top-won-jackpots':
                    WebSiteReportsManager::listTopWonJackpots($json_object->affiliate_name);
                    break;
                case 'list-current-jackpot-levels':
                    WebSiteReportsManager::listCurrentJackpotLevels($json_object->affiliate_name, $json_object->currency);
                    break;
                case 'list-high-wins':
                    WebSiteReportsManager::listHighWins($json_object->affiliate_name);
                    break;

                //WEB SITE PLAYER ACCOUNT
                case 'currency-list-for-new-player':
                    WebSitePlayerAccountManager::currencyListForNewPlayer($json_object->affiliate_id, $json_object->tid_code);
                    break;
                case 'insert-player':
                    WebSitePlayerAccountManager::insertPlayer($json_object->affiliate_id, $json_object->username, $json_object->password, $json_object->email, $json_object->first_name, $json_object->last_name,
	                    $json_object->birthday, $json_object->country, $json_object->zip, $json_object->city, $json_object->street_address1, $json_object->street_address2, $json_object->phone,
	                    $json_object->bank_account, $json_object->bank_country, $json_object->swift, $json_object->iban, $json_object->receive_email, $json_object->currency, $json_object->ip_address,
                        $json_object->registration_code, $json_object->tid_code, $json_object->language);
                    break;
                case 'player-details':
                    WebSitePlayerAccountManager::playerDetails($json_object->session_id, $json_object->ip_address);
                    break;
                case 'update-player':
                    WebSitePlayerAccountManager::updatePlayer($json_object->session_id, $json_object->email, $json_object->first_name, $json_object->last_name,
	                    $json_object->birthday, $json_object->country, $json_object->zip, $json_object->city, $json_object->street_address1, $json_object->street_address2,
	                    $json_object->phone_number, $json_object->bank_account, $json_object->bank_country, $json_object->swift, $json_object->iban, $json_object->receive_email,
                        $json_object->ip_address, $json_object->language);
                    break;
                case 'reset-password':
                    WebSitePlayerAccountManager::resetPassword($json_object->session_id, $json_object->password_old, $json_object->password_new, $json_object->ip_address);
                    break;
                case 'validate-player':
                    WebSitePlayerAccountManager::validatePlayer($json_object->username);
                    break;
                case 'validate-player-phone':
                    WebSitePlayerAccountManager::validatePlayerPhone($json_object->player_id, $json_object->player_phone, $json_object->white_label_name);
                    break;
                case 'validate-player-email':
                    WebSitePlayerAccountManager::validatePlayerEmail($json_object->player_id, $json_object->player_email, $json_object->white_label_name);
                    break;
                case 'validate-player-first-name-last-name-birthday':
                    WebSitePlayerAccountManager::validatePlayerFirstNameLastNameBirthday($json_object->player_id, $json_object->player_first_name, $json_object->player_last_name, $json_object->player_birthday, $json_object->white_label_name);
                    break;
                case 'validate-player-first-name-last-name-address':
                    WebSitePlayerAccountManager::validatePlayerFirstNameLastNameAddress($json_object->player_id, $json_object->player_first_name, $json_object->player_last_name, $json_object->player_city,
                        $json_object->player_address, $json_object->player_address2, $json_object->white_label_name);
                    break;
                case 'validate-player-first-name-last-name-phone':
                    WebSitePlayerAccountManager::validatePlayerFirstNameLastNamePhone($json_object->player_id, $json_object->player_first_name, $json_object->player_last_name, $json_object->player_phone, $json_object->white_label_name);
                    break;
                case 'player-credits':
                    WebSitePlayerAccountManager::playerCredits($json_object->session_id, $json_object->ip_address);
                    break;
                //WEB SITE PLAYER ACCOUNT SETUP
                case 'change-terms-and-conditions-for-cbc':
                    WebSitePlayerAccountSetupManager::ChangeTermsAndConditionsForCBC($json_object->session_id);
                    break;
                case 'change-terms-and-conditions-for-ggl':
                    WebSitePlayerAccountSetupManager::ChangeTermsAndConditionsForGGL($json_object->session_id);
                    break;
                case 'check-terms-and-conditions':
                    WebSitePlayerAccountSetupManager::CheckTermsAndConditions($json_object->session_id);
                    break;
                case 'check-temporary-id':
                    WebSitePlayerAccountSetupManager::CheckTemporaryID($json_object->username, $json_object->id);
                    break;
                case 'change-password':
                    WebSitePlayerAccountSetupManager::ChangePassword($json_object->username, $json_object->id, $json_object->password, $json_object->question, $json_object->answer);
                    break;
                case 'set-security-question-answer':
                    WebSitePlayerAccountSetupManager::SetSecurityQuestionAnswer($json_object->username, $json_object->password, $json_object->question, $json_object->answer);
                    break;
                case 'get-security-question':
                    WebSitePlayerAccountSetupManager::GetSecurityQuestion($json_object->username);
                    break;
                case 'responsible-gaming-setup':
                    WebSitePlayerAccountSetupManager::responsibleGamingSetup($json_object->session_id,
		            $json_object->monthly_deposit_limit, $json_object->monthly_deposit_limit_start_date, $json_object->monthly_deposit_limit_end_date,
		            $json_object->weekly_deposit_limit, $json_object->weekly_deposit_start_date, $json_object->weekly_deposit_end_date,
		            $json_object->daily_deposit_limit, $json_object->daily_deposit_start_date, $json_object->daily_deposit_end_date,
                    $json_object->monthly_max_loss_limit, $json_object->monthly_max_loss_start_date, $json_object->monthly_max_loss_end_date,
		            $json_object->weekly_max_loss_limit, $json_object->weekly_max_loss_start_date, $json_object->weekly_max_loss_end_date,
		            $json_object->daily_max_loss_limit, $json_object->daily_max_loss_start_date, $json_object->daily_max_loss_end_date,
		            $json_object->max_stake_start_date, $json_object->max_stake_end_date, $json_object->max_stake, $json_object->time_limit_minutes,
		            $json_object->banned_start_date, $json_object->banned_end_date, $json_object->banned_status);
                    break;
                case 'responsible-gaming-setup-delay':
                    WebSitePlayerAccountSetupManager::responsibleGamingSetupDelay($json_object->session_id,
                        $json_object->monthly_deposit_limit, $json_object->monthly_deposit_limit_start_date, $json_object->monthly_deposit_limit_end_date,
                        $json_object->weekly_deposit_limit, $json_object->weekly_deposit_start_date, $json_object->weekly_deposit_end_date,
                        $json_object->daily_deposit_limit, $json_object->daily_deposit_start_date, $json_object->daily_deposit_end_date,
                        $json_object->monthly_max_loss_limit, $json_object->monthly_max_loss_start_date, $json_object->monthly_max_loss_end_date,
                        $json_object->weekly_max_loss_limit, $json_object->weekly_max_loss_start_date, $json_object->weekly_max_loss_end_date,
                        $json_object->daily_max_loss_limit, $json_object->daily_max_loss_start_date, $json_object->daily_max_loss_end_date,
                        $json_object->max_stake_start_date, $json_object->max_stake_end_date, $json_object->max_stake, $json_object->time_limit_minutes,
                        $json_object->banned_start_date, $json_object->banned_end_date, $json_object->banned_status);
                    break;
                case 'ban-player':
                    WebSitePlayerAccountSetupManager::banPlayer($json_object->username, $json_object->password);
                    break;
                case 'unlock-account':
                    WebSitePlayerAccountSetupManager::unlockAccount($json_object->player_id);
                    break;
                //WEB SITE PLAYER ACCOUNT MAILS
                case 'forgot-username':
                    WebSitePlayerAccountMailsManager::ForgotUsername($json_object->name, $json_object->familyname, $json_object->birthday, $json_object->email);
                    break;
                case 'send-player-activation-mail':
                    WebSitePlayerAccountMailsManager::sendPlayerActivationMail($json_object->player_id);
                    break;
                case 'forgot-password-with-security-answer':
                    WebSitePlayerAccountMailsManager::ForgotPasswordWithSecurityAnswer($json_object->username, $json_object->answer);
                    break;
                case 'forgot-password-with-personal-data':
                    WebSitePlayerAccountMailsManager::ForgotPasswordWithPersonalData($json_object->username, $json_object->name, $json_object->familyname, $json_object->birthday, $json_object->email);
                    break;
                case 'player-registration-confirmation':
                    WebSitePlayerAccountMailsManager::playerRegistrationConfirmation($json_object->hash_id, $json_object->player_id, $json_object->ip_address);
                    break;

                //WEB SITE DOCUMENT MANAGEMENT MANAGER
                case 'upload-document':
                    WebSiteDocumentManagementManager::uploadDocument($json_object->site_session_id, $json_object->document_site, $json_object->document_location, $json_object->document_file_name);
                    break;
                case 'list-player-documents':
                    WebSiteDocumentManagementManager::listPlayersDocuments($json_object->site_session_id, $json_object->player_id);
                    break;

                //WEB SITE SPORT INTEGRATION
                case 'open-cbc-session':
                    WebSiteSportIntegrationManager::openCbcSession($json_object->pc_session_id);
                    break;
                case 'open-betkiosk-session':
                    WebSiteSportIntegrationManager::openBetKioskSession($json_object->pc_session_id, $json_object->ip_address);
                    break;
                case 'open-maxbet-session':
                    WebSiteSportIntegrationManager::openMaxBetSession($json_object->pc_session_id, $json_object->ip_address);
                    break;
                case 'open-memobet-session':
                    WebSiteSportIntegrationManager::openMemoBetSession($json_object->pc_session_id, $json_object->ip_address);
                    break;
                case 'close-sport-betting-window':
                    WebSiteSportIntegrationManager::closeSportBettingWindow($json_object->pc_session_id);
                    break;
                case 'check-betkiosk-game-status':
                    WebSiteSportIntegrationManager::checkBetkioskGameStatus($json_object->pc_session_id);
                    break;

                //WEB SITE LDC(GGL) INTEGRATION
                case 'get-encrypted-token':
                    WebSiteGglIntegrationManager::getEncryptedToken($json_object->site_session_id);
                    break;
                case 'check-ggl-game-status':
                    WebSiteGglIntegrationManager::checkGGLGameStatus($json_object->pc_session_id);
                    break;

                //WEB SITE VIVO GAMING INTEGRATION
                case 'get-vivo-gaming-token':
                    WebSiteVivoGamingIntegrationManager::getVivoGamingToken($json_object->pc_session_id, $json_object->player_id, $json_object->credits, $json_object->provider_id);
                    break;
                case 'close-vivo-gaming-integration-session':
                    WebSiteVivoGamingIntegrationManager::closeVivoGamingIntegrationSession($json_object->pc_session_id);
                    break;

                //WEB SITE PAYSAFECARD DIRECT MERCHANT INTEGRATION
                case 'get-paysafecard-payment-purchase-message':
                    WebSitePaysafecardDirectMerchantManager::getPaysafecardPaymentPurchaseMessage($json_object->site_session_id, $json_object->pc_session_id,
                        $json_object->amount, $json_object->payment_method, $json_object->payment_method_id, $json_object->ip_address, $json_object->bonus_code, $json_object->css_template
                    );
                    break;
                case 'get-paysafecard-payment-details':
                    WebSitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails($json_object->payment_id);
                    break;
                case 'get-paysafecard-withdraw-request':
                    WebSitePaysafecardDirectMerchantManager::paysafecardWithdrawRequest($json_object->pc_session_id, $json_object->payment_method, $json_object->payment_method_id, $json_object->amount,
                        $json_object->paysafecard_email, $json_object->paysafecard_date_of_birth, $json_object->paysafecard_first_name, $json_object->paysafecard_last_name,
                        $json_object->ip_address);
                    break;

                //WEB SITE WIRECARD MERCHANT INTEGRATION
                case 'get-wirecard-payment-purchase-custom-card-message':
                    WebSiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage($json_object->site_session_id, $json_object->pc_session_id, $json_object->amount, $json_object->payment_method,
                        $json_object->payment_method_id, $json_object->ip_address, $json_object->bonus_code, $json_object->css_template);
                    break;
                case 'get-wirecard-payment-purchase-custom-card-with-token-message':
                    WebSiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardWithTokenMessage($json_object->site_session_id, $json_object->pc_session_id, $json_object->amount, $json_object->payment_method,
                        $json_object->payment_method_id, $json_object->token_id, $json_object->ip_address, $json_object->bonus_code, $json_object->css_template);
                    break;

                case 'get-wirecard-payment-purchase-custom-payment-method-message':
                    WebSiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomPaymentMethodMessage($json_object->site_session_id, $json_object->pc_session_id, $json_object->amount, $json_object->payment_method,
                        $json_object->payment_method_id, $json_object->ip_address, $json_object->bonus_code, $json_object->css_template);
                    break;
                case 'get-wirecard-withdraw-request':
                    WebSiteWirecardMerchantManager::wirecardWithdrawRequest($json_object->pc_session_id, $json_object->wirecard_transaction_id, $json_object->token_id,
                        $json_object->payment_method, $json_object->payment_method_id, $json_object->amount, $json_object->ip_address);
                    break;

                //WEB SITE MERCHANT INTEGRATION - GENERAL CALLS
                case 'get-all-payment-methods':
		            WebSiteMerchantManager::getAllPaymentMethods();
                    break;
                case 'get-transaction-limit-purchase':
                    WebSiteMerchantManager::getTransactionLimitPurchase($json_object->site_session_id, $json_object->amount, $json_object->payment_method, $json_object->ip_address);
                    break;
                case 'get-transaction-limit-payout':
                    WebSiteMerchantManager::getTransactionLimitPayout($json_object->site_session_id, $json_object->amount, $json_object->payment_method, $json_object->ip_address);
                    break;
                case 'pending-payout-status':
                    WebSiteMerchantManager::pendingPayoutStatus($json_object->site_session_id);
                    break;
                case 'is-withdraw-possible':
                    WebSiteMerchantManager::isWithdrawPossible($json_object->site_session_id, $json_object->pc_session_id, $json_object->expected_withdraw_amount, $json_object->transaction_id);
                    break;
                case 'cancel-withdraw':
                    WebSiteMerchantManager::cancelWithdraw($json_object->site_session_id, $json_object->pc_session_id, $json_object->withdraw_amount, $json_object->transaction_id);
                    break;
                case 'list-payment-limits-for-white-label':
                    WebSiteMerchantManager::listPaymentLimitsForWhiteLabel($json_object->site_session_id, $json_object->currency);
                    break;
                case 'set-iban-swift':
                    WebSiteMerchantManager::setIbanSwift($json_object->player_id, $json_object->swift, $json_object->iban);
                    break;
                case 'get-promotion-code':
                    WebSiteMerchantManager::getPromotionCode($json_object->pc_session_id, $json_object->promotion_code);
                    break;

                //WEB SITE MERCHANT INTEGRATION - APCO INTEGRATION
                case 'get-apco-payment-purchase-custom-card-message':
                    WebSiteApcoMerchantManager::getApcoPaymentPurchaseCustomCardMessage($json_object->site_session_id, $json_object->pc_session_id, $json_object->amount, $json_object->payment_method, $json_object->payment_method_id, $json_object->is_3d_secure, $json_object->ip_address, $json_object->bonus_code, $json_object->css_template);
                    break;
                case 'get-apco-purchase-custom-payment-method-message':
                    WebSiteApcoMerchantManager::getApcoPurchaseCustomPaymentMethodMessage($json_object->site_session_id, $json_object->pc_session_id, $json_object->amount, $json_object->payment_method, $json_object->payment_method_id, $json_object->ip_address, $json_object->bonus_code, $json_object->css_template);
                    break;
                case 'get-apco-last-card-numbers':
                    WebSiteApcoMerchantManager::getLastCardNumbers($json_object->site_session_id);
                    break;
                case 'get-apco-register-credit-card-message':
                    WebSiteApcoMerchantManager::getApcoRegisterCreditCardMessage($json_object->site_session_id, $json_object->pc_session_id, $json_object->ip_address);
                    break;
                case 'apco-withdraw-request':
                    WebSiteApcoMerchantManager::apcoWithdrawRequest($json_object->pc_session_id, $json_object->apco_transaction_id, $json_object->payment_method, $json_object->payment_method_id, $json_object->amount, $json_object->ip_address);
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