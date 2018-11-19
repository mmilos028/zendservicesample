<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'WirecardErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';

/**
 * THIS IS FOR MERCHANT PAYOUT OF PLAYERS (PAYOUT PLAYER TO HIS ACCOUNT)
 * Merchant manager to perform transaction processing from Wirecard payment provider ...
 *
 */
class WirecardMerchantManagerOriginalCredit {

    private $DEBUG = false;

	/**
	* Sends Wirecard original credit command
	* This will make player payout from Wirecard
	* it was not confirmed in database
	* return true if successfull or false if there was an error
	* currency code = ex. 978
	* currency text = ex. EUR
	* returns true if command was send to wirecard to void credit
	* returns false if there was an error
	 * @param string $backoffice_session_id
	 * @param string $transaction_id_old
	 * @param string $db_transaction_id
	 * @param string $wirecard_transaction_id
	 * @param string $oref_transaction_id
	 * @param string $player_id
	 * @param float $amount
	 * @param string $currency_text
	 * @param string $currency_code
	 * @param string $payment_method
	 * @param float $fee_amount
     * @param string $token_id
	 * @return mixed
	 */
	public function sendWirecardOriginalCredit($backoffice_session_id, $transaction_id_old, $db_transaction_id, $wirecard_transaction_id, $oref_transaction_id,
	$player_id, $amount, $currency_text, $currency_code, $payment_method, $fee_amount, $token_id
    ){
		$backoffice_session_id = trim(strip_tags($backoffice_session_id));
		$transaction_id_old = trim(strip_tags($transaction_id_old));
		$db_transaction_id = trim(strip_tags($db_transaction_id));
		$wirecard_transaction_id = trim(strip_tags($wirecard_transaction_id));
		$oref_transaction_id = trim(strip_tags($oref_transaction_id));
		$player_id = trim(strip_tags($player_id));
		$amount = doubleval(trim(strip_tags($amount)));
		$currency_text = trim(strip_tags($currency_text));
		$currency_code = trim(strip_tags($currency_code));
		$payment_method = trim(strip_tags($payment_method));
		$fee_amount = doubleval(trim(strip_tags($fee_amount)));
        $token_id = trim(strip_tags($token_id));
		try{

            if($this->DEBUG) {
                //DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE FOR PAYOUT PLAYER
                $message = "WirecardMerchantManagerOriginalCredit::sendWirecardOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id},
			    wirecard_transaction_id={$wirecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text},
	            currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id = {$token_id})";
                WirecardErrorHelper::wirecardAccess($message, $message);
            }

            $account = WirecardMerchantHelper::getAccountSpecificDataPerPaymentMethod($payment_method);
            $wirecard_payment_rest_url = $account['wirecard_payment_rest_url'];
            $wirecard_payment_rest_username = $account['wirecard_payment_rest_username'];
            $wirecard_payment_rest_password = $account['wirecard_payment_rest_password'];
            //$wirecard_payment_method = $account['wirecard_payment_method'];
            $merchant_account_id = $account['merchant_account_id'];

            require_once MODELS_DIR . DS . 'PlayerModel.php';
            $modelPlayer = new PlayerModel();
            $playerDetails = $modelPlayer->getPlayerDetailsMalta(null, $player_id);
            $playerDetails = $playerDetails['details'];

            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            $site_settings = $modelMerchant->findSiteSettings($player_id);

            date_default_timezone_set("UTC");
            $request_time_stamp = gmdate("Y") . gmdate("m") . gmdate("d") . gmdate("H") . gmdate("i") . gmdate("s");
            $request_id = substr(hash('sha256', trim($db_transaction_id . $request_time_stamp)), 0, 32);
            $transaction_type = "original-credit";
            $requested_amount_currency = $currency_text;
            $requested_amount = $amount;
            $first_name = $playerDetails['first_name'];
            $last_name = $playerDetails['last_name'];
            $email = $playerDetails['email'];
            $phone = $playerDetails['phone'];
            $address_street1 = $playerDetails['address'];
            $address_street2 = $playerDetails['address2'];
            $address_city = $playerDetails['city'];
            $address_state = "";
            $address_country = $playerDetails['country_name'];
            $address_postal_code = $playerDetails['zip_code'];

            $payment_ip_address = $playerDetails['ip_address'];
            $wirecard_payment_method = $account['wirecard_payment_method'];
            $casino_name = $site_settings['casino_name'];
            //$order_id = $transaction_id_old . "-" . $db_transaction_id . "-" . $wirecard_transaction_id . "-" . $oref_transaction_id;
            //$order_detail = $casino_name . " - " . $transaction_id_old . "-" . $db_transaction_id . "-" . $wirecard_transaction_id . "-" . $oref_transaction_id;
			$order_id = $casino_name . "-" . $playerDetails['user_name'] . "-" . $db_transaction_id;
			$order_detail = $casino_name . "-" . $playerDetails['user_name'] . "-" . $db_transaction_id;
			
            $notification_transaction_url = $site_settings['wirecard_payout_link'];
            $notifications_format = 'application/x-www-form-urlencoded';

            $csv_udf1 = implode(";", array("BACKOFFICE_SESSION_ID={$backoffice_session_id}", "CURRENCY={$currency_text}", "CASINO_NAME={$casino_name}"));
            $csv_udf2 = implode(";", array("WITHDRAW_REQUEST_ID={$db_transaction_id}", "PLAYER_ID={$player_id}", "TRANSACTION_ID_OLD={$transaction_id_old}", "PAYMENT_METHOD={$payment_method}", "OREF_TRANSACTION_ID={$oref_transaction_id}", "FEE_AMOUNT={$fee_amount}"));
			$csv_udf3 = implode(";", array("USERNAME={$playerDetails['user_name']}", "CASINO_NAME={$casino_name}", "PLAYER_ID={$player_id}" ,"FIRST_NAME={$first_name}", "LAST_NAME={$last_name}"));
			

            if($this->DEBUG) {
                //DEBUG THIS CODE
                $message = "WirecardMerchantManagerOriginalCredit::sendWirecardOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old},
                db_transaction_id={$db_transaction_id}, wirecard_transaction_id={$wirecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method},
	            fee_amount={$fee_amount}, token_id={$token_id}) <br />
	            Original Credit URL: {$wirecard_payment_rest_url}";
                WirecardErrorHelper::wirecardAccess($message, $message);
            }

