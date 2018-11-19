<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'PaysafecardDirectErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
/**
 *
 * Merchant manager to perform transaction processing from Paysafecard Direct payment processor ...
 *
 */
class PaysafecardDirectMerchantManagerPurchase {

    private $DEBUG = false;

    /**
     * THIS IS ENTRY POINT METHOD OF CLASS
     * @param array $unpackedPaysafecardDirectData
     * @return array
     */
    public function processOkOrPendingTransaction($unpackedPaysafecardDirectData){
        //try to confirm transaction in database
        $transactionResult = $this->confirmPurchaseTransactionToDatabase($unpackedPaysafecardDirectData);
        if($transactionResult["status"] == OK){
            //this transaction if confirmed in database
            //transaction is written in bank and in our database
            return array("status"=>OK);
        }else{
            return array("status"=>NOK);
        }

    }

	/**
	 * process confirming transaction to database received from paysafecard_direct payment service
	 * returns true for successful transaction
	 * returns false for not successful transaction
     * @param array $unpackedPaysafecardDirectData
	 * @return mixed
	 */
	private function confirmPurchaseTransactionToDatabase($unpackedPaysafecardDirectData){
		$pc_session_id = intval(trim(strip_tags($unpackedPaysafecardDirectData['pc_session_id'])));
		$transaction_id = trim(strip_tags($unpackedPaysafecardDirectData['transaction_id']));
		$amount = doubleval(trim(strip_tags($unpackedPaysafecardDirectData['amount'])));
		$paysafecard_direct_transaction_id = trim(strip_tags($unpackedPaysafecardDirectData['paysafecard_direct_transaction_id']));
		$currency_text = trim(strip_tags($unpackedPaysafecardDirectData['currency_text']));
		$currency_code = trim(strip_tags($unpackedPaysafecardDirectData['currency_code']));
		$credit_card_number = trim(strip_tags($unpackedPaysafecardDirectData['credit_card_number']));
		$credit_card_date_expires = trim(strip_tags($unpackedPaysafecardDirectData['credit_card_date_expires']));
		$credit_card_holder = trim(strip_tags($unpackedPaysafecardDirectData['credit_card_holder']));
		$credit_card_country = trim(strip_tags($unpackedPaysafecardDirectData['credit_card_country']));
		$credit_card_type = trim(strip_tags($unpackedPaysafecardDirectData['credit_card_type']));
		$start_time = trim(strip_tags($unpackedPaysafecardDirectData['start_time']));
		$bank_code = trim(strip_tags($unpackedPaysafecardDirectData['bank_code']));
		$ip_address = trim(strip_tags($unpackedPaysafecardDirectData['ip_address']));
		$card_issuer_bank = trim(strip_tags($unpackedPaysafecardDirectData['card_issuer_bank']));
		$card_country = trim(strip_tags($unpackedPaysafecardDirectData['card_country']));
		$client_email = trim(strip_tags($unpackedPaysafecardDirectData['client_email']));
		$over_limit = trim(strip_tags($unpackedPaysafecardDirectData['over_limit']));
		$bank_auth_code = trim(strip_tags($unpackedPaysafecardDirectData['bank_auth_code']));
		$payment_method_code = trim(strip_tags($unpackedPaysafecardDirectData['payment_method_code']));
		$merchant_order_reference_number = trim(strip_tags($unpackedPaysafecardDirectData['merchant_order_ref_number']));
		$site_domain = trim(strip_tags($unpackedPaysafecardDirectData['site_domain']));
		$bonus_code = trim(strip_tags($unpackedPaysafecardDirectData['bonus_code']));
		$fee_amount = trim(strip_tags($unpackedPaysafecardDirectData['fee_amount']));
		$player_id = trim(strip_tags($unpackedPaysafecardDirectData['player_id']));
        $token_id = trim(strip_tags($unpackedPaysafecardDirectData['token_id']));
		//if there are ip addresses with , separated as CSV string
		$ip_addresses = explode(",", $ip_address);
		$ip_address = $ip_addresses[0];
		try{
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            if($this->DEBUG) {
                $debug_package = print_r($unpackedPaysafecardDirectData, true);
                $message = "PaysafecardDirectMerchandManagerPurchase::confirmPurchaseTransactionToDatabase method called <br /> unpackedPaysafecardDirectdata = {$debug_package}";
                PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
            }
            //try 20 times to confirm transaction before giving up and voiding purchase is issued
            //expected result is transaction_id + 1
            //$error_code;
            $config = Zend_Registry::get('config');
			$numberOfAttempts = $config->paysafecardDirectConfirmToDatabaseAttempts;
			for($i=0;$i<$numberOfAttempts;$i++){
				//if player has sent a bonus campaign code then call procedure
				if(strlen($bonus_code) > 0){
					$result = $modelMerchant->bonusCreditDeposit($pc_session_id, $transaction_id, $amount, $paysafecard_direct_transaction_id, $currency_text,
						$credit_card_number, $credit_card_date_expires, $credit_card_holder, $credit_card_country, $credit_card_type,
						$start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, $over_limit, $bank_auth_code,
						$payment_method_code, $merchant_order_reference_number, $site_domain, $bonus_code, $fee_amount, PAYSAFECARD_DIRECT_PAYMENT_PROVIDER, $token_id);
					if(strlen($result['error_message']) > 0){
						//if there was an error during bonus credit deposit
						//send an error
						$message = "PaysafecardDirectMerchandManagerPurchase::confirmPurchaseTransactionToDatabase, bonusCreditDeposit called in DB: " . $result['error_message'] . "<br />" .
							"<br /> pc_session_id = {$pc_session_id} <br /> transaction_id = {$transaction_id} <br /> amount = {$amount} <br /> paysafecard_direct_transaction_id (psp_id) = {$paysafecard_direct_transaction_id} " .
							"<br /> currency_text = {$currency_text} <br /> credit_card_number = {$credit_card_number} <br /> credit_card_date_expires = {$credit_card_date_expires} " .
							"<br /> credit_card_holder = {$credit_card_holder} <br /> credit_card_country = {$credit_card_country} <br /> credit_card_type = {$credit_card_type} " .
							"<br /> start_time = {$start_time} <br /> bank_code = {$bank_code} <br /> ip_address = {$ip_address} <br /> card_issuer_bank = {$card_issuer_bank} " .
							"<br /> card_country = {card_country} <br /> client_email = {$client_email} <br /> over_limit = {$over_limit} <br /> bank_auth_code = {$bank_auth_code} " .
							"<br /> payment_method_code = {$payment_method_code} <br /> merchant_order_reference_number (OREF) = {$merchant_order_reference_number} <br /> site_domain = {$site_domain}" .
							"<br /> bonus_code = {$bonus_code} <br /> fee_amount = {$fee_amount} <br /> player_id = {$player_id} <br /> payment_provider = PAYSAFECARD DIRECT <br /> token_id = {$token_id}";
						PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
						return array("status"=>NOK);
					}
				}else{
					$result = $modelMerchant->confirmPurchaseForPaymentProvider($pc_session_id, $transaction_id, $amount, $paysafecard_direct_transaction_id, $currency_text,
					$credit_card_number, $credit_card_date_expires, $credit_card_holder, $credit_card_country, $credit_card_type,
					$start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, $over_limit, $bank_auth_code,
					$payment_method_code, $merchant_order_reference_number, $site_domain, PAYSAFECARD_DIRECT_PAYMENT_PROVIDER, $token_id);
					if(strlen($result['error_message']) > 0){
						//IT IS REQUIRED TO SEND MAIL TO PLAYER HERE
						$message = "PaysafecardDirectMerchandManagerPurchase::confirmPurchaseTransactionToDatabase, ApcoPayIN called in DB: " . $result['error_message'] . "<br />" .
							"<br /> pc_session_id = {$pc_session_id} <br /> transaction_id = {$transaction_id} <br /> amount = {$amount} <br /> paysafecard_direct_transaction_id (psp_id) = {$paysafecard_direct_transaction_id} " .
							"<br /> currency_text = {$currency_text} <br /> credit_card_number = {$credit_card_number} <br /> credit_card_date_expires = {$credit_card_date_expires} " .
							"<br /> credit_card_holder = {$credit_card_holder} <br /> credit_card_country = {$credit_card_country} <br /> credit_card_type = {$credit_card_type} " .
							"<br /> start_time = {$start_time} <br /> bank_code = {$bank_code} <br /> ip_address = {$ip_address} <br /> card_issuer_bank = {$card_issuer_bank} " .
							"<br /> card_country = {card_country} <br /> client_email = {$client_email} <br /> over_limit = {$over_limit} <br /> bank_auth_code = {$bank_auth_code} " .
							"<br /> payment_method_code = {$payment_method_code} <br /> merchant_order_reference_number (OREF) = {$merchant_order_reference_number} <br /> site_domain = {$site_domain}" .
							"<br /> bonus_code = {$bonus_code} <br /> fee_amount = {$fee_amount} <br /> player_id = {$player_id} <br /> payment_provider = PAYSAFECARD DIRECT <br /> token_id = {$token_id}";
						PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
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
								$message = "PaysafecardDirectMerchandManagerPurchase::confirmPurchaseTransactionToDatabase, Fee could not be charged after Paysafecard Direct Payment! <br /> session_id: {$feeResult['session_id']} <br /> player_id: {$feeResult['player_id']}
									<br /> deposit_amount: {$feeResult['deposit_amount']} <br /> currency: {$feeResult['currency']} <br /> fee_value: {$feeResult['fee_value']} <br /> error_code: {$feeResult['error_code']} <br /> error_message: {$feeResult['error_message']} <br /><br />" .
									"<br /> pc_session_id = {$pc_session_id} <br /> transaction_id = {$transaction_id} <br /> amount = {$amount} <br /> paysafecard_direct_transaction_id (psp_id) = {$paysafecard_direct_transaction_id} " .
									"<br /> currency_text = {$currency_text} <br /> credit_card_number = {$credit_card_number} <br /> credit_card_date_expires = {$credit_card_date_expires} " .
									"<br /> credit_card_holder = {$credit_card_holder} <br /> credit_card_country = {$credit_card_country} <br /> credit_card_type = {$credit_card_type} " .
									"<br /> start_time = {$start_time} <br /> bank_code = {$bank_code} <br /> ip_address = {$ip_address} <br /> card_issuer_bank = {$card_issuer_bank} " .
									"<br /> card_country = {card_country} <br /> client_email = {$client_email} <br /> over_limit = {$over_limit} <br /> bank_auth_code = {$bank_auth_code} " .
									"<br /> payment_method_code = {$payment_method_code} <br /> merchant_order_reference_number (OREF) = {$merchant_order_reference_number} <br /> site_domain = {$site_domain}" .
									"<br /> bonus_code = {$bonus_code} <br /> fee_amount = {$fee_amount} <br /> player_id = {$player_id} <br /> payment_provider = PAYSAFECARD DIRECT <br /> token_id = {$token_id}";
								PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
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
								$message = "PaysafecardDirectMerchandManagerPurchase::confirmPurchaseTransactionToDatabase, Fee could not be charged after Paysafecard Direct Payment! <br /> session_id: {$feeResult['session_id']} <br /> player_id: {$feeResult['player_id']}
									<br /> deposit_amount: {$feeResult['deposit_amount']} <br /> currency: {$feeResult['currency']} <br /> fee_value: {$feeResult['fee_value']} <br /> error_code: {$feeResult['error_code']} <br /> error_message: {$feeResult['error_message']}";
								PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
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
					$message = "PaysafecardDirectMerchandManagerPurchase::confirmPurchaseTransactionToDatabase Paysafecard Direct purchase transaction denied in database while game session open: <br />" . CursorToArrayHelper::getExceptionTraceAsString($result['message']);
					PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
					return array("status"=>NOK);
				}
				if($result['status'] == NOK){
					//remember message to variable for sending on mail and printing to log
					$error_code = $result['code'];
					$error_message = $result['message'];
					//confirming that Oracle database did not received transaction successfully
					$message = "PaysafecardDirectMerchandManagerPurchase::confirmPurchaseTransactionToDatabase method exception error: <br /> {$error_message}";
					PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
				}
				//sleep for 100000 microseconds - 100 ms * 10 is 1 second
				$delayTime = $config->paysafecardDirectConfirmToDatabaseTimeDelay;
				usleep($delayTime * 100000);
			}
			return array("status"=>NOK);
		}catch(Zend_Exception $ex){
			//confirming that Oracle database did not received transaction successfully
			$message = "PaysafecardDirectMerchandManagerPurchase::confirmPurchaseTransactionToDatabase method exception error: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
			return array("status"=>NOK);
		}
	}
}
