<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'ApcoErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Merchant manager to perform transaction processing from Apco Limited payment processor ...
 * Cancels purchase and original credit (payout) of player transactions
 */
class MerchantManagerVoidOperation {

    private $DEBUG_VOID_PURCHASE = false;
    private $DEBUG_VOID_CREDIT = false;

    /**
     * @param $xmlFastPayString
     * @return array
     * @throws Zend_Exception
     */
	private function getApcoPaymentVoidPurchaseXml($xmlFastPayString){
        $config = Zend_Registry::get('config');
        if($config->apcoTestCard == "true"){
            return array("status"=>NOK, "result"=>NOK_EXCEPTION);
        }
		$site_session_id = null;
		$resultXmlObject = ApcoMerchantHelper::getParamsAndConvertToXmlObject($xmlFastPayString);
        if($resultXmlObject["status"] != OK){
            return array("status"=>NOK, "result"=>NOK_EXCEPTION);
        }
        $xmlFastPayObject = $resultXmlObject["result"];
		$amount = doubleval(trim(number_format((string)$xmlFastPayObject->Value, 2, '.', '')));
		$currency_code = trim((string)$xmlFastPayObject->Currency);
		$oRef = trim((string)$xmlFastPayObject->ORef);
		$apco_transaction_id = trim((string)$xmlFastPayObject->pspid);
		try{
            //get secret words given by apco payment service
            require_once MODELS_DIR . DS . 'MerchantModel.php';
		    $modelMerchant = new MerchantModel();
			$secretWords = $modelMerchant->getSecretWords($site_session_id);
			if($secretWords["status"] == NOK){
				return array("status"=>NOK, "result"=>NOK_EXCEPTION);
			}
            //load application configuration parametars
            $config = Zend_Registry::get('config');
            //get default language for apco payment form
            $language = $config->apcoCreditCardLanguage;
            //get purchase command code from application configuration
            $actionType = $config->apcoCreditCardVoidPurchaseCommand;
            //create original message xml object that will be sent to apco
            $originalMessageXML = new SimpleXMLElement('<Transaction></Transaction>');
            $originalMessageXML->addAttribute('hash', $secretWords['secret_word']);
            $originalMessageXML->addChild('ProfileID', $secretWords['profile_id']);
            $originalMessageXML->addChild('Lang', $language);
            $originalMessageXML->addChild('Value', $amount);
            $originalMessageXML->addChild('Curr', $currency_code);
            $originalMessageXML->addChild('ORef', $oRef);
            $originalMessageXML->addChild('UDF1', '');
            $originalMessageXML->addChild('UDF2', '');
            $originalMessageXML->addChild('UDF3', '');
            $originalMessageXML->addChild('ActionType', $actionType);
            $originalMessageXML->addChild('PspID', $apco_transaction_id);
            //if in test then add TEST PAYMENT METHOD
            if($config->apcoTestCard == "true"){
                $originalMessageXML->addChild('TESTCARD', '');
            }
            //save original message as text
            $originalMessage = $originalMessageXML->saveXML();
            //remove blank spaces from original message text
            $originalMessage = trim(substr($originalMessage, strpos($originalMessage, "<Transaction")));
            //copy original message xml object into hashed message xml object
            $hashedMessageXML = $originalMessageXML;
            //replace hash attribute with md5 hash value of entire original message
            $hashedMessageXML->attributes()->hash = md5($originalMessage);
            //return hashed message to web site
            $hashedMessage = $hashedMessageXML->saveXML();
            //trim hashed message from xml tag in beggining and empty spaces
            $hashedMessage = trim(substr($hashedMessage, strpos($hashedMessage, "<Transaction")));
            //return urlencode of hashed message
            return array("status"=>OK, "amount"=>$amount, "currency_code"=>$currency_code, "oref"=>$oRef, "apco_transaction_id"=>$apco_transaction_id, "transaction_message"=>urlencode($hashedMessage));
        }catch(Zend_Exception $ex){
			//returns exception to web site
			$message = "MerchantManagerVoidOperation::getApcoPaymentVoidPurchaseXml <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			ApcoErrorHelper::apcoIntegrationError($message, $message);
			return array("status"=>NOK, "result"=>NOK_EXCEPTION);
		}
	}

