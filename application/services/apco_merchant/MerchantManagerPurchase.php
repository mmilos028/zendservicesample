<?php

require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'ApcoErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'ApcoMerchantHelper.php';
/**
 *
 * Merchant manager to perform transaction processing from Apco Limited payment processor ...
 *
 */
class MerchantManagerPurchase {

    private $DEBUG = false;
	/**
	 *
	 * test if communication is done through white listed ip address range ...
	 */
	private function isSecureConnection(){
		$config = Zend_Registry::get('config');
		//will check site ip address caller if true
		if($config->checkApcoIpAddress == 'true'){
			$ip_addresses = explode(' ', $config->apcoIpAddress);
			$host_ip_address = IPHelper::getRealIPAddress();
			$status = in_array($host_ip_address, $ip_addresses);
			if(!$status){
				$message = "MerchantManagerPurchase::isSecureConnection service: Host with blacklisted ip address {$host_ip_address} is trying to connect to merchant payin / payout web service.";
				ApcoErrorHelper::apcoIntegrationError($message, $message);
			}
			return $status;
		} else {
            return true;
        }
	}


	/**
	 * This is called by Apco payment service for purchase so we can
	 * verify that transaction was successfull on Apco and on our side
	 * returns OK, NOK, NOK_EXCEPTION, NOK_VOIDED
	 * @param string $xmlFastPayString
	 * @return mixed
	 */
	//resolve purchase transaction with credit cards
	public function purchase($xmlFastPayString){
        if($this->DEBUG) {
            //DEBUG THIS PART OF CODE
            $message = "MerchantManagerPurchase::purchase method has received xml parametar values xmlFastPayString = {$xmlFastPayString}";
            ApcoErrorHelper::apcoIntegrationAccess($message, $message);
        }
        $xmlFastPayObjectResult = ApcoMerchantHelper::getParamsAndConvertToXmlObject($xmlFastPayString);
        if($xmlFastPayObjectResult["status"] == NOK){
            return array("status"=>NOK, "result"=>$xmlFastPayObjectResult["message"]);
        }
        $xmlFastPayObject = $xmlFastPayObjectResult["result"];
        //check if transaction is coming from our payment processor
        //based on their ip address
        if (!$this->isSecureConnection()){
            return array("status"=>NOK, "result"=>$xmlFastPayObject);
        }
        try{
            if($this->DEBUG) {
                $message = "MerchantManagerPurchase::purchase method has xml parametar values xmlFastPayString = {$xmlFastPayString}";
                ApcoErrorHelper::apcoIntegrationAccess($message, $message);
            }
			//get secret word for soap web service
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			//get secret words given by apco payment service
            $secretWords = $modelMerchant->getSecretWords(null);
            if($secretWords["status"] == NOK){
                return array("status"=>NOK, "result"=>$xmlFastPayObject);
            }
            if($this->DEBUG) {
                $message = "MerchantManagerPurchase::purchase method called secret words profile_id: {$secretWords['profile_id']} secret_word: {$secretWords['secret_word']}";
                ApcoErrorHelper::apcoIntegrationAccess($message, $message);
            }
            $profile_id = $secretWords['profile_id'];
            $secret_word = $secretWords['secret_word'];
            //get merchant code and merchant password from database
            $merchantCodes = $modelMerchant->getMerchantCodes(null);
            if($merchantCodes["status"] == NOK){
                return array("status"=>NOK, "result"=>$xmlFastPayObject);
            }
            $merchant_code = $merchantCodes['merchant_code'];
            $merchant_password = $merchantCodes['merchant_password'];
            if($this->DEBUG) {
                $message = "Merchant manager - purchase method called merchant words";
                ApcoErrorHelper::apcoIntegrationAccess($message, $message);
            }
			//check if message's hash value is correct - check if message is sent by apco service
            $res = ApcoMerchantHelper::reCheckMd5ValidationOnFastPayXMLPurchase($xmlFastPayObject, $secret_word);
			if ($res['status'] == OK) {
                $resultValue = $res['result'];
				//MDF successfully matched
				//send exception message on mail to administrators
                $validResponseResult = ApcoMerchantHelper::validateResponseWithToolPurchase($xmlFastPayString, $xmlFastPayObject, $merchant_code, $merchant_password);
				switch (strtoupper($resultValue)) {
					case BANK_OK:
                        return $this->processOkOrPendingTransaction($validResponseResult, $xmlFastPayObject, $xmlFastPayString);
						break;
                    case BANK_PENDING:
                        $payment_method = $validResponseResult['payment_method_code'];
                        if($payment_method == ENTERCASH){
                            //for entercash if pending process as if OK transaction
                            return $this->processOkOrPendingTransaction($validResponseResult, $xmlFastPayObject, $xmlFastPayString);
                        }else {
                            //still pending transaction error
                            $message = '<br/><strong>PENDING</strong>: The transaction is still pending. <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
                            $message = "<br />Apco purchase: {$message}";
                            ApcoErrorHelper::apcoIntegrationErrorLog($message);
                            return array("status" => NOK, "result" => $xmlFastPayObject);
                        }
                        break;
                    case BANK_DECLINED:
						//bank has declined transaction error
						$message = '<br/><strong>DECLINED</strong>: The transaction was declined. <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
						$message = "<br />Apco purchase: {$message}";
						ApcoErrorHelper::apcoIntegrationErrorLog($message);
						return array("status"=>NOK, "result"=>$xmlFastPayObject);
                        break;
					case BANK_NOK:
						//transaction was not successfull error
						$message = '<br/><strong>NOTOK</strong>: The transaction was not successful. <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
						$message = "<br />Apco purchase: {$message}";
						ApcoErrorHelper::apcoIntegrationErrorLog($message);
						return array("status"=>NOK, "result"=>$xmlFastPayObject);
					case BANK_CANCEL:
						//transaction was canceled error
						$message = '<br/><strong>CANCEL</strong>: The transaction is cancelled. <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
						$message = "<br />Apco purchase: {$message}";
						ApcoErrorHelper::apcoIntegrationErrorLog($message);
						return array("status"=>NOK, "result"=>$xmlFastPayObject);
					default:
						//unknown error
						$message = '<br/><strong>OTHER RESULT</strong>: ' . strtoupper($resultValue) . ' <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
						$message = "<br />Apco purchase: {$message}";
						ApcoErrorHelper::apcoIntegrationErrorLog($message);
						return array("status"=>NOK, "result"=>$xmlFastPayObject);
				}
			} else {
				//ERROR: invalid hash
				$message = "<br /><strong>PURCHASE - Invalid hash value. No match!!</strong> <br /> Merchant Order Reference Number: " . (string)$xmlFastPayObject->ORef;
				$message = "<br />Apco purchase: {$message}";
				ApcoErrorHelper::apcoIntegrationErrorLog($message);
				return array("status"=>NOK, "result"=>$xmlFastPayObject);
			}
		}catch(Zend_Exception $ex){
			$message = "MerchantManagerPurchase::purchase method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			ApcoErrorHelper::apcoIntegrationErrorLog($message);
			return array("status"=>NOK, "result"=>$xmlFastPayObject);
		}
	}

