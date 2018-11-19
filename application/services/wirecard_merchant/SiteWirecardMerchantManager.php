<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WirecardErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';
require_once HELPERS_DIR . DS . 'WirecardMerchantHelper.php';
/**
 *
 * Site Merchant for Wirecard service
 *
 */
class SiteWirecardMerchantManager {

    private $DEBUG = false;

	/**
	 *
	 * test if address in private ip address range
	 */
	private function isSecureConnection(){
		$config = Zend_Registry::get('config');
		//will check site ip address caller if true
		if($config->checkSiteIpAddress == "true"){
			$ip_addresses = explode(' ', $config->siteIpAddress);
			$host_ip_address = IPHelper::getRealIPAddress();
			$status = in_array($host_ip_address, $ip_addresses);
			if(!$status){
				$message = "SiteWirecardMerchantManager service: Host with blacklisted ip address {$host_ip_address} is trying to connect to site merchant web service.";
				WirecardErrorHelper::wirecardError($message, $message);
			}
			return $status;
		} else{
			return true;
		}
	}

    /*
     * @return string
     */
    public function test(){
        return "1";
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
	public function getWirecardPaymentPurchaseCustomCardMessage($site_session_id, $pc_session_id, $amount, $payment_method, $payment_method_id, $ip_address, $bonus_code, $css_template = 'Default'){
        if($this->DEBUG) {
            //DEBUG THIS
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardXml(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
            WirecardErrorHelper::wirecardAccess($message, $message);
        }
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>urlencode(NON_SSL_CONNECTION));
		}
		if(strlen($site_session_id)==0 || strlen($pc_session_id)==0 || strlen($amount)==0 || strlen($payment_method)==0 || strlen($payment_method_id)==0){
      $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
      payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
		  WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>urlencode(NOK_INVALID_DATA));
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
                return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
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
                  return array("status"=>NOK, "message"=>urlencode(NOK_GAME_SESSION_OPENED));
                }
            }

            //check transaction limit for player
            $transaction_limit = NO;
            //get transaction id from database
			$transactionData = $modelMerchant->getTransactionId($site_session_id, $currency_code);
			if($transactionData["status"] == NOK){
				return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
			}
			$transaction_id = $transactionData['transaction_id'];
			$currency_ok = $transactionData['currency_ok'];
			$oref_transaction_id = $transactionData['oref_transaction_id'];
            $db_transaction_id = $transactionData['payment_provider_transaction_id_purchase'];
			//if wirecard payment does not support currency from web site return no currency error
			if($currency_ok != YES){
				return array("status"=>NOK, "message"=>urlencode(NOK_CURRENCY));
			}
			//get player email address on site session id
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$resultDetails = $modelPlayer->getPlayerDetailsMalta($site_session_id, $arrPlayer['player_id']);
			if($resultDetails['status'] != OK){
				return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
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
                return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
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
                    return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION), "deposit_amount"=>$amount, "fee_error_message"=>$feeResult['error_message'], "fee_error_code"=>$feeResult['error_code']);
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

            $account = WirecardMerchantHelper::getAccountSpecificDataPerPaymentMethod($payment_method_name, $is_3d_secure);
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
            return array(
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
            );

        }catch(Zend_Exception $ex){
			//returns exception to web site
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
                payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code},
                css_template = {$css_template}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
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
	public function getWirecardPaymentPurchaseCustomCardWithTokenMessage($site_session_id, $pc_session_id, $amount, $payment_method, $payment_method_id,
        $token_id, $ip_address, $bonus_code, $css_template = 'Default'){
        if($this->DEBUG) {
            //DEBUG THIS
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardXml(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, token_id = {$token_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
            WirecardErrorHelper::wirecardAccess($message, $message);
        }
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>urlencode(NON_SSL_CONNECTION));
		}
		if(strlen($site_session_id)==0 || strlen($pc_session_id)==0 || strlen($amount)==0 || strlen($payment_method)==0 || strlen($payment_method_id)==0 || strlen($token_id) == 0){
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, token_id = {$token_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
		    WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>urlencode(NOK_INVALID_DATA));
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
                return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
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
                  return array("status"=>NOK, "message"=>urlencode(NOK_GAME_SESSION_OPENED));
                }
            }

            //check transaction limit for player
            $transaction_limit = NO;
            //get transaction id from database
			$transactionData = $modelMerchant->getTransactionId($site_session_id, $currency_code);
			if($transactionData["status"] == NOK){
				return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
			}
			$transaction_id = $transactionData['transaction_id'];
			$currency_ok = $transactionData['currency_ok'];
			$oref_transaction_id = $transactionData['oref_transaction_id'];
            $db_transaction_id = $transactionData['payment_provider_transaction_id_purchase'];
			//if wirecard payment does not support currency from web site return no currency error
			if($currency_ok != YES){
				return array("status"=>NOK, "message"=>urlencode(NOK_CURRENCY));
			}
			//get player email address on site session id
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$resultDetails = $modelPlayer->getPlayerDetailsMalta($site_session_id, $arrPlayer['player_id']);
			if($resultDetails['status'] != OK){
				return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
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
                return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
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
                    return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION), "deposit_amount"=>$amount, "fee_error_message"=>$feeResult['error_message'], "fee_error_code"=>$feeResult['error_code']);
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
            return array(
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
            );

        }catch(Zend_Exception $ex){
			//returns exception to web site
      $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
                payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, token_id = {$token_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
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
	public function getWirecardPaymentPurchaseCustomPaymentMethodMessage($site_session_id, $pc_session_id, $amount, $payment_method, $payment_method_id, $ip_address, $bonus_code, $css_template = 'Default'){
        if($this->DEBUG) {
            //DEBUG THIS
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomCardXml(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
            WirecardErrorHelper::wirecardAccess($message, $message);
        }
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>urlencode(NON_SSL_CONNECTION));
		}
		if(strlen($site_session_id)==0 || strlen($pc_session_id)==0 || strlen($amount)==0 || strlen($payment_method)==0 || strlen($payment_method_id)==0){
            $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomPaymentMethodMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
		     WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>urlencode(NOK_INVALID_DATA));
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
                return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
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
                  return array("status"=>NOK, "message"=>urlencode(NOK_GAME_SESSION_OPENED));
                }
            }

            //check transaction limit for player
            $transaction_limit = NO;
            //get transaction id from database
			$transactionData = $modelMerchant->getTransactionId($site_session_id, $currency_code);
			if($transactionData["status"] == NOK){
				return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
			}
			$transaction_id = $transactionData['transaction_id'];
			$currency_ok = $transactionData['currency_ok'];
			$oref_transaction_id = $transactionData['oref_transaction_id'];
            $db_transaction_id = $transactionData['payment_provider_transaction_id_purchase'];
			//if wirecard payment does not support currency from web site return no currency error
			if($currency_ok != YES){
				return array("status"=>NOK, "message"=>urlencode(NOK_CURRENCY));
			}
			//get player email address on site session id
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			$resultDetails = $modelPlayer->getPlayerDetailsMalta($site_session_id, $arrPlayer['player_id']);
			if($resultDetails['status'] != OK){
				return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
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
                return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
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
                    return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION), "deposit_amount"=>$amount, "fee_error_message"=>$feeResult['error_message'], "fee_error_code"=>$feeResult['error_code']);
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

            if(strlen($descriptor) == 0){
                $descriptor = "ORDER " . $request_time_stamp;
            }

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
            return array(
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
            );

        }catch(Zend_Exception $ex){
    			//returns exception to web site
          $message = "SiteWirecardMerchantManager::getWirecardPaymentPurchaseCustomPaymentMethodMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
                    payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
    			WirecardErrorHelper::wirecardError($message, $message);
    			return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
		}
	}

    /**
	 *
	 * Lists last credit card numbers used by player
	 * @param int $site_session_id
	 * @return mixed
	 */
	public function getLastCardNumbers($site_session_id){
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(strlen($site_session_id)==0){
            $message = "SiteMerchantManager::getLastCardNumbers(site_session_id = {$site_session_id})";
            WirecardErrorHelper::wirecardError($message, $message);
			      return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		//get merchant code and merchant password from database
		try{
			$site_session_id = intval(strip_tags($site_session_id));
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
			if($arrPlayer == false){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			//I send player id through clientAccount field
			$player_id = $arrPlayer['player_id'];
			$config = Zend_Registry::get('config');

            /*if($config->wirecardTestCard == "true"){
                $listCreditCards =
                    array(
                        array(
                            "trnID"=> rand ( 33000000, 39999999 ),
                            "cardNumber"=>"420000******0018",
                            "expiryDate"=>'01/2019',
                            "cardHolderName"=>'John Doe'
                        ),
                        array(
                            "trnID"=> rand ( 33000000, 39999999 ),
                            "cardNumber"=> "550000******0012",
                            "expiryDate"=>'01/2019',
                            "cardHolderName"=>'Jane Doe'
                        ),
                        array(
                            "trnID"=> rand ( 33000000, 39999999 ),
                            "cardNumber"=> "401200******1003",
                            "expiryDate"=>'01/2019',
                            "cardHolderName"=>'John Doe TEST'
                        ),
                        array(
                            "trnID"=> rand ( 33000000, 39999999 ),
                            "cardNumber"=> "541333******1006",
                            "expiryDate"=>'01/2019',
                            "cardHolderName"=>'Jane Doe TEST'
                        )
                    );
			    return array("status"=>OK, "listCreditCards"=>$listCreditCards);
            }*/

            require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
            $arrData = $modelMerchant->getLastTokenCreditCardList($player_id);
            if($arrData['status'] == OK && $arrData['token_id'] != '' && $arrData['credit_card_number'] != ''){
                $listCreditCards[] = array(
                    "trnID"=>$arrData['token_id'],
                    "cardNumber"=>$arrData['credit_card_number'],
                    "expiryDate"=>"",
                    "cardHolderName"=>""
                );
                return array("status"=>OK, "listCreditCards"=>$listCreditCards);
            }else{
                $access_message = "NO CARD NUMBERS AVAILABLE FOR CLIENT ACCOUNT (PLAYER_ID): {$player_id}";
				        $message = "SiteWirecardMerchantManager::getLastCardNumbers(site_session_id = {$site_session_id}) <br /> {$access_message}";
				        WirecardErrorHelper::wirecardAccessLog($message);
                return array("status"=>NOK, "message"=>BANK_NO_CARD_NUMBERS);
            }
		}catch(Zend_Exception $ex){
			$message = "SiteMerchantManager::getLastCardNumbers(site_session_id = {$site_session_id}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	 * check transaction limit for player purchase
	 * @param int $site_session_id
	 * @param number $amount
	 * @param string $payment_method
	 * @param string $ip_address
	 * @return mixed
	 */
	public function getTransactionLimitPurchase($site_session_id, $amount, $payment_method, $ip_address){
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(strlen($site_session_id)==0 || strlen($amount)==0 || strlen($payment_method)==0){
            $message = "SiteWirecardMerchantManager::getTransactionLimitPurchase(site_session_id = {$site_session_id}, amount = {$amount}, payment_method={$payment_method}, ip_address={$ip_address})";
            WirecardErrorHelper::wirecardError($message, $message);
			      return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		$site_session_id = intval(strip_tags($site_session_id));
		$amount = strip_tags($amount);
		$ip_address = strip_tags($ip_address);
		$payment_method = strip_tags($payment_method);
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
		try{
            require_once MODELS_DIR . DS . 'MerchantModel.php';
		    $modelMerchant = new MerchantModel();
			//check transaction limit for player
			$transactionLimitResult = $modelMerchant->getTransactionLimit($site_session_id, PAYIN_TRANSACTION, $payment_method, $amount);
			//if I receive result that there is response from database
			if($transactionLimitResult['status'] == OK){
                if($this->DEBUG) {
  				    //DEBUG THIS PART OF CODE
  				    WirecardErrorHelper::sendMail("SiteWirecardMerchantManager::getTransactionLimitPurchase method <br /> SITE_LOGIN.TRANSACTIONS_LIMIT <br />
                        Site session id = {$site_session_id} <br />transaction_limit_out = {$transactionLimitResult['transaction_limit_out']} <br />
                        player_status_out = {$transactionLimitResult['player_status_out']} <br />
                        Amount = {$amount}");
				        }
                //if amount > limit and
                //and if player_system_limit == S
                //Amout is not aproved. Your system limit is set to {$limit}, please check Terms and Conditions and contact our support.
                //or if player_system_limit != S
                //Amout is not aproved. Your limit is set to {$limit}, please check your Responsible Gaming setup and contact our support.
                require_once MODELS_DIR . DS . 'WebSiteModel.php';
                $modelWebSite = new WebSiteModel();
                require_once MODELS_DIR . DS . 'PlayerModel.php';
                $modelPlayer = new PlayerModel();
                $arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
                $playerDetails = $modelPlayer->getPlayerDetailsMalta(null, $arrPlayer['player_id']);
                $details = $playerDetails['details'];
                if($details['kyc_verified'] == NO && in_array(strtoupper($payment_method), array("VISA", "MAESTRO", "MASTERCARD")) && $amount > $transactionLimitResult['transaction_limit_out']) {
                    $siteSettings = $modelMerchant->findSiteSettings($arrPlayer['player_id']);
                    $player_limit = $transactionLimitResult['transaction_limit_out'];
                    $player_mail_send_from = $siteSettings['mail_address_from'];
                    $player_smtp_server = $siteSettings['smtp_server_ip'];
                    $casino_name = $siteSettings['casino_name'];
                    $site_images_location = $siteSettings['site_image_location'];
                    $site_link = $siteSettings['site_link'];
                    $support_link = $siteSettings['support_url_link'];
                    $terms_link = $siteSettings['terms_url_link'];
                    $contact_link = $siteSettings['contact_url_link'];
                    $privacy_policy_link = $siteSettings['privacy_policy_link'];
                    $language_settings = $siteSettings['language_settings'];
                    $player_username = $details['user_name'];
                    $player_mail_address = $details['email'];
                    $currency = $details['currency'];
                    require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
                    $playerMailRes = WebSiteEmailHelper::getDepositLimitPurchaseFailedContent($player_username, $amount, $currency, $player_limit,
                        $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $privacy_policy_link, $language_settings);
                    $title = $playerMailRes['mail_title'];
                    $content = $playerMailRes['mail_message'];
                    $logger_message = "SiteWirecardMerchantManager::getTransactionLimitPurchase. Player with player username: {$player_username} on mail address: {$player_mail_address}
                        has not received mail to send his documents for KYC status.";
                    WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address,
                        $player_smtp_server, $title, $content, $title, $title, $logger_message, $site_images_location);
                }
				return array("status"=>OK, "player_status"=>$transactionLimitResult['player_status_out'], "limit"=>$transactionLimitResult['transaction_limit_out'], "player_system_limit"=>$transactionLimitResult['player_system_limit_out']);
			}else{
			    //While calling for player transaction limit I receive exception error I pass to web site
				return array("status"=>NOK, "message"=>$transactionLimitResult['message']);
			}
		}catch(Zend_Exception $ex){
			//returns exception to web site
      $message = "SiteWirecardMerchantManager::getTransactionLimitPurchase(site_session_id = {$site_session_id}, amount = {$amount}, payment_method={$payment_method}, ip_address={$ip_address}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

	/**
	* check transaction limit for player payout
	* @param int $site_session_id
	* @param number $amount
	* @param string $payment_method
	* @param string $ip_address
	* @return mixed
	*/
	public function getTransactionLimitPayout($site_session_id, $amount, $payment_method, $ip_address){
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(strlen($site_session_id)==0 || strlen($amount)==0 || strlen($payment_method)==0){
            $message = "SiteWirecardMerchantManager::getTransactionLimitPayout(site_session_id = {$site_session_id}, amount = {$amount}, payment_method={$payment_method}, ip_address={$ip_address})";
            WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		$site_session_id = intval(strip_tags($site_session_id));
		$amount = strip_tags($amount);
		$ip_address = strip_tags($ip_address);
		$payment_method = strip_tags($payment_method);
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
		try{
            require_once MODELS_DIR . DS . 'MerchantModel.php';
		    $modelMerchant = new MerchantModel();
			//check transaction limit for player
			$transactionLimitResult = $modelMerchant->getTransactionLimit($site_session_id, PAYOUT_TRANSACTION, $payment_method, $amount);
			//if I receive result that there is response from database
			if($transactionLimitResult['status'] == OK){
				//if I receive for player status N then I will check transaction limit for player
				//DEBUG THIS PART OF CODE
				if($this->DEBUG) {
                    WirecardErrorHelper::sendMail("SiteWirecardMerchantManager::getTransactionLimitPayout method <br />
                        SITE_LOGIN.MTRANSACTIONS_LIMIT <br />
                        SITE_SESSION_ID = {$site_session_id} <br />
                        TRANSACTION_LIMIT_OUT = {$transactionLimitResult['transaction_limit_out']} <br />
                        PLAYER_STATUS_OUT = {$transactionLimitResult['player_status_out']} <br />
                        AMOUNT = {$amount}");
                }
                require_once MODELS_DIR . DS . 'WebSiteModel.php';
                $modelWebSite = new WebSiteModel();
                require_once MODELS_DIR . DS . 'PlayerModel.php';
                $modelPlayer = new PlayerModel();
                $arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
                $playerDetails = $modelPlayer->getPlayerDetailsMalta(null, $arrPlayer['player_id']);
                $details = $playerDetails['details'];
                if($details['kyc_verified'] == NO && in_array(strtoupper($payment_method), array("VISA", "MAESTRO", "MASTERCARD")) && $amount > $transactionLimitResult['transaction_limit_out']) {
                    $siteSettings = $modelMerchant->findSiteSettings($arrPlayer['player_id']);
                    $player_limit = $transactionLimitResult['transaction_limit_out'];
                    $player_mail_send_from = $siteSettings['mail_address_from'];
                    $player_smtp_server = $siteSettings['smtp_server_ip'];
                    $casino_name = $siteSettings['casino_name'];
                    $site_images_location = $siteSettings['site_image_location'];
                    $site_link = $siteSettings['site_link'];
                    $support_link = $siteSettings['support_url_link'];
                    $terms_link = $siteSettings['terms_url_link'];
                    $contact_link = $siteSettings['contact_url_link'];
                    $privacy_policy_link = $siteSettings['privacy_policy_link'];
                    $language_settings = $siteSettings['language_settings'];
                    $player_username = $details['user_name'];
                    $player_mail_address = $details['email'];
                    $currency = $details['currency'];
                    require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
                    $playerMailRes = WebSiteEmailHelper::getPayoutLimitPayoutFailedContent($player_username, $amount, $currency, $player_limit,
                        $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $privacy_policy_link, $language_settings);
                    $title = $playerMailRes['mail_title'];
                    $content = $playerMailRes['mail_message'];
                    $logger_message = "SiteWirecardMerchantManager::getTransactionLimitPayout. Player with player username: {$player_username} on mail address: {$player_mail_address}
                        has not received mail to send his documents for KYC status.";
                    WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address,
                        $player_smtp_server, $title, $content, $title, $title, $logger_message, $site_images_location);
                }
				return array("status"=>OK, "limit"=>$transactionLimitResult['transaction_limit_out'], "player_system_limit"=>$transactionLimitResult['player_system_limit_out'],
                "withdraw_fee"=>$transactionLimitResult['withdraw_fee_out']);
			}else{
			    //While calling for player transaction limit I receive exception error I pass to web site
				return array("status"=>NOK, "message"=>$transactionLimitResult['message']);
			}
		}catch(Zend_Exception $ex){
			//returns exception to web site
            $message = "SiteWirecardMerchantManager::getTransactionLimitPayout(site_session_id = {$site_session_id}, amount = {$amount}, payment_method={$payment_method}, ip_address={$ip_address}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
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
	public function payout($pc_session_id, $wirecard_transaction_id, $token_id, $payment_method, $payment_method_id, $amount, $ip_address){
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
        if($this->DEBUG) {
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
            return array("status"=>NOK_INVALID_DATA);
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
                  return array("status"=>NOK, "message"=>urlencode(NOK_GAME_SESSION_OPENED));
                }
            }

            //get the currency for player
            $currencyData = $modelMerchant->currencyCodeForSession($pc_session_id);
            if($currencyData['status'] != OK){
                return array("status"=>NOK, "code"=>"-1");
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
                return array("status"=>NOK, "code"=>"-1");
            }
            $casinoName = $siteSettings['casino_name'];

            //WITHDRAW FEE PART 1 call - here it takes fee amount from player withdraw amount
            require_once MODELS_DIR . DS . 'WebSiteFeeModel.php';
            $modelWebSiteFee = new WebSiteFeeModel();
            $feeResult = $modelWebSiteFee->withdrawFeePart1($pc_session_id, $arrPlayer['player_id'], $payment_method_id, $amount, $currency_text);
            if($feeResult['status'] != OK){
                return array("status"=>NOK, "code"=>"-1", "fee_error_code"=>$feeResult['error_code'], "fee_error_message"=>$feeResult['error_message']);
            }

            if($this->DEBUG) {
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
                    return array("status"=>NOK, "code"=>"-1");
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
                return array("status"=>OK, "payment_method"=>$payment_method, "amount"=>$amount,
                    "withdraw_amount_with_fee"=>$feeResult['withdraw_amount'], "withdraw_amount_without_fee"=>$feeResult['withdraw_amount_out'],
                    "fee_amount_out"=>$feeResult['fee_amount_out']
                );
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
                    return array("status"=>NOK, "message"=>urlencode(NOK_GAME_SESSION_OPENED));
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
                return array("status"=>NOK, "code"=>$result['code']);
            }
            return array("status"=>NOK, "code"=>"-1");
        }catch(Zend_Exception $ex){
            $message = "SiteWirecardMerchantManager::payout(pc_session_id = {$pc_session_id}, wirecard_transaction_id = {$wirecard_transaction_id},
            token_id = {$token_id}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount}, ip_address = {$ip_address}) method:
            <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            WirecardErrorHelper::wirecardError($message, $message);
            return array("status"=>NOK, "code"=>"-1");
        }
	}

    /**
	* list player pending payout status
	* Calls player from web site to list his payout request in pending status from our database ...
	* @param int $site_session_id
	* @return mixed
	*/
	public function pendingPayoutStatus($site_session_id){
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(strlen($site_session_id)==0){
            $message = "SiteWirecardMerchantManager::pendingPayoutStatus(site_session_id = {$site_session_id})";
            WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK_INVALID_DATA);
		}
		try{
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
			if($arrPlayer == false){
				return array("status"=>NOK_EXCEPTION);
			}
			$player_id = $arrPlayer['player_id'];
			require_once MODELS_DIR . DS . 'ReportsModel.php';
			$modelReports = new ReportsModel();
			$reports = $modelReports->listPendingPaymentProviderPayOuts($player_id);
			if($reports['status'] != OK){
				return array("status"=>NOK_EXCEPTION);
			}
			$report = array();
			foreach($reports["report"] as $rep){
				if($rep['amount'] != "-1"){
					$report[] = array(
                        "transaction_id"=>$rep['id'],
                        "amount" => NumberHelper::convert_double($rep['amount']),
                        "amount_formatted" => NumberHelper::format_double($rep['amount']),
                        "start_time" => $rep['start_time'],
                        "cancel_disabled"=>$rep['cancel_disabled']
                    );
				}
			}
			$arrReport = array(
                "status"=>OK,
                "transaction_sum"=>NumberHelper::format_double($reports['transaction_sum']),
                "transaction_amount"=>NumberHelper::format_double($reports['transaction_count']),
                "report"=>$report,
                "player_id"=>$player_id
            );
			return $arrReport;
		}catch(Zend_Exception $ex){
            $message = "SiteWirecardMerchantManager::pendingPayoutStatus(site_session_id = {$site_session_id}) method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK_EXCEPTION);
		}
	}

	/**
	*
	* @param int $site_session_id
	* @param int $pc_session_id
    * @param double $expected_withdraw_amount
	* @param int $transaction_id
	* @return mixed
	*/
	public function isWithdrawPossible($site_session_id, $pc_session_id, $expected_withdraw_amount, $transaction_id){
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(strlen($site_session_id)==0){
            $message = "SiteWirecardMerchantManager::isWithdrawPossible(site_session_id={$site_session_id}, pc_session_id={$pc_session_id}, expected_withdraw_amount={$expected_withdraw_amount}, transaction_id={$transaction_id})";
            WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
			if($arrPlayer == false){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			$player_id = $arrPlayer['player_id'];
			require_once MODELS_DIR . DS . 'ReportsModel.php';
			$modelReports = new ReportsModel();
			$reports = $modelReports->listPendingPaymentProviderPayOuts($player_id);
			if($reports['status'] != OK){
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
            $result = array(
                "status"=>OK,
                "possible"=>false,
                "transaction_id"=>$transaction_id,
                "player_id"=>$player_id,
                "possible_withdraw_amount"=>$reports['transaction_sum'],
                "expected_withdraw_amount"=>$expected_withdraw_amount,
                "cancel_disabled"=>1
            );
            foreach($reports["report"] as $rep){
                if($rep['id'] == $transaction_id){
                    $result = array(
                        "status"=>OK,
                        "possible"=>true,
                        "transaction_id"=>$transaction_id,
                        "player_id"=>$player_id,
                        "possible_withdraw_amount"=>$reports['transaction_sum'],
                        "expected_withdraw_amount"=>$expected_withdraw_amount,
                        "cancel_disabled"=>$rep['cancel_disabled']
                    );
                    break;
                }
            }
            return $result;
		}catch(Zend_Exception $ex){
            $message = "SiteWirecardMerchantManager::pendingPayoutStatus(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, expected_withdraw_amount = {$expected_withdraw_amount}, transaction_id = {$transaction_id}) method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

	/**
	 *
	 * Player calls for cancel withdraw on his pending payouts ...
	 * @param int $site_session_id
	 * @param int $pc_session_id
	 * @param double $withdraw_amount
     * @param int $transaction_id
	 * @return mixed
	 */
	public function cancelWithdraw($site_session_id, $pc_session_id, $withdraw_amount, $transaction_id){
		//if not a secure ip address / connection used with web service
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		//if one or more parameters are empty values
		if(strlen($site_session_id)==0 || strlen($withdraw_amount)==0){
            $message = "SiteWirecardMerchantManager::cancelWithdraw(site_session_id={$site_session_id}, pc_session_id={$pc_session_id}, withdraw_amount={$withdraw_amount}, transaction_id={$transaction_id})";
            WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$result = $modelMerchant->cancelWithdraw($site_session_id, $transaction_id, $withdraw_amount);
			if($result['status'] == OK){
                require_once MODELS_DIR . DS . 'WebSiteModel.php';
                $modelWebSite = new WebSiteModel();
                $arrPlayer = $modelWebSite->sessionIdToPlayerId($pc_session_id);
                //get player details
                require_once MODELS_DIR . DS . 'PlayerModel.php';
                $modelPlayer = new PlayerModel();
                $resultDetails = $modelPlayer->getPlayerDetailsMalta($site_session_id, $arrPlayer['player_id']);
                if($resultDetails['status'] != OK){
                    return $result;
                }
                //withdraw fee part2
                require_once MODELS_DIR . DS . 'WebSiteFeeModel.php';
                $modelWebSiteFee = new WebSiteFeeModel();
                $modelWebSiteFee->withdrawFeePart2($site_session_id, $arrPlayer['player_id'], $transaction_id);
                //end withdraw fee part2
                $details = $resultDetails['details'];
                //get site settings for this player and his player_id
                $siteSettings = $modelMerchant->findSiteSettings($arrPlayer['player_id']);
                if($siteSettings['status'] != OK){
                    return $result;
                }
                $player_username = $details['user_name'];
                $player_mail_address = $details['email'];
                $player_currency = $details['currency'];
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
                $playerMailRes = WebSiteEmailHelper::getPlayerCanceledHisPayoutContent($player_username, $transaction_id,
                $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
                $logger_message =  "Player with mail address: {$player_mail_address} has not been notified for his his cancelled payout request.";
                WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address, $player_smtp_server, $title, $content, $title, $title, $logger_message, $site_images_location);
                return $result;
			}
			return $result;
		}catch(Zend_Exception $ex){
            $message = "SiteWirecardMerchantManager::cancelWithdraw(site_session_id={$site_session_id}, pc_session_id={$pc_session_id}, withdraw_amount={$withdraw_amount}, transaction_id={$transaction_id}) method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	 *
	 * List payment methods - deposit options on web site, list deposit/withdraw for player deposit/withdraw ...
	 * @param int $site_session_id
     * @param string $currency
	 * @return mixed
	 */
	public function listPaymentLimitsForWhiteLabel($site_session_id, $currency){
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		if(strlen($site_session_id)==0 || strlen($currency)==0){
            $message = "SiteWirecardMerchantManager::listPaymentLimitsForWhiteLabel(site_session_id={$site_session_id}, currency={$currency})";
            WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		$site_session_id = intval(strip_tags($site_session_id));
        $currency = strip_tags($currency);
        try {
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            //list payment methods for player
            $result = $modelMerchant->getPaymentLimitsForWhiteLabel($site_session_id, $currency);
            if($result['status'] != OK){
                return array("status" => NOK, "message"=>NOK_EXCEPTION);
            }else{
                $rows = array();
                foreach($result['cursor'] as $cur){
                    //if($cur['amount_sign'] == -1 && $cur['psp_id'] == "")continue;
                    if($cur['amount_sign'] == -1 && $cur['psp_id'] == "" && strtoupper($cur['payment_type']) != 'ENTERCASH')continue;
                    $country = $this->returnCountryForPaymentMethod($cur['transaction_code']);
                    $rows[] = array(
                        "payment_currency_limit_id"=>$cur['payment_currency_limit_id'],
                        "payment_type_id"=>$cur['payment_type_id'],
                        "payment_type"=>$cur['payment_type'],
                        "amount_sign"=>$cur['amount_sign'],
                        "transaction_code"=>$cur['transaction_code'],
                        "min_amount"=>$cur['min_amount'],
                        "max_amount"=>$cur['max_amount'],
                        "currency"=>$cur['currency'],
                        "white_label_id"=>$cur['white_label'],
                        "white_label_name"=>$cur['white_label_name'],
                        "button1"=>$cur['button1'],
                        "button2"=>$cur['button2'],
                        "button3"=>$cur['button3'],
                        "button4"=>$cur['button4'],
                        "button5"=>$cur['button5'],
                        "credit_card"=>$cur['credit_card'],
                        "psp_id"=>$cur['psp_id'],
                        "credits"=>$result['credits'],

                        "fee_for_wl_id"=>$cur['fee_for_wl_id'],
                        "fee_profile_name"=>$cur['fee_profile_name'],
                        "fee_fix_amount"=>$cur['fee_fix_amount'],
                        "fee_percent"=>$cur['fee_percent'],
                        "fee_y_n"=>$cur['fee_y_n'],

                        "order_priority" => $cur['order_priority'],
                        "type_of_payment" => $cur['type_of_payment'],
                        "process_time" => $cur['process_time'],
                        "withdraw_fee"=> $cur['withdraw_fee'],
                        "deposit_fee"=>$cur['deposit_fee'],
                        "country"=>$country,
                        "is_deposit"=>$cur['is_deposit'],
                        "is_withdraw"=>$cur['is_payout'],
                        "kyc_deposit"=>$cur['kyc_deposit'],
                        "kyc_withdraw"=>$cur['kyc_payout'],
                        "show_entercash_err_msg"=>$cur['show_entercash_err_msg'],
                        "swift"=>$cur['swift'],
                        "iban"=>$cur['iban'],

                        "payment_provider_id"=>$cur['payment_provider'],
                        "payment_provider_name"=>$cur['payment_provider_name'],

                        "token_id"=>$cur['token_id'],

                        "active_inactive_status" => $cur['payment_limit_rec_sts']
                    );
                }
                return array("status"=>OK, "credits"=>$result['credits'], "active_promotion"=>$result['active_promotion'], "report"=>$rows);
            }
        }catch(Zend_Exception $ex){
			$message = "SiteWirecardMerchantManager::listPaymentLimitsForWhiteLabel(site_session_id = {$site_session_id}, currency={$currency}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status" => NOK, "message"=>NOK_EXCEPTION);
        }
	}

    private function returnCountryForPaymentMethod($payment_method_code_name){
        $country = "";
        switch($payment_method_code_name){
            case "VISA":
                $country = "Worldwide";
                break;
            case "VISA3D":
                $country = "Worldwide";
                break;
            case "MASTERCARD":
                $country = "Worldwide";
                break;
            case "MASTERCARDSECURE":
                $country = "Worldwide";
                break;
            case "ENTERCASH":
                $country = "EUR countries, Sweden, Denmark, Czech Republic";
                break;
            case "ZIMPLER":
                $country = "Worldwide";
                break;
            case "MBKR":
                $country = "Worldwide";
                break;
            case "NT":
                $country = "Worldwide";
                break;
            case "EUTELLER":
                $country = "Finland";
                break;
            case "SEPA":
                $country = "SEPA countries";
                break;
            case "IDEAL":
                $country = "Netherlands";
                break;
            case "SOFORT":
                $country = "Austria, Belgium, France, Germany, Spain";
                break;
            case "TRUSTPAY":
                $country = "Bulgaria, Czech Republic, Estonia, Hungary, Latvia, Lithuania, Poland, Romania, Slovakia";
                break;
            case "PRZLEWY":
                $country = "Poland";
                break;
            case "QIWI":
                $country = "Russia, Ukraine, Kazakhstan";
                break;
            case "TRUSTLY":
                $country = "Sweden";
                break;
            case "PTEST":
                $country = "Worldwide";
                break;
            case "PSC":
                $country = "";
                break;
            case "UKASH":
                $country = "";
                break;
            case "ECOW":
                $country = "";
                break;
            case "MAESTRO":
                $country = "";
                break;
            case "ENVOY":
                $country = "";
                break;
            default:
                $country = "";
        }
        return $country;
    }

    /**
	 *
	 * Player calls for cancel withdraw on his pending payouts ...
	 * @param int $player_id
	 * @param string $swift
	 * @param string $iban
	 * @return mixed
	 */
	public function setIbanSwift($player_id, $swift, $iban){
		//if not a secure ip address / connection used with web service
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		//if one or more parameters are empty values
		if(strlen($player_id) == 0 || strlen($swift)==0 || strlen($iban)==0){
            $message = "SiteWirecardMerchantManager::setIbanSwift(player_id={$player_id}, swift={$swift}, iban={$iban})";
            WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
        $player_id = trim(strip_tags($player_id));
        $swift = trim(strip_tags($swift));
        $iban = trim(strip_tags($iban));
		try{
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$result = $modelMerchant->setIbanSwiftForPlayer($player_id, $iban, $swift);
			if($result['status'] == OK && $result['status_out'] == "1"){
                return array(
                    "status"=>OK,
                    "result"=>OK
                );
			}else{
                return array(
                    "status"=>OK,
                    "result"=>NOK
                );
            }
		}catch(Zend_Exception $ex){
      $message = "SiteWirecardMerchantManager::setIbanSwift(player_id={$player_id}, swift={$swift}, iban={$iban}) method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
     * @param int $pc_session_id
     * @param string $promotion_code
     * @return array
     */
    public function getPromotionCode($pc_session_id, $promotion_code){
        //if not a secure ip address / connection used with web service
		if (!$this->isSecureConnection()){
			return array("status"=>NOK, "message"=>NON_SSL_CONNECTION);
		}
		//if one or more parameters are empty values
		if(strlen($pc_session_id) == 0 || strlen($promotion_code)==0){
            $message = "SiteWirecardMerchantManager::getPromotionCode(pc_session_id={$pc_session_id}, promotion_code={$promotion_code})";
            WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
        $pc_session_id = trim(strip_tags($pc_session_id));
        $promotion_code = trim(strip_tags($promotion_code));
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$result = $modelWebSiteBonus->getPromotionCode($pc_session_id, $promotion_code);
			if($result['status'] == OK && $result['status_out'] == "1"){
                return array(
                    "status"=>OK,
                    "result"=>OK
                );
			}
            else if($result['status'] == OK && $result['status_out'] == "-1"){
                return array(
                    "status"=>OK,
                    "result"=>NOK,
                    "message"=>"NOK_INVALID_PROMOTION_CODE"
                );
            }
			else if($result['status'] == NOK && $result['message'] == "NOK_PROMOTION_USED_BY_PLAYER")
            {
                return array(
                    "status"=>OK,
                    "result"=>NOK,
                    "message"=>"NOK_PROMOTION_USED_BY_PLAYER"
                );
            }
            else if($result['status'] == NOK && $result['message'] == "NOK_PROMOTION_PLAYER_HAS_ACTIVE_BONUS")
            {
                return array(
                    "status"=>OK,
                    "result"=>NOK,
                    "message"=>"NOK_PROMOTION_PLAYER_HAS_ACTIVE_BONUS"
                );
            }
            else{
                return array(
                    "status"=>OK,
                    "result"=>NOK
                );
            }
		}catch(Zend_Exception $ex){
      $message = "SiteWirecardMerchantManager::getPromotionCode(pc_session_id = {$pc_session_id}, promotion_code = {$promotion_code}) method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
    }
}