    /**
     * @param $backoffice_session_id
     * @param $amount
     * @param $currency_code
     * @param $oRef
     * @param $apco_transaction_id
     * @return array
     * @throws Zend_Exception
     */
	private function getApcoPaymentVoidCreditXml($backoffice_session_id, $amount, $currency_code, $oRef, $apco_transaction_id){
        $config = Zend_Registry::get('config');
        if($config->apcoTestCard == "true"){
            return array("status"=>NOK, "result"=>NOK_EXCEPTION);
        }
		$backoffice_session_id = intval(trim(strip_tags($backoffice_session_id)));
		$amount = doubleval(trim(strip_tags($amount)));
		$currency_code = trim(strip_tags($currency_code));
		$oRef = trim(strip_tags($oRef));
		$apco_transaction_id = intval(trim(strip_tags($apco_transaction_id)));
		try{
            require_once MODELS_DIR . DS . 'MerchantModel.php';
		    $modelMerchant = new MerchantModel();
		    //get secret words given by apco payment service
			$secretWords = $modelMerchant->getSecretWords($backoffice_session_id);
			if($secretWords["status"] == NOK){
				return array("status"=>NOK, "result"=>NOK_EXCEPTION);
			}
            //load application configuration parametars
            $config = Zend_Registry::get('config');
            //get default language for apco payment form
            $language = $config->apcoCreditCardLanguage;
            //get purchase command code from application configuration
            $actionType = $config->apcoCreditCardVoidCreditCommand;
            //create original message xml object that will be sent to apco
            $originalMessageXML = new SimpleXMLElement('<Transaction></Transaction>');
            $originalMessageXML->addAttribute('hash', $secretWords['secret_word']);
            $originalMessageXML->addChild('ProfileID', $secretWords['profile_id']);
            $originalMessageXML->addChild('Lang', $language);
            $originalMessageXML->addChild('Value', $amount);
            $originalMessageXML->addChild('Curr', $currency_code);
            $originalMessageXML->addChild('ORef', $oRef);
            $originalMessageXML->addChild('UDF1', '');
            $originalMessageXML->addChild('UDF2', '');
            $originalMessageXML->addChild('UDF3', '');
            $originalMessageXML->addChild('ActionType', $actionType);
            $originalMessageXML->addChild('PspID', $apco_transaction_id);
            //IF IN TEST ADD TEST PAYMENT METHOD
            /*if($config->apcoTestCard == "true"){
                $originalMessageXML->addChild('TEST', '');
            }*/
            //save original message as text
            $originalMessage = $originalMessageXML->saveXML();
            //remove blank spaces from original message text
            $originalMessage = trim(substr($originalMessage, strpos($originalMessage, "<Transaction")));
            //copy original message xml object into hashed message xml object
            $hashedMessageXML = $originalMessageXML;
            //replace hash attribute with md5 hash value of entire original message
            $hashedMessageXML->attributes()->hash = md5($originalMessage);
            //return hashed message to web site
            $hashedMessage = $hashedMessageXML->saveXML();
            //trim hashed message from xml tag in beggining and empty spaces
            $hashedMessage = trim(substr($hashedMessage, strpos($hashedMessage, "<Transaction")));
            //return urlencode of hashed message
            return array("status"=>OK, "result"=>urlencode($hashedMessage));
        }catch(Zend_Exception $ex){
			//returns exception to web site
			$message = "MerchantManagerVoidOperation::getApcoPaymentVoidCreditXml: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			ApcoErrorHelper::apcoIntegrationError($message, $message);
			return array("status"=>NOK, "result"=>NOK_EXCEPTION);
		}
	}