    /**
     * @param array $validResponseResult
     * @param object $xmlFastPayObject
     * @param string $xmlFastPayString
     * @return array
     */
    private function processOkOrPendingTransaction($validResponseResult, $xmlFastPayObject, $xmlFastPayString){
        if($this->DEBUG) {
            $message = "MerchantManagerPurchase::purchase method called validateResponseWithToolPurchase = {$validResponseResult['status']}, status BANK OK";
            ApcoErrorHelper::apcoIntegrationAccess($message, $message);
        }
        if($validResponseResult['status'] == OK){
            //try to confirm transaction in database
            $transactionResult = $this->confirmPurchaseTransactionToDatabase($validResponseResult);
            if($transactionResult["status"] == OK){
                //this transaction if confirmed in database
                //transaction is written in bank and in our database
                return array("status"=>OK, "result"=>$xmlFastPayObject);
            }else{
                //this transaction is not confirmed in database
                //transaction is written in bank but not in our database
                //send signal to apco service to void purchase
                require_once SERVICES_DIR . DS . 'apco_merchant' . DS . 'MerchantManagerVoidOperation.php';
                $merchantManagerVoidOperation = new MerchantManagerVoidOperation();
                $merchantManagerVoidOperation->sendApcoVoidPurchase($xmlFastPayString);
                //send error mail to player and administrator, write error log for service
                return array("status"=>NOK, "result"=>$xmlFastPayObject);
            }
        }else{
            //If we could not validate transaction in apco soap web service
            //TRANSACTION NOT OK ????
            $message = '<br/><strong>NOTOK</strong>: The transaction was not successful. <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
            $message = "<br />Apco purchase: {$message}";
            ApcoErrorHelper::apcoIntegrationError($message, $message);
            return array("status"=>NOK, "result"=>$xmlFastPayObject);
        }
    }

