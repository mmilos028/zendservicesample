<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'WirecardErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'WirecardMerchantHelper.php';
/**
 *
 * Merchant manager to perform transaction processing from Wirecard payment processor ...
 *
 */
class WirecardMerchantManagerPurchase {

    private $DEBUG = false;
	/**
	 *
	 * test if communication is done through white listed ip address range ...
	 */
	private function isSecureConnection(){
		$config = Zend_Registry::get('config');
		//will check site ip address caller if true
		if($config->checkWirecardIpAddress == 'true'){
			$ip_addresses = explode(' ', $config->wirecardIpAddress);
			$host_ip_address = IPHelper::getRealIPAddress();
			$status = in_array($host_ip_address, $ip_addresses);
			if(!$status){
				$message = "WirecardMerchantManagerPurchase::isSecureConnection service: Host with blacklisted ip address {$host_ip_address} is trying to connect to merchant payin / payout web service.";
				WirecardErrorHelper::wirecardError($message, $message);
			}
			return $status;
		} else {
            return true;
        }
	}


	/**
	 * This is called by Wirecard payment service for purchase so we can
	 * verify that transaction was successfull on Wirecard and on our side
	 * returns OK, NOK, NOK_EXCEPTION, NOK_VOIDED
	 * @param array $resultsArray
	 * @return mixed
	 */
	//resolve purchase transaction with credit cards
	public function purchase($resultsArray){
        $resultsString = print_r($resultsArray, true);
        if($this->DEBUG) {
            //DEBUG THIS PART OF CODE
            $message = "MerchantManagerPurchase::purchase method has received xml parametar values resultsString = {$resultsString}";
            WirecardErrorHelper::wirecardAccess($message, $message);
        }
        /*if($resultsArray["transaction_state"] != "success"){
            return array("status"=>NOK, "result"=>$resultsArray);
        }*/
        //check if transaction is coming from our payment processor
        //based on their ip address
        if (!$this->isSecureConnection()){
            return array("status"=>NOK, "result"=>$resultsArray);
        }
        try{
            if($this->DEBUG) {
                $message = "MerchantManagerPurchase::purchase method has xml parametar values resultsString = {$resultsString}";
                WirecardErrorHelper::wirecardAccess($message, $message);
            }

			if (WirecardMerchantHelper::testValidMessage($resultsArray, $resultsString)) {
				switch (strtoupper($resultsArray["transaction_state"])) {
					case "SUCCESS":
                        $unpackedWirecardData = WirecardMerchantHelper::validatePurchaseResponse($resultsArray, $resultsString);
                        return $this->processOkOrPendingTransaction($resultsArray, $unpackedWirecardData);
						break;
                    case "IN-PROGRESS":
                        //still pending transaction error
                        $message = "<br/><strong>WirecardMerchantManagerPurchase::purchase() IN PROGRESS</strong>: The transaction is still in progress. <br />Transaction ID: {$resultsArray['transaction_id']} <br />Status Code: {$resultsArray['status_code_1']} <br />Status Description: {$resultsArray['status_description_1']}";
                        $message = "<br />WireCard purchase: {$message}";
                        WirecardErrorHelper::wirecardErrorLog($message);
                        return array("status" => NOK, "result"=>$resultsArray);
                        break;
                    case "FAILED":
						//bank has declined transaction error
						$message = "<br/><strong>WirecardMerchantManagerPurchase::purchase() DECLINED</strong>: The transaction was declined. <br />Transaction ID: {$resultsArray['transaction_id']} <br />Status Code: {$resultsArray['status_code_1']} <br />Status Description: {$resultsArray['status_description_1']}";
						$message = "<br />WireCard purchase: {$message}";
						WirecardErrorHelper::wirecardErrorLog($message);
						return array("status"=>NOK, "result"=>$resultsArray);
                        break;
					default:
						//unknown error
						$message = "<br/><strong>WirecardMerchantManagerPurchase::purchase() OTHER RESULT</strong>: " . strtoupper($resultsArray['transaction_state']) . " <br />Transaction ID: {$resultsArray['transaction_id']} <br />Status Code: {$resultsArray['status_code_1']} <br />Status Description: {$resultsArray['status_description_1']}";
						$message = "<br />WireCard purchase: {$message}";
						WirecardErrorHelper::wirecardErrorLog($message);
						return array("status"=>NOK, "result"=>$resultsArray);
				}
			} else {
				//ERROR: invalid response signature
				$message = "<br /><strong>WirecardMerchantManagerPurchase::purchase() PURCHASE - Invalid Response Signature. Transaction is invalid, because message was tempered and response signature is invalid!</strong> <br />Transaction ID: {$resultsArray['transaction_id']} <br />Status Code: {$resultsArray['status_code_1']} <br />Status Description: {$resultsArray['status_description_1']}";
				$message = "<br />WireCard purchase: {$message}";
				WirecardErrorHelper::wirecardErrorLog($message);
				return array("status"=>NOK, "result"=>$resultsArray);
			}
		}catch(Zend_Exception $ex){
			$message = "WirecardMerchantManagerPurchase::purchase method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex) . "<br />Transaction ID: {$resultsArray['transaction_id']} <br />Status Code: {$resultsArray['status_code_1']} <br />Status Description: {$resultsArray['status_description_1']}";
			WirecardErrorHelper::wirecardErrorLog($message);
			return array("status"=>NOK, "result"=>$resultsArray);
		}
	}