			$fields = array(
				'merchant_account_id' => $merchant_account_id,
                'request_id' => $request_id,
                'transaction_type' => $transaction_type,
                'requested_amount_currency' => $requested_amount_currency,
                'requested_amount' => $requested_amount,

                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'address_street1' => $address_street1,
                'address_street2' => $address_street2,
                'address_city' => $address_city,
                'address_state' => $address_state,
                'address_country' => $address_country,
                'address_postal_code' => $address_postal_code,

                'order_id' => $order_id,
                'order_detail' => $order_detail,
                'field_name_1' => 'udf1',
                'field_value_1' => $csv_udf1,
                'field_name_2' => 'udf2',
                'field_value_2' => $csv_udf2,
				'field_name_3' => 'udf3',
                'field_value_3' => $csv_udf3,
                'notification_transaction_url'=> $notification_transaction_url,
                'notifications_format'=> $notifications_format,
                'token_id' => $token_id
			);
            $fields_string = "";
			foreach($fields as $key=>$value) {
				$fields_string .= $key.'='.$value.'&';
			}
			rtrim($fields_string, '&');
			//start post init to wirecard rest payments URL
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $wirecard_payment_rest_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 300);
			curl_setopt($ch, CURLOPT_POST, count($fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			//disable ssl verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "$wirecard_payment_rest_username:$wirecard_payment_rest_password");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Connection: keep-alive'
		    ));
			$data = curl_exec($ch);
			if(curl_errno($ch)){
				//there was an error sending post to original credit player's transaction (error in player withdraw with WIRECARD)
				$error_message = curl_error($ch);
				$message = "WirecardMerchantManagerOriginalCredit::sendWirecardOriginalCredit(backoffice_session_id={$backoffice_session_id},
                transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id}, wirecard_transaction_id={$wirecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id={$token_id})
				<br /> Transaction was confirmed in database. <br />Exception message: <br /> {$error_message}";
				WirecardErrorHelper::wirecardError($message, $message);
				return array("status"=>NOK, "error_message"=>"Error while sending player payout to WIRECARD");
			}else{
                if($this->DEBUG) {
                    //DEBUG THIS CODE
                    $post_string = "";
                    foreach($fields as $key=>$value) {
                        $post_string .= $key . '=' . $value. '<br />';
                    }
                    $message = "WirecardMerchantManagerOriginalCredit::sendWirecardOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old},
                    db_transaction_id={$db_transaction_id}, wirecard_transaction_id={$wirecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
                    player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method},
                    fee_amount={$fee_amount}, token_id={$token_id}) <br />
                    Original Credit URL: {$wirecard_payment_rest_url} <br />
                    POST DATA: <br /> {$post_string} <br />
                    POST STRING: {$fields_string}
                    ";
                    WirecardErrorHelper::wirecardAccess($message, $message);
                }

				//player payout was success
				curl_close($ch);
				$message = "WirecardMerchantManagerOriginalCredit::sendWirecardOriginalCredit(backoffice_session_id={$backoffice_session_id},
                transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id}, wirecard_transaction_id={$wirecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id={$token_id}) <br />
				Player's payout request was confirmed in database and his Wirecard payout is to be confirmed. <br />";
				WirecardErrorHelper::wirecardAccessLog($message);
				return array("status"=>OK, "message"=>$data);
			}
		}catch(Zend_Exception $ex){
			//there was an error in player payout transaction
			$message = "WirecardMerchantManagerOriginalCredit::sendWirecardOriginalCredit(backoffice_session_id={$backoffice_session_id},
                transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id}, wirecard_transaction_id={$wirecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id={$token_id})
	            <br /> Exception occured in making payout command for Wirecard transaction.
	            <br />Transaction was confirmed in database, but there was no payout with Wirecard.
	            <br />Exception message: <br /> {$ex->getMessage()}";
			WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "error_message"=>"There was an error while making payout for WIRECARD transaction.
                <br />Transaction was confirmed in database, but there was no payout with Wirecard.
                <br />AMOUNT: {$amount}
                <br />CURRENCY_TEXT: {$currency_text}
                <br />CURRENCY_CODE: {$currency_code}
                <br />OREF_TRANSACTION_ID: {$oref_transaction_id}
                <br />WIRECARD_TRANSACTION_ID: {$wirecard_transaction_id}
                <br />TOKEN_ID: {$token_id}"
            );
		}
	}
}