	/**
	 * process confirming transaction to database received from apco payment service
	 * returns true for successful transaction
	 * returns false for not successful transaction
	 * @param array $validResponseResult
	 * @return mixed
	 */
	private function confirmPurchaseTransactionToDatabase($validResponseResult){
		$pc_session_id = trim(strip_tags($validResponseResult['pc_session_id']));
		$transaction_id = trim(strip_tags($validResponseResult['transaction_id']));
		$amount = doubleval(trim(strip_tags($validResponseResult['amount'])));
		$apco_transaction_id = trim(strip_tags($validResponseResult['apco_transaction_id']));
		$currency_text = trim(strip_tags($validResponseResult['currency_text']));
		$currency_code = trim(strip_tags($validResponseResult['currency_code']));
		$credit_card_number = trim(strip_tags($validResponseResult['credit_card_number']));
		$credit_card_date_expires = trim(strip_tags($validResponseResult['credit_card_date_expires']));
		$credit_card_holder = trim(strip_tags($validResponseResult['credit_card_holder']));
		$credit_card_country = trim(strip_tags($validResponseResult['credit_card_country']));
		$credit_card_type = trim(strip_tags($validResponseResult['credit_card_type']));
		$start_time = trim(strip_tags($validResponseResult['start_time']));
		$bank_code = trim(strip_tags($validResponseResult['bank_code']));
		$ip_address = trim(strip_tags($validResponseResult['ip_address']));
		$card_issuer_bank = trim(strip_tags($validResponseResult['card_issuer_bank']));
		$card_country = trim(strip_tags($validResponseResult['card_country']));
		$client_email = trim(strip_tags($validResponseResult['client_email']));
		$over_limit = trim(strip_tags($validResponseResult['over_limit']));
		$bank_auth_code = trim(strip_tags($validResponseResult['bank_auth_code']));
		$payment_method_code = trim(strip_tags($validResponseResult['payment_method_code']));
		$merchant_order_reference_number = trim(strip_tags($validResponseResult['merchant_order_ref_number']));
		$site_domain = trim(strip_tags($validResponseResult['site_domain']));
		$bonus_code = trim(strip_tags($validResponseResult['bonus_code']));
		$fee_amount = trim(strip_tags($validResponseResult['fee_amount']));
		$player_id = trim(strip_tags($validResponseResult['player_id']));
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
		try{
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            if($this->DEBUG) {
                $message = "MerchantManagerPurchase::confirmPurchaseTransactionToDatabase method called";
                ApcoErrorHelper::apcoIntegrationAccess($message, $message);
            }
            //try 20 times to confirm transaction before giving up and voiding purchase is issued
            //expected result is transaction_id + 1
            //$error_code;
            $config = Zend_Registry::get('config');
			$numberOfAttempts = $config->apcoConfirmToDatabaseAttempts;
			for($i=0;$i<$numberOfAttempts;$i++){
				//if player has sent a bonus campaign code then call procedure
				if(strlen($bonus_code) > 0){
					$result = $modelMerchant->bonusCreditDeposit($pc_session_id, $transaction_id, $amount, $apco_transaction_id, $currency_text,
						$credit_card_number, $credit_card_date_expires, $credit_card_holder, $credit_card_country, $credit_card_type,
						$start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, $over_limit, $bank_auth_code,
						$payment_method_code, $merchant_order_reference_number, $site_domain, $bonus_code, $fee_amount, APCO_PAYMENT_PROVIDER);
					if(strlen($result['error_message']) > 0){
						//if there was an error during bonus credit deposit
						//send an error
						$message = "MerchantManagerPurchase::confirmPurchaseTransactionToDatabase, bonusCreditDeposit called in DB: " . $result['error_message'] . "<br />" .
							"<br /> pc_session_id = {$pc_session_id} <br /> transaction_id = {$transaction_id} <br /> amount = {$amount} <br /> apco_transaction_id (psp_id) = {$apco_transaction_id} " .
							"<br /> currency_text = {$currency_text} <br /> credit_card_number = {$credit_card_number} <br /> credit_card_date_expires = {$credit_card_date_expires} " .
							"<br /> credit_card_holder = {$credit_card_holder} <br /> credit_card_country = {$credit_card_country} <br /> credit_card_type = {$credit_card_type} " .
							"<br /> start_time = {$start_time} <br /> bank_code = {$bank_code} <br /> ip_address = {$ip_address} <br /> card_issuer_bank = {$card_issuer_bank} " .
							"<br /> card_country = {card_country} <br /> client_email = {$client_email} <br /> over_limit = {$over_limit} <br /> bank_auth_code = {$bank_auth_code} " .
							"<br /> payment_method_code = {$payment_method_code} <br /> merchant_order_reference_number (OREF) = {$merchant_order_reference_number} <br /> site_domain = {$site_domain}" .
							"<br /> bonus_code = {$bonus_code} <br /> fee_amount = {$fee_amount} <br /> player_id = {$player_id}";
						ApcoErrorHelper::apcoIntegrationError($message, $message);
						return array("status"=>NOK);
					}
				}else{
					$result = $modelMerchant->confirmPurchaseForPaymentProvider($pc_session_id, $transaction_id, $amount, $apco_transaction_id, $currency_text,
					$credit_card_number, $credit_card_date_expires, $credit_card_holder, $credit_card_country, $credit_card_type,
					$start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, $over_limit, $bank_auth_code,
					$payment_method_code, $merchant_order_reference_number, $site_domain, APCO_PAYMENT_PROVIDER);
					if(strlen($result['error_message']) > 0){
						//IT IS REQUIRED TO SEND MAIL TO PLAYER HERE
						$message = "MerchantManagerPurchase::confirmPurchaseTransactionToDatabase, ApcoPayIN called in DB: " . $result['error_message'] . "<br />" .
							"<br /> pc_session_id = {$pc_session_id} <br /> transaction_id = {$transaction_id} <br /> amount = {$amount} <br /> apco_transaction_id (psp_id) = {$apco_transaction_id} " .
							"<br /> currency_text = {$currency_text} <br /> credit_card_number = {$credit_card_number} <br /> credit_card_date_expires = {$credit_card_date_expires} " .
							"<br /> credit_card_holder = {$credit_card_holder} <br /> credit_card_country = {$credit_card_country} <br /> credit_card_type = {$credit_card_type} " .
							"<br /> start_time = {$start_time} <br /> bank_code = {$bank_code} <br /> ip_address = {$ip_address} <br /> card_issuer_bank = {$card_issuer_bank} " .
							"<br /> card_country = {card_country} <br /> client_email = {$client_email} <br /> over_limit = {$over_limit} <br /> bank_auth_code = {$bank_auth_code} " .
							"<br /> payment_method_code = {$payment_method_code} <br /> merchant_order_reference_number (OREF) = {$merchant_order_reference_number} <br /> site_domain = {$site_domain}" .
							"<br /> bonus_code = {$bonus_code} <br /> fee_amount = {$fee_amount} <br /> player_id = {$player_id}";
						ApcoErrorHelper::apcoIntegrationError($message, $message);
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
								$message = "MerchantManagerPurchase::confirmPurchaseTransactionToDatabase, Fee could not be charged after APCO Payment! <br /> session_id: {$feeResult['session_id']} <br /> player_id: {$feeResult['player_id']}
									<br /> deposit_amount: {$feeResult['deposit_amount']} <br /> currency: {$feeResult['currency']} <br /> fee_value: {$feeResult['fee_value']} <br /> error_code: {$feeResult['error_code']} <br /> error_message: {$feeResult['error_message']} <br /><br />" .
									"<br /> pc_session_id = {$pc_session_id} <br /> transaction_id = {$transaction_id} <br /> amount = {$amount} <br /> apco_transaction_id (psp_id) = {$apco_transaction_id} " .
									"<br /> currency_text = {$currency_text} <br /> credit_card_number = {$credit_card_number} <br /> credit_card_date_expires = {$credit_card_date_expires} " .
									"<br /> credit_card_holder = {$credit_card_holder} <br /> credit_card_country = {$credit_card_country} <br /> credit_card_type = {$credit_card_type} " .
									"<br /> start_time = {$start_time} <br /> bank_code = {$bank_code} <br /> ip_address = {$ip_address} <br /> card_issuer_bank = {$card_issuer_bank} " .
									"<br /> card_country = {card_country} <br /> client_email = {$client_email} <br /> over_limit = {$over_limit} <br /> bank_auth_code = {$bank_auth_code} " .
									"<br /> payment_method_code = {$payment_method_code} <br /> merchant_order_reference_number (OREF) = {$merchant_order_reference_number} <br /> site_domain = {$site_domain}" .
									"<br /> bonus_code = {$bonus_code} <br /> fee_amount = {$fee_amount} <br /> player_id = {$player_id}";
								ApcoErrorHelper::apcoIntegrationError($message, $message);
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
								$message = "MerchantManagerPurchase::confirmPurchaseTransactionToDatabase, Fee could not be charged after APCO Payment! <br /> session_id: {$feeResult['session_id']} <br /> player_id: {$feeResult['player_id']}
									<br /> deposit_amount: {$feeResult['deposit_amount']} <br /> currency: {$feeResult['currency']} <br /> fee_value: {$feeResult['fee_value']} <br /> error_code: {$feeResult['error_code']} <br /> error_message: {$feeResult['error_message']}";
								ApcoErrorHelper::apcoIntegrationError($message, $message);
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
					$message = "MerchantManagerPurchase::confirmPurchaseTransactionToDatabase Apco purchase transaction denied in database while game session open: <br />" . CursorToArrayHelper::getExceptionTraceAsString($result['message']);
					ApcoErrorHelper::apcoIntegrationError($message, $message);
					return array("status"=>NOK);
				}
				if($result['status'] == NOK){
					//remember message to variable for sending on mail and printing to log
					$error_code = $result['code'];
					$error_message = $result['message'];
					//confirming that Oracle database did not received transaction successfully
					$message = "MerchantManagerPurchase::confirmPurchaseTransactionToDatabase method exception error: <br /> {$error_message}";
					ApcoErrorHelper::apcoIntegrationError($message, $message);
				}
				//sleep for 100000 microseconds - 100 ms * 10 is 1 second
				$delayTime = $config->apcoConfirmToDatabaseTimeDelay;
				usleep($delayTime * 100000);
			}
			return array("status"=>NOK);
		}catch(Zend_Exception $ex){
			//confirming that Oracle database did not received transaction successfully
			$message = "MerchantManagerPurchase::confirmPurchaseTransactionToDatabase method exception error: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			ApcoErrorHelper::apcoIntegrationError($message, $message);
			return array("status"=>NOK);
		}
	}
}
