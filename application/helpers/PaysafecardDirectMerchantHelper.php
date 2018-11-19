<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'PaysafecardDirectErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';


class PaysafecardDirectMerchantHelper {

		public static $DEBUG = false;
    /**
	* extract data from received array for Paysafecard Direct payment provider
    * @param string $amount
    * @param string $currency
    * @param string $paysafecard_direct_transaction_id
    * @param string $player_id
    * @param string $ip_address
    * @param string $card_number
    * @param string $card_country
    * @param string $udf1
    * @param string $udf2
    * @param string $udf3
    * @param string $resultsString
	* @return mixed
	*/
	public static function compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3) {
		try {
			//if(self::$DEBUG){
				//PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantHelper::compactPurchaseResponse(amount = {$amount}, currency = {$currency}, paysafecard_direct_transaction_id = {$paysafecard_direct_transaction_id}, player_id = {$player_id}, ip_address = {$ip_address}, card_number = {$card_number}, card_country = {$card_country}, udf1 = {$udf1}, udf2 = {$udf2}, udf3 = {$udf3}) ");
			//}
            //EXTRACT UDF1
			//contains pc_session_id, payment_method as csv value element
			$csv_array1 = explode(";", $udf1);
            //extract casino_name (oref)
            $csv_array_1_0 = explode("=", $csv_array1[0]);
			$site_domain = (string)$csv_array_1_0[1];
            //extract pc_session_id
			$csv_array_1_1 = explode("=", $csv_array1[1]);
			$pc_session_id = $csv_array_1_1[1];
			//extract payment_method
            $csv_array_1_2 = explode("=", $csv_array1[2]);
            $payment_method_code = (string)$csv_array_1_2[1];
            //extract currency send by our system
            $csv_array_1_3 = explode("=", $csv_array1[3]);
            $currency_text = (string)$csv_array_1_3[1];
            //extract payment method id
            $csv_array_1_4 = explode("=", $csv_array1[4]);
            $payment_method_id = (string)$csv_array_1_4[1];

			//EXTRACT UDF2
			//contains csv array of values: transaction limit if is checked here AND player_id AND merchant order reference number site domain AND bonus campaign code separated with ;
			$csv_array2 = explode(";", $udf2);
			//test of over limit status
			$csv_array_2_0 = explode("=", $csv_array2[0]); //TRANSACTION_LIMIT
			$over_limit = $csv_array_2_0[1];
			//unique player id in our system
			$csv_array_2_1 = explode("=", $csv_array2[1]); //PLAYER_ID
			$player_id = $csv_array_2_1[1];
			//oref transaction from our database
			$csv_array_2_2 = explode("=", $csv_array2[2]); //OREF_TRANSACTION_ID
			$oref_transaction_id = $csv_array_2_2[1];
			//bonus code in our database if player had entered it
			$csv_array_2_3 = explode("=", $csv_array2[3]); //BONUS_CODE
			$bonus_code = $csv_array_2_3[1];

			//EXTRACT UDF3
			//contains transaction_id from database will be 0 for first attempt
			//contains csv array of values: transaction_id from database will be 0 for first attempt to verify transaction
			//AND fee amount (tax) AND what player wanted to pay (basic amount)
			$csv_array3 = explode(";", $udf3);
			$csv_array_3_0 = explode("=", $csv_array3[0]); //TRANSACTION_ID
			//transaction_id to verify transaction with database attempts 0
			$transaction_id = $csv_array_3_0[1];
			//how much fee (tax) is on player's deposit amount
			$csv_array_3_1 = explode("=", $csv_array3[1]); //FEE_AMOUNT
			$fee_amount = $csv_array_3_1[1];
			//how much player wanted to deposit without fee (basic amount that player entered on web site without fee tax)
			$csv_array_3_2 = explode("=", $csv_array3[2]); //DEPOSIT_AMOUNT
			$deposit_amount = $csv_array_3_2[1];
            //db transaction id
            $csv_array_3_3 = explode("=", $csv_array3[3]); //DB TRANSACTION ID
            $db_transaction_id = $csv_array_3_3[1];
            //email
            $csv_array_3_4 = explode("=", $csv_array3[4]); //EMAIL
            $email = $csv_array_3_4[1];

            //get currency text from currency code (from 978 to get EUR)
			require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
			$helperCurrencyList = new CurrencyListHelper();
            $currency_code = $helperCurrencyList->getCurrencyCode($currency_text);

            $access_message = "PAYSAFECARD DIRECT PURCHASE RESPONSE> PAYSAFECARD DIRECT TRANSACTION ID: {$paysafecard_direct_transaction_id} AMOUNT: {$amount} CURRENCY CODE: {$currency_code}
			 CURRENCY TEXT: {$currency_text} MASKED CARD NUMBER: CARD EXPIRY DATE:
			 CARD HOLDER NAME: PAYMENT METHOD CODE: {$payment_method_code} CARD COUNTRY: {$card_country} CARD TYPE: PAYSAFECARD
			 WEB SITE DOMAIN: {$site_domain} PC SESSION ID (UDF1): {$pc_session_id}
			 OREF TRANSACTION ID (UDF2): {$oref_transaction_id} TRANSACTION LIMIT (UDF2): {$over_limit} PLAYER ID (UDF2): {$player_id} BONUS CODE (UDF2): {$bonus_code}
			 TRANSACTION ID (UDF3): {$transaction_id} FEE AMOUNT (UDF3): {$fee_amount} DEPOSIT AMOUNT (UDF3): {$deposit_amount} DB TRANSACTION ID (UDF3): {$db_transaction_id}
			 MERCHANT ACCOUNT ID: TOKEN_ID:
			 <END STATUS URL PARAMS PURCHASE";
			PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantHelper::compactPurchaseResponse: {$access_message}");


			//pass data required to confirm transaction in database
			return array(
                "status"=>OK,
                "pc_session_id"=>$pc_session_id,
                "transaction_id"=>$transaction_id,
                "amount"=>$amount,
			    "paysafecard_direct_transaction_id"=>$paysafecard_direct_transaction_id,
                "currency_text"=>$currency_text,
                "currency_code"=>$currency_code,
                "credit_card_number"=>$card_number,
			    "credit_card_date_expires"=>"",
                "credit_card_holder"=>"",
                "credit_card_country"=>$card_country,
			    "credit_card_type"=>"PAYSAFECARD",
                "start_time"=>"",
                "bank_code"=>"",
                "ip_address"=>$ip_address,
			    "card_issuer_bank"=>"",
                "card_country_ip"=>$ip_address,
                "client_email"=>$email,
                "over_limit"=>$over_limit,
			    "bank_auth_code"=>"",
                "payment_method_code"=>$payment_method_code,
                "payment_method_id"=>$payment_method_id,
                "merchant_order_ref_number"=>$oref_transaction_id,
                "site_domain"=>$site_domain,
			    "bonus_code"=>$bonus_code,
                "fee_amount"=>$fee_amount,
                "player_basic_deposit_amount"=>$deposit_amount,
                "player_id"=>$player_id,
                "db_transaction_id"=>$db_transaction_id,
                "merchant_account_id"=>"",
                "token_id"=>""
            );
		} catch (Zend_Exception $ex) {
			$message = "PaysafecardDirectMerchantHelper::compactPurchaseResponse method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
		  PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	* extract data from received array for Paysafecard Direct payment provider
    * @param string $amount
    * @param string $paysafecard_direct_transaction_id
    * @param string $udf1
    * @param string $udf2
    * @param string $udf3
    * @param string $resultsString
	* @return mixed
	*/
	public static function compactBeginPurchaseResponse($amount, $paysafecard_direct_transaction_id, $udf1, $udf2, $udf3) {
		try {
			//if(self::$DEBUG){
				//PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantHelper::compactBeginPurchaseResponse(amount = {$amount}, paysafecard_direct_transaction_id = {$paysafecard_direct_transaction_id}, udf1 = {$udf1}, udf2 = {$udf2}, udf3 = {$udf3}) ");
			//}

            //EXTRACT UDF1
			//contains pc_session_id, payment_method as csv value element
			$csv_array1 = explode(";", $udf1);
            //extract casino_name (oref)
            $csv_array_1_0 = explode("=", $csv_array1[0]);
			$site_domain = (string)$csv_array_1_0[1];
            //extract pc_session_id
			$csv_array_1_1 = explode("=", $csv_array1[1]);
			$pc_session_id = $csv_array_1_1[1];
			//extract payment_method
            $csv_array_1_2 = explode("=", $csv_array1[2]);
            $payment_method_code = (string)$csv_array_1_2[1];
            //extract currency send by our system
            $csv_array_1_3 = explode("=", $csv_array1[3]);
            $currency_text = (string)$csv_array_1_3[1];
            //extract payment method id
            $csv_array_1_4 = explode("=", $csv_array1[4]);
            $payment_method_id = (string)$csv_array_1_4[1];

			//EXTRACT UDF2
			//contains csv array of values: transaction limit if is checked here AND player_id AND merchant order reference number site domain AND bonus campaign code separated with ;
			$csv_array2 = explode(";", $udf2);
			//test of over limit status
			$csv_array_2_0 = explode("=", $csv_array2[0]); //TRANSACTION_LIMIT
			$over_limit = $csv_array_2_0[1];
			//unique player id in our system
			$csv_array_2_1 = explode("=", $csv_array2[1]); //PLAYER_ID
			$player_id = $csv_array_2_1[1];
			//oref transaction from our database
			$csv_array_2_2 = explode("=", $csv_array2[2]); //OREF_TRANSACTION_ID
			$oref_transaction_id = $csv_array_2_2[1];
			//bonus code in our database if player had entered it
			$csv_array_2_3 = explode("=", $csv_array2[3]); //BONUS_CODE
			$bonus_code = $csv_array_2_3[1];

			//EXTRACT UDF3
			//contains transaction_id from database will be 0 for first attempt
			//contains csv array of values: transaction_id from database will be 0 for first attempt to verify transaction
			//AND fee amount (tax) AND what player wanted to pay (basic amount)
			$csv_array3 = explode(";", $udf3);
			$csv_array_3_0 = explode("=", $csv_array3[0]); //TRANSACTION_ID
			//transaction_id to verify transaction with database attempts 0
			$transaction_id = $csv_array_3_0[1];
			//how much fee (tax) is on player's deposit amount
			$csv_array_3_1 = explode("=", $csv_array3[1]); //FEE_AMOUNT
			$fee_amount = $csv_array_3_1[1];
			//how much player wanted to deposit without fee (basic amount that player entered on web site without fee tax)
			$csv_array_3_2 = explode("=", $csv_array3[2]); //DEPOSIT_AMOUNT
			$deposit_amount = $csv_array_3_2[1];
            //db transaction id
            $csv_array_3_3 = explode("=", $csv_array3[3]); //DB TRANSACTION ID
            $db_transaction_id = $csv_array_3_3[1];
            //email
            $csv_array_3_4 = explode("=", $csv_array3[4]); //EMAIL
            $email = $csv_array_3_4[1];

            //get currency text from currency code (from 978 to get EUR)
			require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
			$helperCurrencyList = new CurrencyListHelper();
            $currency_code = $helperCurrencyList->getCurrencyCode($currency_text);

            $access_message = "PAYSAFECARD DIRECT PURCHASE RESPONSE> PAYSAFECARD DIRECT TRANSACTION ID: {$paysafecard_direct_transaction_id} AMOUNT: {$amount} CURRENCY CODE: {$currency_code}
			 CURRENCY TEXT: {$currency_text} MASKED CARD NUMBER: CARD EXPIRY DATE:
			 CARD HOLDER NAME: PAYMENT METHOD CODE: {$payment_method_code} CARD COUNTRY: CARD TYPE: PAYSAFECARD
			 WEB SITE DOMAIN: {$site_domain} PC SESSION ID (UDF1): {$pc_session_id}
			 OREF TRANSACTION ID (UDF2): {$oref_transaction_id} TRANSACTION LIMIT (UDF2): {$over_limit} PLAYER ID (UDF2): {$player_id} BONUS CODE (UDF2): {$bonus_code}
			 TRANSACTION ID (UDF3): {$transaction_id} FEE AMOUNT (UDF3): {$fee_amount} DEPOSIT AMOUNT (UDF3): {$deposit_amount} DB TRANSACTION ID (UDF3): {$db_transaction_id}
			 MERCHANT ACCOUNT ID: TOKEN_ID:
			 <END STATUS URL PARAMS PURCHASE";
			PaysafecardDirectErrorHelper::paysafecardDirectErrorLog("PaysafecardDirectMerchantHelper::compactBeginPurchaseResponse: {$access_message}");


			//pass data required to confirm transaction in database
			return array(
                "status"=>OK,
                "pc_session_id"=>$pc_session_id,
                "transaction_id"=>$transaction_id,
                "amount"=>$amount,
			    "paysafecard_direct_transaction_id"=>$paysafecard_direct_transaction_id,
                "currency_text"=>$currency_text,
                "currency_code"=>$currency_code,
                "credit_card_number"=>"",
			    "credit_card_date_expires"=>"",
                "credit_card_holder"=>"",
                "credit_card_country"=>"",
			    "credit_card_type"=>"PAYSAFECARD",
                "start_time"=>"",
                "bank_code"=>"",
                "ip_address"=>"",
			    "card_issuer_bank"=>"",
                "card_country_ip"=>"",
                "client_email"=>$email,
                "over_limit"=>$over_limit,
			    "bank_auth_code"=>"",
                "payment_method_code"=>$payment_method_code,
                "payment_method_id"=>$payment_method_id,
                "merchant_order_ref_number"=>$oref_transaction_id,
                "site_domain"=>$site_domain,
			    "bonus_code"=>$bonus_code,
                "fee_amount"=>$fee_amount,
                "player_basic_deposit_amount"=>$deposit_amount,
                "player_id"=>$player_id,
                "db_transaction_id"=>$db_transaction_id,
                "merchant_account_id"=>"",
                "token_id"=>""
            );
		} catch (Zend_Exception $ex) {
			$message = "PaysafecardDirectMerchantHelper::compactBeginPurchaseResponse method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
		  PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}


	public static function getParameterValueFromStringUrl($url, $parameter_name)
	{
	    $parts = parse_url($url);
	    if(isset($parts['query']))
	    {
	        parse_str($parts['query'], $query);
	        if(isset($query[$parameter_name]))
	        {
	            return $query[$parameter_name];
	        }
	        else
	        {
	            return null;
	        }
	    }
	    else
	    {
	        return null;
	    }
	}
}
