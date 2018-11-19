<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'ApcoErrorHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
class MerchantController extends Zend_Controller_Action{

    private $DEBUG_PURCHASE = false;

    private $DEBUG_PAYOUT = false;

    private $DEBUG_ORIGINAL_CREDIT = false;

	public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
	}

	public function indexAction(){
		header('Location: http://www.google.com/');
	}
	//
	//  This url is targeted by apco service in status_url parameter
	//  when Apco transaction payment is done successfully
	//  It is expected to send in url address params with xml structure
	//
	public function purchaseAction(){
		//receives url encoded xml result from purchase transaction
        $xmlFastPayString = "";
		if (isset($_POST['params'])) {
			//ONLY WHEN THIS INTEGRATION PAGE IS LIVE WILL BE RETRIEVED BY POST (localhost will not get XML via post)
			$xmlFastPayString = $_POST['params'];
		} elseif (isset($_GET['params'])) {
			//WHEN WORKING ON LOCAL HOST YOU WILL NOT GET THE RESULT FROM POST THEREFORE USE THE GET (QueryString)
			$xmlFastPayString = $_GET['params'];
		}
        if(strlen($xmlFastPayString) == 0){
            $message = "MerchantController::purchaseAction(). xmlFastPayString = {$xmlFastPayString}";
            ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
            exit(NOK);
        }
        if($this->DEBUG_PURCHASE) {
            //DEBUG THIS PART OF CODE
            ApcoErrorHelper::sendMail("MerchantController::purchase <br /> {$xmlFastPayString}");
            ApcoErrorHelper::apcoIntegrationAccessLog("MerchantController::purchase {$xmlFastPayString}");
        }
		try{
			if(isset($xmlFastPayString)){
                require_once SERVICES_DIR . DS . 'apco_merchant' . DS . 'MerchantManagerPurchase.php';
                $merchantManagerPurchase = new MerchantManagerPurchase();
                $purchaseResult = $merchantManagerPurchase->purchase($xmlFastPayString);
                if($purchaseResult['status'] == OK){
                    if($this->DEBUG_PURCHASE) {
                        //DEBUG THIS CODE
                        ApcoErrorHelper::sendMail("MerchantController::purchase action success. ");
                        ApcoErrorHelper::apcoIntegrationAccessLog("MerchantController::purchase action success. ");
                    }
                    exit(OK);
                }
                if($purchaseResult['status'] != OK){
                    ///HERE IT TRIES TO RETURN MONEY TO APCO
                    //$this->voidPurchaseOperation($xmlFastPayString);
                    //there was an error while processing payment
                    //send message to player via email address
                    $xmlFastPayObject = $purchaseResult['result'];
                    $config = Zend_Registry::get('config');
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
                    //inside UDF2 tag is array of csv values transaction_limit and player_id separated with ;
                    $udf2 = $xmlFastPayObject->UDF2;
                    //EXTRACT UDF2
                    $csv_array2 = explode(";", $udf2);
                    //test of over limit status
                    $csv_array_2_0 = explode("=", $csv_array2[0]); //TRANSACTION_LIMIT
                    $over_limit = $csv_array_2_0[1];
                    //unique player id in our system
                    $csv_array_2_1 = explode("=", $csv_array2[1]); //PLAYER_ID
                    $player_id = intval($csv_array_2_1[1]);
                    //oref transaction from our database
                    $csv_array_2_2 = explode("=", $csv_array2[2]); //OREF_TRANSACTION_ID
                    $oref_transaction_id = $csv_array_2_2[1];
                    //bonus code in our database if player had entered it
                    $csv_array_2_3 = explode("=", $csv_array2[3]); //BONUS_CODE
                    $bonus_code = $csv_array_2_3[1];
                    //inside UDF3 tag is array of csv values transaction_id and fee_amount and deposit_amount separated with ;
                    $udf3 = $xmlFastPayObject->UDF3;
                    //EXTRACT UDF3
                    $csv_array3 = explode(";", $udf3);
                    //transaction id
                    $csv_array_3_0 = explode("=", $csv_array3[0]); //TRANSACTION_ID
                    $transaction_id = $csv_array_3_0[1];
                    //fee amount
                    $csv_array_3_1 = explode("=", $csv_array3[1]); //FEE_AMOUNT
                    $fee_amount = intval($csv_array_3_1[1]);
                    //deposit amount
                    $csv_array_3_2 = explode("=", $csv_array3[2]); //DEPOSIT_AMOUNT
                    $deposit_amount = $csv_array_3_2[1];
                    //db transaction id
                    $csv_array_3_3 = explode("=", $csv_array3[3]); //DEPOSIT_AMOUNT
                    $db_transaction_id = $csv_array_3_3[1];
                    $currency_code = $xmlFastPayObject->Currency;//currency code ex. 978

                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $modelMerchant->transactionApiResponse(
                        $db_transaction_id,
                        (string)$xmlFastPayObject->Result,
                        (string)$xmlFastPayObject->ExtendedErr,
                        '',
                        $player_id,
                        1,
                        $payment_method,
                        APCO_PAYMENT_PROVIDER,
                        $deposit_amount,
                        $currency
                    );

                    $bo_session_id = null;
                    require_once MODELS_DIR . DS . 'PlayerModel.php';
                    $modelPlayer = new PlayerModel();
                    //retrieve player details - required for player email addres
                    $playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
                    if($playerDetails['status'] != OK){
                        $message = "MerchantController::purchaseAction(). xmlFastPayString = {$xmlFastPayString}";
                        ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
                        exit(NOK);
                    }
                    $details = $playerDetails['details'];
                    //get site settings for player with player_id
                    $siteSettings = $modelMerchant->findSiteSettings($player_id);
                    if($siteSettings['status'] != OK){
                        if($this->DEBUG_PURCHASE) {
                            //DEBUG THIS CODE
                            ApcoErrorHelper::sendMail("MerchantController::purchase action failed, can not get web site settings.");
                            ApcoErrorHelper::apcoIntegrationErrorLog("MerchantController::purchase action failed, can not get web site settings.");
                        }
                        $message = "MerchantController::purchaseAction(). xmlFastPayString = {$xmlFastPayString}";
                        ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
                        exit(NOK);
                    }
                    $player_mail_address = $details['email'];
                    $player_username = $details['user_name'];
                    $player_mail_send_from = $siteSettings['mail_address_from'];
                    $player_smtp_server = $siteSettings['smtp_server_ip'];
                    $casino_name = $siteSettings['casino_name'];
                    $site_link = $siteSettings['site_link'];
                    $contact_link = $siteSettings['contact_url_link'];
                    $support_link = $siteSettings['support_url_link'];
                    $terms_link = $siteSettings['terms_url_link'];
                    $site_images_location = $siteSettings['site_image_location'];
                    $language_settings = $siteSettings['language_settings'];
                    //notify player of failed purchase
                    if(strlen($player_mail_address) != 0){
                        $playerMailRes = WebSiteEmailHelper::getPurchaseFailedContent($deposit_amount, $fee_amount, $currency, $db_transaction_id, $payment_method,
                        $player_username, $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings);
                        $title = $playerMailRes['mail_title'];
                        $content = $playerMailRes['mail_message'];
                        $logger_message =  "Player with mail address: {$player_mail_address} username: {$player_username}, has not been notified for failed credit transfer payment through Apco payment processor via email.";
                        WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address, $player_smtp_server, $title, $content,
                            $title, $title, $logger_message, $site_images_location);
                    }
                    if($purchaseResult['status'] != OK){
                        $message = "MerchantController::purchaseAction(). xmlFastPayString = {$xmlFastPayString}";
                        ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
                    }
                    exit($purchaseResult['status']);
                }
			}else{
                $message = "MerchantController::purchase action has not received parameters <br /> IP Address: " . IPHelper::getRealIPAddress();
                ApcoErrorHelper::apcoIntegrationError($message, $message);
                $message = "MerchantController::purchaseAction(). xmlFastPayString = {$xmlFastPayString}";
                ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
            $message = "MerchantController::purchase action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            ApcoErrorHelper::apcoIntegrationError($message, $message);
            $message = "MerchantController::purchaseAction(). xmlFastPayString = {$xmlFastPayString}";
            ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
            ///HERE IT TRIES TO RETURN MONEY TO APCO
			$this->voidPurchaseOperation($xmlFastPayString);
			exit(NOK);
		}
	}

	//call operation to void purchase
	private function voidPurchaseOperation($xmlFastPayString)
    {
        if (strlen($xmlFastPayString) != 0) {
            require_once SERVICES_DIR . DS . 'apco_merchant' . DS . 'MerchantManagerVoidOperation.php';
            $merchantManagerVoidOperation = new MerchantManagerVoidOperation();
            $status = $merchantManagerVoidOperation->sendApcoVoidPurchase($xmlFastPayString);
            return array("status"=>OK, "result"=>$status["status"]);
        }else {
            $message = "MerchantController::voidPurchaseOperation. xmlFastPayString = {$xmlFastPayString}";
            ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
            return array("status"=>NOK);
        }
	}

	//
	// This url is targeted by Apco service in status_url parameter
	// when Apco transaction payment is done successfully
	// It is expected to send in url address params with xml structure
	// returns string OK or NOK
	//
	public function payoutAction(){
		//receives url encoded xml result from payout transaction
        $xmlFastPayString = "";
		if (isset($_POST['params'])) {
			//ONLY WHEN THIS INTEGRATION PAGE IS LIVE WILL BE RETRIEVED BY POST (localhost will not get XML via post)
			$xmlFastPayString = $_POST['params'];
		} elseif (isset($_GET['params'])) {
			//WHEN WORKING ON LOCAL HOST YOU WILL NOT GET THE RESULT FROM POST THEREFORE USE THE GET (QueryString)
			$xmlFastPayString = $_GET['params'];
		}
        if(strlen($xmlFastPayString) == 0){
            $message = "MerchantController::payoutAction(). xmlFastPayString = {$xmlFastPayString}";
            ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
            exit(NOK);
        }
		try{
			if(isset($xmlFastPayString)){
                require_once SERVICES_DIR . DS . 'apco_merchant' . DS . 'MerchantManagerPayout.php';
                $merchantManagerPayout = new MerchantManagerPayout();
                $payoutResult = $merchantManagerPayout->payout($xmlFastPayString);
                if($payoutResult["status"] == OK){
                    ///SUCCESSFUL PAYOUT FROM APCO
                    if($this->DEBUG_PAYOUT) {
                        //DEBUG THIS CODE
                        ApcoErrorHelper::sendMail("MerchantController::payout action success.");
                        ApcoErrorHelper::apcoIntegrationAccessLog("MerchantController::payout action success.");
                    }
                    //there was an error while processing payment
                    //send message to player via email address
                    $xmlFastPayObject = $payoutResult['result'];
                    //get players email address and first name from player_id
                    //extract player_id as second in array of UDF2 tag string
                    $udf2 = (string)$xmlFastPayObject->UDF2;
                    // id (p_transaction_id), player_id, transaction_id_old (p_transaction_id_hang_in), payment_method, oRef, fee_amount by this order
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
                    $oref_transaction_id = $csvArray_2_4[1];
                    $csvArray_2_5 = explode("=", $csvArray2[5]); //FEE_AMOUNT=10.00
                    $fee_amount = $csvArray_2_5[1];
                    $currency_code = (string)$xmlFastPayObject->Currency; //from Apco is received in format 978 (example for EUR)
                    $amount = (string)$xmlFastPayObject->Value;
                    require_once MODELS_DIR . DS . 'PlayerModel.php';
                    $modelPlayer = new PlayerModel();
                    //retrieve player details - required for player email address
                    $bo_session_id = null;
                    $playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
                    if($playerDetails['status'] != OK){
                        $message = "MerchantController::payoutAction(). xmlFastPayString = {$xmlFastPayString}";
                        ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
                        exit(NOK);
                    }
                    $details = $playerDetails['details'];
                    //get site settings for player with player_id
                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $siteSettings = $modelMerchant->findSiteSettings($player_id);
                    if($siteSettings['status'] != OK){
                        $message = "MerchantController::payoutAction(). xmlFastPayString = {$xmlFastPayString}";
                        ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
                        exit(NOK);
                    }
                    $player_mail_address = $details['email'];
                    $player_username = $details['user_name'];	//username of player
                    $player_currency = $details['currency'];
                    $player_mail_send_from = $siteSettings['mail_address_from'];
                    $player_smtp_server = $siteSettings['smtp_server_ip'];
                    $casino_name = $siteSettings['casino_name'];
                    $site_link = $siteSettings['site_link'];
                    $contact_link = $siteSettings['contact_url_link'];
                    $support_link = $siteSettings['support_url_link'];
                    $terms_link = $siteSettings['terms_url_link'];
                    $site_images_location = $siteSettings['site_image_location'];
                    $language_settings = $siteSettings['language_settings'];
                    $playerMailRes = WebSiteEmailHelper::getPayoutConfirmationFromApcoBackofficeContent($player_username, $withdraw_request_id, $amount, $fee_amount, $player_currency,
                    $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings);
                    $title = $playerMailRes['mail_title'];
                    $content = $playerMailRes['mail_message'];
                    $logger_message =  "Player with mail address: {$player_mail_address} username: {$player_username}, has not been notified for successful credit transfer payout through Apco payment processor via email.";
                    WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address, $player_smtp_server, $title, $content, $title, $title, $logger_message, $site_images_location);
                    exit(OK);
                }
                if($this->DEBUG_PAYOUT) {
                    //DEBUG THIS CODE
                    ApcoErrorHelper::sendMail("MerchantController::payout action: " . $xmlFastPayString->asXML() . " SERVER IP: " . IPHelper::getRealIPAddress());
                    ApcoErrorHelper::apcoIntegrationAccessLog("MerchantController::payout action: " . $xmlFastPayString->asXML() . " SERVER IP: " . IPHelper::getRealIPAddress());
                }
                if($payoutResult["status"] != OK){
                    ///FAILED PAYOUT FROM APCO
                    //there was an error while processing payment
                    //send message to player via email address
                    $xmlFastPayObject = $payoutResult['result'];
                    //get players email address and first name from player_id
                    //extract player_id as second in array of UDF2 tag string
                    $udf2 = (string)$xmlFastPayObject->UDF2;
                    // id (p_transaction_id), player_id, transaction_id_old (p_transaction_id_hang_in), payment_method, oRef, fee_amount by this order
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
                    $oref_transaction_id = $csvArray_2_4[1];
                    $csvArray_2_5 = explode("=", $csvArray2[5]); //FEE_AMOUNT=10.00
                    $fee_amount = $csvArray_2_5[1];
                    $currency_code = (string)$xmlFastPayObject->Currency; //from Apco is received in format 978 (example for EUR)
                    $amount = (string)$xmlFastPayObject->Value;

                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $currencyListHelper = new CurrencyListHelper();
                    $currency_text = $currencyListHelper->getCurrencyText($currency_code);
                    $modelMerchant->transactionApiResponse(
                        $withdraw_request_id,
                        (string)$xmlFastPayObject->Result,
                        (string)$xmlFastPayObject->ExtendedErr,
                        '',
                        $player_id,
                        1,
                        $payment_method,
                        APCO_PAYMENT_PROVIDER,
                        $amount,
                        $currency_text
                    );

                    $bo_session_id = null;
                    require_once MODELS_DIR . DS . 'PlayerModel.php';
                    $modelPlayer = new PlayerModel();
                    //retrieve player details - required for player email addres
                    $playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
                    if($playerDetails['status'] != OK){
                        $message = "MerchantController::payoutAction(). xmlFastPayString = {$xmlFastPayString}";
                        ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
                        exit(NOK);
                    }
                    $details = $playerDetails['details'];
                    //get site settings for player with player_id
                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $siteSettings = $modelMerchant->findSiteSettings($player_id);
                    if($siteSettings['status'] != OK){
                        $message = "MerchantController::payoutAction(). xmlFastPayString = {$xmlFastPayString}";
                        ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
                        exit(NOK);
                    }
                    $player_mail_address = $details['email'];
                    $player_username = $details['user_name'];	//username of player
                    $player_currency = $details['currency'];
                    $player_mail_send_from = $siteSettings['mail_address_from'];
                    $player_smtp_server = $siteSettings['smtp_server_ip'];
                    $casino_name = $siteSettings['casino_name'];
                    $site_link = $siteSettings['site_link'];
                    $contact_link = $siteSettings['contact_url_link'];
                    $support_link = $siteSettings['support_url_link'];
                    $terms_link = $siteSettings['terms_url_link'];
                    $site_images_location = $siteSettings['site_image_location'];
                    $language_settings = $siteSettings['language_settings'];
                    if(strlen($player_mail_address) != 0){
                        $playerMailRes = WebSiteEmailHelper::getPayoutDeclinedFromApcoBackofficeContent($player_username, $withdraw_request_id, $amount, $fee_amount, $player_currency,
                            $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings);
                        $title = $playerMailRes['mail_title'];
                        $content = $playerMailRes['mail_message'];
                        $logger_message = "Player with mail address: {$player_mail_address} username: {$player_username}, has not been notified for failed credit transfer payout through Apco payment processor via email.";
                        WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address, $player_smtp_server, $title, $content, $title, $title, $logger_message, $site_images_location);
                    }
                    if($payoutResult['status'] != OK){
                        $message = "MerchantController::payoutAction(). xmlFastPayString = {$xmlFastPayString}";
                        ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
                    }
                    exit($payoutResult['status']);
                }
			}else{
                $message = "MerchantController::payout action has not received parameters ip address: " . IPHelper::getRealIPAddress();
                ApcoErrorHelper::apcoIntegrationError($message, $message);
                $message = "MerchantController::payoutAction(). xmlFastPayString = {$xmlFastPayString}";
                ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
            $message = "MerchantController::payout action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            ApcoErrorHelper::apcoIntegrationError($message, $message);
            $message = "MerchantController::payoutAction(). xmlFastPayString = {$xmlFastPayString}";
            ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog($message);
			exit(NOK);
		}
	}

	//
	// This is called by backoffice application to payout player
	// when his payout request is successfully verified for payout
	// returns encoded json as response
	public function originalCreditAction(){
        $loginSuccessful = false;
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
            $config = Zend_Registry::get('config');
            $apco_http_user = $config->apcoHttpUser;
            $apco_http_password = $config->apcoHttpPassword;
            if ($username == $apco_http_user && $password == $apco_http_password){
                $loginSuccessful = true;
            }
        }
        if (!$loginSuccessful){
            $ip_address = IPHelper::getRealIPAddress();
            $message = "MerchantController::originalCreditAction HTTP authentication failed! <br /> Detected IP Address={$ip_address}";
            ApcoErrorHelper::apcoIntegrationError($message, $message);

            header('WWW-Authenticate: Basic realm="Secret page"');
            header('HTTP/1.0 401 Unauthorized');
            exit("Login failed!\n");
        }

        $backoffice_session_id = trim(strip_tags(urldecode($this->_getParam('backoffice_session_id'))));
        $transaction_id_old = trim(strip_tags(urldecode($this->_getParam('transaction_id_old'))));
        $db_transaction_id = trim(strip_tags(urldecode($this->_getParam('db_transaction_id'))));
        $apco_transaction_id = trim(strip_tags(urldecode($this->_getParam('apco_transaction_id'))));
        $oref_transaction_id = trim(strip_tags(urldecode($this->_getParam('oref_transaction_id'))));
        $player_id = trim(strip_tags(urldecode($this->_getParam('player_id'))));
        $amount = doubleval(trim(strip_tags(urldecode($this->_getParam('amount')))));
        $currency_text = trim(strip_tags(urldecode($this->_getParam('currency_text'))));
        $currency_code = trim(strip_tags(urldecode($this->_getParam('currency_code'))));
        $payment_method = strip_tags(trim(urldecode($this->_getParam('payment_method'))));
        $fee_amount = doubleval(trim(strip_tags(urldecode($this->_getParam('fee_amount')))));
		try{
            if($this->DEBUG_PAYOUT) {
                //DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE FOR PAYOUT PLAYER
                require_once HELPERS_DIR . DS . 'ErrorHelper.php';
                $errorHelper = new ErrorHelper();
                $errorHelper->sendMail("MerchantController::originalCredit method: " .
                "<br /> Backoffice Session_id = {$backoffice_session_id} <br /> Apco_transaction_id = {$apco_transaction_id}" .
                "<br /> OREF_TRANSACTION_ID = {$oref_transaction_id} <br /> Player ID = {$player_id}" .
                "<br /> Amount = {$amount} <br /> Currency = {$currency_text} <br /> Currency Code = {$currency_code}");
                $errorHelper->merchantErrorLog("MerchantController::originalCredit method: " .
                " Backoffice Session_id = {$backoffice_session_id} Apco_transaction_id = {$apco_transaction_id}" .
                " OREF_TRANSACTION_ID = {$oref_transaction_id} Player ID = {$player_id}" .
                " Amount = {$amount} Currency = {$currency_text} Currency Code = {$currency_code}");
            }
			require_once SERVICES_DIR . DS . 'apco_merchant' . DS . 'MerchantManagerOriginalCredit.php';
			$merchantManagerOriginalCredit = new MerchantManagerOriginalCredit();
			$resArray = $merchantManagerOriginalCredit->sendApcoOriginalCredit($backoffice_session_id, $transaction_id_old, $db_transaction_id, $apco_transaction_id, $oref_transaction_id, $player_id,
			$amount, $currency_text, $currency_code, $payment_method, $fee_amount);
			if($resArray['status'] == OK){
				//returns true if success prints OK
				exit(Zend_Json::encode($resArray));
			}else{
				//returns false if not success prints NOK
                ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog("Error, declined transaction. MerchantController::originalCreditAction(backoffice_session_id = {$backoffice_session_id}, transaction_id_old = {$transaction_id_old},
                db_transaction_id = {$db_transaction_id}, apco_transaction_id = {$apco_transaction_id}, oref_transaction_id = {$oref_transaction_id}, player_id = {$player_id}, amount = {$amount},
                currency_text = {$currency_text}, currency_code = {$currency_code}, payment_method = {$payment_method}, fee_amount = {$fee_amount})");
				exit(Zend_Json::encode($resArray));
			}
		}catch(Zend_Exception $ex){
            $message = "MerchantController::originalCredit action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            ApcoErrorHelper::apcoIntegrationError($message, $message);
            ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog("Error, declined transaction. MerchantController::originalCreditAction(backoffice_session_id = {$backoffice_session_id}, transaction_id_old = {$transaction_id_old},
            db_transaction_id = {$db_transaction_id}, apco_transaction_id = {$apco_transaction_id}, oref_transaction_id = {$oref_transaction_id}, player_id = {$player_id}, amount = {$amount},
            currency_text = {$currency_text}, currency_code = {$currency_code}, payment_method = {$payment_method}, fee_amount = {$fee_amount})");
			$json = Zend_Json::encode(array("status"=>NOK));
			exit($json);
		}
	}

	//
	//
	// This is called from backoffice to send mail to player
	// that his payout request has been denied
	//
	public function cancelOriginalCreditAction(){
        $loginSuccessful = false;
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
            $config = Zend_Registry::get('config');
            $apco_http_user = $config->apcoHttpUser;
            $apco_http_password = $config->apcoHttpPassword;
            if ($username == $apco_http_user && $password == $apco_http_password){
                $loginSuccessful = true;
            }
        }
        if (!$loginSuccessful){
            $ip_address = IPHelper::getRealIPAddress();
            $message = "MerchantController::originalCreditAction HTTP authentication failed! <br /> Detected IP Address={$ip_address}";
            ApcoErrorHelper::apcoIntegrationError($message, $message);

            header('WWW-Authenticate: Basic realm="Secret page"');
            header('HTTP/1.0 401 Unauthorized');
            exit("Login failed!\n");
        }

        $transaction_id = trim(strip_tags(urldecode($this->_getParam('transaction_id'))));
        $player_id = trim(strip_tags(urldecode($this->_getParam('player_id'))));
        if(!isset($transaction_id) && !isset($player_id)){
            exit(NOK);
        }
        $amount = trim(strip_tags(urldecode($this->_getParam('amount'))));
        $currency = trim(strip_tags(urldecode($this->_getParam('currency'))));
		try{
            if($this->DEBUG_ORIGINAL_CREDIT) {
                //DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE FOR PLAYER PAYOUT REJECTION
                ApcoErrorHelper::sendMail("MerchantController::cancelOriginalCredit method. <br /> Transaction id = {$transaction_id} <br /> Player id = {$player_id} <br /> Currency = {$currency} <br /> Amount = {$amount}");
                ApcoErrorHelper::apcoIntegrationErrorLog("MerchantController::cancelOriginalCredit method. Transaction id = {$transaction_id} Player id = {$player_id} Currency = {$currency} Amount = {$amount}");
            }
            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			//retrieve player details - required for player email addres
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
			if($playerDetails['status'] != OK){
                ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog("Error, declined transaction. MerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
                amount = {$amount}, currency = {$currency})");
				exit(NOK);
			}
			$details = $playerDetails['details'];
			//get site settings for player with player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$siteSettings = $modelMerchant->findSiteSettings($player_id);
			if($siteSettings['status'] != OK){
                ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog("Error, declined transaction. MerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
                amount = {$amount}, currency = {$currency})");
				exit(NOK);
			}
			$player_mail_address = $details['email'];
			$player_name = $details['user_name'];
			$player_mail_send_from = $siteSettings['mail_address_from'];
			$player_smtp_server = $siteSettings['smtp_server_ip'];
			$site_image_location = $siteSettings['site_image_location'];
			$casino_name = $siteSettings['casino_name'];
			$site_link = $siteSettings['site_link'];
			$contact_link = $siteSettings['contact_url_link'];
			$support_link = $siteSettings['support_url_link'];
			$terms_link = $siteSettings['terms_url_link'];
            $language_settings = $siteSettings['language_settings'];
			if(strlen($player_mail_address) != 0){
				$playerMailRes = WebSiteEmailHelper::getPayoutRequestCanceledBySupportContent($player_name, $transaction_id, $amount, $currency,
				$site_image_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				$logger_message =  "Player with mail address: {$player_mail_address} username: {$player_name}, has not been notified for his denied payout request via email.";
				WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address, $player_smtp_server, $title, $content, $title, $title, $logger_message, "");
				exit(OK);
			}
			else{
                ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog("Error, declined transaction. MerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
                amount = {$amount}, currency = {$currency})");
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
            $message = "MerchantController::cancelOriginalCredit action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            ApcoErrorHelper::apcoIntegrationError($message, $message);
            ApcoErrorHelper::apcoIntegrationDeclinedTransactionsLog("Error, declined transaction. MerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
                amount = {$amount}, currency = {$currency})");
			exit(NOK);
		}
	}
}