    /**
     * @param $resultsArray
     * @param $unpackedWirecardData
     * @return array
     * @throws Zend_Exception
     */
    private function processOkOrPendingTransaction($resultsArray, $unpackedWirecardData){
        if($this->DEBUG) {
            $message = "MerchantManagerPurchase::purchase method called validateResponseWithToolPurchase = {$resultsArray['transaction_state']}, status SUCCESS";
            WirecardErrorHelper::wirecardAccess($message, $message);
        }
        //try to confirm transaction in database
        $transactionResult = $this->confirmPurchaseTransactionToDatabase($resultsArray, $unpackedWirecardData);
        if($transactionResult["status"] == OK){
            //this transaction if confirmed in database
            //transaction is written in bank and in our database
            return array("status"=>OK, "result"=>$resultsArray);
        }else{
            //this transaction is not confirmed in database
            //transaction is written in bank but not in our database
            //send signal to wirecard service to void purchase
            require_once SERVICES_DIR . DS . 'wirecard_merchant' . DS . 'wirecardMerchantManagerVoidOperation.php';
            $wirecardMerchantManagerVoidOperation = new WirecardMerchantManagerVoidOperation();
            $wirecardMerchantManagerVoidOperation->sendWirecardVoidPurchase($resultsArray);
            //send error mail to player and administrator, write error log for service
            return array("status"=>NOK, "result"=>$resultsArray);
        }

    }

