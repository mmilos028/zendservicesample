<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class WebSiteMerchantManager {

    private static $DEBUG = false;

    private static function returnCountryForPaymentMethod($payment_method_code_name){
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
	* List all payment methods ...
	* @return mixed
	*/
	public static function getAllPaymentMethods(){
        try {
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            //list payment methods for player
            $result = $modelMerchant->listAllPaymentMethods();
            if ($result['status'] == OK) {
                $arrData = array();
                foreach ($result['payment_methods'] as $res) {
                    $country = self::returnCountryForPaymentMethod($res['transaction_code']);
                    $arrData[] = array(
                        "id" => $res['id'],
                        "name" => $res['name'],
                        "min_amount" => NumberHelper::convert_double($res['min_amount']),
                        "min_amount_formatted" => NumberHelper::format_double($res['min_amount']),
                        "max_amount" => NumberHelper::convert_double($res['max_amount']),
                        "max_amount_formatted" => NumberHelper::format_double($res['max_amount']),
                        "credit_card" => $res['credit_card'],
                        "amount_sign" => $res['amount_sign'],
                        "transaction_code" => $res['transaction_code'],
                        "order_priority" => $res['order_priority'],
                        "type_of_payment" => $res['type_of_payment'],
                        "process_time" => $res['process_time'],
                        "withdraw_fee"=> NumberHelper::convert_double($res['withdraw_fee']),
                        "withdraw_fee_formatted"=> NumberHelper::format_double($res['withdraw_fee']),
                        "deposit_fee"=> NumberHelper::convert_double($res['deposit_fee']),
                        "deposit_fee_formatted"=> NumberHelper::format_double($res['deposit_fee']),
                        "country"=>$country,
                        "is_deposit"=>$res['is_deposit'],
                        "is_withdraw"=>$res['is_payout'],
                        "kyc_deposit"=>NumberHelper::convert_double($res['kyc_deposit']),
                        "kyc_deposit_formatted"=>NumberHelper::format_double($res['kyc_deposit']),
                        "kyc_withdraw"=>NumberHelper::convert_double($res['kyc_payout']),
                        "kyc_withdraw_formatted"=>NumberHelper::format_double($res['kyc_payout']),
                    );
                }
                $json_message = Zend_Json::encode(array("status"=>OK, "payment_methods"=>$arrData));
                exit($json_message);
            } else {
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
			$message = "SiteMerchantManager:: getAllPaymentMethods method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
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
	public static function getTransactionLimitPurchase($site_session_id, $amount, $payment_method, $ip_address){
		if(strlen($site_session_id)==0 || strlen($amount)==0 || strlen($payment_method)==0){
            $errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::getTransactionLimitPurchase(site_session_id = {$site_session_id}, amount = {$amount}, payment_method={$payment_method}, ip_address={$ip_address})";
            $errorHelper->merchantError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
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
                if(self::$DEBUG) {
                    //DEBUG THIS PART OF CODE
                    $errorHelper = new ErrorHelper();
                    $errorHelper->sendMail("SiteMerchantManager::getTransactionLimitPurchase method <br /> SITE_LOGIN.TRANSACTIONS_LIMIT <br /> Site session id = {$site_session_id} <br />transaction_limit_out = {$transactionLimitResult['transaction_limit_out']} <br /> player_status_out = {$transactionLimitResult['player_status_out']} <br /> Amount = {$amount}");
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
                    $currency_text = $details['currency'];
                    require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
                    $playerMailRes = WebSiteEmailHelper::getDepositLimitPurchaseFailedContent($player_username, $amount, $currency_text, $player_limit,
                        $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $privacy_policy_link, $language_settings);
                    $title = $playerMailRes['mail_title'];
                    $content = $playerMailRes['mail_message'];
                    $logger_message = "SiteMerchantManager::getTransactionLimitPurchase. Player with player username: {$player_username} on mail address: {$player_mail_address}
                        has not received mail to send his documents for KYC status.";
                    WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address,
                        $player_smtp_server, $title, $content, $title, $title, $logger_message, $site_images_location);
                }
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                        "player_status"=>$transactionLimitResult['player_status_out'],
                        "limit"=>NumberHelper::convert_double($transactionLimitResult['transaction_limit_out']),
                        "limit_formatted"=>NumberHelper::format_double($transactionLimitResult['transaction_limit_out']),
                        "player_system_limit"=>NumberHelper::convert_double($transactionLimitResult['player_system_limit_out']),
                        "player_system_limit_formatted"=>NumberHelper::format_double($transactionLimitResult['player_system_limit_out'])
                    )
                );
                exit($json_message);
			}else{
			    //While calling for player transaction limit I receive exception error I pass to web site
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>$transactionLimitResult['message']));
                exit($json_message);
			}
		}catch(Zend_Exception $ex){
			//returns exception to web site
			$errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::getTransactionLimitPurchase(site_session_id = {$site_session_id}, amount = {$amount}, payment_method={$payment_method}, ip_address={$ip_address}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
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
	public static function getTransactionLimitPayout($site_session_id, $amount, $payment_method, $ip_address){
		if(strlen($site_session_id)==0 || strlen($amount)==0 || strlen($payment_method)==0){
            $errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::getTransactionLimitPayout(site_session_id = {$site_session_id}, amount = {$amount}, payment_method={$payment_method}, ip_address={$ip_address})";
            $errorHelper->merchantError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
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
                if(self::$DEBUG) {
                    //DEBUG THIS PART OF CODE
                    $errorHelper = new ErrorHelper();
                    $errorHelper->sendMail("SiteMerchantManager::getTransactionLimitPayout method <br /> SITE_LOGIN.MTRANSACTIONS_LIMIT <br />Site session id = {$site_session_id} <br />transaction_limit_out = {$transactionLimitResult['transaction_limit_out']} <br />player_status_out = {$transactionLimitResult['player_status_out']} <br />Amount = {$amount}");
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
                    $currency_text = $details['currency'];
                    require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
                    $playerMailRes = WebSiteEmailHelper::getPayoutLimitPayoutFailedContent($player_username, $amount, $currency_text, $player_limit,
                        $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $privacy_policy_link, $language_settings);
                    $title = $playerMailRes['mail_title'];
                    $content = $playerMailRes['mail_message'];
                    $loggerMessage = "SiteMerchantManager::getTransactionLimitPayout. Player with player username: {$player_username} on mail address: {$player_mail_address}
                        has not received mail to send his documents for KYC status.";
                    WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address,
                        $player_smtp_server, $title, $content, $title, $title, $loggerMessage, $site_images_location);
                }
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                        "limit"=>NumberHelper::convert_double($transactionLimitResult['transaction_limit_out']),
                        "limit_formatted"=>NumberHelper::format_double($transactionLimitResult['transaction_limit_out']),
                        "player_system_limit"=>NumberHelper::convert_double($transactionLimitResult['player_system_limit_out']),
                        "player_system_limit_formatted"=>NumberHelper::format_double($transactionLimitResult['player_system_limit_out']),
                        "withdraw_fee"=>NumberHelper::convert_double($transactionLimitResult['withdraw_fee_out']),
                        "withdraw_fee_formatted"=>NumberHelper::format_double($transactionLimitResult['withdraw_fee_out']),
                    )
                );
                exit($json_message);
			}else{
			    //While calling for player transaction limit I receive exception error I pass to web site
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>$transactionLimitResult['message']));
                exit($json_message);
			}
		}catch(Zend_Exception $ex){
			//returns exception to web site
			$errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::getTransactionLimitPayout(site_session_id = {$site_session_id}, amount = {$amount}, payment_method={$payment_method}, ip_address={$ip_address}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}
	}

    /**
	* list player pending payout status
	* Calls player from web site to list his payout request in pending status from our database ...
	* @param int $site_session_id
	* @return mixed
	*/
	public static function pendingPayoutStatus($site_session_id){
		if(strlen($site_session_id)==0){
            $errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::pendingPayoutStatus(site_session_id = {$site_session_id})";
            $errorHelper->merchantError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		try{
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
			if($arrPlayer == false){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
                exit($json_message);
			}
			$player_id = $arrPlayer['player_id'];
			require_once MODELS_DIR . DS . 'ReportsModel.php';
			$modelReports = new ReportsModel();
			$reports = $modelReports->listPendingPaymentProviderPayOuts($player_id);
			if($reports['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
                exit($json_message);
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
            $json_message = Zend_Json::encode(
                array(
                    "status"=>OK,
                    "transaction_sum"=>NumberHelper::convert_double($reports['transaction_sum']),
                    "transaction_sum_formatted"=>NumberHelper::format_double($reports['transaction_sum']),
                    "transaction_amount"=>NumberHelper::convert_double($reports['transaction_count']),
                    "transaction_amount_formatted"=>NumberHelper::format_double($reports['transaction_count']),
                    "report"=>$report,
                    "player_id"=>$player_id
                )
            );
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::pendingPayoutStatus(site_session_id = {$site_session_id}) method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
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
	public static function isWithdrawPossible($site_session_id, $pc_session_id, $expected_withdraw_amount, $transaction_id){
		if(strlen($site_session_id)==0){
            $errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::isWithdrawPossible(site_session_id={$site_session_id}, pc_session_id={$pc_session_id}, expected_withdraw_amount={$expected_withdraw_amount}, transaction_id={$transaction_id})";
            $errorHelper->merchantError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		try{
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$arrPlayer = $modelWebSite->sessionIdToPlayerId($site_session_id);
			if($arrPlayer == false){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
                exit($json_message);
			}
			$player_id = $arrPlayer['player_id'];
			require_once MODELS_DIR . DS . 'ReportsModel.php';
			$modelReports = new ReportsModel();
			$reports = $modelReports->listPendingPaymentProviderPayOuts($player_id);
			if($reports['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
                exit($json_message);
			}
            $result = array(
                "status"=>OK,
                "possible"=>false,
                "transaction_id"=>$transaction_id,
                "player_id"=>$player_id,
                "possible_withdraw_amount"=>NumberHelper::convert_double($reports['transaction_sum']),
                "possible_withdraw_amount_formatted"=>NumberHelper::format_double($reports['transaction_sum']),
                "expected_withdraw_amount"=>NumberHelper::convert_double($expected_withdraw_amount),
                "expected_withdraw_amount_formatted"=>NumberHelper::format_double($expected_withdraw_amount),
                "cancel_disabled"=>1
            );
            foreach($reports["report"] as $rep){
                if($rep['id'] == $transaction_id){
                    $result = array(
                        "status"=>OK,
                        "possible"=>true,
                        "transaction_id"=>$transaction_id,
                        "player_id"=>$player_id,
                        "possible_withdraw_amount"=>NumberHelper::convert_double($reports['transaction_sum']),
                        "possible_withdraw_amount_formatted"=>NumberHelper::format_double($reports['transaction_sum']),
                        "expected_withdraw_amount"=>NumberHelper::convert_double($expected_withdraw_amount),
                        "expected_withdraw_amount_formatted"=>NumberHelper::format_double($expected_withdraw_amount),
                        "cancel_disabled"=>$rep['cancel_disabled']
                    );
                    break;
                }
            }
            $json_message = Zend_Json::encode($result);
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::pendingPayoutStatus(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, expected_withdraw_amount = {$expected_withdraw_amount}, transaction_id = {$transaction_id}) method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}
	}

    /**
	 *
	 * Player calls for cancel withdraw on his pending payouts ...
	 * @param int $site_session_id
	 * @param int $pc_session_id
	 * @param double $withdraw_amount
     * @param string $transaction_id
	 * @return mixed
	 */
	public static function cancelWithdraw($site_session_id, $pc_session_id, $withdraw_amount, $transaction_id){
		//if one or more parameters are empty values
		if(strlen($site_session_id)==0 || strlen($withdraw_amount)==0){
            $errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::cancelWithdraw(site_session_id={$site_session_id}, pc_session_id={$pc_session_id}, withdraw_amount={$withdraw_amount}, transaction_id={$transaction_id})";
            $errorHelper->merchantError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
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
                    $json_message = Zend_Json::encode($result);
                    exit($json_message);
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
                    $json_message = Zend_Json::encode($result);
                    exit($json_message);
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
                $json_message = Zend_Json::encode($result);
                exit($json_message);
			}
            $json_message = Zend_Json::encode($result);
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::cancelWithdraw(site_session_id={$site_session_id}, pc_session_id={$pc_session_id}, withdraw_amount={$withdraw_amount}, transaction_id={$transaction_id}) method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}
	}

    /**
	 *
	 * List payment methods - deposit options on web site, list deposit/withdraw for player deposit/withdraw ...
	 * @param int $site_session_id
     * @param string $currency
	 * @return mixed
	 */
	public static function listPaymentLimitsForWhiteLabel($site_session_id, $currency){
		if(strlen($site_session_id)==0 || strlen($currency)==0){
            $errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::listPaymentLimitsForWhiteLabel(site_session_id={$site_session_id}, currency={$currency})";
            $errorHelper->merchantError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		$site_session_id = intval(strip_tags($site_session_id));
        $currency = strip_tags($currency);
        try {
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            //list payment methods for player
            $result = $modelMerchant->getPaymentLimitsForWhiteLabel($site_session_id, $currency);
            if($result['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
                exit($json_message);
            }else{
                $rows = array();
                foreach($result['cursor'] as $cur){
                    //if($cur['amount_sign'] == -1 && $cur['psp_id'] == "")continue;
                    if($cur['amount_sign'] == -1 && $cur['psp_id'] == "" && strtoupper($cur['payment_type']) != 'ENTERCASH')continue;
                    $country = self::returnCountryForPaymentMethod($cur['transaction_code']);
                    $rows[] = array(
                        "payment_currency_limit_id"=>$cur['payment_currency_limit_id'],
                        "payment_type_id"=>$cur['payment_type_id'],
                        "payment_type"=>$cur['payment_type'],
                        "amount_sign"=>$cur['amount_sign'],
                        "transaction_code"=>$cur['transaction_code'],
                        "min_amount"=>NumberHelper::convert_double($cur['min_amount']),
                        "min_amount_formatted"=>NumberHelper::format_double($cur['min_amount']),
                        "max_amount"=>NumberHelper::convert_double($cur['max_amount']),
                        "max_amount_formatted"=>NumberHelper::format_double($cur['max_amount']),
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
                        "credits"=>NumberHelper::convert_double($result['credits']),
                        "credits_formatted"=>NumberHelper::format_double($result['credits']),

                        "fee_for_wl_id"=>$cur['fee_for_wl_id'],
                        "fee_profile_name"=>$cur['fee_profile_name'],
                        "fee_fix_amount"=>NumberHelper::convert_double($cur['fee_fix_amount']),
                        "fee_fix_amount_formatted"=>NumberHelper::format_double($cur['fee_fix_amount']),
                        "fee_percent"=>$cur['fee_percent'],
                        "fee_y_n"=>$cur['fee_y_n'],

                        "order_priority" => $cur['order_priority'],
                        "type_of_payment" => $cur['type_of_payment'],
                        "process_time" => $cur['process_time'],
                        "withdraw_fee"=> NumberHelper::convert_double($cur['withdraw_fee']),
                        "withdraw_fee_formatted"=> NumberHelper::format_double($cur['withdraw_fee']),
                        "deposit_fee"=>NumberHelper::convert_double($cur['deposit_fee']),
                        "deposit_fee_formatted"=>NumberHelper::format_double($cur['deposit_fee']),
                        "country"=>$country,
                        "is_deposit"=>$cur['is_deposit'],
                        "is_withdraw"=>$cur['is_payout'],
                        "kyc_deposit"=> NumberHelper::convert_double($cur['kyc_deposit']),
                        "kyc_deposit_formatted"=> NumberHelper::format_double($cur['kyc_deposit']),
                        "kyc_withdraw"=>NumberHelper::convert_double($cur['kyc_payout']),
                        "kyc_withdraw_formatted"=>NumberHelper::format_double($cur['kyc_payout']),
                        "show_entercash_err_msg"=>$cur['show_entercash_err_msg'],
                        "swift"=>$cur['swift'],
                        "iban"=>$cur['iban'],

                        "payment_provider_id"=>$cur['payment_provider'],
                        "payment_provider_name"=>$cur['payment_provider_name'],

                        "token_id"=>$cur['token_id'],

                        "active_inactive_status" => $cur['payment_limit_rec_sts']
                    );
                }
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                        "credits"=>NumberHelper::convert_double($result['credits']),
                        "credits_formatted"=>NumberHelper::format_double($result['credits']),
                        "active_promotion"=>$result['active_promotion'],
                        "report"=>$rows
                    )
                );
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
			$message = "SiteMerchantManager::listPaymentLimitsForWhiteLabel(site_session_id = {$site_session_id}, currency={$currency}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
            $json_message = Zend_Json::encode(array("status" => NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
        }
	}

    /**
	 *
	 * Player calls for cancel withdraw on his pending payouts ...
	 * @param int $player_id
	 * @param string $swift
	 * @param string $iban
	 * @return mixed
	 */
	public static function setIbanSwift($player_id, $swift, $iban){
		//if one or more parameters are empty values
		if(strlen($player_id) == 0 || strlen($swift)==0 || strlen($iban)==0){
            $errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::setIbanSwift(player_id={$player_id}, swift={$swift}, iban={$iban})";
            $errorHelper->merchantError($message, $message);
            $json_message = Zend_Json::encode(array("status" => NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
        $player_id = trim(strip_tags($player_id));
        $swift = trim(strip_tags($swift));
        $iban = trim(strip_tags($iban));
		try{
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$result = $modelMerchant->setIbanSwiftForPlayer($player_id, $iban, $swift);
			if($result['status'] == OK && $result['status_out'] == "1"){
                $json_message = Zend_Json::encode(array("status" => OK, "result"=>OK));
                exit($json_message);
			}else{
                $json_message = Zend_Json::encode(array("status" => OK, "result"=>NOK));
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::setIbanSwift(player_id={$player_id}, swift={$swift}, iban={$iban}) method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
            $json_message = Zend_Json::encode(array("status" => OK, "result"=>NOK_EXCEPTION));
            exit($json_message);
		}
	}

    /**
     * @param int $pc_session_id
     * @param string $promotion_code
     * @return array
     */
    public static function getPromotionCode($pc_session_id, $promotion_code){
		//if one or more parameters are empty values
		if(strlen($pc_session_id) == 0 || strlen($promotion_code)==0){
            $errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::getPromotionCode(pc_session_id={$pc_session_id}, promotion_code={$promotion_code})";
            $errorHelper->merchantError($message, $message);
            $json_message = Zend_Json::encode(array("status" => OK, "result"=>NOK_INVALID_DATA));
            exit($json_message);
		}
        $pc_session_id = trim(strip_tags($pc_session_id));
        $promotion_code = trim(strip_tags($promotion_code));
		try{
			require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
			$modelWebSiteBonus = new WebSiteBonusModel();
			$result = $modelWebSiteBonus->getPromotionCode($pc_session_id, $promotion_code);
			if($result['status'] == OK && $result['status_out'] == "1"){
                $json_message = Zend_Json::encode(array("status" => OK, "result"=>OK));
                exit($json_message);
			}
            else if($result['status'] == OK && $result['status_out'] == "-1"){
                $json_message = Zend_Json::encode(array("status" => OK, "result"=>NOK, "message"=>"NOK_INVALID_PROMOTION_CODE"));
                exit($json_message);
            }
			else if($result['status'] == NOK && $result['message'] == "NOK_PROMOTION_USED_BY_PLAYER")
            {
                $json_message = Zend_Json::encode(array("status" => OK, "result"=>NOK, "message"=>"NOK_PROMOTION_USED_BY_PLAYER"));
                exit($json_message);
            }
            else if($result['status'] == NOK && $result['message'] == "NOK_PROMOTION_PLAYER_HAS_ACTIVE_BONUS")
            {
                $json_message = Zend_Json::encode(array("status" => OK, "result"=>NOK, "message"=>"NOK_PROMOTION_PLAYER_HAS_ACTIVE_BONUS"));
                exit($json_message);
            }
            else{
                $json_message = Zend_Json::encode(array("status" => OK, "result"=>NOK));
                exit($json_message);
            }
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $message = "SiteMerchantManager::getPromotionCode(pc_session_id = {$pc_session_id}, promotion_code = {$promotion_code}) method exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			$json_message = Zend_Json::encode(array("status" => OK, "result"=>NOK_EXCEPTION));
            exit($json_message);
		}
    }

}
