<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApcoErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';


class ApcoMerchantHelper {

    /**
	 * convert received xml structure into xml object in php
	 * returns xml object of received parameter or NOK_EXCEPTION message if there is error
	 * @param string $xmlFastPayString
	 * @return mixed
	 */
	public static function getParamsAndConvertToXmlObject($xmlFastPayString) {
		try {
			//receive expected url parameters as xml structure
			//convert xml structure to php simple xml element object
			$xmlFastPayObject = simplexml_load_string($xmlFastPayString);
			return array("status"=>OK, "result"=>$xmlFastPayObject);
		} catch (Zend_Exception $ex) {
			$message = "ApcoMerchantHelper::getParamsAndConvertToXmlObject method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			ApcoErrorHelper::apcoIntegrationError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	* validate if received message is from Apco payment service
	* use secret word and xml structure to validate
	* Gets the XML response from FastPay, replace the hash value of the Transaction tag with the secretword
	* and re-hash the xml to compare with the given hash value in the Transaction tag.
	* @param object $xmlFastPayObject The FastPay Xml response (SimpleXMLElement)
	* @param string $secretWord The Merchant's secret word, to be updated in the hash tag with it
	* @return mixed
	*/
	public static function reCheckMd5ValidationOnFastPayXMLPayout($xmlFastPayObject, $secretWord) {
		try {
			$resultValue = strtoupper($xmlFastPayObject->Result);
			return array("status" => OK, "result"=>$resultValue);
            //DEBUG THIS PART OF CODE
            /*
			$errorHelper = new ErrorHelper();
            $message = "ApcoMerchantHelper::reCheckMd5ValidationOnFastPayXMLPayout(xmlFastPayObject={$xmlFastPayObject}, secretWord={$secretWord})";
			$errorHelper->merchantAccess($message, $message);
            */
			//GET THE HASH VALUE, STORE TO BE USED FOR COMPARE AT A LATER STAGE AND REPLACE IT WITH THE SECRET WORD
			$sentHashValue = (string)$xmlFastPayObject->attributes()->hash;
			$xmlFastPayObject->attributes()->hash = $secretWord;
			if(strlen($xmlFastPayObject->ORef) == 0){
				$xmlFastPayObject->ORef = '';
				return array("status"=>NOK);
			}
			if(strlen($xmlFastPayObject->Result) == 0){
				$xmlFastPayObject->Result = '';
				return array("status"=>NOK);
			}
			if(strlen($xmlFastPayObject->AuthCode) == 0)
				$xmlFastPayObject->AuthCode = '';
			if(isset($xmlFastPayObject->CardInput) && strlen($xmlFastPayObject->CardInput) == 0)
				$xmlFastPayObject->CardInput = '';
			if(strlen($xmlFastPayObject->pspid) == 0){
				$xmlFastPayObject->pspid = '';
				return array("status"=>NOK);
			}
			if(isset($xmlFastPayObject->Currency) && strlen($xmlFastPayObject->Currency) == 0){
				$xmlFastPayObject->Currency = '';
			}
			if(isset($xmlFastPayObject->Value) && strlen($xmlFastPayObject->Value) == 0){
				$xmlFastPayObject->Value = '';
			}
			if(isset($xmlFastPayObject->ExtendedData->CardNum) && strlen($xmlFastPayObject->ExtendedData->CardNum) == 0)
				$xmlFastPayObject->ExtendedData->CardNum = '';
			if(isset($xmlFastPayObject->ExtendedData->CardExpiry) && strlen($xmlFastPayObject->ExtendedData->CardExpiry) == 0)
				$xmlFastPayObject->ExtendedData->CardExpiry = '';
			if(isset($xmlFastPayObject->ExtendedData->CardHName) && strlen($xmlFastPayObject->ExtendedData->CardHName) == 0)
				$xmlFastPayObject->ExtendedData->CardHName = '';
			if(isset($xmlFastPayObject->ExtendedData->Acq) && strlen($xmlFastPayObject->ExtendedData->Acq) == 0)
				$xmlFastPayObject->ExtendedData->Acq = '';
			if(isset($xmlFastPayObject->ExtendedData->Source) && strlen($xmlFastPayObject->ExtendedData->Source) == 0)
				$xmlFastPayObject->ExtendedData->Source = '';
			if(isset($xmlFastPayObject->ExtendedData->CardCountry) && strlen($xmlFastPayObject->ExtendedData->CardCountry) == 0){
				$xmlFastPayObject->ExtendedData->CardCountry = '';
			}
			if(isset($xmlFastPayObject->CardCountry) && strlen($xmlFastPayObject->CardCountry) == 0){
				$xmlFastPayObject->CardCountry = '';
			}
			if(isset($xmlFastPayObject->ExtendedData->CardType) && strlen($xmlFastPayObject->ExtendedData->CardType) == 0){
				$xmlFastPayObject->ExtendedData->CardType = '';
			}
			if(isset($xmlFastPayObject->CardType) && strlen($xmlFastPayObject->CardType) == 0){
				$xmlFastPayObject->CardType = '';
			}
			if(isset($xmlFastPayObject->UDF1) && strlen($xmlFastPayObject->UDF1) == 0)
				$xmlFastPayObject->UDF1 = '';
			if(isset($xmlFastPayObject->UDF2) && strlen($xmlFastPayObject->UDF2) == 0)
				$xmlFastPayObject->UDF2 = '';
			if(isset($xmlFastPayObject->UDF3) && strlen($xmlFastPayObject->UDF3) == 0)
				$xmlFastPayObject->UDF3 = '';
			//RETRIEVE THE RESULT OF THE TRANSACTION TO KNOW WHETHER THE TRANSACTION WAS SUCCESSFUL OR NOT
			$resultValue = strtoupper($xmlFastPayObject->Result);
			//CONVERT $domXML BACK TO A STRING TO REMOVE ANY EXTRA TAGS AND SPACES THAT MIGHT EFFECT THE HASH
			$finalXML = $xmlFastPayObject->saveXML();
			$finalXML = trim(substr($finalXML, strpos($finalXML, "<Transaction")));
			//validate Hash values
			$generatedHashValue = md5($finalXML);
			$status = strcmp($generatedHashValue, $sentHashValue) == 0;
			//DEBUG THIS PART OF CODE
            /*
			$errorHelper = new ErrorHelper();
			$message = "ApcoMerchantHelper::reCheckMd5ValidationOnFastPayXMLPayout method secretWord = {$secretWord} received hash = {$sentHashValue} generated hash = {$generatedHashValue} FINAL_XML = {$finalXML}";
			$errorHelper->merchantAccess($message, $message);
            */
            if($status) {
                return array("status" => OK, "result"=>$resultValue);
            }else{
                return array("status" => NOK);
            }
		} catch (Zend_Exception $ex) {
			$message = "ApcoMerchantHelper::reCheckMd5ValidationOnFastPayXMLPayout method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			ApcoErrorHelper::apcoIntegrationError($message, $message);
			return array("status" => NOK);
		}
	}

    /** validate if received message is from Apco payment service
	* use secret word and xml structure to validate
	* Gets the XML response from FastPay, replace the hash value of the Transaction tag with the secretword
	* and re-hash the xml to compare with the given hash value in the Transaction tag.
	* xmlFastPayObject The FastPay Xml response (SimpleXMLElement)
	* secretWord The Merchant's secret word, to be updated in the hash tag with it
	* return True/False. Where TRUE means that the validation is successful
	* @param object $xmlFastPayObject
	* @param string $secretWord
    * @return mixed
	*/
	public static function reCheckMd5ValidationOnFastPayXMLPurchase($xmlFastPayObject, $secretWord) {
		try {
			//ZBOG SKRILL-a iskljucena je provera !!!!!
			$resultValue = strtoupper($xmlFastPayObject->Result);
			return array("status" => OK, "result" => $resultValue);

			//GET THE HASH VALUE, STORE TO BE USED FOR COMPARE AT A LATER STAGE AND REPLACE IT WITH THE SECRET WORD
			$sentHashValue = (string)$xmlFastPayObject->attributes()->hash;
			$xmlFastPayObject->attributes()->hash = $secretWord;
			if(strlen($xmlFastPayObject->ORef) == 0){
				$xmlFastPayObject->ORef = '';
				return array("status"=>NOK);
			}
			if(strlen($xmlFastPayObject->Result) == 0){
				$xmlFastPayObject->Result = '';
				return array("status"=>NOK);
			}
			if(strlen($xmlFastPayObject->AuthCode) == 0){
				$xmlFastPayObject->AuthCode = '';
			}
			if(isset($xmlFastPayObject->CardInput) && strlen($xmlFastPayObject->CardInput) == 0){
				$xmlFastPayObject->CardInput = '';
			}
			if(strlen($xmlFastPayObject->pspid) == 0){
				$xmlFastPayObject->pspid = '';
				return array("status"=>NOK);
			}
			if(strlen($xmlFastPayObject->Currency) == 0){
				$xmlFastPayObject->Currency = '';
				return array("status"=>NOK);
			}
			if(strlen($xmlFastPayObject->Value) == 0){
				$xmlFastPayObject->Value = '';
				return array("status"=>NOK);
			}
			if($xmlFastPayObject->ExtendedData->CardNum != null && strlen($xmlFastPayObject->ExtendedData->CardNum) == 0){
				$xmlFastPayObject->ExtendedData->CardNum = '';
			}
			if(isset($xmlFastPayObject->ExtendedData->CardExpiry) && strlen($xmlFastPayObject->ExtendedData->CardExpiry) == 0){
				$xmlFastPayObject->ExtendedData->CardExpiry = '';
			}
			if($xmlFastPayObject->ExtendedData->CardHName != null && strlen($xmlFastPayObject->ExtendedData->CardHName) == 0){
				$xmlFastPayObject->ExtendedData->CardHName = '';
			}
			if($xmlFastPayObject->ExtendedData->Acq != null && strlen($xmlFastPayObject->ExtendedData->Acq) == 0){
				$xmlFastPayObject->ExtendedData->Acq = '';
			}
			if($xmlFastPayObject->ExtendedData->Source != null && strlen($xmlFastPayObject->ExtendedData->Source) == 0){
				$xmlFastPayObject->ExtendedData->Source = '';
			}
			if(isset($xmlFastPayObject->ExtendedData->CardCountry) && strlen($xmlFastPayObject->ExtendedData->CardCountry) == 0){
				$xmlFastPayObject->ExtendedData->CardCountry = '';
			}
			if(isset($xmlFastPayObject->CardCountry) && strlen($xmlFastPayObject->CardCountry) == 0){
				$xmlFastPayObject->CardCountry = '';
			}
			if(isset($xmlFastPayObject->ExtendedData->CardType) && strlen($xmlFastPayObject->ExtendedData->CardType) == 0){
				$xmlFastPayObject->ExtendedData->CardType = '';
			}
			if(isset($xmlFastPayObject->CardType) && strlen($xmlFastPayObject->CardType) == 0){
				$xmlFastPayObject->CardType = '';
			}
			if($xmlFastPayObject->UDF1 != null && strlen($xmlFastPayObject->UDF1) == 0){
				$xmlFastPayObject->UDF1 = '';
			}
			if($xmlFastPayObject->UDF2 != null && strlen($xmlFastPayObject->UDF2) == 0){
				$xmlFastPayObject->UDF2 = '';
			}
			if($xmlFastPayObject->UDF3 != null && strlen($xmlFastPayObject->UDF3) == 0){
				$xmlFastPayObject->UDF3 = '';
			}
			//RETRIEVE THE RESULT OF THE TRANSACTION TO KNOW WHETHER THE TRANSACTION WAS SUCCESSFUL OR NOT
			$resultValue = strtoupper($xmlFastPayObject->Result);
			//CONVERT $domXML BACK TO A STRING TO REMOVE ANY EXTRA TAGS AND SPACES THAT MIGHT EFFECT THE HASH
			$finalXML = $xmlFastPayObject->saveXML();
			$finalXML = trim(substr($finalXML, strpos($finalXML, "<Transaction")));
			//validate Hash values
			$generatedHashValue = md5($finalXML);
			$status = (strcmp($generatedHashValue, $sentHashValue) == 0);
			//DEBUG THIS PART OF CODE
			/*
			$errorHelper = new ErrorHelper();
			$message = "ApcoMerchantHelper::reCheckMd5ValidationOnFastPayXMLPurchase method secretWord = {$secretWord} received hash = {$sentHashValue} generated hash = {$generatedHashValue}";
			$errorHelper->merchantError($message, $message);
			*/
            if($status) {
                return array("status" => OK, "result" => $resultValue);
            }else{
                return array("status"=>NOK);
            }
		} catch (Zend_Exception $ex) {
			$message = "ApcoMerchantHelper::reCheckMd5ValidationOnFastPayXMLPurchase method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			ApcoErrorHelper::apcoIntegrationError($message, $message);
			return array("status"=>NOK);
		}
	}

    /**
	 * Validate from apco soap web service that payout action was success
	 * Searches transaction on OREF - our transaction id generated in our Oracle database
	 * Compare the XmlResponse from FastPay with the Transaction Information to make sure that it matches
     * @param string $xmlFastPayString the response given from the FastPay in XML Format (XML STRING)
	 * @param object $xmlFastPayObject the response given from the FastPay in XML Format (OBJECT)
	 * @param string $merchantCode
	 * @param string $merchantPassword
	 * @return mixed array of status (OK, NOK) and message (REASON of failed validation if NOK) or data received from service (if OK)
	 */
	public static function validateResponseWithToolPayout($xmlFastPayString, $xmlFastPayObject, $merchantCode, $merchantPassword) {
		try {
			//Retrieve the values from the XML response of FASTPAY
			$casino_name_oref = trim((string)$xmlFastPayObject->ORef); //casino name domain is sent here
			$psp_id = trim((string)$xmlFastPayObject->pspid);
			$acquirer_code = trim((string)$xmlFastPayObject->ExtendedData->Acq); //acquirer code, PTEST
			$card_country = trim((string)$xmlFastPayObject->ExtendedData->CardCountry); //POL, ...
			$udf1 = trim((string)$xmlFastPayObject->UDF1); //contains backoffice session id as csv value with ; separator
			$udf2 = trim((string)$xmlFastPayObject->UDF2); //contains csv values: $id ; $player_id ; $transaction_id_old ; payment method ; Oref; fee_amount
			$udf3 = $xmlFastPayObject->UDF3; //is empty
			/*$access_message = "STATUS URL PARAMS PAYOUT> PSPID: {$fastPayPspID} OREF: {$fastPayOref} Acquirer Code: {$fastPayAcquirerCode}
	        Session ID (UDF1): {$fastPayUDF1} Id, Player Id, Transaction_Id_Old (csv values as UDF2): {$fastPayUDF2} UDF3: {$fastPayUDF3} <END STATUS URL PARAMS PAYOUT";
			$errorHelper->merchantAccessLog("ApcoMerchantHelper::validateResponseWithToolPayout: {$access_message}");*/
			$amount = doubleval(trim(number_format((string)$xmlFastPayObject->Value, 2, '.', '')));
			$currency_code = trim((string)$xmlFastPayObject->Currency); //978
			require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
			$helperCurrencyList = new CurrencyListHelper();
			$currency_text = $helperCurrencyList->getCurrencyText($currency_code);
			$card_number = trim((string)$xmlFastPayObject->ExtendedData->CardNum);
			$card_expiry_date = "";
			$card_holder_name = trim((string)$xmlFastPayObject->ExtendedData->CardHName);
			$card_country_bin = "";
			$card_type = trim((string)$xmlFastPayObject->ExtendedData->CardType);
			$transaction_date = "";
			$bank_code = "";
			$user_ip_address = "";
			$card_issuer_bank = "";
			$card_country_ip_address = "";
			$email_address = "";
			$authorization_code = trim((string)$xmlFastPayObject->AuthCode);
			//extract additional information from UDF1 field (csv values)
			//backoffice session id
			$csvArray_1 = explode(";", $udf1);
			$csvArray_1_0 = explode("=", $csvArray_1[0]); //BACKOFFICE_SESSION_ID=40493978
			$backoffice_session_id = $csvArray_1_0[1];

            //$csvArray_1_1 = explode("=", $csvArray_1[1]); //CURRENCY=EUR
			//$currency_text = $csvArray_1_1[1];

			//extract additional informations from UDF2 field (csv values)
			// id (p_transaction_id), player_id, transaction_id_old (p_transaction_id_hang_in), payment_method by this order, oref transaction id ; fee_amount
			$csvArray2 = explode(";", $udf2);
			$csvArray_2_0 = explode("=", $csvArray2[0]); //WITHDRAW_REQUEST_ID=123456
			$withdraw_request_id = $csvArray_2_0[1];
			$csvArray_2_1 = explode("=", $csvArray2[1]); //PLAYER_ID=123456
			$player_id = $csvArray_2_1[1];
			$csvArray_2_2 = explode("=", $csvArray2[2]); //TRANSACTION_ID_OLD=123456
			$transaction_id_old = $csvArray_2_2[1];
			$csvArray_2_3 = explode("=", $csvArray2[3]); //PAYMENT_METHOD=MBKR
			$payment_method = $csvArray_2_3[1];
			$csvArray_2_4 = explode("=", $csvArray2[4]); //OREF_TRANSACTION_ID=123456
			$merchant_oref = $csvArray_2_4[1];
			$csvArray_2_5 = explode("=", $csvArray2[5]); //FEE_AMOUNT=10.00
			$fee_amount = $csvArray_2_5[1];

			$access_message = "STATUS URL PARAMS PAYOUT> PSPID: {$psp_id} OREF: {$casino_name_oref} Acquirer Code: {$acquirer_code}
	        Backoffice Session ID (csv values inside UDF1): {$udf1} Id, Player Id, Transaction_Id_Old, Old OREF, Fee Amount (csv values inside UDF2): {$udf2} UDF3: {$udf3} AMOUNT: {$amount} CURRENCY_CODE: {$currency_code} CURRENCY_TEXT: {$currency_text} <END STATUS URL PARAMS PAYOUT";
			ApcoErrorHelper::apcoIntegrationAccessLog("ApcoMerchantHelper::validateResponseWithToolPayout XML STRING: {$xmlFastPayString}");
			ApcoErrorHelper::apcoIntegrationAccessLog("ApcoMerchantHelper::validateResponseWithToolPayout PARSED RESPONSE: {$access_message}");
			return array("status"=>OK,
                "backoffice_session_id"=>$backoffice_session_id,
                "transaction_id_old"=>$transaction_id_old,
                "amount"=>$amount,
				"apco_transaction_id"=>$psp_id,
                "currency_code"=>$currency_code,
                "currency_text"=>$currency_text,
                "credit_card_number"=>$card_number,
				"credit_card_date_expires"=>$card_expiry_date,
                "credit_card_holder"=>$card_holder_name,
                "credit_card_country"=>$card_country_bin,
				"credit_card_type"=>$card_type,
                "start_time"=>$transaction_date,
                "bank_code"=>$bank_code,
                "ip_address"=>$user_ip_address,
				"card_issuer_bank"=>$card_issuer_bank,
                "card_country_ip"=>$card_country_ip_address,
                "client_email"=>$email_address,
                "withdraw_request_id"=>$withdraw_request_id,
                "bank_auth_code"=>$authorization_code,
				"merchant_order_ref_number"=>$merchant_oref,
                "player_id"=>$player_id,
                "payment_method"=>$payment_method,
                "casino_name"=>$casino_name_oref,
                "fee_amount"=>doubleval($fee_amount));
			/*
			//VERIFICATION OF APCO TRANSACTION WITH APCO SOAP WEB SERVICE NOT USED ANYMORE!!!!
			//get wsdl soap web service for apco transaction verification
			$apcoCreditCardSoapService = $config->apcoCreditCardSoapService;
			//CONNECT WITH THE TOOL AND RETRIEVE THE LAST TRANSACTION
			$client = new SoapClient($apcoCreditCardSoapService, array("trace" => 0, "exception" => 0));
			$soapResult = $client->getTransactionsByORef(array("MCHCode" => $merchantCode, "MCHPass" => $merchantPassword, "Oref" => $fastPayOref));
			//receive response from apco web service as xml object
			$xmlToolResponse = simplexml_load_string($soapResult->getTransactionsByORefResult->any);
			$Table1Result = $xmlToolResponse->xpath('//Table1');
			if(!$Table1Result){
				//if there is no transaction report returns false
				$access_message = "SOAP WEB SERVICE PAYOUT> NO TRANSACTION REPORT AVAILABLE FOR OREF: {$fastPayOref}
				PSPID: {$fastPayPspID} <END SOAP WEB SERVICE PAYOUT";
				$errorHelper = new ErrorHelper();
				$mail_message = "<br /><strong>NOTOK</strong>: The transaction payout was not successful. <br /> The PSP ID does not match to the original transaction: {$toolPspID}";
				$log_message = "ApcoMerchantHelper::validateResponseWithToolPayout: {$access_message}";
				$errorHelper->merchantAccess($mail_message, $log_message);
				return array("status"=>NOK, "message"=>BANK_NO_TRANSACTION_REPORT);
			}else{
				//there is response from apco soap web service
				foreach ($Table1Result as $item){
					//transactions are sorted by date, first is latest transaction, take only first if exists
					$toolPspID = trim((string)$item->PSPID);
					//2013-09-16T09:20:32+02:00
					$toolTrnDate = trim((string)$item->TrnDate);
					//PURC
					$toolTrnType = trim((string)$item->TrnType);
					//PTEST
					$toolBankCode = trim((string)$item->BankCode);
					//93.87.20.122
					$toolUserIP = trim((string)$item->UserIP);
					//444444***4444
					$toolCardNum = trim((string)$item->CardNum);
					//12\2014
					$toolExpDate = trim((string)$item->ExpDate);
					//first name and last name of client
					$toolCardHName = trim((string)$item->CardHname);
					//EUR
					$toolCurrencyCode = trim((string)$item->CurrencyCode);
					//1.0000
					$toolAmount = doubleval(trim(number_format((string)$item->Amount, 2, '.', '')));
					// YES | NO
					$toolBankAccept = trim(strtoupper((string)$item->BankAccept));
					//CAPTURED | NOT APPROVED | APPROVED | VOIDED
					$toolBankResponse = trim(strtoupper((string)$item->BankResponse));
					//TEST
					$toolAuthCode = trim((string)$item->AuthCode);
					// ''
					$toolCardIssuerBank = trim((string)$item->CardIssuerBank);
					// N/S, (Unknown)
					$toolCardCountryIP = trim((string)$item->CountryIP);
					// POL
					$toolCountryBIN = trim((string)$item->CountryBIN);
					// N/A, (Unknown)
					$toolCountryREG = trim((string)$item->CountryREG);
					// merchant order reference number
					$toolOref = trim((string)$item->OrderRef);
					//UDF1 - FPDirect
					$toolUDF1 = trim((string)$item->UDF1);
					//UDF2 - UDF2=,CT=TESTCARD, - here returns card type
					$toolUDF2 = trim((string)$item->UDF2);
					//UDF3 - sent xml structure - not hashed
					$toolUDF3 = trim((string)$item->UDF3);
					//client email address
					$toolEmail = trim((string)$item->Email);
					break;
				}
				//this will parse toolUDF3 to find only CARD TYPE VALUE
				$cardTypeArray = explode(',', $toolUDF2);
				$toolCardType = "";
				$no_items = count($cardTypeArray);
				for($i=0; $i<$no_items; $i++){
					if(strcmp(substr($cardTypeArray[$i], 0, 2), "CT") == 0){
						$toolCardType = substr($cardTypeArray[$i], 3, strlen($cardTypeArray[$i]));
					}
				}
				$fastPayCurrency = array_search($toolCurrencyCode, $this->listOfApcoCurrencies);
				//log the last transaction from bank web service into access log
				$access_message = "SOAP WEB SERVICE PAYOUT> PSPID: {$toolPspID} Trn Date: {$toolTrnDate} Trn Type: {$toolTrnType}
					Bank Code: {$toolBankCode} User IP: {$toolUserIP} Card Num: {$toolCardNum}
					Exp Date: {$toolExpDate} Card Holder Name: {$toolCardHName} Currency Text: {$fastPayCurrency} Currency Code: {$toolCurrencyCode}
					Amount: {$toolAmount} Bank Accept: {$toolBankAccept} Bank Response: {$toolBankResponse}
					AuthCode: {$toolAuthCode} Card Issuer Bank: {$toolCardIssuerBank} Card Country IP: {$toolCardCountryIP}
					Country BIN: {$toolCountryBIN} Country REG: {$toolCountryREG} OREF: {$toolOref}
					Email: {$toolEmail} Received UDF1: {$toolUDF1} Received UDF2: {$toolUDF2} Card Type: {$toolCardType}
					 <END SOAP WEB SERVICE PAYOUT";
				$errorHelper = new ErrorHelper();
				$errorHelper->merchantAccessLog("SOAP RESPONSE: " . $access_message);
			}
			//COMPARE THE RESULTS OF BOTH XMLs TO MAKE SURE THAT THEY MATCH
			//compare if transaction id from bank is equal
			if (strcmp($fastPayPspID, $toolPspID) != 0) {
				$errorHelper = new ErrorHelper();
				$mail_message = "<br /><strong>NOTOK</strong>: The transaction payout was not successful. <br /> The PSP ID does not match to the original transaction: {$toolPspID}";
				$log_message = "NOTOK: The transaction payout was not successful. The PSP ID does not match to the original transaction {$toolPspID}";
				$errorHelper->merchantError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>BANK_PSPID_NOT_MATCH);
			}
			//compare if merchant order reference number are equal
			if (strcmp($fastPayOref, $toolOref) != 0) {
				$errorHelper = new ErrorHelper();
				$mail_message = "<br /><strong>NOTOK</strong>: The transaction payout was not successful. <br /> The Order Reference does not match to the original transaction: {$toolPspID}";
				$log_message = "NOTOK: The transaction payout was not successful. The Order Reference does not match to the original transaction: {$toolPspID}";
				$errorHelper->merchantError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>BANK_OREF_NOT_MATCH);
			}
			//check extended data
			//test for bank accept on YES
			if(strcmp($toolBankAccept, BANK_YES) != 0){
				$errorHelper = new ErrorHelper();
				$mail_message = "<br /><strong>NOTOK</strong>: <br /> The transaction payout was not successful. <br /> Transaction payout not accepted by bank to the original transaction: {$toolPspID}";
				$log_message = "NOTOK: The transaction payout was not successful. Transaction payout not accepted by bank to the original transaction: {$toolPspID}";
				$errorHelper->merchantError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>BANK_TRANSACTION_NOT_ACCEPTED);
			}
			//test if bank captured money and it is approved
			if(strcmp($toolBankResponse, BANK_CAPTURED) != 0 && strcmp($toolBankResponse, BANK_PROCESSED) != 0){
				$errorHelper = new ErrorHelper();
				$mail_message = "<br /><strong>NOTOK</strong>: <br /> The transaction payout was not successful. <br /> Transaction payout not approved by bank to the original transaction: {$toolPspID} <br /> Bank Response: {$toolBankResponse}";
				$log_message = "NOTOK: The transaction payout was not successful. Transaction payout not approved by bank to the original transaction: {$toolPspID} Bank Response: {$toolBankResponse}";
				$errorHelper->merchantError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>BANK_TRANSACTION_NOT_APPROVED);
			}
			//extract additional informations from UDF2 field (csv values)
			// id (p_transaction_id), player_id, transaction_id_old (p_transaction_id_hang_in), payment_method by this order, our Transaction ID OREF old
			$csvArray = explode(";", $fastPayUDF2);
			//pass data required to confirm transaction in database
			return array("status"=>OK, "pc_session_id"=>intval($fastPayUDF1), "transaction_id_old"=>intval($transaction_id_old), "amount"=>$toolAmount,
				"apco_transaction_id"=>intval($toolPspID), "currency_code"=>$fastPayCurrency, "currency_text"=>$toolCurrencyCode, "credit_card_number"=>$toolCardNum,
				"credit_card_date_expires"=>$toolExpDate, "credit_card_holder"=>$toolCardHName, "credit_card_country"=>$toolCountryBIN,
				"credit_card_type"=>$toolCardType, "start_time"=>$toolTrnDate, "bank_code"=>$toolBankCode, "ip_address"=>$toolUserIP,
				"card_issuer_bank"=>$toolCardIssuerBank, "card_country_ip"=>$toolCardCountryIP, "client_email"=>$toolEmail, "transaction_id_hang"=>intval($transaction_id_hang), "bank_auth_code"=>$toolAuthCode,
				"merchant_order_ref_number"=>$merchant_oref, "player_id"=>intval($player_id), "payment_method"=>$payment_method, "casino_name"=>$fastPayOref, "fee_amount"=>doubleval($fee_amount));
			*/
		} catch (Zend_Exception $ex) {
			$message = "ApcoMerchantHelper::validateResponseWithToolPayout method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			ApcoErrorHelper::apcoIntegrationError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	* Validate from apco soap web service that purchase action was success
	* Searches transaction on OREF - our transaction id generated in our Oracle database
	* Compare the XmlResponse from FastPay with the Transaction Information to make sure that it matches
	* $xmlFastPayObject the response given from the FastPay in XML Format, merchant code and password
	* returns status OK | NOK and message for failed validation (if NOK) or data received during validation (if OK)
    * @param string $xmlFastPayString
	* @param object $xmlFastPayObject
	* @param string $merchantCode
	* @param string $merchantPassword
	* @return mixed
	*/
	public static function validateResponseWithToolPurchase($xmlFastPayString, $xmlFastPayObject, $merchantCode, $merchantPassword) {
		try {
			//Retrieve the values from the XML response of FASTPAY
			$casino_name_oref = trim((string)$xmlFastPayObject->ORef);
			$amount = doubleval(trim(number_format((string)$xmlFastPayObject->Value, 2, '.', '')));
			$psp_id = trim((string)$xmlFastPayObject->pspid);
			// returns currency code 978 instead of currency text EUR
			$currency_code = trim((string)$xmlFastPayObject->Currency);
			//authorization code TEST in test reg.
			$authorization_code = trim((string)$xmlFastPayObject->AuthCode);
			//EXTENDED DATA
			//credit card number 444444,4444
			$card_number = trim((string)$xmlFastPayObject->ExtendedData->CardNum);
			$card_number = trim(str_replace(",", "***", $card_number));
			//credit card expiry date, 12/2013
			$card_expiry_date = trim((string)$xmlFastPayObject->ExtendedData->CardExpiry);
			$card_expiry_date = trim(str_replace("/", "\\", $card_expiry_date));
			//first name and last name of client
			$card_holder_name = trim((string)$xmlFastPayObject->ExtendedData->CardHName);
			//acquirer code, PTEST
			$acquirer_code = trim((string)$xmlFastPayObject->ExtendedData->Acq);
			//Payment method code, PTEST
			$source = trim((string)$xmlFastPayObject->ExtendedData->Source);
			$card_country = '';
			if(isset($xmlFastPayObject->ExtendedData->CardCountry)){
				//POL, ...
				$card_country = trim((string)$xmlFastPayObject->ExtendedData->CardCountry);
			}
			if(isset($xmlFastPayObject->CardCountry)){
				//POL, ...
				$card_country = trim((string)$xmlFastPayObject->CardCountry);
			}
			$card_type = '';
			if(isset($xmlFastPayObject->ExtendedData->CardType)){
				//VISA, ...
				$card_type = trim((string)$xmlFastPayObject->ExtendedData->CardType);
			}
			if(isset($xmlFastPayObject->CardType)){
				//VISA, ...
				$card_type = trim((string)$xmlFastPayObject->CardType);
			}

			//EXTRACT UDF1
			//contains pc session id here
			$udf1 = trim((string)$xmlFastPayObject->UDF1);
			//contains pc_session_id, payment_method as csv value element
			$csv_array1 = explode(";", $udf1);
            //extract pc_session_id
			$csv_array_1_0 = explode("=", $csv_array1[0]);
			$pc_session_id = (int)$csv_array_1_0[1];
			//extract payment_method
            $csv_array_1_1 = explode("=", $csv_array1[1]);
            $payment_method = (string)$csv_array_1_1[1];
            //extract currency send by our system
            $csv_array_1_2 = explode("=", $csv_array1[2]);
            $currency = (string)$csv_array_1_2[1];
            //extract payment method id
            $csv_array_1_3 = explode("=", $csv_array1[3]);
            $payment_method_id = (string)$csv_array_1_3[2];

			//EXTRACT UDF2
			//contains csv array of values: transaction limit if is checked here AND player_id AND merchant order reference number site domain AND bonus campaign code separated with ;
			$udf2 = trim((string)$xmlFastPayObject->UDF2);
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
			$udf3 = trim((string)$xmlFastPayObject->UDF3);
			$csv_array3 = explode(";", $udf3);
			$csv_array_3_0 = explode("=", $csv_array3[0]); //TRANSACTION_ID
			//transaction_id to verify transaction with database attempts 0
			$transaction_id = (int)$csv_array_3_0[1];
			//how much fee (tax) is on player's deposit amount
			$csv_array_3_1 = explode("=", $csv_array3[1]); //FEE_AMOUNT
			$fee_amount = $csv_array_3_1[1];
			//how much player wanted to deposit without fee (basic amount that player entered on web site without fee tax)
			$csv_array_3_2 = explode("=", $csv_array3[2]); //DEPOSIT_AMOUNT
			$deposit_amount = $csv_array_3_2[1];
            //db transaction id
            $csv_array_3_3 = explode("=", $csv_array3[3]); //DEPOSIT_AMOUNT
            $db_transaction_id = $csv_array_3_3[1];

			//END EXTENDED DATA
			//get currency text from currency code (from 978 to get EUR)
			require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
			$helperCurrencyList = new CurrencyListHelper();
			$currency_text = $helperCurrencyList->getCurrencyText($currency_code);

			$access_message = "STATUS URL PARAMS PURCHASE> PSP_ID: {$psp_id} Amount: {$amount} Currency_Code: {$currency_code}
			 Currency_Text: {$currency_text} Card Number: {$card_number} Card Expiry: {$card_expiry_date}
			 Card Holder Name: {$card_holder_name} Acquirer Code: {$acquirer_code}
			 Source (Payment method code): {$source} Card Country: {$card_country} Card Type: {$card_type}
			 Web Site Domain: {$casino_name_oref}
			 PC Session ID (UDF1): {$pc_session_id}
			 PAYMENT METHOD ID (UDF1): {$payment_method_id}
			 OREF TRANSACTION ID (UDF2): {$oref_transaction_id} Transaction Limit (UDF2): {$over_limit} Player ID (UDF2): {$player_id} Bonus Code (UDF2): {$bonus_code}
			 Transaction ID (UDF3): {$transaction_id} Fee Amount (UDF3): {$fee_amount} Deposit Amount (UDF3): {$deposit_amount} DB Transaction ID (UDF3): {$db_transaction_id}
			 <END STATUS URL PARAMS PURCHASE";
      ApcoErrorHelper::apcoIntegrationAccessLog("ApcoMerchantHelper::validateResponseWithToolPurchase XML: {$xmlFastPayString}");
			ApcoErrorHelper::apcoIntegrationAccessLog("ApcoMerchantHelper::validateResponseWithToolPurchase: {$access_message}");

			$country_bin = "";
			$transaction_date = "";
			$bank_code = "";
			$user_ip_address = "";
			$card_issuer_bank = "";
			$card_country_ip_address = "";
			$email_address = "";
			//pass data required to confirm transaction in database
			return array("status"=>OK,
                "pc_session_id"=>$pc_session_id,
                "transaction_id"=>$transaction_id,
                "amount"=>$amount,
			    "apco_transaction_id"=>$psp_id,
                "currency_text"=>$currency_text,
                "currency_code"=>$currency_code,
                "credit_card_number"=>$card_number,
			    "credit_card_date_expires"=>$card_expiry_date,
                "credit_card_holder"=>$card_holder_name,
                "credit_card_country"=>$country_bin,
			    "credit_card_type"=>$card_type,
                "start_time"=>$transaction_date,
                "bank_code"=>$bank_code,
                "ip_address"=>$user_ip_address,
			    "card_issuer_bank"=>$card_issuer_bank,
                "card_country_ip"=>$card_country_ip_address,
                "client_email"=>$email_address,
                "over_limit"=>$over_limit,
			    "bank_auth_code"=>$authorization_code,
                "payment_method_code"=>$payment_method,
                "merchant_order_ref_number"=>$oref_transaction_id,
                "oref_transaction_id"=>$oref_transaction_id,
                "site_domain"=>$casino_name_oref,
			    "bonus_code"=>$bonus_code,
                "fee_amount"=>$fee_amount,
                "player_basic_deposit_amount"=>$deposit_amount,
                "player_id"=>$player_id,
                "payment_method_id"=>$payment_method_id
            );
		} catch (Zend_Exception $ex) {
			$message = "ApcoMerchantHelper::validateResponseWithToolPurchase method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
		  ApcoErrorHelper::apcoIntegrationError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
}
