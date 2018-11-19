<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'ApcoErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';

/**
 * THIS IS FOR MERCHANT PAYOUT OF PLAYERS (PAYOUT PLAYER TO HIS ACCOUNT)
 * Merchant manager to perform transaction processing from Apco Limited payment processor ...
 *
 */
class MerchantManagerOriginalCredit {

    private $DEBUG = false;

	/**
	 *
	 * PAYOUT PLAYER FROM BACKOFFICE - makes message to payout credits to player - ORIGINAL CREDIT FROM BACKOFFICE
	 * session_id sent from backoffice
	 * @param string $backoffice_session_id
	 * @param string $transaction_id_old
	 * @param string $db_transaction_id
	 * @param string $apco_transaction_id
	 * @param string $oref_transaction_id
	 * @param string $player_id
	 * @param float $amount
	 * @param string $currency_text
	 * @param string $currency_code
	 * @param string $payment_method
	 * @param float $fee_amount
	 * @return mixed
	 */
	private function getApcoPayoutCustomPaymentMethodXml($backoffice_session_id, $transaction_id_old, $db_transaction_id, $apco_transaction_id,
	$oref_transaction_id, $player_id, $amount, $currency_text, $currency_code, $payment_method, $fee_amount){
		//check if some of received parameters are invalid
		if(!isset($backoffice_session_id) || !isset($transaction_id_old) || !isset($db_transaction_id) || !isset($oref_transaction_id) ||
            !isset($player_id) || !isset($amount) || $amount<0 || !isset($currency_text) || !isset($currency_code) || !isset($payment_method) || !isset($fee_amount)){
            if($this->DEBUG) {
                //DEBUG THIS CODE
                $message = "MerchantManagerOriginalCredit::getApcoPayoutCustomPaymentMethodXml method:
                            <br /> Backoffice Session_id = {$backoffice_session_id} <br /> Apco_transaction_id = {$apco_transaction_id}
                            <br /> OREF TRANSACTION = {$oref_transaction_id} <br /> Player ID = {$player_id}
                            <br /> Amount = {$amount} <br /> Currency Text = {$currency_text} <br /> Currency Code = {$currency_code} Fee Amount = {$fee_amount}";
                ApcoErrorHelper::apcoIntegrationError($message, $message);
            }
			return array("status"=>NOK, "message"=>urlencode(NOK_INVALID_DATA));
		}
		$backoffice_session_id = trim(strip_tags($backoffice_session_id)); //backoffice session id from active operator that verified this payout
		$transaction_id_old = trim(strip_tags($transaction_id_old));
		$db_transaction_id = trim(strip_tags($db_transaction_id));
		$apco_transaction_id = trim(strip_tags($apco_transaction_id));
		$oref_transaction_id = trim(strip_tags($oref_transaction_id));
		$player_id = trim(strip_tags($player_id));
		$amount = doubleval(trim(strip_tags($amount)));
		$currency_text = trim(strip_tags($currency_text));
		$currency_code = trim(strip_tags($currency_code));
		$payment_method = trim(strip_tags($payment_method));
		$fee_amount = doubleval(trim(strip_tags($fee_amount)));
		try{
		    //load application configuration parametars
            $config = Zend_Registry::get('config');
            require_once MODELS_DIR . DS . 'MerchantModel.php';
		    $modelMerchant = new MerchantModel();
		    //get secret words given by apco payment service
			$secretWords = $modelMerchant->getSecretWords($backoffice_session_id);
			if($secretWords["status"] == NOK){
				//there was an exception in database
				return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
			}
            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			//retrieve player details - required for player email address
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
			if($playerDetails['status'] != OK){
				return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
			}
			$details = $playerDetails['details'];
			$player_email = $details['email'];
            //get site settings for player with player_id
            $siteSettings = $modelMerchant->findSiteSettings($player_id);
            if($siteSettings['status'] != OK){
                return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
            }
            $casino_name = $siteSettings['casino_name'];

            $entercash_beneficiary_account_number = "";
            $entercash_beneficiary_bank_id = "";
            $entercash_beneficiary_id = "";
            $entercash_beneficiary_name = "";
            $clearing_house_chars = "DE";
            if($payment_method == ENTERCASH) {
                //entercash extra details
                if (strlen($details['iban']) == 0 || strlen($details['swift']) == 0 || strlen($details['first_name']) == 0 || strlen($details['last_name']) == 0) {
                    return array("status" => NOK, "message" => urlencode(NOK_INVALID_DATA));
                }
                $entercash_beneficiary_account_number = trim(strip_tags($details['iban']));
                $entercash_beneficiary_bank_id = trim(strip_tags($details['swift']));
                $entercash_beneficiary_id = trim(strip_tags($player_id));
                $entercash_beneficiary_name = ucwords(StringHelper::replaceSpecialLetters(trim(strip_tags($details['first_name'] . " " . $details['last_name']))));

                if ($details['currency'] == "SEK") {
                    $clearing_house_chars = "SE";
                } else if ($details['currency'] == "EUR") {
                    $clearing_house_chars = "DE";
                } else {
                    $clearing_house_chars = "DE";
                }
            }

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
            //get original credit command code from application configuration
            $action_type = $config->apcoCreditCardOriginalCreditCommand;
            //get credit card payout redirection url for successfull transaction
            $redirection_url = $siteSettings['apco_redirection_site_success_link'];
            //get credit card payout failed redirection url for unsuccessfull transaction
            $failedRedirection_url = $siteSettings['apco_redirection_site_failed_link'];
            //get credit card payout status url listener for transaction
            $status_url = $siteSettings['apco_payout_link'];
            //create original message xml object that will be sent to apco
            $originalMessageXML = new SimpleXMLElement('<Transaction></Transaction>');
            $originalMessageXML->addAttribute('hash', $secretWords['secret_word']);
            $originalMessageXML->addChild('ProfileID', $secretWords['profile_id']);
            $originalMessageXML->addChild('Value', $amount);
            $originalMessageXML->addChild('Curr', $currency_code);
            $originalMessageXML->addChild('Lang', $language);
            if(isset($apco_transaction_id) || strlen($apco_transaction_id) > 0 || $apco_transaction_id != "0" || $apco_transaction_id != 0) {
                $originalMessageXML->addChild('PspID', $apco_transaction_id);
            }
            $originalMessageXML->addChild('ORef', $casino_name);
            //send pc session through UDF1 field
            $csv_val1 = implode(";", array("BACKOFFICE_SESSION_ID={$backoffice_session_id}", "CURRENCY={$currency_text}"));
            $originalMessageXML->addChild('UDF1', $csv_val1);
            //saljem transaction id sa withdraw request (polje id)
            //player_id sa withdraw request kao polje player_id
            //transaction_id_old sa withdraw request kao polje transaction_id_old
            //payment_method je sifra metode placanja NT, VISA, PSC...
            //csv vrednost je: $id ; $player_id, $transaction_id_old ; $payment_method
            $csv_val2 = implode(";", array("WITHDRAW_REQUEST_ID={$db_transaction_id}", "PLAYER_ID={$player_id}", "TRANSACTION_ID_OLD={$transaction_id_old}", "PAYMENT_METHOD={$payment_method}", "OREF_TRANSACTION_ID={$oref_transaction_id}", "FEE_AMOUNT={$fee_amount}"));
            //saljem transaction id sa withdraw request (polje id) i player_id sa withdraw request, transaction_id_old i payment_method i oref transaction id i iznos takse placanja fee_amount sa withdraw request izvestaja kao csv vrednost
            //$id ; $player_id ; $transaction_id_old ; $payment_method (source) ; oRef transaction id ; $fee_amount
            $originalMessageXML->addChild('UDF2', $csv_val2);
            //send empty through UDF3 field
            $originalMessageXML->addChild('UDF3', '');
            //player email address is sent here
            $originalMessageXML->addChild('Email', $player_email);
            $originalMessageXML->addChild('RedirectionURL', $redirection_url);
            $originalMessageXML->addChild('ActionType', $action_type);
            if($payment_method == ENTERCASH) {
                //extra entercash tags for payout
                $originalMessageXML->addChild('BankAccount', $entercash_beneficiary_account_number);
                $originalMessageXML->addChild('BBankID', $entercash_beneficiary_bank_id);
                $originalMessageXML->addChild('BeneficiaryID', $entercash_beneficiary_id);
                $originalMessageXML->addChild('BeneficiaryName', $entercash_beneficiary_name);
                $originalMessageXML->addChild('ClearingHouse', $clearing_house_chars);
            }
            $originalMessageXML->addChild('Enc', 'UTF-8');
            //will send player id as client account reference
            $originalMessageXML->addChild('ClientAcc', $player_id);
            $originalMessageXML->addChild('status_url', $status_url);
            //will return apco transaction id
            $originalMessageXML->addChild('return_pspid', '');
            //will return amount and currency from payout on status url
            $originalMessageXML->addChild('CA', '');
            //will return extended data
            $originalMessageXML->addChild('ExtendedData', '');
            //will return extended data part 2
            $originalMessageXML->addChild('ExtendedData2', '');
            //$failedTransXmlElement = $originalMessageXML->addChild('FailedTrans', '');
            //$failedTransXmlElement->addChild('FailedRedirectionURL', $failedRedirectionURL);
            //if in test then add TEST payment method
            if($config->apcoTestCard == "true"){
                $originalMessageXML->addChild('TEST', ''); //TO BE COMMENTED ON LIVE VERSION
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
            return array("status"=>OK, "message"=>urlencode($hashedMessage));
        }catch(Zend_Exception $ex){
			//returns exception to web site
			$message = "MerchantManagerOriginalCredit::getApcoPayoutCustomPaymentMethodXml method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			ApcoErrorHelper::apcoIntegrationError($message, $message);
			return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
		}
	}

    /**
	 *
	 * PAYOUT PLAYER FROM BACKOFFICE - makes message to payout credits to player - ORIGINAL CREDIT FROM BACKOFFICE - ENTERCASH without psp_id (apco_transaction_id)
	 * session_id sent from backoffice
	 * @param string $backoffice_session_id
	 * @param string $transaction_id_old
	 * @param string $db_transaction_id
	 * @param string $apco_transaction_id
	 * @param string $oref_transaction_id
	 * @param string $player_id
	 * @param float $amount
	 * @param string $currency_text
	 * @param string $currency_code
	 * @param string $payment_method
	 * @param float $fee_amount
	 * @return mixed
	 */
	private function getApcoPayoutEntercashPaymentMethodXml($backoffice_session_id, $transaction_id_old, $db_transaction_id, $apco_transaction_id,
	$oref_transaction_id, $player_id, $amount, $currency_text, $currency_code, $payment_method, $fee_amount){
		//check if some of received parameters are invalid
		if(!isset($backoffice_session_id) || !isset($transaction_id_old) || !isset($db_transaction_id) || !isset($oref_transaction_id) ||
            !isset($player_id) || !isset($amount) || $amount<0 || !isset($currency_text) || !isset($currency_code) || !isset($payment_method) || !isset($fee_amount)){
            if($this->DEBUG) {
                //DEBUG THIS CODE
                $message = "MerchantManagerOriginalCredit::getApcoPayoutCustomPaymentMethodXml method:
                            <br /> Backoffice Session_id = {$backoffice_session_id} <br /> Apco_transaction_id = {$apco_transaction_id}
                            <br /> OREF TRANSACTION ID = {$oref_transaction_id} <br /> Player ID = {$player_id}
                            <br /> Amount = {$amount} <br /> Currency = {$currency_text} <br /> Currency Code = {$currency_code} Fee Amount = {$fee_amount}";
                ApcoErrorHelper::apcoIntegrationError($message, $message);
            }
			return array("status"=>NOK, "message"=>urlencode(NOK_INVALID_DATA));
		}
		$backoffice_session_id = trim(strip_tags($backoffice_session_id)); //backoffice session id from active operator that verified this payout
		$transaction_id_old = trim(strip_tags($transaction_id_old));
		$db_transaction_id = trim(strip_tags($db_transaction_id));
		$apco_transaction_id = trim(strip_tags($apco_transaction_id));
		$oref_transaction_id = trim(strip_tags($oref_transaction_id));
		$player_id = trim(strip_tags($player_id));
		$amount = doubleval(trim(strip_tags($amount)));
		$currency_text = trim(strip_tags($currency_text));
		$currency_code = trim(strip_tags($currency_code));
		$payment_method = trim(strip_tags($payment_method));
		$fee_amount = doubleval(trim(strip_tags($fee_amount)));
		try{
		    //load application configuration parametars
            $config = Zend_Registry::get('config');
            require_once MODELS_DIR . DS . 'MerchantModel.php';
		    $modelMerchant = new MerchantModel();
		    //get secret words given by apco payment service
			$secretWords = $modelMerchant->getSecretWords($backoffice_session_id);
			if($secretWords["status"] == NOK){
				//there was an exception in database
				return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
			}
            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			//retrieve player details - required for player email address
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
			if($playerDetails['status'] != OK){
				return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
			}
			$details = $playerDetails['details'];
			$player_email = $details['email'];
            //get site settings for player with player_id
            $siteSettings = $modelMerchant->findSiteSettings($player_id);
            if($siteSettings['status'] != OK){
                return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
            }
            $casino_name = $siteSettings['casino_name'];
            //entercash extra details
            if(strlen($details['iban']) == 0 || strlen($details['swift']) == 0 || strlen($details['first_name']) == 0 || strlen($details['last_name']) == 0){
                return array("status"=>NOK, "message"=>urlencode(NOK_INVALID_DATA));
            }
            $entercash_beneficiary_account_number = trim(strip_tags($details['iban']));
            $entercash_beneficiary_bank_id = trim(strip_tags($details['swift']));
            $entercash_beneficiary_id = trim(strip_tags($player_id));
            $entercash_beneficiary_name = ucwords(StringHelper::replaceSpecialLetters(trim(strip_tags($details['first_name'] . " " . $details['last_name']))));
            $clearing_house_chars = "DE";

            if ($details['currency'] == "SEK") {
                $clearing_house_chars = "SE";
            } else if ($details['currency'] == "EUR") {
                $clearing_house_chars = "DE";
            } else {
                $clearing_house_chars = "DE";
            }

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
            //get original credit command code from application configuration
            $action_type = $config->apcoCreditCardOriginalCreditCommand;
            //get credit card payout redirection url for successfull transaction
            $redirection_url = $siteSettings['apco_redirection_site_success_link'];
            //get credit card payout failed redirection url for unsuccessfull transaction
            $failedRedirection_url = $siteSettings['apco_redirection_site_failed_link'];
            //get credit card payout status url listener for transaction
            $status_url = $siteSettings['apco_payout_link'];
            //create original message xml object that will be sent to apco
            $originalMessageXML = new SimpleXMLElement('<Transaction></Transaction>');
            $originalMessageXML->addAttribute('hash', $secretWords['secret_word']);
            $originalMessageXML->addChild('ProfileID', $secretWords['profile_id']);
            $originalMessageXML->addChild('Value', $amount);
            $originalMessageXML->addChild('Curr', $currency_code);
            $originalMessageXML->addChild('Lang', $language);
            /*if(isset($apco_transaction_id) || strlen($apco_transaction_id) > 0 || $apco_transaction_id != "0" || $apco_transaction_id != 0) {
                $originalMessageXML->addChild('PspID', $apco_transaction_id);
            }*/
            $originalMessageXML->addChild('ORef', $casino_name);
            //send pc session through UDF1 field
            $csv_val1 = implode(";", array("BACKOFFICE_SESSION_ID={$backoffice_session_id}", "CURRENCY={$currency_text}"));
            $originalMessageXML->addChild('UDF1', $csv_val1);
            //saljem transaction id sa withdraw request (polje id)
            //player_id sa withdraw request kao polje player_id
            //transaction_id_old sa withdraw request kao polje transaction_id_old
            //payment_method je sifra metode placanja NT, VISA, PSC...
            //csv vrednost je: $id ; $player_id, $transaction_id_old ; $payment_method
            $csv_val2 = implode(";", array("WITHDRAW_REQUEST_ID={$db_transaction_id}", "PLAYER_ID={$player_id}", "TRANSACTION_ID_OLD={$transaction_id_old}", "PAYMENT_METHOD={$payment_method}", "OREF_TRANSACTION_ID={$oref_transaction_id}", "FEE_AMOUNT={$fee_amount}"));
            //saljem transaction id sa withdraw request (polje id) i player_id sa withdraw request, transaction_id_old i payment_method i oref transaction id i iznos takse placanja fee_amount sa withdraw request izvestaja kao csv vrednost
            //$id ; $player_id ; $transaction_id_old ; $payment_method (source) ; oRef transaction id ; $fee_amount
            $originalMessageXML->addChild('UDF2', $csv_val2);
            //send empty through UDF3 field
            $originalMessageXML->addChild('UDF3', '');
            //player email address is sent here
            $originalMessageXML->addChild('Email', $player_email);
            $originalMessageXML->addChild('RedirectionURL', $redirection_url);
            $originalMessageXML->addChild('ActionType', $action_type);
            $originalMessageXML->addChild('ForcePayment', $payment_method);
            //will send player id as client account reference
            $originalMessageXML->addChild('ClientAcc', $player_id);
            //extra entercash tags for payout
            $originalMessageXML->addChild('BankAccount', $entercash_beneficiary_account_number);
            $originalMessageXML->addChild('BBankID', $entercash_beneficiary_bank_id);
            $originalMessageXML->addChild('BeneficiaryID', $entercash_beneficiary_id);
            $originalMessageXML->addChild('BeneficiaryName', $entercash_beneficiary_name);
            $originalMessageXML->addChild('ClearingHouse', $clearing_house_chars);
            $originalMessageXML->addChild('Enc', 'UTF-8');
            //////
            $originalMessageXML->addChild('status_url', $status_url);
            //will return apco transaction id
            $originalMessageXML->addChild('return_pspid', '');
            //will return amount and currency from payout on status url
            $originalMessageXML->addChild('CA', '');
            //will return extended data
            $originalMessageXML->addChild('ExtendedData', '');
            //will return extended data part 2
            $originalMessageXML->addChild('ExtendedData2', '');
            //$failedTransXmlElement = $originalMessageXML->addChild('FailedTrans', '');
            //$failedTransXmlElement->addChild('FailedRedirectionURL', $failedRedirectionURL);
            //if in test then add TEST payment method
            if($config->apcoTestCard == "true"){
                $originalMessageXML->addChild('TEST', ''); //TO BE COMMENTED ON LIVE VERSION
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
            return array("status"=>OK, "message"=>urlencode($hashedMessage));
        }catch(Zend_Exception $ex){
			//returns exception to web site
			$message = "MerchantManagerOriginalCredit::getApcoPayoutCustomPaymentMethodXml method: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			ApcoErrorHelper::apcoIntegrationError($message, $message);
			return array("status"=>NOK, "message"=>urlencode(NOK_EXCEPTION));
		}
	}

	/**
	* Sends apco original credit command
	* This will make player payout from Apco
	* it was not confirmed in database
	* return true if successfull or false if there was an error
	* currency code = ex. 978
	* currency text = ex. EUR
	* returns true if command was send to apco to void credit
	* returns false if there was an error
	 * @param string $backoffice_session_id
	 * @param string $transaction_id_old
	 * @param string $db_transaction_id
	 * @param string $apco_transaction_id
	 * @param string $oref_transaction_id
	 * @param string $player_id
	 * @param float $amount
	 * @param string $currency_text
	 * @param string $currency_code
	 * @param string $payment_method
	 * @param float $fee_amount
	 * @return mixed
	 */
	public function sendApcoOriginalCredit($backoffice_session_id, $transaction_id_old, $db_transaction_id, $apco_transaction_id, $oref_transaction_id,
	$player_id, $amount, $currency_text, $currency_code, $payment_method, $fee_amount){
		$backoffice_session_id = trim(strip_tags($backoffice_session_id));
		$transaction_id_old = trim(strip_tags($transaction_id_old));
		$db_transaction_id = trim(strip_tags($db_transaction_id));
		$apco_transaction_id = trim(strip_tags($apco_transaction_id));
		$oref_transaction_id = trim(strip_tags($oref_transaction_id));
		$player_id = trim(strip_tags($player_id));
		$amount = doubleval(trim(strip_tags($amount)));
		$currency_text = trim(strip_tags($currency_text));
		$currency_code = trim(strip_tags($currency_code));
		$payment_method = trim(strip_tags($payment_method));
		$fee_amount = doubleval(trim(strip_tags($fee_amount)));
		try{
            if($this->DEBUG) {
                //DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE FOR PAYOUT PLAYER
                $message = "MerchantManagerOriginalCredit::sendApcoOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id}, apco_transaction_id={$apco_transaction_id}, oref_transaction_id={$oref_transaction_id},
                    player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount})";
                ApcoErrorHelper::apcoIntegrationAccess($message, $message);
            }
			//get xml structure for original credit command
			//get apco original credit command to payout player verified from backoffice
            if((string)$apco_transaction_id != "0") {
                $result = $this->getApcoPayoutCustomPaymentMethodXml($backoffice_session_id, $transaction_id_old, $db_transaction_id, $apco_transaction_id, $oref_transaction_id, $player_id, $amount, $currency_text, $currency_code, $payment_method, $fee_amount);
            }else{
                if($payment_method == ENTERCASH) {
                    $result = $this->getApcoPayoutEntercashPaymentMethodXml($backoffice_session_id, $transaction_id_old, $db_transaction_id, $apco_transaction_id, $oref_transaction_id, $player_id, $amount, $currency_text, $currency_code, $payment_method, $fee_amount);
                }else{
                    return array("status"=>NOK, "error_message"=>"Cannot payout player, not ENTERCASH payment or no deposits on payment method from this player !");
                }
            }
			$decodedTransactionXmlString = urldecode($result["message"]);
			if($result["status"] != OK){
				$message = "MerchantManagerOriginalCredit::sendApcoOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id}, apco_transaction_id={$apco_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}) <br /> Error in XML transaction string. <br /> Returns status {$decodedTransactionXmlString}";
				ApcoErrorHelper::apcoIntegrationError($message, $message);
				return array("status"=>NOK, "error_message"=>"Error in XML string");
			}
            $encodedTransactionXmlString = $result["message"];
            $config = Zend_Registry::get("config");
            //obtain fastpay token !!!!!!!!!!!!!!!
            $apcoMerchantToolsService = $config->apcoCreditCardSoapService;
            require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
            $merchantCodes = $modelMerchant->getMerchantCodes(null);
            if($merchantCodes["status"] == NOK){
                return array("status"=>NOK, "error_message"=>"Cannot obtain merchant codes for merchant tools web service !");
            }
            $merchant_code = $merchantCodes['merchant_code'];
            $merchant_password = $merchantCodes['merchant_password'];

            if($config->apcoIntegrationTestSimulationMode != "true") {
                $client = new SoapClient($apcoMerchantToolsService, array("trace" => 0, "exception" => 0));
                $soapResult = $client->BuildXMLToken(array("MerchID" => $merchant_code, "MerchPass" => $merchant_password, "XMLParam" => $encodedTransactionXmlString, "errorMsg" => "OK"));
                //receive response from apco web service as xml object
                $token_result = $soapResult->BuildXMLTokenResult;
                //end obtaining fastpay token
            }else{
                $token_result = "abc123456789";
            }

            //get apco checkout page to send original credit command
            $apcoCheckoutURL = $config->apcoCheckoutPage . "?FPToken=" . $token_result;
            if($this->DEBUG) {
                //DEBUG THIS CODE
                $message = "MerchantManagerOriginalCredit::sendApcoOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id}, apco_transaction_id={$apco_transaction_id}, oref_transaction_id={$oref_transaction_id},
                    player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}) <br /> XML message: <br /> {$decodedTransactionXmlString} <br />
                    Checkout URL: {$apcoCheckoutURL}";
                ApcoErrorHelper::apcoIntegrationAccess($message, $message);
            }
			$fields = array(
				'params' => $encodedTransactionXmlString
			);
            $fields_string = "";
			foreach($fields as $key=>$value) {
				$fields_string .= $key.'='.$value.'&';
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
			if(curl_errno($ch)){
				//there was an error sending post to original credit player's transaction (error in player withdraw with APCO)
				$error_message = curl_error($ch);
				$message = "MerchantManagerOriginalCredit::sendApcoOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id}, apco_transaction_id={$apco_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}) <br /> Error in verifying player payout command for Apco transaction. <br />
				Must manually payout player through Apco payment portal on https://www.apsp.biz/Manage. <br /> Transaction was confirmed in database. <br />Exception message: <br /> {$error_message}";
				ApcoErrorHelper::apcoIntegrationError($message, $message);
				return array("status"=>NOK, "error_message"=>"Error while sending player payout to APCO");
			}else{
				//player payout was success
				curl_close($ch);
				$message = "MerchantManagerOriginalCredit::sendApcoOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id}, apco_transaction_id={$apco_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}) <br />
				Payout Apco transaction from player was successfully sent. <br />
				Player's  payout request was confirmed in database and his Apco payout is to be confirmed. <br />
				This payout transaction must be verfied on Apco payment portal on https://www.apsp.biz/Manage to have been payout.";
				ApcoErrorHelper::apcoIntegrationAccessLog($message);
				return array("status"=>OK, "message"=>$data);
			}
		}catch(Zend_Exception $ex){
			//there was an error in player payout transaction
			$message = "MerchantManagerOriginalCredit::sendApcoOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id}, apco_transaction_id={$apco_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}) <br /> Exception occured in making payout command for Apco transaction. <br />Must be verified manually through Apco payment portal https://www.apsp.biz/Manage. <br />Transaction was confirmed in database, but there was no payout with Apco. <br />Exception message: <br /> {$ex->getMessage()}";
			ApcoErrorHelper::apcoIntegrationError($message, $message);
			return array("status"=>NOK, "error_message"=>"There was an error while making payout for APCO transaction. <br />Must be verified manually through Apco payment portal https://www.apsp.biz/Manage. <br />Transaction was confirmed in database, but there was no payout with Apco. <br />Amount: {$amount} <br />Currency text: {$currency_text} <br />Currency code: {$currency_code} <br />OREF TRANSACTION ID: {$oref_transaction_id} <br />PSP ID: {$apco_transaction_id}");
		}
	}
}
