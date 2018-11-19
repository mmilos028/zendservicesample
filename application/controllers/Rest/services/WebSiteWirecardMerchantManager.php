<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'WirecardErrorHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class WebSiteWirecardMerchantManager {

    private static $DEBUG = false;

    /**
	* PURCHASE ACTION WITH CUSTOM CARD (VISA, MAESTRO, MASTERCARD) ON MERCHANT
	* @param int $site_session_id
	* @param int $pc_session_id
	* @param string $amount
	* @param string $payment_method
    * @param string $payment_method_id
	* @param string $ip_address
	* @param string $bonus_code
    * @param string $css_template
	* @return mixed
	*/
	public static function getWirecardPaymentPurchaseCustomCardMessage($site_session_id, $pc_session_id, $amount, $payment_method, $payment_method_id, $ip_address, $bonus_code, $css_template = 'Default'){
        if(self::$DEBUG) {
            //DEBUG THIS
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardXml(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
            WirecardErrorHelper::wirecardAccess($message, $message);
        }
		if(strlen($site_session_id)==0 || strlen($pc_session_id)==0 || strlen($amount)==0 || strlen($payment_method)==0 || strlen($payment_method_id)==0){
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
		    WirecardErrorHelper::wirecardError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_INVALID_DATA)));
            exit($json_message);
		}
		$site_session_id = intval(strip_tags($site_session_id));
		$pc_session_id = intval(strip_tags($pc_session_id));
		$amount = strip_tags($amount);
		$payment_method = strip_tags($payment_method);
        $payment_method_id = strip_tags($payment_method_id);
		$bonus_code = strip_tags($bonus_code);
        $css_template = strip_tags($css_template);
		$ip_address = strip_tags($ip_address);
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];

        $is_3d_secure = WirecardMerchantHelper::isSecureCard($payment_method);

        $card_type = WirecardMerchantHelper::returnCardType($payment_method);

        if(strlen($css_template) == 0){
            $css_template = 'Default';
        }

		try{
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            //get the currency for player
            $currencyData = $modelMerchant->currencyCodeForSession($pc_session_id);
            if($currencyData['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
            }
            //ex. EUR
            $currency_text = $currencyData['currency_text'];
            //ex. 978
            $currency_code = $currencyData['currency_code'];
            //check if player is playing games during purchase
            $openGameSessionStatus = $modelMerchant->checkOpenGameSession($pc_session_id);
            if($openGameSessionStatus['status'] == NOK){
                //player has opened game session cannot payin credits via Wirecard
                $res = $modelMerchant->closeOpenedGameSession($pc_session_id);
                if($res['status'] != OK){
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_GAME_SESSION_OPENED)));
                    exit($json_message);
                }
            }

            //check transaction limit for player
            $transaction_limit = NO;
            //get transaction id from database
			$transactionData = $modelMerchant->getTransactionId($site_session_id, $currency_code);
			if($transactionData["status"] == NOK){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
			}
			$transaction_id = $transactionData['transaction_id'];
			$currency_ok = $transactionData['currency_ok'];
			$oref_transaction_id = $transactionData['oref_transaction_id'];
            $db_transaction_id = $transactionData['payment_provider_transaction_id_purchase'];
			//if wirecard payment does not support currency from web site return no currency error
			if($currency_ok != YES){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_CURRENCY)));
                exit($json_message);
			}
			//get player email address on site session id
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$resultDetails = $modelPlayer->getPlayerDetailsMalta($site_session_id, $arrPlayer['player_id']);
			if($resultDetails['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
			}
			$details = $resultDetails['details'];
			$player_email = $details['email'];
			//client account number is players unique id
			$player_id = $arrPlayer['player_id'];
            //player default language
            switch($details['bo_default_language']){
                case 'de_DE':
                case 'de': //german
                case 'at_AT':
                case 'at':
                    $language = 'de';
                    break;
                case 'se_SE':
                case 'sv_SE':
                case 'se': //swedish
                    $language = 'se';
                    break;
                case 'da_DK':
                case 'da': //denmark
                    $language = 'dk';
                    break;
                case 'it_IT':
                case 'it': //italian
                    $language = 'it';
                    break;
                case 'ru_RU':
                case 'ru': //russia
                    $language = 'ru';
                    break;
                case 'pl_PL':
                case 'pl': //poland
                    $language = 'pl';
                    break;
                case 'hr_HR':
                case 'hr': //croatian
                    $language = 'hr';
                    break;
                case 'rs_RS':
                case 'rs': //serbian
                    $language = 'rs';
                    break;
                case 'hu_HU':
                case 'hu': //hungarian
                    $language = 'hu';
                    break;
                case 'fr_FR':
                case 'fr': //french
                    $language = 'fr';
                    break;
                case 'mt_MT':
                case 'mt': //maltese
                    $language = 'mt';
                    break;
                case 'cs_CZ':
                case 'cz':
                case 'cs': //chech republic
                    $language = "cz";
                    break;
                case 'sk_SK':
                case 'sk': //slovak
                    $language = 'sk';
                    break;
                case 'es_ES':
                case 'es': //spanish
                    $language = 'es';
                    break;
                case 'nl_NL':
                case 'nl': //netherland (dutch)
                    $language = 'nl';
                    break;
                case 'bg_BG':
                case 'bg': //bulgarian
                    $language = 'bg';
                    break;
                case 'pt_PT': //portugal
                case 'pt':
                    $language = 'pt';
                    break;
                case 'nb_NO': //norway
                case 'nn_NO':
                case 'no':
                    $language = 'no';
                    break;
                case 'tr_TR': //turkey
                case 'tr':
                    $language = 'tr';
                    break;
                case 'el_EL': //greek
                case 'el_GR':
                case 'gr':
                    $language = 'gr';
                    break;
                case 'sv_FI': //finish
                case 'sv':
                    $language = 'fi';
                    break;
                case 'ro_RO': //romania
                case 'ro':
                    $language = 'ro';
                    break;
                case 'sl_SI': //slovenia
                case 'si':
                    $language = 'si';
                    break;
                case 'zh_Cn': //chineese
                case 'zh':
                    $language = 'zh';
                    break;
                case 'ja_JP': //japan
                case 'jp':
                    $language = 'jp';
                    break;
                case 'en_GB': //english (default)
                default:
                    $language = "en";
            }
            //find site settings for player with his player_id
            $siteSettings = $modelMerchant->findSiteSettings($arrPlayer['player_id']);
            if($siteSettings['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
            }
            //FEE TAX BEGIN
            $fee_amount = 0;
            require_once MODELS_DIR . DS . 'WebSiteFeeModel.php';
            $modelWebSiteFee = new WebSiteFeeModel();
            $feeResult = $modelWebSiteFee->depositFeePart1($arrPlayer['player_id'], $amount, $currency_text, $payment_method_id);
            if($feeResult['status'] != OK && $feeResult['error_code'] == 20303){
                //if player has deposited large amount he is free of fee payment
                $deposit_amount = $feeResult['deposit_amount'];
                $amount = $feeResult['deposit_amount_out'];
                $fee_amount = $feeResult['fee_value_out'];
            }else{
                if($feeResult['status'] != OK){
                    //if fee could not be processed for any reason in our database
                    $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
                        payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template}) method:
                        <br /> Fee Could not be processed";
                    WirecardErrorHelper::wirecardError($message, $message);
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION), "deposit_amount"=>$amount, "fee_error_message"=>$feeResult['error_message'], "fee_error_code"=>$feeResult['error_code']));
                    exit($json_message);
                }else{
                    //if fee could get processed in our database with correct result response
                    //player entered this amount (basic amount he wanted to pay)
                    $deposit_amount = $feeResult['deposit_amount'];
                    //new amount from player + our fee on his amount (player_amount + fee_amount)
                    $amount = $feeResult['deposit_amount_out'];
                    //only fee amount on player's amount (fee_amount)
                    $fee_amount = $feeResult['fee_value_out'];
                }
            }

            date_default_timezone_set("UTC");
            $request_time_stamp = gmdate("Y") . gmdate("m") . gmdate("d") . gmdate("H") . gmdate("i") . gmdate("s");
            $request_id = substr(hash('sha256', trim($oref_transaction_id . $request_time_stamp)), 0, 32);

            $casino_name = $siteSettings['casino_name'];
            $payment_method_name = $payment_method;
            $payment_method = "creditcard";

            $account = WirecardMerchantHelper::getAccountSpecificDataPerPaymentMethod($payment_method_name);
            $merchant_account_id = $account['merchant_account_id'];
            $secret_key = $account['secret_key'];
            $transaction_type = $account['transaction_type'];
            $descriptor = $account['descriptor'];

            $requested_amount = $deposit_amount;
            $requested_amount_currency = $currency_text;

            $request_signature = hash('sha256', trim(
                $request_time_stamp .
                $request_id .
                $merchant_account_id .
                $transaction_type .
                $requested_amount .
                $requested_amount_currency .
                $ip_address .
                $secret_key
            ));

            $success_redirect_url = $siteSettings['wirecard_redirection_site_success_link'];
            $fail_redirect_url = $siteSettings['wirecard_redirection_site_failed_link'];
            $notification_transaction_url = $siteSettings['wirecard_purchase_link'];
            $notifications_format = "application/x-www-form-urlencoded";
            $locale = $language;
            $field_name_1 = "field_value_1";
            $field_value_1 = implode(';', array("OREF={$casino_name}", "PC_SESSION_ID={$pc_session_id}", "PAYMENT_METHOD={$payment_method_name}", "CURRENCY={$currency_text}", "PAYMENT_METHOD_ID={$payment_method_id}"));
            $field_name_2 = "field_value_2";
            $field_value_2 = implode(';', array("TRANSACTION_LIMIT={$transaction_limit}", "PLAYER_ID={$player_id}", "OREF_TRANSACTION_ID={$oref_transaction_id}", "BONUS_CODE={$bonus_code}"));
            $field_name_3 = "field_value_3";
            $field_value_3 = implode(';', array("TRANSACTION_ID={$transaction_id}", "FEE_AMOUNT={$fee_amount}", "DEPOSIT_AMOUNT={$deposit_amount}", "DB_TRANSACTION_ID={$db_transaction_id}"));
            $email = $player_email;
            $merchant_crm_id = $player_id;
            $attempt_three_d = ($is_3d_secure == true) ? "true" : "false";

            //returns status, message for wirecard post,, how much player wants to deposit, how much is fee + player deposit amount, how much is only fee we charge, currency that player is using, fee error message text
            $json_message = Zend_Json::encode(array(
                "status"=>OK,
                "player_deposit_amount"=>$deposit_amount,
                "player_fee_deposit_amount"=>$amount,
                "fee_amount"=>$fee_amount,
                "currency"=>$currency_text,
                "fee_error_message"=>$feeResult['error_message'],

                "request_time_stamp"=>$request_time_stamp,
                "request_id"=> $request_id,
                "merchant_account_id"=>$merchant_account_id,
                "transaction_type"=>$transaction_type,
                "requested_amount"=>$requested_amount,
                "requested_amount_currency"=>$requested_amount_currency,
                "ip_address"=>$ip_address,
                "request_signature"=>$request_signature,
                "card_type"=>$card_type,
                "payment_method"=>$payment_method,
                //"payment_method_name"=>$payment_method_name,
                "success_redirect_url"=>$success_redirect_url,
                "fail_redirect_url"=>$fail_redirect_url,
                "notification_transaction_url"=>$notification_transaction_url,
                "redirect_url"=>$notification_transaction_url,
                "notifications_format"=>$notifications_format,
                "locale"=>$locale,
                "field_name_1"=>$field_name_1,
                "field_value_1"=>$field_value_1,
                "field_name_2"=>$field_name_2,
                "field_value_2"=>$field_value_2,
                "field_name_3"=>$field_name_3,
                "field_value_3"=>$field_value_3,
                "email"=>$email,
                "merchant_crm_id"=>$merchant_crm_id,
                "attempt_three_d"=>$attempt_three_d
            ));
            exit($json_message);

        }catch(Zend_Exception $ex){
			//returns exception to web site
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
                payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code},
                css_template = {$css_template}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
            exit($json_message);
		}
	}

    /**
	* PURCHASE ACTION WITH CUSTOM CARD (VISA, MAESTRO, MASTERCARD) ON MERCHANT WITH RECCURING TRANSACTION THROUGH TOKEN ID
	* @param int $site_session_id
	* @param int $pc_session_id
	* @param string $amount
	* @param string $payment_method
    * @param string $payment_method_id
    * @param string $token_id
	* @param string $ip_address
	* @param string $bonus_code
    * @param string $css_template
	* @return mixed
	*/
	public static function getWirecardPaymentPurchaseCustomCardWithTokenMessage($site_session_id, $pc_session_id, $amount, $payment_method, $payment_method_id,
        $token_id, $ip_address, $bonus_code, $css_template = 'Default'){
        if(self::$DEBUG) {
            //DEBUG THIS
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardXml(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, token_id = {$token_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
            WirecardErrorHelper::wirecardAccess($message, $message);
        }
		if(strlen($site_session_id)==0 || strlen($pc_session_id)==0 || strlen($amount)==0 || strlen($payment_method)==0 || strlen($payment_method_id)==0 || strlen($token_id) == 0){
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, token_id = {$token_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
		    WirecardErrorHelper::wirecardError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_INVALID_DATA)));
            exit($json_message);
		}
		$site_session_id = intval(strip_tags($site_session_id));
		$pc_session_id = intval(strip_tags($pc_session_id));
		$amount = strip_tags($amount);
		$payment_method = strip_tags($payment_method);
        $payment_method_id = strip_tags($payment_method_id);
		$bonus_code = strip_tags($bonus_code);
        $css_template = strip_tags($css_template);
		$ip_address = strip_tags($ip_address);
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
        $token_id = strip_tags($token_id);

        $is_3d_secure = WirecardMerchantHelper::isSecureCard($payment_method);

        $card_type = WirecardMerchantHelper::returnCardType($payment_method);

        if(strlen($css_template) == 0){
            $css_template = 'Default';
        }

		try{
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            //get the currency for player
            $currencyData = $modelMerchant->currencyCodeForSession($pc_session_id);
            if($currencyData['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
            }
            //ex. EUR
            $currency_text = $currencyData['currency_text'];
            //ex. 978
            $currency_code = $currencyData['currency_code'];
            //check if player is playing games during purchase
            $openGameSessionStatus = $modelMerchant->checkOpenGameSession($pc_session_id);
            if($openGameSessionStatus['status'] == NOK){
                //player has opened game session cannot payin credits via Wirecard
                $res = $modelMerchant->closeOpenedGameSession($pc_session_id);
                if($res['status'] != OK){
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_GAME_SESSION_OPENED)));
                    exit($json_message);
                }
            }

            //check transaction limit for player
            $transaction_limit = NO;
            //get transaction id from database
			$transactionData = $modelMerchant->getTransactionId($site_session_id, $currency_code);
			if($transactionData["status"] == NOK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
			}
			$transaction_id = $transactionData['transaction_id'];
			$currency_ok = $transactionData['currency_ok'];
			$oref_transaction_id = $transactionData['oref_transaction_id'];
            $db_transaction_id = $transactionData['payment_provider_transaction_id_purchase'];
			//if wirecard payment does not support currency from web site return no currency error
			if($currency_ok != YES){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_CURRENCY)));
                exit($json_message);
			}
			//get player email address on site session id
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$resultDetails = $modelPlayer->getPlayerDetailsMalta($site_session_id, $arrPlayer['player_id']);
			if($resultDetails['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
			}
			$details = $resultDetails['details'];
			$player_email = $details['email'];
			//client account number is players unique id
			$player_id = $arrPlayer['player_id'];
            //player default language
            switch($details['bo_default_language']){
                case 'de_DE':
                case 'de': //german
                case 'at_AT':
                case 'at':
                    $language = 'de';
                    break;
                case 'se_SE':
                case 'sv_SE':
                case 'se': //swedish
                    $language = 'se';
                    break;
                case 'da_DK':
                case 'da': //denmark
                    $language = 'dk';
                    break;
                case 'it_IT':
                case 'it': //italian
                    $language = 'it';
                    break;
                case 'ru_RU':
                case 'ru': //russia
                    $language = 'ru';
                    break;
                case 'pl_PL':
                case 'pl': //poland
                    $language = 'pl';
                    break;
                case 'hr_HR':
                case 'hr': //croatian
                    $language = 'hr';
                    break;
                case 'rs_RS':
                case 'rs': //serbian
                    $language = 'rs';
                    break;
                case 'hu_HU':
                case 'hu': //hungarian
                    $language = 'hu';
                    break;
                case 'fr_FR':
                case 'fr': //french
                    $language = 'fr';
                    break;
                case 'mt_MT':
                case 'mt': //maltese
                    $language = 'mt';
                    break;
                case 'cs_CZ':
                case 'cz':
                case 'cs': //chech republic
                    $language = "cz";
                    break;
                case 'sk_SK':
                case 'sk': //slovak
                    $language = 'sk';
                    break;
                case 'es_ES':
                case 'es': //spanish
                    $language = 'es';
                    break;
                case 'nl_NL':
                case 'nl': //netherland (dutch)
                    $language = 'nl';
                    break;
                case 'bg_BG':
                case 'bg': //bulgarian
                    $language = 'bg';
                    break;
                case 'pt_PT': //portugal
                case 'pt':
                    $language = 'pt';
                    break;
                case 'nb_NO': //norway
                case 'nn_NO':
                case 'no':
                    $language = 'no';
                    break;
                case 'tr_TR': //turkey
                case 'tr':
                    $language = 'tr';
                    break;
                case 'el_EL': //greek
                case 'el_GR':
                case 'gr':
                    $language = 'gr';
                    break;
                case 'sv_FI': //finish
                case 'sv':
                    $language = 'fi';
                    break;
                case 'ro_RO': //romania
                case 'ro':
                    $language = 'ro';
                    break;
                case 'sl_SI': //slovenia
                case 'si':
                    $language = 'si';
                    break;
                case 'zh_Cn': //chineese
                case 'zh':
                    $language = 'zh';
                    break;
                case 'ja_JP': //japan
                case 'jp':
                    $language = 'jp';
                    break;
                case 'en_GB': //english (default)
                default:
                    $language = "en";
            }
            //find site settings for player with his player_id
            $siteSettings = $modelMerchant->findSiteSettings($arrPlayer['player_id']);
            if($siteSettings['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
            }
            //FEE TAX BEGIN
            $fee_amount = 0;
            require_once MODELS_DIR . DS . 'WebSiteFeeModel.php';
            $modelWebSiteFee = new WebSiteFeeModel();
            $feeResult = $modelWebSiteFee->depositFeePart1($arrPlayer['player_id'], $amount, $currency_text, $payment_method_id);
            if($feeResult['status'] != OK && $feeResult['error_code'] == 20303){
                //if player has deposited large amount he is free of fee payment
                $deposit_amount = $feeResult['deposit_amount'];
                $amount = $feeResult['deposit_amount_out'];
                $fee_amount = $feeResult['fee_value_out'];
            }else{
                if($feeResult['status'] != OK){
                    //if fee could not be processed for any reason in our database
                    $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
                        payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, token_id = {$token_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code},
                        css_template = {$css_template}) method: <br /> Fee Could not be processed";
                    WirecardErrorHelper::wirecardError($message, $message);
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION), "deposit_amount"=>$amount, "fee_error_message"=>$feeResult['error_message'], "fee_error_code"=>$feeResult['error_code']));
                    exit($json_message);
                }else{
                    //if fee could get processed in our database with correct result response
                    //player entered this amount (basic amount he wanted to pay)
                    $deposit_amount = $feeResult['deposit_amount'];
                    //new amount from player + our fee on his amount (player_amount + fee_amount)
                    $amount = $feeResult['deposit_amount_out'];
                    //only fee amount on player's amount (fee_amount)
                    $fee_amount = $feeResult['fee_value_out'];
                }
            }

            date_default_timezone_set("UTC");
            $request_time_stamp = gmdate("Y") . gmdate("m") . gmdate("d") . gmdate("H") . gmdate("i") . gmdate("s");
            $request_id = substr(hash('sha256', trim($oref_transaction_id . $request_time_stamp)), 0, 32);

            $casino_name = $siteSettings['casino_name'];
            $payment_method_name = $payment_method;
            $payment_method = "creditcard";

            $account = WirecardMerchantHelper::getAccountSpecificDataPerPaymentMethod($payment_method_name);
            $merchant_account_id = $account['merchant_account_id'];
            $secret_key = $account['secret_key'];
            $transaction_type = $account['transaction_type'];
            $descriptor = $account['descriptor'];

            $requested_amount = $deposit_amount;
            $requested_amount_currency = $currency_text;

            $request_signature = hash('sha256', trim(
                $request_time_stamp .
                $request_id .
                $merchant_account_id .
                $transaction_type .
                $requested_amount .
                $requested_amount_currency .
                $ip_address .
                $secret_key
            ));

            $success_redirect_url = $siteSettings['wirecard_redirection_site_success_link'];
            $fail_redirect_url = $siteSettings['wirecard_redirection_site_failed_link'];
            $notification_transaction_url = $siteSettings['wirecard_purchase_link'];
            $notifications_format = "application/x-www-form-urlencoded";
            $locale = $language;
            $field_name_1 = "field_value_1";
            $field_value_1 = implode(';', array("OREF={$casino_name}", "PC_SESSION_ID={$pc_session_id}", "PAYMENT_METHOD={$payment_method_name}", "CURRENCY={$currency_text}", "PAYMENT_METHOD_ID={$payment_method_id}"));
            $field_name_2 = "field_value_2";
            $field_value_2 = implode(';', array("TRANSACTION_LIMIT={$transaction_limit}", "PLAYER_ID={$player_id}", "OREF_TRANSACTION_ID={$oref_transaction_id}", "BONUS_CODE={$bonus_code}"));
            $field_name_3 = "field_value_3";
            $field_value_3 = implode(';', array("TRANSACTION_ID={$transaction_id}", "FEE_AMOUNT={$fee_amount}", "DEPOSIT_AMOUNT={$deposit_amount}", "DB_TRANSACTION_ID={$db_transaction_id}"));
            $email = $player_email;
            $merchant_crm_id = $player_id;
            $attempt_three_d = ($is_3d_secure == true) ? "true" : "false";

            //returns status, message for wirecard post,, how much player wants to deposit, how much is fee + player deposit amount, how much is only fee we charge, currency that player is using, fee error message text
            $json_message = Zend_Json::encode(array(
                "status"=>OK,
                "player_deposit_amount"=>$deposit_amount,
                "player_fee_deposit_amount"=>$amount,
                "fee_amount"=>$fee_amount,
                "currency"=>$currency_text,
                "fee_error_message"=>$feeResult['error_message'],

                "request_time_stamp"=>$request_time_stamp,
                "request_id"=> $request_id,
                "merchant_account_id"=>$merchant_account_id,
                "transaction_type"=>$transaction_type,
                "requested_amount"=>$requested_amount,
                "requested_amount_currency"=>$requested_amount_currency,
                "ip_address"=>$ip_address,
                "request_signature"=>$request_signature,
                "card_type"=>$card_type,
                "payment_method"=>$payment_method,
                //"payment_method_name"=>$payment_method_name,
                "success_redirect_url"=>$success_redirect_url,
                "fail_redirect_url"=>$fail_redirect_url,
                "notification_transaction_url"=>$notification_transaction_url,
                "redirect_url"=>$notification_transaction_url,
                "notifications_format"=>$notifications_format,
                "locale"=>$locale,
                "field_name_1"=>$field_name_1,
                "field_value_1"=>$field_value_1,
                "field_name_2"=>$field_name_2,
                "field_value_2"=>$field_value_2,
                "field_name_3"=>$field_name_3,
                "field_value_3"=>$field_value_3,
                "email"=>$email,
                "merchant_crm_id"=>$merchant_crm_id,
                "attempt_three_d"=>$attempt_three_d,
                "token_id"=>$token_id,
                "periodic_type"=> "recurring"
            ));
            exit($json_message);

        }catch(Zend_Exception $ex){
			//returns exception to web site
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
                payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, token_id = {$token_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
            exit($json_message);
		}
	}

    /**
	* PURCHASE ACTION WITH CUSTOM CARD (VISA, MAESTRO, MASTERCARD) ON MERCHANT
	* @param int $site_session_id
	* @param int $pc_session_id
	* @param string $amount
	* @param string $payment_method
    * @param string $payment_method_id
	* @param string $ip_address
	* @param string $bonus_code
    * @param string $css_template
	* @return mixed
	*/
	public static function getWirecardPaymentPurchaseCustomPaymentMethodMessage($site_session_id, $pc_session_id, $amount, $payment_method, $payment_method_id, $ip_address, $bonus_code, $css_template = 'Default'){
        if(self::$DEBUG) {
            //DEBUG THIS
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardXml(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
            WirecardErrorHelper::wirecardAccess($message, $message);
        }
		if(strlen($site_session_id)==0 || strlen($pc_session_id)==0 || strlen($amount)==0 || strlen($payment_method)==0 || strlen($payment_method_id)==0){
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomPaymentMethodMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
		    WirecardErrorHelper::wirecardError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_INVALID_DATA)));
            exit($json_message);
		}
		$site_session_id = intval(strip_tags($site_session_id));
		$pc_session_id = intval(strip_tags($pc_session_id));
		$amount = strip_tags($amount);
		$payment_method = strip_tags($payment_method);
        $payment_method_id = strip_tags($payment_method_id);
		$bonus_code = strip_tags($bonus_code);
        $css_template = strip_tags($css_template);
		$ip_address = strip_tags($ip_address);
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];

        switch(strtoupper($payment_method)){
            case 'PSC':
                $payment_method_for_provider = "PAYSAFECARD";
                break;
            case 'MBKR':
                $payment_method_for_provider = "SKRILL";
                break;
            case 'SOFORT':
                $payment_method_for_provider = "SOFORTBANKING";
                break;
            case 'PRZLEWY':
                $payment_method_for_provider = "P24";
                break;
            default:
                $payment_method_for_provider = strtolower($payment_method);
        }
        $payment_method_for_provider = strtolower($payment_method_for_provider);

        if(strlen($css_template) == 0){
            $css_template = 'Default';
        }

		try{
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            //get the currency for player
            $currencyData = $modelMerchant->currencyCodeForSession($pc_session_id);
            if($currencyData['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
            }
            //ex. EUR
            $currency_text = $currencyData['currency_text'];
            //ex. 978
            $currency_code = $currencyData['currency_code'];
            //check if player is playing games during purchase
            $openGameSessionStatus = $modelMerchant->checkOpenGameSession($pc_session_id);
            if($openGameSessionStatus['status'] == NOK){
                //player has opened game session cannot payin credits via Wirecard
                $res = $modelMerchant->closeOpenedGameSession($pc_session_id);
                if($res['status'] != OK){
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_GAME_SESSION_OPENED)));
                    exit($json_message);
                }
            }

            //check transaction limit for player
            $transaction_limit = NO;
            //get transaction id from database
			$transactionData = $modelMerchant->getTransactionId($site_session_id, $currency_code);
			if($transactionData["status"] == NOK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
			}
			$transaction_id = $transactionData['transaction_id'];
			$currency_ok = $transactionData['currency_ok'];
			$oref_transaction_id = $transactionData['oref_transaction_id'];
            $db_transaction_id = $transactionData['payment_provider_transaction_id_purchase'];
			//if wirecard payment does not support currency from web site return no currency error
			if($currency_ok != YES){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_CURRENCY)));
                exit($json_message);
			}
			//get player email address on site session id
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$resultDetails = $modelPlayer->getPlayerDetailsMalta($site_session_id, $arrPlayer['player_id']);
			if($resultDetails['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
			}
			$details = $resultDetails['details'];
			$player_email = $details['email'];
			//client account number is players unique id
			$player_id = $arrPlayer['player_id'];
            //player default language
            switch($details['bo_default_language']){
                case 'de_DE':
                case 'de': //german
                case 'at_AT':
                case 'at':
                    $language = 'de';
                    break;
                case 'se_SE':
                case 'sv_SE':
                case 'se': //swedish
                    $language = 'se';
                    break;
                case 'da_DK':
                case 'da': //denmark
                    $language = 'dk';
                    break;
                case 'it_IT':
                case 'it': //italian
                    $language = 'it';
                    break;
                case 'ru_RU':
                case 'ru': //russia
                    $language = 'ru';
                    break;
                case 'pl_PL':
                case 'pl': //poland
                    $language = 'pl';
                    break;
                case 'hr_HR':
                case 'hr': //croatian
                    $language = 'hr';
                    break;
                case 'rs_RS':
                case 'rs': //serbian
                    $language = 'rs';
                    break;
                case 'hu_HU':
                case 'hu': //hungarian
                    $language = 'hu';
                    break;
                case 'fr_FR':
                case 'fr': //french
                    $language = 'fr';
                    break;
                case 'mt_MT':
                case 'mt': //maltese
                    $language = 'mt';
                    break;
                case 'cs_CZ':
                case 'cz':
                case 'cs': //chech republic
                    $language = "cz";
                    break;
                case 'sk_SK':
                case 'sk': //slovak
                    $language = 'sk';
                    break;
                case 'es_ES':
                case 'es': //spanish
                    $language = 'es';
                    break;
                case 'nl_NL':
                case 'nl': //netherland (dutch)
                    $language = 'nl';
                    break;
                case 'bg_BG':
                case 'bg': //bulgarian
                    $language = 'bg';
                    break;
                case 'pt_PT': //portugal
                case 'pt':
                    $language = 'pt';
                    break;
                case 'nb_NO': //norway
                case 'nn_NO':
                case 'no':
                    $language = 'no';
                    break;
                case 'tr_TR': //turkey
                case 'tr':
                    $language = 'tr';
                    break;
                case 'el_EL': //greek
                case 'el_GR':
                case 'gr':
                    $language = 'gr';
                    break;
                case 'sv_FI': //finish
                case 'sv':
                    $language = 'fi';
                    break;
                case 'ro_RO': //romania
                case 'ro':
                    $language = 'ro';
                    break;
                case 'sl_SI': //slovenia
                case 'si':
                    $language = 'si';
                    break;
                case 'zh_Cn': //chineese
                case 'zh':
                    $language = 'zh';
                    break;
                case 'ja_JP': //japan
                case 'jp':
                    $language = 'jp';
                    break;
                case 'en_GB': //english (default)
                default:
                    $language = "en";
            }
            //find site settings for player with his player_id
            $siteSettings = $modelMerchant->findSiteSettings($arrPlayer['player_id']);
            if($siteSettings['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
                exit($json_message);
            }
            //FEE TAX BEGIN
            $fee_amount = 0;
            require_once MODELS_DIR . DS . 'WebSiteFeeModel.php';
            $modelWebSiteFee = new WebSiteFeeModel();
            $feeResult = $modelWebSiteFee->depositFeePart1($arrPlayer['player_id'], $amount, $currency_text, $payment_method_id);
            if($feeResult['status'] != OK && $feeResult['error_code'] == 20303){
                //if player has deposited large amount he is free of fee payment
                $deposit_amount = $feeResult['deposit_amount'];
                $amount = $feeResult['deposit_amount_out'];
                $fee_amount = $feeResult['fee_value_out'];
            }else{
                if($feeResult['status'] != OK){
                    //if fee could not be processed for any reason in our database
                    $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomPaymentMethodMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
                        payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template}) method: <br /> Fee Could not be processed";
                    WirecardErrorHelper::wirecardError($message, $message);
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION), "deposit_amount"=>$amount, "fee_error_message"=>$feeResult['error_message'], "fee_error_code"=>$feeResult['error_code']));
                    exit($json_message);
                }else{
                    //if fee could get processed in our database with correct result response
                    //player entered this amount (basic amount he wanted to pay)
                    $deposit_amount = $feeResult['deposit_amount'];
                    //new amount from player + our fee on his amount (player_amount + fee_amount)
                    $amount = $feeResult['deposit_amount_out'];
                    //only fee amount on player's amount (fee_amount)
                    $fee_amount = $feeResult['fee_value_out'];
                }
            }

            date_default_timezone_set("UTC");
            $request_time_stamp = gmdate("Y") . gmdate("m") . gmdate("d") . gmdate("H") . gmdate("i") . gmdate("s");
            //$request_id = $db_transaction_id;
            $request_id = substr(hash('sha256', trim($oref_transaction_id . $request_time_stamp)), 0, 32);

            $descriptor = "";

            $account = WirecardMerchantHelper::getAccountSpecificDataPerPaymentMethod($payment_method_for_provider);
            $merchant_account_id = $account['merchant_account_id'];
            $secret_key = $account['secret_key'];
            $transaction_type = $account['transaction_type'];
            $descriptor = $account['descriptor'];

            $requested_amount = $deposit_amount;
            $requested_amount_currency = $currency_text;

            if($secret_key != "") {
                $request_signature = hash('sha256', trim(
                    $request_time_stamp .
                    $request_id .
                    $merchant_account_id .
                    $transaction_type .
                    $requested_amount .
                    $requested_amount_currency .
                    $ip_address .
                    $secret_key
                ));
            }else{
                $request_signature = "";
            }

            $casino_name = $siteSettings['casino_name'];
            $payment_method_name = $payment_method;

            $success_redirect_url = $siteSettings['wirecard_redirection_site_success_link'];
            $fail_redirect_url = $siteSettings['wirecard_redirection_site_failed_link'];
            $cancel_redirect_url = $siteSettings['wirecard_redirection_site_failed_link']; /// replace with CANCEL URL
            $notification_transaction_url = $siteSettings['wirecard_purchase_link'];
            $notifications_format = "application/x-www-form-urlencoded";
            $locale = $language;
            $field_name_1 = "field_value_1";
            $field_value_1 = implode(';', array("OREF={$casino_name}", "PC_SESSION_ID={$pc_session_id}", "PAYMENT_METHOD={$payment_method_name}", "CURRENCY={$currency_text}", "PAYMENT_METHOD_ID={$payment_method_id}"));
            $field_name_2 = "field_value_2";
            $field_value_2 = implode(';', array("TRANSACTION_LIMIT={$transaction_limit}", "PLAYER_ID={$player_id}", "OREF_TRANSACTION_ID={$oref_transaction_id}", "BONUS_CODE={$bonus_code}"));
            $field_name_3 = "field_value_3";
            $field_value_3 = implode(';', array("TRANSACTION_ID={$transaction_id}", "FEE_AMOUNT={$fee_amount}", "DEPOSIT_AMOUNT={$deposit_amount}", "DB_TRANSACTION_ID={$db_transaction_id}"));
            $email = $player_email;
            $merchant_crm_id = $player_id;

            //returns status, message for wirecard post,, how much player wants to deposit, how much is fee + player deposit amount, how much is only fee we charge, currency that player is using, fee error message text
            $json_message = Zend_Json::encode(array(
                "status"=>OK,
                "player_deposit_amount"=>$deposit_amount,
                "player_fee_deposit_amount"=>$amount,
                "fee_amount"=>$fee_amount,
                "currency"=>$currency_text,
                "fee_error_message"=>$feeResult['error_message'],

                "request_time_stamp"=>$request_time_stamp,
                "request_id"=> $request_id,
                "merchant_account_id"=>$merchant_account_id,
                "transaction_type"=>$transaction_type,
                "requested_amount"=>$requested_amount,
                "requested_amount_currency"=>$requested_amount_currency,
                "ip_address"=>$ip_address,
                "request_signature"=>$request_signature,
                "payment_method"=>$payment_method_for_provider,
                //"payment_method_name"=>$payment_method_name,
                "success_redirect_url"=>$success_redirect_url,
                "cancel_redirect_url"=>$cancel_redirect_url,
                "fail_redirect_url"=>$fail_redirect_url,
                "notification_transaction_url"=>$notification_transaction_url,
                "redirect_url"=>$notification_transaction_url,
                "notifications_format"=>$notifications_format,
                "locale"=>$locale,
                "field_name_1"=>$field_name_1,
                "field_value_1"=>$field_value_1,
                "field_name_2"=>$field_name_2,
                "field_value_2"=>$field_value_2,
                "field_name_3"=>$field_name_3,
                "field_value_3"=>$field_value_3,
                "email"=>$email,
                "merchant_crm_id"=>$merchant_crm_id,

                "descriptor"=>$descriptor
            ));
            exit($json_message);

        }catch(Zend_Exception $ex){
			//returns exception to web site
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomPaymentMethodMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
                payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION)));
            exit($json_message);
		}
	}

    /**
	 *
	 * Calls player from web site to send his payout request for our database ...
	 * @param int $pc_session_id
	 * @param string $wirecard_transaction_id
     * @param string $token_id
	 * @param string $payment_method
     * @param string $payment_method_id
	 * @param number $amount
	 * @param string $ip_address
	 * @return mixed
	 */
	public static function wirecardWithdrawRequest($pc_session_id, $wirecard_transaction_id, $token_id, $payment_method, $payment_method_id, $amount, $ip_address){
        if(self::$DEBUG) {
            //DEBUG HERE
            $message = "SiteWirecardMerchantManager::payout(pc_session_id = {$pc_session_id}, wirecard_transaction_id = {$wirecard_transaction_id},
            token_id = {$token_id}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount}, ip_address = {$ip_address})";
            WirecardErrorHelper::wirecardAccess($message, $message);
        }
        if($payment_method == 'ENTERCASH' && $wirecard_transaction_id == 'undefined'){
			$wirecard_transaction_id = null;
		}
		if(strlen($pc_session_id)==0 || strlen($payment_method)==0 || strlen($payment_method_id)==0 || strlen($amount)==0 || strlen($ip_address) == 0 || $wirecard_transaction_id == 'undefined' || strlen($token_id) == 0){
            $message = "SiteWirecardMerchantManager::payout(pc_session_id = {$pc_session_id}, wirecard_transaction_id = {$wirecard_transaction_id},
            $token_id = {$token_id}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount}, ip_address = {$ip_address})";
		    WirecardErrorHelper::wirecardError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_INVALID_DATA)));
            exit($json_message);
		}
		//receive parameters, convert some to numbers
		//strip parameters from malvare html / javascript tags
		$pc_session_id = intval(strip_tags($pc_session_id));
		$wirecard_transaction_id = strip_tags($wirecard_transaction_id);
        $token_id = strip_tags($token_id);
		$payment_method = strip_tags($payment_method);
        $payment_method_id = strip_tags($payment_method_id);
		$amount = doubleval(strip_tags($amount));
		$ip_address = strip_tags($ip_address);

        $transaction_id = null;
        $credit_card_number = null;
        $credit_card_expiration_date = null;
		$credit_card_holder = null;
		$credit_card_country = null;
        $credit_card_type = "WIRECARD";
		$start_time = null;
        $bank_code = null;
		$ip_address = null;
        $card_issuer_bank = null;
		$card_country = null;
        $client_email = null;
		$over_limit = null;
        $bank_auth_code = null;
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
        try{
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();

            //check if player has opened game sessions
            $openGameSessionStatus = $modelMerchant->checkOpenGameSession($pc_session_id);
            if($openGameSessionStatus['status'] == NOK){
                $res = $modelMerchant->closeOpenedGameSession($pc_session_id);
                if($res['status'] != OK){
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_GAME_SESSION_OPENED)));
                    exit($json_message);
                }
            }

            //get the currency for player
            $currencyData = $modelMerchant->currencyCodeForSession($pc_session_id);
            if($currencyData['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1"));
                exit($json_message);
            }
            //ex. EUR
            $currency_text = $currencyData['currency_text'];
            //ex. 978
            $currency_code = $currencyData['currency_code'];
            //get player_id from pc_session_id
            require_once MODELS_DIR . DS . 'WebSiteModel.php';
            $modelWebSite = new WebSiteModel();
            $arrPlayer = $modelWebSite->sessionIdToPlayerId($pc_session_id);
            //get site settings for this player and his player_id
            $siteSettings = $modelMerchant->findSiteSettings($arrPlayer['player_id']);
            if($siteSettings['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1"));
                exit($json_message);
            }
            $casinoName = $siteSettings['casino_name'];

            //WITHDRAW FEE PART 1 call - here it takes fee amount from player withdraw amount
            require_once MODELS_DIR . DS . 'WebSiteFeeModel.php';
            $modelWebSiteFee = new WebSiteFeeModel();
            $feeResult = $modelWebSiteFee->withdrawFeePart1($pc_session_id, $arrPlayer['player_id'], $payment_method_id, $amount, $currency_text);
            if($feeResult['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1", "fee_error_code"=>$feeResult['error_code'], "fee_error_message"=>$feeResult['error_message']));
                exit($json_message);
            }

            if(self::$DEBUG) {
                $message = "SiteWirecardMerchantManager::payout(pc_session_id = {$pc_session_id}, wirecard_transaction_id = {$wirecard_transaction_id},
                token_id = {$token_id}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, withdraw_amount_out = {$feeResult['withdraw_amount_out']},
                fee_amount_out = {$feeResult['fee_amount_out']}, ip_address = {$ip_address})";
                WirecardErrorHelper::wirecardAccess($message, $message);
            }
            if(strlen($feeResult['withdraw_amount_out'] == 0))$feeResult['withdraw_amount_out'] = $amount;
			if(strlen($feeResult['fee_amount_out'] == 0))$feeResult['fee_amount_out'] = 0;
            //new withdraw amount with fee deducted
            $new_amount = doubleval(trim(strip_tags($feeResult['withdraw_amount_out']))) - doubleval(trim(strip_tags($feeResult['fee_amount_out'])));
            if(strlen($new_amount) > 0){
                $amount = $new_amount;
            }

            //payouts player here
            $result = $modelMerchant->paymentProviderWithdrawRequest($pc_session_id, $transaction_id, $amount, $wirecard_transaction_id, $currency_text,
            $credit_card_number, $credit_card_expiration_date, $credit_card_holder, $credit_card_country, $credit_card_type,
            $start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, $over_limit, $bank_auth_code,
                $payment_method, $casinoName, $feeResult['fee_transaction_id'], WIRECARD_PAYMENT_PROVIDER, $token_id);
            //if payout is ok
            if($result['status'] == OK){
                //confirming that Oracle database received transaction successfully
                //send payout successfully mail to player
                $db_transaction_id = $result['db_transaction_id'];
                //get player details
                require_once MODELS_DIR . DS . 'PlayerModel.php';
                $modelPlayer = new PlayerModel();
                $resultDetails = $modelPlayer->getPlayerDetailsMalta($pc_session_id, $arrPlayer['player_id']);
                if($resultDetails['status'] != OK){
                    $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1"));
                    exit($json_message);
                }
                $details = $resultDetails['details'];
                $player_username = $details['user_name']; // $details['first_name']; $details['last_name'] or $arrPlayer['player_name'] or $details['user_name']
                $player_mail_address = $details['email'];
                $player_mail_send_from = $siteSettings['mail_address_from'];
                $player_smtp_server = $siteSettings['smtp_server_ip'];
                $site_images_location = $siteSettings['site_image_location'];
                $casino_name = $siteSettings['casino_name'];
                $site_link = $siteSettings['site_link'];
                $contact_link = $siteSettings['contact_url_link'];
                $support_link = $siteSettings['support_url_link'];
                $terms_link = $siteSettings['terms_url_link'];
                $language_settings = $siteSettings['language_settings'];
                require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
                $playerMailRes = WebSiteEmailHelper::getPayoutSuccessContent($player_username, $db_transaction_id, $currency_text, $amount, $feeResult['fee_amount_out'], $payment_method,
                $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
                $logger_message =  "Player with mail address: {$player_mail_address} has not been notified for his request for payout through Wirecard payment processor via email.";
                WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address, $player_smtp_server, $title, $content, $title, $title, $logger_message, $site_images_location);
                $json_message = Zend_Json::encode(array("status"=>OK, "payment_method"=>$payment_method, "amount"=>$amount,
                    "withdraw_amount_with_fee"=>$feeResult['withdraw_amount'], "withdraw_amount_without_fee"=>$feeResult['withdraw_amount_out'],
                    "fee_amount_out"=>$feeResult['fee_amount_out']
                ));
                exit($json_message);
            }
            //return failed transaction to database if game session is open
            if($result['status'] == NOK && $result['code'] == 20101){
                //return array("status"=>NOK_GAME_SESSION_OPENED, "code"=>$result['code']);
                $res = $modelMerchant->closeOpenedGameSession($pc_session_id);
                if($res['status'] != OK){
                    $message = "SiteWirecardMerchantManager::payout(pc_session_id = {$pc_session_id}, wirecard_transaction_id = {$wirecard_transaction_id},
                    token_id = {$token_id}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount}, ip_address = {$ip_address}) method:
                    <br /> Credit card payout transaction denied in database while game session open!";
                    WirecardErrorHelper::wirecardError($message, $message);
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_GAME_SESSION_OPENED)));
                    exit($json_message);
                }
            }

            if($result['status'] == NOK){
                //remember message to variable for sending on mail and printing to log
                $error_message = $result['message'];
                //confirming that Oracle database did not received transaction successfully
                $message = "SiteWirecardMerchantManager::payout(pc_session_id = {$pc_session_id}, wirecard_transaction_id = {$wirecard_transaction_id},
                token_id = {$token_id}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount}, ip_address = {$ip_address}) method
                exception error: <br /> {$error_message}";
                WirecardErrorHelper::wirecardError($message, $message);
                $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>$result['code']));
                exit($json_message);
            }
            $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1"));
            exit($json_message);
        }catch(Zend_Exception $ex){
            $message = "SiteWirecardMerchantManager::payout(pc_session_id = {$pc_session_id}, wirecard_transaction_id = {$wirecard_transaction_id},
            token_id = {$token_id}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount}, ip_address = {$ip_address}) method:
            <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            WirecardErrorHelper::wirecardError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1"));
            exit($json_message);
        }
	}

}