    /**
     * @param $xmlFastPayString
     * @return array
     * @throws Zend_Exception
     */
	public function sendApcoVoidPurchase($xmlFastPayString){
        $config = Zend_Registry::get("config");
        if($config->apcoDoVoidPurchaseOperation == "true") {
            try {
                //get xml structure for void purchase command
                $res = $this->getApcoPaymentVoidPurchaseXml($xmlFastPayString);
                if ($res["status"] != OK) {
                    return array("status" => NOK, "error_message" => "Cannot obtain void purchase XML message !");
                }
                $amount = $res['amount'];
                $currency_code = $res['currency_code'];
                $oref = $res['oref'];
                $psp_id = $res['apco_transaction_id'];
                $encodedTransactionXmlString = $res['transaction_message'];
                if($this->DEBUG_VOID_PURCHASE){
                    //DEBUG HERE
                    $message = urldecode($encodedTransactionXmlString);
                    ApcoErrorHelper::apcoIntegrationAccess($message, $message);
                }
                //obtain fastpay token !!!!!!!!!!!!!!!
                require_once MODELS_DIR . DS . 'MerchantModel.php';
                $modelMerchant = new MerchantModel();
                $apcoMerchantToolsService = $config->apcoCreditCardSoapService;
                $merchantCodes = $modelMerchant->getMerchantCodes(null);
                if ($merchantCodes["status"] == NOK) {
                    return array("status" => NOK, "error_message" => "Cannot obtain merchant codes for merchant tools web service !");
                }
                $merchantCode = $merchantCodes['merchant_code'];
                $merchantPassword = $merchantCodes['merchant_password'];

                if($config->apcoIntegrationTestSimulationMode != "true") {
                    $client = new SoapClient($apcoMerchantToolsService, array("trace" => 0, "exception" => 0));
                    $soapResult = $client->BuildXMLToken(array("MerchID" => $merchantCode, "MerchPass" => $merchantPassword, "XMLParam" => $encodedTransactionXmlString, "errorMsg" => "OK"));
                    //receive response from apco web service as xml object
                    $tokenResult = $soapResult->BuildXMLTokenResult;
                    //end obtaining fastpay token
                }else{
                    $tokenResult = "abc123456789";
                }

                //transform currency code to currency text (ex. 978 to EUR)
                require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
                $helperCurrencyList = new CurrencyListHelper();
                $currency_text = $helperCurrencyList->getCurrencyText($currency_code);
                //get apco checkout page to send void purchase command
                $apcoCheckoutURL = $config->apcoCheckoutPage . "?FPToken=" . $tokenResult;
                $fields = array(
                    'params' => $encodedTransactionXmlString
                );
                $fields_string = "";
                foreach ($fields as $key => $value) {
                    $fields_string .= $key . '=' . $value . '&';
                }
                rtrim($fields_string, '&');
                //start post init to apco payment page
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apcoCheckoutURL);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                curl_setopt($ch, CURLOPT_POST, count($fields));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                //disable ssl verification
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Connection: keep-alive'
                ));
                $data = curl_exec($ch);
                if (curl_errno($ch)) {
                    //there was an error sending post to void purchase player's transaction
                    $error_message = curl_error($ch);
                    $message = "MerchantManagerVoidOperation::sendApcoVoidPurchase - send Apco void purchase method. Error in canceling purchase command for Apco transaction. <br />Must be canceled manually through Apco payment portal on https://www.apsp.biz/Manage. <br />Transaction was not confirmed in database. <br />Amount: {$amount} <br />Currency text: {$currency_text} <br />Currency code: {$currency_code} <br />OREF: {$oref} <br />PSP ID: {$psp_id} <br />Exception message: <br /> {$error_message}";
                    ApcoErrorHelper::apcoIntegrationError($message, $message);
                    return array("status" => NOK);
                } else {
                    //players void purchase was success
                    curl_close($ch);
                    $message = "MerchantManagerVoidOperation::sendApcoVoidPurchase - send Apco void purchase method. Purchase Apco transaction from player was successfully aborted. <br />Player's transaction was not confirmed in database and his Apco purchase is revoked. <br /> This aborted transaction must be verfied on Apco payment portal on https://www.apsp.biz/Manage to have been revoked. <br />Amount: {$amount} <br />Currency text: {$currency_text} <br />Currency code: {$currency_code} <br />OREF: {$oref} <br />PSP ID: {$psp_id}";
                    ApcoErrorHelper::apcoIntegrationAccess($message, $message);
                    return array("status" => OK);
                }
            } catch (Zend_Exception $ex) {
                //there was an error in players void purchase transaction
                $message = "MerchantManagerVoidOperation::sendApcoVoidPurchase - send Apco void purchase method. Exception occured in canceling purchase command for Apco transaction. <br />Must be canceled manually through Apco payment portal https://www.apsp.biz/Manage. <br />Transaction was not confirmed in database. XmlFastPayString = {$xmlFastPayString} <br />Exception message: <br /> {$ex->getMessage()}";
                ApcoErrorHelper::apcoIntegrationError($message, $message);
                return array("status" => NOK);
            }
        }else{
            if($this->DEBUG_VOID_PURCHASE){
                //DEBUG HERE
                $message = $xmlFastPayString;
                ApcoErrorHelper::apcoIntegrationAccess($message, $message);
            }
            return array("status" => OK);
        }
	}

    /**
     * @param $validResponseResult
     * @return array
     * @throws Zend_Exception
     */
	public function sendApcoVoidCredit($validResponseResult){
		$backoffice_session_id = trim(strip_tags($validResponseResult['backoffice_session_id']));
		$amount = trim(strip_tags($validResponseResult['amount']));
		$currency_text = trim(strip_tags($validResponseResult['currency_text']));
		$currency_code = trim(strip_tags($validResponseResult['currency_code']));
		$oref = trim(strip_tags($validResponseResult['merchant_order_ref_number']));
		$psp_id = trim(strip_tags($validResponseResult['apco_transaction_id']));
        $config = Zend_Registry::get("config");
        if($config->apcoDoVoidCreditOperation == "true") {
            try {
                //get xml structure for void purchase command
                $res = $this->getApcoPaymentVoidCreditXml($backoffice_session_id, $amount, $currency_code, $oref, $psp_id);
                if ($res["status"] != OK) {
                    return array("status" => NOK, "error_message" => "Cannot obtain void credit XML message !");
                }
                $encodedTransactionXmlString = $res["result"];
                //obtain fastpay token !!!!!!!!!!!!!!!
                require_once MODELS_DIR . DS . 'MerchantModel.php';
                $modelMerchant = new MerchantModel();
                $apcoMerchantToolsService = $config->apcoCreditCardSoapService;
                $merchantCodes = $modelMerchant->getMerchantCodes(null);
                if ($merchantCodes["status"] == NOK) {
                    return array("status" => NOK, "error_message" => "Cannot obtain merchant codes for merchant tools web service !");
                }
                $merchantCode = $merchantCodes['merchant_code'];
                $merchantPassword = $merchantCodes['merchant_password'];

                if($config->apcoIntegrationTestSimulationMode != "true") {
                    $client = new SoapClient($apcoMerchantToolsService, array("trace" => 0, "exception" => 0));
                    $soapResult = $client->BuildXMLToken(array("MerchID" => $merchantCode, "MerchPass" => $merchantPassword, "XMLParam" => $encodedTransactionXmlString, "errorMsg" => "OK"));
                    //receive response from apco web service as xml object
                    $tokenResult = $soapResult->BuildXMLTokenResult;
                }else{
                    $tokenResult = "abc123456789";
                }

                //end obtaining fastpay token
                if($this->DEBUG_VOID_CREDIT){
                    //DEBUG HERE
                    $message = urldecode($encodedTransactionXmlString);
                    ApcoErrorHelper::apcoIntegrationAccess($message, $message);
                }
                //get apco checkout page to send void purchase command
                $apcoCheckoutURL = $config->apcoCheckoutPage . "?FPToken=" . $tokenResult;
                $fields = array(
                    'params' => $encodedTransactionXmlString
                );
                $fields_string = "";
                foreach ($fields as $key => $value) {
                    $fields_string .= $key . '=' . $value . '&';
                }
                rtrim($fields_string, '&');
                //start post init to apco payment page
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apcoCheckoutURL);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                curl_setopt($ch, CURLOPT_POST, count($fields));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                //disable ssl verification
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Connection: keep-alive'
                ));
                $data = curl_exec($ch);
                if (curl_errno($ch)) {
                    //there was an error sending post to void credit player's transaction
                    $error_message = curl_error($ch);
                    $message = "MerchantManagerVoidOperation::sendApcoVoidCredit - send Apco void credit method. Error in canceling payout command for Apco transaction. <br />Must be canceled manually through Apco payment portal on https://www.apsp.biz/Manage. <br />Transaction was not confirmed in database. <br />Amount: {$amount} <br />Currency text: {$currency_text} <br />Currency code: {$currency_code} <br />OREF: {$oref} <br />PSP ID: {$psp_id} <br />Exception message: <br /> {$error_message}";
                    ApcoErrorHelper::apcoIntegrationError($message, $message);
                    return array("status" => NOK);
                } else {
                    //players void credit was success
                    curl_close($ch);
                    $message = "MerchantManagerVoidOperation::sendApcoVoidCredit - send Apco void credit method. Payout Apco transaction from player was successfully aborted. <br />Player's transaction was not confirmed in database and his Apco payout is revoked. <br /> This aborted transaction must be verfied on Apco payment portal on https://www.apsp.biz/Manage to have been revoked. <br />Amount: {$amount} <br />Currency text: {$currency_text} <br />Currency code: {$currency_code} <br />OREF: {$oref} <br />PSP ID: {$psp_id}";
                    ApcoErrorHelper::apcoIntegrationAccessLog($message);
                    return array("status" => OK);
                }
            } catch (Zend_Exception $ex) {
                //there was an error in players void credit transaction
                $message = "MerchantManagerVoidOperation::sendApcoVoidCredit - send apco void credit method. Exception occured in canceling payout command for Apco transaction. <br />Must be canceled manually through Apco payment portal https://www.apsp.biz/Manage. <br />Transaction was not confirmed in database. <br />Amount: {$amount} <br />Currency text: {$currency_text} <br />Currency code: {$currency_code} <br />OREF: {$oref} <br />PSP ID: {$psp_id} <br />Exception message: <br /> {$ex->getMessage()}";
                ApcoErrorHelper::apcoIntegrationError($message, $message);
                return array("status" => NOK);
            }
        }else{
            if($this->DEBUG_VOID_CREDIT){
                //DEBUG HERE
                $message = "MerchantManagerVoidOperation::sendApcoVoidCredit(backoffice_session_id = {$backoffice_session_id}, amount = {$amount}, currency_text={$currency_text},
                currency_code = {$currency_code}, oref = {$oref}, psp_id = {$psp_id})";
                ApcoErrorHelper::apcoIntegrationAccess($message, $message);
            }
            return array("status" => OK);
        }
	}
}
