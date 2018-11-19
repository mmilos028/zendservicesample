<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'PaysafecardDirectErrorHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'PaysafecardDirectMerchantHelper.php';
require_once SERVICES_DIR . DS . 'paysafecard_direct' . DS . 'PaysafecardDirectMerchantManagerPurchase.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class WebSitePaysafecardDirectMerchantManager {

    private static $DEBUG = false;

    /**
	* PURCHASE ACTION WITH CUSTOM CARD (PAYSAFECARD) ON MERCHANT
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
	public static function getPaysafecardPaymentPurchaseMessage($site_session_id, $pc_session_id, $amount, $payment_method, $payment_method_id, $ip_address, $bonus_code, $css_template = 'Default'){
        if(self::$DEBUG) {
            //DEBUG THIS
            $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentPurchaseMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
            PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
        }
		if(strlen($site_session_id)==0 || strlen($pc_session_id)==0 || strlen($amount)==0 || strlen($payment_method)==0 || strlen($payment_method_id)==0){
            $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentPurchaseMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template})";
		    PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_INVALID_DATA));
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

        if(strlen($css_template) == 0){
            $css_template = 'Default';
        }

		try{
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            //get the currency for player
            $currencyData = $modelMerchant->currencyCodeForSession($pc_session_id);
            if($currencyData['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
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
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_GAME_SESSION_OPENED));
                    exit($json_message);
                }
            }
            //check transaction limit for player
            $transaction_limit = NO;
            //get transaction id from database
			$transactionData = $modelMerchant->getTransactionId($site_session_id, $currency_code);
			if($transactionData["status"] == NOK){
				$json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
                exit($json_message);
			}
			$transaction_id = $transactionData['transaction_id'];
			$currency_ok = $transactionData['currency_ok'];
			$oref_transaction_id = $transactionData['oref_transaction_id'];
            $db_transaction_id = $transactionData['payment_provider_transaction_id_purchase'];
			//if paysafecard payment does not support currency from web site return no currency error
			if($currency_ok != YES){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_CURRENCY));
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
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
                exit($json_message);
			}
			$details = $resultDetails['details'];
			//client account number is players unique id
			$player_id = $arrPlayer['player_id'];
            //client email
            $player_email = $details['email'];
            //find site settings for player with his player_id
            $siteSettings = $modelMerchant->findSiteSettings($arrPlayer['player_id']);
            if($siteSettings['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
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
                    $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentPurchaseMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
                        payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code}, css_template = {$css_template}) method:
                        <br /> Fee Could not be processed";
                    PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION, "deposit_amount"=>$amount, "fee_error_message"=>$feeResult['error_message'], "fee_error_code"=>$feeResult['error_code']));
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

            $casino_name = $siteSettings['casino_name'];

            $config = Zend_Registry::get('config');
            $success_redirect_url = $siteSettings['paysafecard_direct_redirection_site_success_link'];
            $fail_redirect_url = $siteSettings['paysafecard_direct_redirection_site_failed_link'];
            $notification_transaction_url = $config->paysafecardDirectNotificationUrl;

            $udf_field_value_1 = implode(';', array("OREF={$casino_name}", "PC_SESSION_ID={$pc_session_id}", "PAYMENT_METHOD={$payment_method}", "CURRENCY={$currency_text}", "PAYMENT_METHOD_ID={$payment_method_id}"));
            $udf_field_value_2 = implode(';', array("TRANSACTION_LIMIT={$transaction_limit}", "PLAYER_ID={$player_id}", "OREF_TRANSACTION_ID={$oref_transaction_id}", "BONUS_CODE={$bonus_code}"));
            $udf_field_value_3 = implode(';', array("TRANSACTION_ID={$transaction_id}", "FEE_AMOUNT={$fee_amount}", "DEPOSIT_AMOUNT={$deposit_amount}", "DB_TRANSACTION_ID={$db_transaction_id}", "EMAIL={$player_email}"));

            $paysafecardInitiatePaymentJsonObject = array(
                "type" => "PAYSAFECARD",
                "amount" => doubleval($deposit_amount),
                "currency" => $currency_text,
                "redirect" => array(
                  "success_url" => $success_redirect_url . "&udf1=" . urlencode($udf_field_value_1) . "&udf2=" . urlencode($udf_field_value_2) . "&udf3=" . urlencode($udf_field_value_3),
                  "failure_url" => $fail_redirect_url . "&udf1=" . urlencode($udf_field_value_1) . "&udf2=" . urlencode($udf_field_value_2) . "&udf3=" . urlencode($udf_field_value_3),
                ),
                "notification_url" => $notification_transaction_url . "?udf1=" . urlencode($udf_field_value_1) . "&udf2=" . urlencode($udf_field_value_2) . "&udf3=" . urlencode($udf_field_value_3),
                "customer" => array(
                    "id" => $player_id
                )
            );

            //$paysafecard_initiate_payment_json_message = urlencode(json_encode($paysafecardInitiatePaymentJsonObject));
            $paysafecard_initiate_payment_json_message = json_encode($paysafecardInitiatePaymentJsonObject);

            if(self::$DEBUG){
                $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentPurchaseMessage request = " . $paysafecard_initiate_payment_json_message;
                PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
            }

                $http_user = base64_encode($config->paysafecardDirectHttpUser);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $config->paysafecardDirectUrl . "payments");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $paysafecard_initiate_payment_json_message);
                if ($config->paysafecardVerifyCertificate == "true") {
                    curl_setopt($ch, CURLOPT_CAINFO, APP_DIR . "/configs/paysafecard_certificates/" . $config->paysafecardCertificateFileName);
                }else {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                  "Content-Type: application/json",
                  "Authorization: Basic {$http_user}"
                ));
                $response = curl_exec($ch);
                if(curl_errno($ch)){
                    //there was an error sending post to original credit player's transaction (error in player withdraw with WIRECARD)
                    $error_message = curl_error($ch);
                    curl_close($ch);
                    $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentPurchaseMessage(
                        site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id},
                        amount = {$amount}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address},
                        bonus_code = {$bonus_code}, css_template = {$css_template}) ERROR MESSAGE = " . $error_message;
                    PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
                    exit($json_message);
                }
                curl_close($ch);

                if(self::$DEBUG){
                    $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentPurchaseMessage response = " . $response;
                    PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                }

                $responsePaysafecardDirectPaymentJsonObject = json_decode($response, true);

                $object = $responsePaysafecardDirectPaymentJsonObject['object']; // PAYMENT
                $payment_id = $responsePaysafecardDirectPaymentJsonObject['id'];
                $created = $responsePaysafecardDirectPaymentJsonObject['created'];
                $updated = $responsePaysafecardDirectPaymentJsonObject['updated'];
                $status = $responsePaysafecardDirectPaymentJsonObject['status']; /// INITIATED
                $type = $responsePaysafecardDirectPaymentJsonObject['type']; /// PAYSAFECARD
                $success_url = $responsePaysafecardDirectPaymentJsonObject['redirect']['success_url'];
                $failure_url = $responsePaysafecardDirectPaymentJsonObject['redirect']['failure_url'];
                $auth_url = $responsePaysafecardDirectPaymentJsonObject['redirect']['auth_url'];
                $returned_player_id = $responsePaysafecardDirectPaymentJsonObject['customer']['id'];
                $notification_url = $responsePaysafecardDirectPaymentJsonObject['notification_url'];

            //returns status, message for wirecard post,, how much player wants to deposit, how much is fee + player deposit amount, how much is only fee we charge, currency that player is using, fee error message text

            $json_message = Zend_Json::encode(
                array(
                    "status"=>OK,
                    "player_deposit_amount"=>NumberHelper::convert_double($deposit_amount),
                    "player_deposit_amount_formatted"=>NumberHelper::format_double($deposit_amount),
                    "player_fee_deposit_amount"=>NumberHelper::convert_double($amount),
                    "player_fee_deposit_amount_formatted"=>NumberHelper::format_double($amount),
                    "fee_amount"=>NumberHelper::convert_double($fee_amount),
                    "fee_amount_formatted"=>NumberHelper::format_double($fee_amount),
                    "currency"=>$currency_text,
                    "fee_error_message"=>$feeResult['error_message'],

                    "requested_amount"=>NumberHelper::convert_double($deposit_amount),
                    "requested_amount_formatted"=>NumberHelper::format_double($deposit_amount),
                    "requested_amount_currency"=>$currency_text,
                    "ip_address"=>$ip_address,
                    "payment_method"=>$payment_method_id,
                    "payment_method_name"=>$payment_method,
                    "success_redirect_url"=>$success_url,
                    "fail_redirect_url"=>$failure_url,
                    "notification_transaction_url"=>$notification_url,
                    "redirect_url"=>$notification_url,
                    "player_id"=>$returned_player_id,
                    "auth_url"=>$auth_url,
                    "payment_id"=>$payment_id,
                    "created"=>$created,
                    "updated"=>$updated,
                    "status_payment"=>$status,
                    "type"=>$type
                )
            );
            exit($json_message);

        }catch(Zend_Exception $ex){
			//returns exception to web site
            $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentPurchaseMessage(site_session_id = {$site_session_id}, pc_session_id = {$pc_session_id}, amount = {$amount},
                payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, ip_address = {$ip_address}, bonus_code = {$bonus_code},
                css_template = {$css_template}) method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
            exit($json_message);
		}
	}

    public static function getPaysafecardPaymentDetails($payment_id){
        if(self::$DEBUG) {
            //DEBUG THIS
            $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails(payment_id = {$payment_id})";
            PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
        }
		if(strlen($payment_id)==0){
        $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails(payment_id = {$payment_id})";
		    PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
        $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_INVALID_DATA));
        exit($json_message);
		}
        try{
            $config = Zend_Registry::get('config');
            $http_user = base64_encode($config->paysafecardDirectHttpUser);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $config->paysafecardDirectUrl . "payments/" . $payment_id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            if ($config->paysafecardVerifyCertificate == "true") {
                curl_setopt($ch, CURLOPT_CAINFO, APP_DIR . "/configs/paysafecard_certificates/" . $config->paysafecardCertificateFileName);
            }else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              "Content-Type: application/json",
              "Authorization: Basic {$http_user}"
            ));
            $response = curl_exec($ch);
            if(curl_errno($ch)){
                //there was an error sending post to paysafecard to check payment
                $error_message = curl_error($ch);
                curl_close($ch);
                $message = "sitePaysafecardDirectMerchantController::getPaysafecardPaymentDetails(payment_id={$payment_id}) ERROR MESSAGE = " . $error_message;
                PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_INVALID_DATA));
                exit($json_message);
            }
            curl_close($ch);

            if(self::$DEBUG) {
                //DEBUG THIS PART OF CODE
                $message = $response;
                PaysafecardDirectErrorHelper::sendMail("SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails({$payment_id}) RETRIEVE PAYMENT DETAILS <br /> {$message}");
                PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails({$payment_id}) RETRIEVE PAYMENT DETAILS {$message}");
            }

            $responsePaysafecardDirectRetrievePaymentDetailsJsonObject = json_decode($response, true);

            $object = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['object'];
            $id = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['id'];
            $created = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['created'];
            $updated = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['updated'];
            $amount = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['amount'];
            $currency = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['currency'];
            $status = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['status'];
            $type = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['type'];
            $redirect_success_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'];
            $redirect_failure_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['failure_url'];
            $redirect_auth_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['auth_url'];
            $customer_id = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['customer']['id'];
            $customer_ip = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['customer']['ip'];
            $notification_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'];
            $card_details_serial = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['serial'];
            $card_details_type = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['type'];
            $card_details_country = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['country'];
            $card_details_currency = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['currency'];
            $card_details_amount = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['amount'];

            $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'] = urldecode($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url']);
                $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['failure_url'] = urldecode($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['failure_url']);
                $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['auth_url'] = urldecode($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['auth_url']);
                $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'] = urldecode($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url']);

                $udf1 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'], 'udf1');
                if(!isset($udf1)){
                  $udf1 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'], 'udf1');
                }
                $udf2 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'], 'udf2');
                if(!isset($udf2)){
                  $udf2 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'], 'udf2');
                }
                $udf3 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'], 'udf3');
                if(!isset($udf3)){
                  $udf3 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'], 'udf3');
                }

            //CAPTURE PAYMENT DETAILS
            if($status == "SUCCESS") {

                $capture_url = $config->paysafecardDirectUrl . "payments/" . $id . "/capture";
                if (self::$DEBUG) {
                    PaysafecardDirectErrorHelper::sendMail("SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails CAPTURE PAYMENT ON URL <br /> {$capture_url}");
                    PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails CAPTURE PAYMENT ON URL {$capture_url}");
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $capture_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                if ($config->paysafecardVerifyCertificate == "true") {
                    curl_setopt($ch, CURLOPT_CAINFO, APP_DIR . "/configs/paysafecard_certificates/" . $config->paysafecardCertificateFileName);
                }else {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Authorization: Basic {$http_user}"
                ));
                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    //there was an error sending post to PAYSAFECARD to capture payment
                    $error_message = curl_error($ch);
                    curl_close($ch);
                    $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails({$payment_id}) CAPTURE PAYMENT 2 ERROR MESSAGE = " . $error_message;
                    PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);

                    $paysafecard_direct_transaction_id = $id;
                    $player_id = $customer_id;
                    $ip_address = $customer_ip;
                    $card_number = $card_details_serial;
                    $card_country = $card_details_country;
                    //$unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                    //$this->notifyFailedTransaction($unpackedPaysafecardDirectData);
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
                    exit($json_message);
                } else {
                    //returns SUCCESS or AUTHORIZE
                    curl_close($ch);
                    $responsePaysafecardDirectCapturePaymentDetailsJsonObject = json_decode($response, true);
                    $object = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['object'];
                    $id = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['id'];
                    $created = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['created'];
                    $updated = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['updated'];
                    $amount = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['amount'];
                    $currency = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['currency'];
                    $status = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['status'];
                    $type = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['type'];
                    $redirect_success_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['redirect']['success_url'];
                    $redirect_failure_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['redirect']['failure_url'];
                    $redirect_auth_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['redirect']['auth_url'];
                    $customer_id = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['customer']['id'];
                    $customer_ip = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['customer']['ip'];
                    $notification_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['notification_url'];
                    $card_details_serial = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['serial'];
                    $card_details_type = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['type'];
                    $card_details_country = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['country'];
                    $card_details_currency = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['currency'];
                    $card_details_amount = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['amount'];

                    $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'] = urldecode($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url']);
                    $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['failure_url'] = urldecode($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['failure_url']);
                    $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['auth_url'] = urldecode($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['auth_url']);
                    $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'] = urldecode($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url']);

                    if (self::$DEBUG) {
                        //DEBUG THIS PART OF CODE
                        $message = $response;
                        PaysafecardDirectErrorHelper::sendMail("SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails RESPONSE CAPTURE PAYMENT 2 <br /> {$message}");
                        PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails RESPONSE CAPTURE PAYMENT 2 {$message}");
                    }

                    //SAVE CAPTURED TRANSACTION TO OUR database
                    switch ($status){
                        case "SUCCESS":
                            //do deposit action
                            if(self::$DEBUG) {
                                //DEBUG THIS PART OF CODE
                                $message = $response;
                                //PaysafecardDirectErrorHelper::sendMail("SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails RESPONSE CAPTURE PAYMENT 2 SUCCESS <br /> {$message}");
                                PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails RESPONSE CAPTURE PAYMENT 2 SUCCESS {$message}");
                            }
                            $paysafecard_direct_transaction_id = $id;
                            $player_id = $customer_id;
                            $ip_address = $customer_ip;
                            $card_number = $card_details_serial;
                            $card_country = $card_details_country;

                            $udf1 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'], 'udf1');
                            if(!isset($udf1)){
                              $udf1 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'], 'udf1');
                            }
                            $udf2 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'], 'udf2');
                            if(!isset($udf2)){
                              $udf2 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'], 'udf2');
                            }
                            $udf3 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'], 'udf3');
                            if(!isset($udf3)){
                              $udf3 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'], 'udf3');
                            }

                            $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                            $paysafecardDirectMerchantManagerPurchase = new PaysafecardDirectMerchantManagerPurchase();
                            $transactionResult = $paysafecardDirectMerchantManagerPurchase->processOkOrPendingTransaction($unpackedPaysafecardDirectData);
                            if($transactionResult['status'] == OK){
                              $json_message = Zend_Json::encode(array("status"=>OK, "result"=>$responsePaysafecardDirectRetrievePaymentDetailsJsonObject));
                              exit($json_message);
                            }else{
                                $paysafecard_direct_transaction_id = $id;
                                $player_id = $customer_id;
                                $ip_address = $customer_ip;
                                $card_number = $card_details_serial;
                                $card_country = $card_details_country;
                                $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                                self::notifyFailedTransaction($unpackedPaysafecardDirectData);
                                $json_message = Zend_Json::encode(array("status"=>NOK, "result"=>$responsePaysafecardDirectRetrievePaymentDetailsJsonObject));
                                exit($json_message);
                            }
                            break;
                        default:
                            $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails(payment_id = {$payment_id}) CAPTURE PAYMENT 2 ERROR {$status} PAYSAFECARD RECEIVED MESSAGE {$response} ";
                            PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                            $paysafecard_direct_transaction_id = $id;
                            $player_id = $customer_id;
                            $ip_address = $customer_ip;
                            $card_number = $card_details_serial;
                            $card_country = $card_details_country;

                            $udf1 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'], 'udf1');
                            if(!isset($udf1)){
                              $udf1 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'], 'udf1');
                            }
                            $udf2 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'], 'udf2');
                            if(!isset($udf2)){
                              $udf2 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'], 'udf2');
                            }
                            $udf3 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'], 'udf3');
                            if(!isset($udf3)){
                              $udf3 = PaysafecardDirectMerchantHelper::getParameterValueFromStringUrl($responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'], 'udf3');
                            }

                            $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                            self::notifyFailedTransaction($unpackedPaysafecardDirectData);
                            $json_message = Zend_Json::encode(array("status"=>NOK, "result"=>$responsePaysafecardDirectRetrievePaymentDetailsJsonObject));
                            exit($json_message);
                    }
                    //END SAVE PAYSAFECARD TRANSACTION TO OUR DATABASE
                }
            }
            //END CAPTURE PAYMENT DETAILS
            $json_message = Zend_Json::encode(array("status"=>NOK, "result"=>$responsePaysafecardDirectRetrievePaymentDetailsJsonObject));
            exit($json_message);

        }catch(Zend_Exception $ex){
            $error_message = $ex->getMessage();
            $message = "SitePaysafecardDirectMerchantManager::getPaysafecardPaymentDetails(payment_id={$payment_id}) ERROR MESSAGE = " . $error_message;
            PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
            exit($json_message);
        }
    }

    /**
	 *
	 * Calls player from web site to send his payout request for our database ...
	 * @param int $pc_session_id
	 * @param string $payment_method
     * @param string $payment_method_id
	 * @param number $amount
     * @param string $paysafecard_email
     * @param string $paysafecard_date_of_birth
     * @param string $paysafecard_first_name
     * @param string $paysafecard_last_name
	 * @param string $ip_address
	 * @return mixed
	 */
	public static function paysafecardWithdrawRequest($pc_session_id, $payment_method, $payment_method_id, $amount,
        $paysafecard_email, $paysafecard_date_of_birth, $paysafecard_first_name, $paysafecard_last_name,
        $ip_address){
        $paysafecard_email = urldecode($paysafecard_email);
        $paysafecard_first_name = urldecode($paysafecard_first_name);
        $paysafecard_last_name = urldecode($paysafecard_last_name);
        if(self::$DEBUG) {
            //DEBUG HERE
            $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount},
            paysafecard_email = {$paysafecard_email}, paysafecard_date_of_birth = {$paysafecard_date_of_birth}, paysafecard_first_name = {$paysafecard_first_name}, paysafecard_last_name = {$paysafecard_last_name},
            ip_address = {$ip_address})";
            PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
        }
		if(strlen($pc_session_id)==0 || strlen($payment_method)==0 || strlen($payment_method_id)==0 || strlen($amount)==0 ||
            strlen($paysafecard_email) == 0 || strlen($paysafecard_date_of_birth) == 0 || strlen($paysafecard_first_name) == 0 ||
            strlen($paysafecard_last_name) == 0 || strlen($ip_address) == 0){
            $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id},
            payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount},
            paysafecard_email = {$paysafecard_email}, paysafecard_date_of_birth = {$paysafecard_date_of_birth}, paysafecard_first_name = {$paysafecard_first_name}, paysafecard_last_name = {$paysafecard_last_name},
            ip_address = {$ip_address})";
		    PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_INVALID_DATA));
            exit($json_message);
		}
		//receive parameters, convert some to numbers
		//strip parameters from malvare html / javascript tags
		$pc_session_id = intval(strip_tags($pc_session_id));
		$payment_method = strip_tags($payment_method);
        $payment_method_id = strip_tags($payment_method_id);
		$amount = doubleval(strip_tags($amount));
		$ip_address = strip_tags($ip_address);

		$transaction_id = null;
        $credit_card_number = null;
        $credit_card_expiration_date = null;
		$credit_card_holder = null;
		$credit_card_country = null;
        $credit_card_type = "PAYSAFECARD";
		$start_time = null;
        $bank_code = null;
		//$ip_address = null;
        $card_issuer_bank = null;
		$card_country = null;
        $client_email = null;
		$over_limit = null;
        $bank_auth_code = null;
        $token_id = null;
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
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> urlencode(NOK_GAME_SESSION_OPENED)));
                    exit($json_message);
                }
            }

            //get the currency for player
            $currencyData = $modelMerchant->currencyCodeForSession($pc_session_id);
            if($currencyData['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1", "details"=>"invalid_currency"));
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

            $paysafecard_id = $arrPlayer['player_id'];
            $paysafecard_currency_text = $currency_text;

            $config = Zend_Registry::get('config');

            if($config->paysafecardDirectTestMode == "true"){
                //$paysafecard_currency_text = "EUR";
                if(strpos("eQrVaMNAVX@DJJCPiBNhS.bZB", $paysafecard_email, 0) !== false){
                    //EUR currency
                    $paysafecard_id = "374020759950";
                    $paysafecard_date_of_birth = "1987-07-31";
                    $paysafecard_first_name = "Test";
                    $paysafecard_last_name = "ôïäüøøγνЋЇqLTwiXlsxgQUeXiZ";
                }
                else if(strpos("IOGNfLIAUK@qqYzjTMDrh.JoM", $paysafecard_email, 0) !== false){
                    //EUR currency
                    $paysafecard_id = "911960365631";
                    $paysafecard_date_of_birth = "1987-07-31";
                    $paysafecard_first_name = "Test";
                    $paysafecard_last_name = "ùëßöØØζκЄЀoLaVCPMBgfdiquLZ";
                } else if(strpos("oVbkGSzmZV@IHcwAWvbcP.wTp", $paysafecard_email, 0) !== false){
                    //CHF currency
                    $paysafecard_id = "490998687423";
                    $paysafecard_date_of_birth = "1987-07-31";
                    $paysafecard_first_name = "Test";
                    $paysafecard_last_name = "àçääÆÅεαЂІHKnNfLNKgyXHkNQR";
                } else if(strpos("wCypGOxAhd@PuMLkHwqFD.oJO", $paysafecard_email, 0) !== false){
                    //CHF currency
                    $paysafecard_id = "700822526303";
                    $paysafecard_date_of_birth = "1987-07-31";
                    $paysafecard_first_name = "Test";
                    $paysafecard_last_name = "ôœüöØæβθЈІwPNjOmcCOotLRWAL";
                }
                else if(strpos("nTEkOxIClp@SzYMEQoWaT.JgL", $paysafecard_email, 0) !== false){
                    //GBP currency
                    $paysafecard_id = "911506171127";
                    $paysafecard_date_of_birth = "1987-07-31";
                    $paysafecard_first_name = "Test";
                    $paysafecard_last_name = "âîßßææανЄЂhNcCKeDDJKObmboC";
                }
                else if(strpos("lqFMvvExxj@nbgynvJZWc.WQv", $paysafecard_email, 0) !== false){
                    //GBP currency
                    $paysafecard_id = "689749119138";
                    $paysafecard_date_of_birth = "1987-07-31";
                    $paysafecard_first_name = "Test";
                    $paysafecard_last_name = "ëûööæøιγЅЅRPOdLgwCtExLDMyL";
                }
                else if(strpos("jOTQoeUxoi@HDdkoQTUMT.ntO", $paysafecard_email, 0) !== false){
                    //GBP currency
                    $paysafecard_id = "931473734596";
                    $paysafecard_date_of_birth = "1987-07-31";
                    $paysafecard_first_name = "Test";
                    $paysafecard_last_name = "îîäöøåξθЀЀYtOkXCpjYuWNZbJA";
                }
                else if(strpos("sauVgeCTQb@dzquxaOwUz.sTv", $paysafecard_email, 0) !== false){
                    //GBP currency
                    $paysafecard_id = "252081888572";
                    $paysafecard_date_of_birth = "1987-07-31";
                    $paysafecard_first_name = "Test";
                    $paysafecard_last_name = "ÿùßäØÅμδЉЈdDYkLuAgiYfAsMIc";
                }
                else{
                    //EUR currency
                    $paysafecard_id = "911506171127";
                    $paysafecard_date_of_birth = "1987-07-31";
                    $paysafecard_first_name = "Test";
                    $paysafecard_last_name = "âîßßææανЄЂhNcCKeDDJKObmboC";
                }
                /*
                $paysafecard_id = "merchantclientid5HzDvoZSodKDJ7X7VQKrtestAutomation";
                $paysafecard_email = "psc.mypins+9000001500_xZteDVTw@gmail.com";
                $paysafecard_date_of_birth = "1986-06-28";
                $paysafecard_first_name = "SuAeRHtjkNJSoraWHZAERgaRdA";
                $paysafecard_last_name = "VgObhlCPEXNexGsXqSuIWhzDtt";
                */
            }
			/*
			$paysafecard_email = "eQrVaMNAVX@DJJCPiBNhS.bZB";
            $paysafecard_id = "374020759950";
            $paysafecard_date_of_birth = "1987-07-31";
            $paysafecard_first_name = "Test";
            $paysafecard_last_name = "ôïäüøøγνЋЇqLTwiXlsxgQUeXiZ";
			*/

            $paysafecardValidatePayoutJsonObject = array(
                "type" => "PAYSAFECARD",
                "amount" => doubleval($amount),
                "currency" => $paysafecard_currency_text,
                "customer" => array(
                    "id" => $paysafecard_id,
                    "email" => $paysafecard_email,
                    "date_of_birth" => $paysafecard_date_of_birth,
                    "first_name" => $paysafecard_first_name,
                    "last_name" => $paysafecard_last_name,
                ),
                "capture" => false,
            );

            //$paysafecard_initiate_payment_json_message = urlencode(json_encode($paysafecardInitiatePaymentJsonObject));
            $paysafecard_validate_payout_json_message = json_encode($paysafecardValidatePayoutJsonObject);

            if(self::$DEBUG){
                $message = "SitePaysafecardDirectMerchantManager::payout request = " . $paysafecard_validate_payout_json_message;
                PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
            }

            if($config->paysafecardDirectTestMode == "true"){
                $http_user = "cHNjX0R4dThqSnI1LVdPYXhLWnpjOXdyMUtNLXd1Y3dZMXg=";
            }else{
                $http_user = base64_encode($config->paysafecardDirectHttpUser);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $config->paysafecardDirectUrl . "payouts");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paysafecard_validate_payout_json_message);
            if ($config->paysafecardVerifyCertificate == "true") {
                curl_setopt($ch, CURLOPT_CAINFO, APP_DIR . "/configs/paysafecard_certificates/" . $config->paysafecardCertificateFileName);
            }else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              "Content-Type: application/json",
              "Authorization: Basic {$http_user}"
            ));
            $response = curl_exec($ch);
            $info = curl_getinfo($ch);
            if(curl_errno($ch)){
                //there was an error sending post to original credit player's transaction (error in player withdraw with WIRECARD)
                $error_message = curl_error($ch);
                curl_close($ch);
                $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id},
                    payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount},
                    paysafecard_email = {$paysafecard_email}, paysafecard_date_of_birth = {$paysafecard_date_of_birth}, paysafecard_first_name = {$paysafecard_first_name}, paysafecard_last_name = {$paysafecard_last_name},
                    ip_address = {$ip_address}) ERROR MESSAGE = " . $error_message;
                PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=> NOK_EXCEPTION));
                exit($json_message);

            }
            curl_close($ch);

            if(self::$DEBUG){
                $message = "SitePaysafecardDirectMerchantManager::payout response = " . $response;
                PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
            }

            $responsePaysafecardDirectValidateResponseJsonObject = json_decode($response, true);

            //bad request from paysafecard validate payout
            if($info['http_code'] == 400){
                $code = $responsePaysafecardDirectValidateResponseJsonObject['code'];
                $message = $responsePaysafecardDirectValidateResponseJsonObject['message'];
                $message_number = $responsePaysafecardDirectValidateResponseJsonObject['number'];
                $param = $responsePaysafecardDirectValidateResponseJsonObject['param'];

                if(self::$DEBUG){
                    $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id},
                        payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount},
                        paysafecard_email = {$paysafecard_email}, paysafecard_date_of_birth = {$paysafecard_date_of_birth}, paysafecard_first_name = {$paysafecard_first_name}, paysafecard_last_name = {$paysafecard_last_name},
                        ip_address = {$ip_address}) Response (code = {$code}, message = {$message}, message_number = {$message_number}, param = {$param})";
                    PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
                }

                $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id},
                    payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount},
                    paysafecard_email = {$paysafecard_email}, paysafecard_date_of_birth = {$paysafecard_date_of_birth}, paysafecard_first_name = {$paysafecard_first_name}, paysafecard_last_name = {$paysafecard_last_name},
                    ip_address = {$ip_address}) Response (code = {$code}, message = {$message}, message_number = {$message_number}, param = {$param})";
                PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);

                $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>$code, "message"=>$message, "message_number"=>$message_number, "param"=>$param));
                exit($json_message);
            }else
            //correct request and passed validation from paysafecard validate payout
            if($info['http_code'] == 201){
                $object = $responsePaysafecardDirectValidateResponseJsonObject['object']; // PAYMENT
                $payment_id = $responsePaysafecardDirectValidateResponseJsonObject['id'];
                $created = $responsePaysafecardDirectValidateResponseJsonObject['created'];
                $updated = $responsePaysafecardDirectValidateResponseJsonObject['updated'];
                $currency = $responsePaysafecardDirectValidateResponseJsonObject['currency'];
                $paysafecard_amount = $responsePaysafecardDirectValidateResponseJsonObject['amount'];
                $customer_id = $responsePaysafecardDirectValidateResponseJsonObject['customer']['id'];
                $customer_email = $responsePaysafecardDirectValidateResponseJsonObject['customer']['email'];
                $customer_currency = $responsePaysafecardDirectValidateResponseJsonObject['customer_currency'];
                $customer_amount = $responsePaysafecardDirectValidateResponseJsonObject['customer_amount'];
                $status = $responsePaysafecardDirectValidateResponseJsonObject['status'];

                if(self::$DEBUG){
                    $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id},
                        payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount},
                        paysafecard_email = {$paysafecard_email}, paysafecard_date_of_birth = {$paysafecard_date_of_birth}, paysafecard_first_name = {$paysafecard_first_name}, paysafecard_last_name = {$paysafecard_last_name},
                        ip_address = {$ip_address}) Response(object = {$object}, payment_id = {$payment_id}, created = {$created}, updated = {$updated}, currency = {$currency}, paysafecard_amount = {$paysafecard_amount},
                            customer_id = {$customer_id}, customer_email = {$customer_email}, customer_currency = {$customer_currency}, customer_amount = {$customer_amount}, status = {$status})";
                    PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
                }

                if($status != "VALIDATION_SUCCESSFUL"){
                    $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id},
                        payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount},
                        paysafecard_email = {$paysafecard_email}, paysafecard_date_of_birth = {$paysafecard_date_of_birth}, paysafecard_first_name = {$paysafecard_first_name}, paysafecard_last_name = {$paysafecard_last_name},
                        ip_address = {$ip_address}) Response(object = {$object}, payment_id = {$payment_id}, created = {$created}, updated = {$updated}, currency = {$currency}, paysafecard_amount = {$paysafecard_amount},
                            customer_id = {$customer_id}, customer_email = {$customer_email}, customer_currency = {$customer_currency}, customer_amount = {$customer_amount}, status = {$status})";
                    PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                    $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1", "details"=>"paysafecard_validation_not_successful", "http_code"=>$info['http_code']));
                    exit($json_message);
                }
            }else{
                $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id},
                    payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount},
                    paysafecard_email = {$paysafecard_email}, paysafecard_date_of_birth = {$paysafecard_date_of_birth}, paysafecard_first_name = {$paysafecard_first_name}, paysafecard_last_name = {$paysafecard_last_name},
                    ip_address = {$ip_address})";
                PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1", "details"=>"paysafecard_validation_not_successful", "http_code"=>$info['http_code']));
                exit($json_message);
            }

            //get site settings for this player and his player_id
            $siteSettings = $modelMerchant->findSiteSettings($arrPlayer['player_id']);
            if($siteSettings['status'] != OK){
                $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1", "details"=>"player_find_site_settings_error"));
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
                $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, withdraw_amount_out = {$feeResult['withdraw_amount_out']},
                fee_amount_out = {$feeResult['fee_amount_out']}, ip_address = {$ip_address})";
                PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
            }
            if(strlen($feeResult['withdraw_amount_out'] == 0)){
                $feeResult['withdraw_amount_out'] = $amount;
            }
			if(strlen($feeResult['fee_amount_out'] == 0)){
                $feeResult['fee_amount_out'] = 0;
            }
            //new withdraw amount with fee deducted
            $new_amount = doubleval(trim(strip_tags($feeResult['withdraw_amount_out']))) - doubleval(trim(strip_tags($feeResult['fee_amount_out'])));
            if(strlen($new_amount) > 0){
                $amount = $new_amount;
            }

            //payouts player here
           $result = $modelMerchant->paymentProviderWithdrawRequest($pc_session_id, $transaction_id, $amount, $payment_id, $currency_text,
            $credit_card_number, $credit_card_expiration_date, $credit_card_holder, $credit_card_country, $credit_card_type,
            $start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, $over_limit, $bank_auth_code,
                $payment_method, $casinoName, $feeResult['fee_transaction_id'], PAYSAFECARD_DIRECT_PAYMENT_PROVIDER, $token_id);
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
                $logger_message =  "Player with mail address: {$player_mail_address} has not been notified for his request for payout through Paysafecard Direct payment processor via email.";
                WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address, $player_smtp_server, $title, $content, $title, $title, $logger_message, $site_images_location);
                $json_message = Zend_Json::encode(
                    array(
                        "status"=>OK,
                        "payment_method"=>$payment_method,
                        "amount"=>NumberHelper::convert_double($amount),
                        "amount_formatted"=>NumberHelper::format_double($amount),
                        "withdraw_amount_with_fee"=>NumberHelper::convert_double($feeResult['withdraw_amount']),
                        "withdraw_amount_with_fee_formatted"=>NumberHelper::format_double($feeResult['withdraw_amount']),
                        "withdraw_amount_without_fee"=>NumberHelper::convert_double($feeResult['withdraw_amount_out']),
                        "withdraw_amount_without_fee_formatted"=>NumberHelper::format_double($feeResult['withdraw_amount_out']),
                        "fee_amount_out"=>NumberHelper::convert_double($feeResult['fee_amount_out']),
                        "fee_amount_out_formatted"=>NumberHelper::format_double($feeResult['fee_amount_out'])
                    )
                );
                exit($json_message);
            }
            //return failed transaction to database if game session is open
            if($result['status'] == NOK && $result['code'] == 20101){
                //return array("status"=>NOK_GAME_SESSION_OPENED, "code"=>$result['code']);
                $res = $modelMerchant->closeOpenedGameSession($pc_session_id);
                if($res['status'] != OK){
                    $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount}, ip_address = {$ip_address}) method:
                    <br /> Credit card payout transaction denied in database while game session open!";
                    PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>urlencode(NOK_GAME_SESSION_OPENED)));
                    exit($json_message);
                }
            }

            if($result['status'] == NOK){
                //remember message to variable for sending on mail and printing to log
                $error_message = $result['message'];
                //confirming that Oracle database did not received transaction successfully
                $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount}, ip_address = {$ip_address}) method
                exception error: <br /> {$error_message}";
                PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>$result['code']));
                exit($json_message);
            }
            $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1"));
            exit($json_message);
        }catch(Zend_Exception $ex){
            $message = "SitePaysafecardDirectMerchantManager::payout(pc_session_id = {$pc_session_id}, payment_method = {$payment_method}, payment_method_id = {$payment_method_id}, amount = {$amount}, ip_address = {$ip_address}) method:
            <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
            $json_message = Zend_Json::encode(array("status"=>NOK, "code"=>"-1"));
            exit($json_message);
        }
	}

	public static function notifyFailedTransaction($unpackedPaysafecardDirect){
        $player_id = $unpackedPaysafecardDirect['player_id'];
        $received_data = print_r($unpackedPaysafecardDirect, true);
        $bo_session_id = null;
        require_once MODELS_DIR . DS . 'PlayerModel.php';
        $modelPlayer = new PlayerModel();
        //retrieve player details - required for player email addres
        $player_details = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
        if($player_details['status'] != OK){
            $message = "PaysafecardDirectMerchantController::purchase() <br /> {$received_data}";
            PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog($message);
            exit(NOK);
        }
        $details = $player_details['details'];
        //get site settings for player with player_id
        require_once MODELS_DIR . DS . 'MerchantModel.php';
        $modelMerchant = new MerchantModel();

        $modelMerchant->transactionApiResponse(
            $unpackedPaysafecardDirect['db_transaction_id'],
            NOK,
            print_r($unpackedPaysafecardDirect, true),
            "",
            $player_id,
            1,
            $unpackedPaysafecardDirect['payment_method_code'],
            PAYSAFECARD_DIRECT_PAYMENT_PROVIDER,
            $unpackedPaysafecardDirect['player_basic_deposit_amount'],
            $unpackedPaysafecardDirect['currency_text']
        );

        $site_settings = $modelMerchant->findSiteSettings($player_id);
        if($site_settings['status'] != OK){
            if(self::$DEBUG) {
                //DEBUG THIS CODE
                PaysafecardDirectErrorHelper::sendMail("PaysafecardDirectMerchantController::purchase action failed, can not get web site settings. <br /> {$received_data}");
                PaysafecardDirectErrorHelper::paysafecardDirectErrorLog("PaysafecardDirectMerchantController::purchase action failed, can not get web site settings. <br /> {$received_data}");
            }
            $message = "PaysafecardDirectMerchantController::purchaseAction(). resultsString = {$received_data}";
            PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog($message);
            exit(NOK);
        }
        $player_email_address = $details['email'];
        $player_username = $details['user_name'];
        $player_email_send_from = $site_settings['mail_address_from'];
        $player_smtp_server = $site_settings['smtp_server_ip'];
        $casino_name = $site_settings['casino_name'];
        $site_link = $site_settings['site_link'];
        $contact_link = $site_settings['contact_url_link'];
        $support_link = $site_settings['support_url_link'];
        $terms_link = $site_settings['terms_url_link'];
        $site_images_location = $site_settings['site_image_location'];
        $language_settings = $site_settings['language_settings'];
        //notify player of failed purchase
        $deposit_amount = $unpackedPaysafecardDirect['player_basic_deposit_amount'];
        $fee_amount = $unpackedPaysafecardDirect['fee_amount'];
        $currency_text = $unpackedPaysafecardDirect['currency_text'];
        $db_transaction_id = $unpackedPaysafecardDirect['db_transaction_id'];
        $payment_method = $unpackedPaysafecardDirect['payment_method_code'];
        if(strlen($player_email_address) != 0){
            $playerMailRes = WebSiteEmailHelper::getPurchaseFailedContent($deposit_amount, $fee_amount, $currency_text, $db_transaction_id, $payment_method,
            $player_username, $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings);
            $title = $playerMailRes['mail_title'];
            $content = $playerMailRes['mail_message'];
            $logger_message =  "Player with mail address: {$player_email_address} username: {$player_username}, has not been notified for failed credit transfer payment through Paysafecard Direct payment processor via email.";
            WebSiteEmailHelper::sendMailToPlayer($player_email_send_from, $player_email_address, $player_smtp_server, $title, $content,
                $title, $title, $logger_message, $site_images_location);
        }
    }

}