	/**
	 * process confirming transaction to database received from wirecard payment service
	 * returns true for successful transaction
	 * returns false for not successful transaction
	 * @param array $resultsArray
     * @param array $unpackedWirecardData
	 * @return mixed
	 */
	private function confirmPurchaseTransactionToDatabase($resultsArray, $unpackedWirecardData){
		$pc_session_id = intval(trim(strip_tags($unpackedWirecardData['pc_session_id'])));
		$transaction_id = trim(strip_tags($unpackedWirecardData['transaction_id']));
		$amount = doubleval(trim(strip_tags($unpackedWirecardData['amount'])));
		$wirecard_transaction_id = trim(strip_tags($unpackedWirecardData['wirecard_transaction_id']));
		$currency_text = trim(strip_tags($unpackedWirecardData['currency_text']));
		$currency_code = trim(strip_tags($unpackedWirecardData['currency_code']));
		$credit_card_number = trim(strip_tags($unpackedWirecardData['credit_card_number']));
		$credit_card_date_expires = trim(strip_tags($unpackedWirecardData['credit_card_date_expires']));
		$credit_card_holder = trim(strip_tags($unpackedWirecardData['credit_card_holder']));
		$credit_card_country = trim(strip_tags($unpackedWirecardData['credit_card_country']));
		$credit_card_type = trim(strip_tags($unpackedWirecardData['credit_card_type']));
		$start_time = trim(strip_tags($unpackedWirecardData['start_time']));
		$bank_code = trim(strip_tags($unpackedWirecardData['bank_code']));
		$ip_address = trim(strip_tags($unpackedWirecardData['ip_address']));
		$card_issuer_bank = trim(strip_tags($unpackedWirecardData['card_issuer_bank']));
		$card_country = trim(strip_tags($unpackedWirecardData['card_country']));
		$client_email = trim(strip_tags($unpackedWirecardData['client_email']));
		$over_limit = trim(strip_tags($unpackedWirecardData['over_limit']));
		$bank_auth_code = trim(strip_tags($unpackedWirecardData['bank_auth_code']));
		$payment_method_code = trim(strip_tags($unpackedWirecardData['payment_method_code']));
		$merchant_order_reference_number = trim(strip_tags($unpackedWirecardData['merchant_order_ref_number']));
		$site_domain = trim(strip_tags($unpackedWirecardData['site_domain']));
		$bonus_code = trim(strip_tags($unpackedWirecardData['bonus_code']));
		$fee_amount = trim(strip_tags($unpackedWirecardData['fee_amount']));
		$player_id = trim(strip_tags($unpackedWirecardData['player_id']));
        $token_id = trim(strip_tags($unpackedWirecardData['token_id']));
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
		try{
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            if($this->DEBUG) {
                $message = "WirecardMerchantManagerPurchase::confirmPurchaseTransactionToDatabase method called";
                WirecardErrorHelper::wirecardAccess($message, $message);
            }
            //try 20 times to confirm transaction before giving up and voiding purchase is issued
            //expected result is transaction_id + 1
            //$error_code;
            $config = Zend_Registry::get('config');
			$numberOfAttempts = $config->wirecardConfirmToDatabaseAttempts;
			for($i=0;$i<$numberOfAttempts;$i++){
				//if player has sent a bonus campaign code then call procedure
				if(strlen($bonus_code) > 0){
					$result = $modelMerchant->bonusCreditDeposit($pc_session_id, $transaction_id, $amount, $wirecard_transaction_id, $currency_text,
						$credit_card_number, $credit_card_date_expires, $credit_card_holder, $credit_card_country, $credit_card_type,
						$start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, $over_limit, $bank_auth_code,
						$payment_method_code, $merchant_order_reference_number, $site_domain, $bonus_code, $fee_amount, WIRECARD_PAYMENT_PROVIDER, $token_id);
					if(strlen($result['error_message']) > 0){
						//if there was an error during bonus credit deposit
						//send an error
						$message = "WirecardMerchantManagerPurchase::confirmPurchaseTransactionToDatabase, bonusCreditDeposit called in DB: " . $result['error_message'] . "<br />" .
							"<br /> pc_session_id = {$pc_session_id} <br /> transaction_id = {$transaction_id} <br /> amount = {$amount} <br /> wirecard_transaction_id (psp_id) = {$wirecard_transaction_id} " .
							"<br /> currency_text = {$currency_text} <br /> credit_card_number = {$credit_card_number} <br /> credit_card_date_expires = {$credit_card_date_expires} " .
							"<br /> credit_card_holder = {$credit_card_holder} <br /> credit_card_country = {$credit_card_country} <br /> credit_card_type = {$credit_card_type} " .
							"<br /> start_time = {$start_time} <br /> bank_code = {$bank_code} <br /> ip_address = {$ip_address} <br /> card_issuer_bank = {$card_issuer_bank} " .
							"<br /> card_country = {card_country} <br /> client_email = {$client_email} <br /> over_limit = {$over_limit} <br /> bank_auth_code = {$bank_auth_code} " .
							"<br /> payment_method_code = {$payment_method_code} <br /> merchant_order_reference_number (OREF) = {$merchant_order_reference_number} <br /> site_domain = {$site_domain}" .
							"<br /> bonus_code = {$bonus_code} <br /> fee_amount = {$fee_amount} <br /> player_id = {$player_id} <br /> payment_provider = WIRECARD <br /> token_id = {$token_id}";
						WirecardErrorHelper::wirecardError($message, $message);
						return array("status"=>NOK);
					}
				}else{
					$result = $modelMerchant->confirmPurchaseForPaymentProvider($pc_session_id, $transaction_id, $amount, $wirecard_transaction_id, $currency_text,
					$credit_card_number, $credit_card_date_expires, $credit_card_holder, $credit_card_country, $credit_card_type,
					$start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, $over_limit, $bank_auth_code,
					$payment_method_code, $merchant_order_reference_number, $site_domain, WIRECARD_PAYMENT_PROVIDER, $token_id);
					if(strlen($result['error_message']) > 0){
						//IT IS REQUIRED TO SEND MAIL TO PLAYER HERE
						$message = "WirecardMerchantManagerPurchase::confirmPurchaseTransactionToDatabase, ApcoPayIN called in DB: " . $result['error_message'] . "<br />" .
							"<br /> pc_session_id = {$pc_session_id} <br /> transaction_id = {$transaction_id} <br /> amount = {$amount} <br /> wirecard_transaction_id (psp_id) = {$wirecard_transaction_id} " .
							"<br /> currency_text = {$currency_text} <br /> credit_card_number = {$credit_card_number} <br /> credit_card_date_expires = {$credit_card_date_expires} " .
							"<br /> credit_card_holder = {$credit_card_holder} <br /> credit_card_country = {$credit_card_country} <br /> credit_card_type = {$credit_card_type} " .
							"<br /> start_time = {$start_time} <br /> bank_code = {$bank_code} <br /> ip_address = {$ip_address} <br /> card_issuer_bank = {$card_issuer_bank} " .
							"<br /> card_country = {card_country} <br /> client_email = {$client_email} <br /> over_limit = {$over_limit} <br /> bank_auth_code = {$bank_auth_code} " .
							"<br /> payment_method_code = {$payment_method_code} <br /> merchant_order_reference_number (OREF) = {$merchant_order_reference_number} <br /> site_domain = {$site_domain}" .
							"<br /> bonus_code = {$bonus_code} <br /> fee_amount = {$fee_amount} <br /> player_id = {$player_id} <br /> payment_provider = WIRECARD <br /> token_id = {$token_id}";
						WirecardErrorHelper::wirecardError($message, $message);
						return array("status"=>NOK);
					}
				}

				if($result['status'] == OK){
					//convert to int from string
					$transaction_id_out = intval($result['transaction_id_out']);
					$transaction_id_rec = intval($transaction_id);
					//if transaction_id_out == -1 return true immediately
					if($transaction_id_out == -1){
						//make final transaction for FEE system - deposit FEE part 2
						//fee amount can be 0 if player deposited a large amount, he is free of fee payment
						if($fee_amount > 0){
							require_once MODELS_DIR . DS . 'WebSiteFeeModel.php';
							$modelWebSiteFee = new WebSiteFeeModel();
							$feeResult = $modelWebSiteFee->depositFeePart2($pc_session_id, $player_id, $currency_text, $fee_amount, $amount);
							if($feeResult['status'] != OK){
								//error if fee could not be charged
								$message = "WirecardMerchantManagerPurchase::confirmPurchaseTransactionToDatabase, Fee could not be charged after Wirecard Payment! <br /> session_id: {$feeResult['session_id']} <br /> player_id: {$feeResult['player_id']}
									<br /> deposit_amount: {$feeResult['deposit_amount']} <br /> currency: {$feeResult['currency']} <br /> fee_value: {$feeResult['fee_value']} <br /> error_code: {$feeResult['error_code']} <br /> error_message: {$feeResult['error_message']} <br /><br />" .
									"<br /> pc_session_id = {$pc_session_id} <br /> transaction_id = {$transaction_id} <br /> amount = {$amount} <br /> wirecard_transaction_id (psp_id) = {$wirecard_transaction_id} " .
									"<br /> currency_text = {$currency_text} <br /> credit_card_number = {$credit_card_number} <br /> credit_card_date_expires = {$credit_card_date_expires} " .
									"<br /> credit_card_holder = {$credit_card_holder} <br /> credit_card_country = {$credit_card_country} <br /> credit_card_type = {$credit_card_type} " .
									"<br /> start_time = {$start_time} <br /> bank_code = {$bank_code} <br /> ip_address = {$ip_address} <br /> card_issuer_bank = {$card_issuer_bank} " .
									"<br /> card_country = {card_country} <br /> client_email = {$client_email} <br /> over_limit = {$over_limit} <br /> bank_auth_code = {$bank_auth_code} " .
									"<br /> payment_method_code = {$payment_method_code} <br /> merchant_order_reference_number (OREF) = {$merchant_order_reference_number} <br /> site_domain = {$site_domain}" .
									"<br /> bonus_code = {$bonus_code} <br /> fee_amount = {$fee_amount} <br /> player_id = {$player_id} <br /> payment_provider = WIRECARD <br /> token_id = {$token_id}";
								WirecardErrorHelper::wirecardError($message, $message);
								//return that deposit failed here because of fee deposit failed
								return array("status"=>NOK);
							}
						}
						//confirming that Oracle database received transaction successfully
						return array("status"=>OK);
					}
					//if transaction_id is for 1 larger in database return true immediately
					if($transaction_id_out == ($transaction_id_rec + 1)){
						//make final transaction for FEE system - deposit FEE part 2
						if($fee_amount > 0){
							require_once MODELS_DIR . DS . 'WebSiteFeeModel.php';
							$modelWebSiteFee = new WebSiteFeeModel();
							$feeResult = $modelWebSiteFee->depositFeePart2($pc_session_id, $player_id, $currency_text, $fee_amount, $amount);
							if($feeResult['status'] != OK){
								//error if fee could not be charged
								$message = "WirecardMerchantManagerPurchase::confirmPurchaseTransactionToDatabase, Fee could not be charged after Wirecard Payment! <br /> session_id: {$feeResult['session_id']} <br /> player_id: {$feeResult['player_id']}
									<br /> deposit_amount: {$feeResult['deposit_amount']} <br /> currency: {$feeResult['currency']} <br /> fee_value: {$feeResult['fee_value']} <br /> error_code: {$feeResult['error_code']} <br /> error_message: {$feeResult['error_message']}";
								WirecardErrorHelper::wirecardError($message, $message);
								//return that deposit failed here because of fee deposit failed
								return array("status"=>NOK);
							}
						}
						//confirming that Oracle database received transaction successfully
						return array("status"=>OK);
					}
				}
				//return failed transaction to database if game session is open
				if($result['status'] == NOK && $result['code'] == "20101"){
					$message = "WirecardMerchantManagerPurchase::confirmPurchaseTransactionToDatabase Wirecard purchase transaction denied in database while game session open: <br />" . CursorToArrayHelper::getExceptionTraceAsString($result['message']);
					WirecardErrorHelper::wirecardError($message, $message);
					return array("status"=>NOK);
				}
				if($result['status'] == NOK){
					//remember message to variable for sending on mail and printing to log
					$error_code = $result['code'];
					$error_message = $result['message'];
					//confirming that Oracle database did not received transaction successfully
					$message = "WirecardMerchantManagerPurchase::confirmPurchaseTransactionToDatabase method exception error: <br /> {$error_message}";
					WirecardErrorHelper::wirecardError($message, $message);
				}
				//sleep for 100000 microseconds - 100 ms * 10 is 1 second
				$delayTime = $config->wirecardConfirmToDatabaseTimeDelay;
				usleep($delayTime * 100000);
			}
			return array("status"=>NOK);
		}catch(Zend_Exception $ex){
			//confirming that Oracle database did not received transaction successfully
			$message = "WirecardMerchantManagerPurchase::confirmPurchaseTransactionToDatabase method exception error: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK);
		}
	}
}
