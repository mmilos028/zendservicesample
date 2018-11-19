<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'WirecardErrorHelper.php';
require_once HELPERS_DIR . DS . 'WirecardMerchantHelper.php';
class WirecardMerchantController extends Zend_Controller_Action{

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
	//  This url is targeted by wirecard service in status_url parameter
	//  when wirecard transaction payment is done successfully
	//  It is expected to send in url address params with xml structure
	//
	public function purchaseAction(){
        foreach($_POST as $key=>$value){
            $_POST[$key] = urldecode($value);
        }
        $resultsArray = $_POST;
        $resultsString = print_r($resultsArray, true);
        if(!isset($resultsArray)){
            $message = "WirecardMerchantController::purchaseAction(). No POST results to page!";
            WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
            exit(NOK);
        }
        if($this->DEBUG_PURCHASE) {
            //DEBUG THIS PART OF CODE
            WirecardErrorHelper::sendMail("WirecardMerchantController::purchase <br /> {$resultsString}");
            WirecardErrorHelper::wirecardAccessLog("WirecardMerchantController::purchase {$resultsString}");
        }
		try{
			if(isset($resultsArray)){
                if($resultsArray['transaction_type'] == "purchase") {
                    //only purchase transaction type allowed other transaction messages are only logged and sent to mail, not processed
                    require_once SERVICES_DIR . DS . 'wirecard_merchant' . DS . 'WirecardMerchantManagerPurchase.php';
                    $wirecardMerchantManagerPurchase = new WirecardMerchantManagerPurchase();
                    $purchaseResult = $wirecardMerchantManagerPurchase->purchase($resultsArray);
                    if ($purchaseResult['status'] == OK) {
                        if ($this->DEBUG_PURCHASE) {
                            //DEBUG THIS CODE
                            WirecardErrorHelper::sendMail("WirecardMerchantController::purchase action success. <br /> {$resultsString} ");
                            WirecardErrorHelper::wirecardAccessLog("WirecardMerchantController::purchase action success. <br /> {$resultsString}");
                        }
                        exit(OK);
                    }
                }else{
                    //these are not processed because of 3D / Secure credit cards, these are additional transaction messages sent by wirecard
                    //transaction types: check-payer-response, check-enrollment
                    if ($this->DEBUG_PURCHASE) {
                        //DEBUG THIS CODE
                        WirecardErrorHelper::sendMail("WirecardMerchantController::purchase action success. <br /> Additional Transaction Messages <br /> {$resultsString} ");
                        WirecardErrorHelper::wirecardAccessLog("WirecardMerchantController::purchase action success. <br/> Additional Transaction Messages <br /> {$resultsString}");
                    }
                    exit(OK);
                }
                if($purchaseResult['status'] != OK){
                    ///HERE IT TRIES TO RETURN MONEY TO WIRECARD
                    //$this->voidPurchaseOperation($resultsArray);
                    //there was an error while processing payment
                    //send message to player via email address

                    $unpackedWirecardData = WirecardMerchantHelper::validatePurchaseResponse($resultsArray, $resultsString);
                    $player_id = $unpackedWirecardData['player_id'];
                    $deposit_amount = $unpackedWirecardData['player_basic_deposit_amount'];
                    $fee_amount = $unpackedWirecardData['fee_amount'];
                    $db_transaction_id = $unpackedWirecardData['db_transaction_id'];
                    $currency_text = $unpackedWirecardData['currency_text'];
                    $payment_method = $unpackedWirecardData['payment_method_code'];
                    $wirecard_transaction_id = $unpackedWirecardData['wirecard_transaction_id'];
                    $transaction_id = $unpackedWirecardData['transaction_id'];
                    $auth_code = $unpackedWirecardData['bank_auth_code'];

                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $modelMerchant->transactionApiResponse(
                        $db_transaction_id,
                        $unpackedWirecardData['transaction_state'],
                        $unpackedWirecardData['status_description_1'] . ";" . $unpackedWirecardData['status_description_2'] . ";" . $unpackedWirecardData['status_description_3'] . ";Wirecard Transaction ID = {$wirecard_transaction_id};Transaction ID = {$transaction_id};Authorization Code = {$auth_code}",
                        substr($unpackedWirecardData['status_code_1'] . ";" . $unpackedWirecardData['status_code_2'] . ";" . $unpackedWirecardData['status_code_3'], 0, 19 ),
                        $player_id,
                        1,
                        $payment_method,
                        WIRECARD_PAYMENT_PROVIDER,
                        $deposit_amount,
                        $currency_text
                    );

                    $bo_session_id = null;
                    require_once MODELS_DIR . DS . 'PlayerModel.php';
                    $modelPlayer = new PlayerModel();
                    //retrieve player details - required for player email addres
                    $player_details = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
                    if($player_details['status'] != OK){
                        $message = "WirecardMerchantController::purchaseAction(). resultsString = {$resultsString}";
                        WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
                        exit(NOK);
                    }
                    $details = $player_details['details'];
                    //get site settings for player with player_id
                    $site_settings = $modelMerchant->findSiteSettings($player_id);
                    if($site_settings['status'] != OK){
                        if($this->DEBUG_PURCHASE) {
                            //DEBUG THIS CODE
                            require_once HELPERS_DIR . DS . 'ErrorHelper.php';
                            $errorHelper = new ErrorHelper();
                            $errorHelper->sendMail("WirecardMerchantController::purchase action failed, can not get web site settings. <br /> {$resultsString}");
                            $errorHelper->merchantErrorLog("WirecardMerchantController::purchase action failed, can not get web site settings. <br /> {$resultsString}");
                        }
                        $message = "WirecardMerchantController::purchaseAction(). resultsString = {$resultsString}";
                        WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
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
                    if(strlen($player_email_address) != 0){
                        $playerMailRes = WebSiteEmailHelper::getPurchaseFailedContent($deposit_amount, $fee_amount, $currency_text, $db_transaction_id, $payment_method,
                        $player_username, $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings);
                        $title = $playerMailRes['mail_title'];
                        $content = $playerMailRes['mail_message'];
                        $logger_message =  "Player with mail address: {$player_email_address} username: {$player_username}, has not been notified for failed credit transfer payment through Wirecard payment processor via email.";
                        WebSiteEmailHelper::sendMailToPlayer($player_email_send_from, $player_email_address, $player_smtp_server, $title, $content,
                            $title, $title, $logger_message, $site_images_location);
                    }
                    if($purchaseResult['status'] != OK){
                        $message = "MerchantController::purchaseAction(). resultsString = {$resultsString}";
                        WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
                    }
                    exit($purchaseResult['status']);
                }
			}else{
                $message = "WirecardMerchantController::purchase action has not received parameters <br /> IP Address: " . IPHelper::getRealIPAddress();
                WirecardErrorHelper::wirecardError($message, $message);
                $message = "WirecardMerchantController::purchaseAction(). resultsString = {$resultsString}";
                WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
            $message = "WirecardMerchantController::purchase action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            WirecardErrorHelper::wirecardError($message, $message);
            $message = "WirecardMerchantController::purchaseAction(). resultsString = {$resultsString}";
            WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
            ///HERE IT TRIES TO RETURN MONEY TO WIRECARD
			$this->voidPurchaseOperation($resultsArray);
			exit(NOK);
		}
	}

	//call operation to void purchase
    /**
     * @param $resultsArray
     * @return array
     */
	private function voidPurchaseOperation($resultsArray)
    {
        $resultsString = print_r($resultsArray, true);
        if (strlen($resultsString) != 0) {
            require_once SERVICES_DIR . DS . 'wirecard_merchant' . DS . 'WirecardMerchantManagerVoidOperation.php';
            $wirecardMerchantManagerVoidOperation = new WirecardMerchantManagerVoidOperation();
            $status = $wirecardMerchantManagerVoidOperation->sendWirecardVoidPurchase($resultsArray);
            return array("status"=>OK, "result"=>$status["status"]);
        }else {
            $message = "WirecardMerchantController::voidPurchaseOperation. resultsString = {$resultsString}";
            WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
            return array("status"=>NOK);
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
            $wirecard_http_user = $config->wirecardHttpUser;
            $wirecard_http_password = $config->wirecardHttpPassword;
            if ($username == $wirecard_http_user && $password == $wirecard_http_password){
                $loginSuccessful = true;
            }
        }
        if (!$loginSuccessful){
            $ip_address = IPHelper::getRealIPAddress();
            $message = "WirecardMerchantController::originalCreditAction authentication failed! <br /> Detected IP Address={$ip_address}";
            WirecardErrorHelper::wirecardError($message, $message);

            header('WWW-Authenticate: Basic realm="Secret page"');
            header('HTTP/1.0 401 Unauthorized');
            exit("Login failed!\n");
        }

        $backoffice_session_id = trim(strip_tags(urldecode($this->_getParam('backoffice_session_id'))));
        $transaction_id_old = trim(strip_tags(urldecode($this->_getParam('transaction_id_old'))));
        $db_transaction_id = trim(strip_tags(urldecode($this->_getParam('db_transaction_id'))));
        $wirecard_transaction_id = trim(strip_tags(urldecode($this->_getParam('wirecard_transaction_id'))));
        $oref_transaction_id = trim(strip_tags(urldecode($this->_getParam('oref_transaction_id'))));
        $player_id = trim(strip_tags(urldecode($this->_getParam('player_id'))));
        $amount = doubleval(trim(strip_tags(urldecode($this->_getParam('amount')))));
        $currency_text = strip_tags(trim(urldecode($this->_getParam('currency_text'))));
        $currency_code = trim(strip_tags(urldecode($this->_getParam('currency_code'))));
        $payment_method = strip_tags(trim(urldecode($this->_getParam('payment_method'))));
        $fee_amount = doubleval(trim(strip_tags(urldecode($this->_getParam('fee_amount')))));
        $token_id = trim(strip_tags(urldecode($this->_getParam('token_id'))));

		try{

            if($this->DEBUG_ORIGINAL_CREDIT) {
                //DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE FOR PAYOUT PLAYER
                WirecardErrorHelper::sendMail("WirecardMerchantController::originalCreditAction method: " .
                "<br /> Backoffice Session_id = {$backoffice_session_id} <br /> Transaction Id Old = {$transaction_id_old} <br /> DB Transaction ID = {$db_transaction_id}
                 <br /> Wirecard_transaction_id = {$wirecard_transaction_id}" . "<br /> OREF_TRANSACTION_ID = {$oref_transaction_id} <br /> Player ID = {$player_id}
                 <br /> Amount = {$amount} <br /> Currency = {$currency_text} <br /> Currency Code = {$currency_code} <br /> Payment Method = {$payment_method} <br /> Fee Amount = {$fee_amount}
                 <br /> Token ID = {$token_id}");
                WirecardErrorHelper::wirecardErrorLog("WirecardMerchantController::originalCreditAction method:
                Backoffice Session_id = {$backoffice_session_id} Transaction Id Old = {$transaction_id_old} DB Transaction ID = {$db_transaction_id}
                Wirecard_transaction_id = {$wirecard_transaction_id} OREF_TRANSACTION_ID = {$oref_transaction_id} Player ID = {$player_id}
                Amount = {$amount} Currency = {$currency_text} Currency Code = {$currency_code} Payment Method = {$payment_method} Fee Amount = {$fee_amount} Token ID = {$token_id}");
            }

			require_once SERVICES_DIR . DS . 'wirecard_merchant' . DS . 'WirecardMerchantManagerOriginalCredit.php';
			$wirecardMerchantManagerOriginalCredit = new WirecardMerchantManagerOriginalCredit();
			$resArray = $wirecardMerchantManagerOriginalCredit->sendWirecardOriginalCredit($backoffice_session_id, $transaction_id_old, $db_transaction_id, $wirecard_transaction_id, $oref_transaction_id, $player_id,
			    $amount, $currency_text, $currency_code, $payment_method, $fee_amount, $token_id
            );

			if($resArray['status'] == OK){
				//returns true if success prints OK
				exit(Zend_Json::encode($resArray));
			}else{
				//returns false if not success prints NOK
                WirecardErrorHelper::wirecardDeclinedTransactionsLog("Error, declined transaction. WirecardMerchantController::originalCreditAction(backoffice_session_id = {$backoffice_session_id}, transaction_id_old = {$transaction_id_old},
                db_transaction_id = {$db_transaction_id}, wirecard_transaction_id = {wirecard_transaction_id}, oref_transaction_id = {$oref_transaction_id}, player_id = {$player_id}, amount = {$amount},
                currency_text = {$currency_text}, currency_code = {$currency_code}, payment_method = {$payment_method}, fee_amount = {$fee_amount}, token_id = {$token_id})");
				exit(Zend_Json::encode($resArray));
			}
		}catch(Zend_Exception $ex){
            $message = "WirecardMerchantController::originalCreditAction exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            WirecardErrorHelper::wirecardError($message, $message);
            WirecardErrorHelper::wirecardDeclinedTransactionsLog("Error, declined transaction. WirecardMerchantController::originalCreditAction(backoffice_session_id = {$backoffice_session_id}, transaction_id_old = {$transaction_id_old},
            db_transaction_id = {$db_transaction_id}, wirecard_transaction_id = {$wirecard_transaction_id}, oref_transaction_id = {$oref_transaction_id}, player_id = {$player_id}, amount = {$amount},
            currency_text = {$currency_text}, currency_code = {$currency_code}, payment_method = {$payment_method}, fee_amount = {$fee_amount}, token_id = {$token_id})");
			$json = Zend_Json::encode(array("status"=>NOK));
			exit($json);
		}
	}

    //
	// This url is targeted by WIRECARD service in notification URL
	// when WIRECARD payout transaction notification arrives
	// It is expected to send in url address params with xml structure
	// returns string OK or NOK
	//
	public function payoutAction(){
        foreach($_POST as $key=>$value){
            $_POST[$key] = urldecode($value);
        }				
        $resultsArray = $_POST;
        $resultsString = print_r($resultsArray, true);
		
		if($this->DEBUG_PAYOUT){
			WirecardErrorHelper::sendMail("WirecardMerchantController::payoutAction Arrived Transaction <br /> resultsString = {$resultsString}");
            WirecardErrorHelper::wirecardAccessLog("WirecardMerchantController::payoutAction Arrived Transaction. resultsString = {$resultsString}");
		}
		
        if(!isset($resultsArray)){
            $message = "WirecardMerchantController::payoutAction(). No POST results to page!";
            WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
            exit(NOK);
        }
		try{
			if(isset($resultsArray)){
                require_once SERVICES_DIR . DS . 'wirecard_merchant' . DS . 'WirecardMerchantManagerPayout.php';
                $wirecardMerchantManagerPayout = new WirecardMerchantManagerPayout();
                $payoutResult = $wirecardMerchantManagerPayout->payout($resultsArray, $resultsString);
                if($payoutResult["status"] == OK){
                    ///SUCCESSFUL PAYOUT FROM WIRECARD
                    if($this->DEBUG_PAYOUT) {
                        //DEBUG THIS CODE
                        WirecardErrorHelper::sendMail("WirecardMerchantController::payoutAction success. <br /> resultsString = {$resultsString}");
                        WirecardErrorHelper::wirecardAccessLog("WirecardMerchantController::payoutAction success. resultsString = {$resultsString}");
                    }

                    $unpackedWirecardData = WirecardMerchantHelper::validatePayoutResponse($resultsArray, $resultsString);

                    $withdraw_request_id = $unpackedWirecardData['withdraw_request_id'];
                    $player_id = $unpackedWirecardData['player_id'];
                    $amount = $unpackedWirecardData['amount'];
                    $fee_amount = $unpackedWirecardData['fee_amount'];

                    require_once MODELS_DIR . DS . 'PlayerModel.php';
                    $modelPlayer = new PlayerModel();
                    //retrieve player details - required for player email address
                    $bo_session_id = null;
                    $playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
                    if($playerDetails['status'] != OK){
                        $message = "WirecardMerchantController::payoutAction(). resultsString = {$resultsString}";
                        WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
                        exit(NOK);
                    }
                    $details = $playerDetails['details'];
                    //get site settings for player with player_id
                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $site_settings = $modelMerchant->findSiteSettings($player_id);
                    if($site_settings['status'] != OK){
                        $message = "WirecardMerchantController::payoutAction(). resultsString = {$resultsString}";
                        WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
                        exit(NOK);
                    }
                    $player_mail_address = $details['email'];
                    $player_username = $details['user_name'];
                    $player_currency = $details['currency'];
                    $player_mail_send_from = $site_settings['mail_address_from'];
                    $player_smtp_server = $site_settings['smtp_server_ip'];
                    $casino_name = $site_settings['casino_name'];
                    $site_url_link = $site_settings['site_link'];
                    $contact_url_link = $site_settings['contact_url_link'];
                    $support_url_link = $site_settings['support_url_link'];
                    $terms_url_link = $site_settings['terms_url_link'];
                    $site_images_location = $site_settings['site_image_location'];
                    $language_settings = $site_settings['language_settings'];
                    $playerMailRes = WebSiteEmailHelper::getPayoutConfirmationFromApcoBackofficeContent($player_username, $withdraw_request_id, $amount, $fee_amount, $player_currency,
                    $site_images_location, $casino_name, $site_url_link, $contact_url_link, $support_url_link, $terms_url_link, $language_settings);
                    $title = $playerMailRes['mail_title'];
                    $content = $playerMailRes['mail_message'];
                    $logger_message =  "Player with mail address: {$player_mail_address} username: {$player_username}, has not been notified for successful credit transfer payout through Wirecard payment provider via email.";
                    WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address, $player_smtp_server, $title, $content, $title, $title, $logger_message, $site_images_location);
                    exit(OK);
                }

                if($this->DEBUG_PAYOUT) {
                    //DEBUG THIS CODE
                    WirecardErrorHelper::sendMail("WirecardMerchantController::payoutAction: " . $resultsString . " SERVER IP: " . IPHelper::getRealIPAddress());
                    WirecardErrorHelper::wirecardAccessLog("WirecardMerchantController::payoutAction: " . $resultsString . " SERVER IP: " . IPHelper::getRealIPAddress());
                }

                if($payoutResult["status"] != OK){
                    ///FAILED PAYOUT FROM WIRECARD
                    //there was an error while processing payment
                    //send message to player via email address
					
					if($this->DEBUG_PAYOUT){
						WirecardErrorHelper::sendMail("WirecardMerchantController::payoutAction Declined Transaction <br /> resultsString = {$resultsString}");
						WirecardErrorHelper::wirecardAccessLog("WirecardMerchantController::payoutAction Declined Transaction. resultsString = {$resultsString}");
					}

                    $unpackedWirecardData = WirecardMerchantHelper::validatePayoutResponse($resultsArray, $resultsString);

                    $player_id = $unpackedWirecardData['player_id'];
                    $amount = $unpackedWirecardData['amount'];
                    $fee_amount = $unpackedWirecardData['fee_amount'];
                    $withdraw_request_id = $unpackedWirecardData['withdraw_request_id'];

                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $modelMerchant->transactionApiResponse(
                        $withdraw_request_id,
                        NOK,
                        print_r($unpackedWirecardData, true),
                        "",
                        $player_id,
                        -1,
                        $unpackedWirecardData['payment_method'],
                        WIRECARD_PAYMENT_PROVIDER,
                        $amount,
                        $unpackedWirecardData['currency_text']
                    );

                    $bo_session_id = null;
                    require_once MODELS_DIR . DS . 'PlayerModel.php';
                    $modelPlayer = new PlayerModel();
                    $playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
                    if($playerDetails['status'] != OK){
                        $message = "WirecardMerchantController::payoutAction(). resultsString = {$resultsString}";
                        WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
                        exit(NOK);
                    }
                    $details = $playerDetails['details'];
                    //get site settings for player with player_id
                    $site_settings = $modelMerchant->findSiteSettings($player_id);
                    if($site_settings['status'] != OK){
                        $message = "WirecardMerchantController::payoutAction(). resultsString = {$resultsString}";
                        WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
                        exit(NOK);
                    }
                    $player_mail_address = $details['email'];
                    $player_username = $details['user_name'];	//username of player
                    $player_currency = $details['currency'];
                    $player_mail_send_from = $site_settings['mail_address_from'];
                    $player_smtp_server = $site_settings['smtp_server_ip'];
                    $casino_name = $site_settings['casino_name'];
                    $site_url_link = $site_settings['site_link'];
                    $contact_url_link = $site_settings['contact_url_link'];
                    $support_url_link = $site_settings['support_url_link'];
                    $terms_url_link = $site_settings['terms_url_link'];
                    $site_images_location = $site_settings['site_image_location'];
                    $language_settings = $site_settings['language_settings'];
                    if(strlen($player_mail_address) != 0){
                        $playerMailRes = WebSiteEmailHelper::getPayoutDeclinedFromApcoBackofficeContent($player_username, $withdraw_request_id, $amount, $fee_amount, $player_currency,
                            $site_images_location, $casino_name, $site_url_link, $contact_url_link, $support_url_link, $terms_url_link, $language_settings);
                        $title = $playerMailRes['mail_title'];
                        $content = $playerMailRes['mail_message'];
                        $logger_message =  "Player with mail address: {$player_mail_address} username: {$player_username}, has not been notified for failed credit transfer payout through Wirecard payment provider via email.";
                        WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address, $player_smtp_server, $title, $content, $title, $title, $logger_message, $site_images_location);
                    }
                    if($payoutResult['status'] != OK){
                        $message = "WirecardMerchantController::payoutAction(). resultsString = {$resultsString}";
                        MerchantErrorHelper::merchantDeclinedTransactionsLog($message);
                    }
                    exit($payoutResult['status']);
                }
			}else{
                $message = "WirecardMerchantController::payoutAction() has not received parameters ip address: " . IPHelper::getRealIPAddress();
                WirecardErrorHelper::wirecardError($message, $message);
                $message = "WirecardMerchantController::payoutAction(). resultsString = {$resultsString}";
                WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
            $message = "WirecardMerchantController::payout action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            WirecardErrorHelper::wirecardError($message, $message);
            $message = "WirecardMerchantController::payoutAction(). resultsString = {$resultsString}";
            WirecardErrorHelper::wirecardDeclinedTransactionsLog($message);
			exit(NOK);
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
            $wirecard_http_user = $config->wirecardHttpUser;
            $wirecard_http_password = $config->wirecardHttpPassword;
            if ($username == $wirecard_http_user && $password == $wirecard_http_password){
                $loginSuccessful = true;
            }
        }
        if (!$loginSuccessful){
            $ip_address = IPHelper::getRealIPAddress();
            $message = "WirecardMerchantController::cancelOriginalCreditAction HTTP authentication failed! <br /> Detected IP Address={$ip_address}";
            WirecardErrorHelper::wirecardError($message, $message);

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
                WirecardErrorHelper::sendMail("WirecardMerchantController::cancelOriginalCredit method. <br /> Transaction id = {$transaction_id} <br /> Player id = {$player_id} <br /> Currency = {$currency} <br /> Amount = {$amount}");
                WirecardErrorHelper::wirecardErrorLog("WirecardMerchantController::cancelOriginalCredit method. Transaction id = {$transaction_id} Player id = {$player_id} <br /> Currency = {$currency} <br /> Amount = {$amount}");
            }
            $bo_session_id = null;
      			require_once MODELS_DIR . DS . 'PlayerModel.php';
      			$modelPlayer = new PlayerModel();
      			//retrieve player details - required for player email addres
      			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
      			if($playerDetails['status'] != OK){
                WirecardErrorHelper::wirecardDeclinedTransactionsLog("Error, declined transaction. WirecardMerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
                amount = {$amount}, currency = {$currency})");
				        exit(NOK);
			}
			$details = $playerDetails['details'];
			//get site settings for player with player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$siteSettings = $modelMerchant->findSiteSettings($player_id);
			if($siteSettings['status'] != OK){
                WirecardErrorHelper::wirecardDeclinedTransactionsLog("Error, declined transaction. WirecardMerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
                amount = {$amount}, currency = {$currency})");
				exit(NOK);
			}
			$player_mail_address = $details['email'];
			$player_username = $details['user_name'];
			$player_mail_send_from = $siteSettings['mail_address_from'];
			$player_smtp_server = $siteSettings['smtp_server_ip'];
			$site_images_location = $siteSettings['site_image_location'];
			$casino_name = $siteSettings['casino_name'];
			$site_link = $siteSettings['site_link'];
			$contact_link = $siteSettings['contact_url_link'];
			$support_link = $siteSettings['support_url_link'];
			$terms_link = $siteSettings['terms_url_link'];
            $language_settings = $siteSettings['language_settings'];
			if(strlen($player_mail_address) != 0){
				$playerMailRes = WebSiteEmailHelper::getPayoutRequestCanceledBySupportContent($player_username, $transaction_id, $amount, $currency,
				$site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings);
                $title = $playerMailRes['mail_title'];
                $content = $playerMailRes['mail_message'];
				$logger_message =  "Player with mail address: {$player_mail_address} username: {$player_username}, has not been notified for his denied payout request via email.";
				WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address, $player_smtp_server, $title, $content, $title, $title, $logger_message, "");
				exit(OK);
			}
			else{
                WirecardErrorHelper::wirecardDeclinedTransactionsLog("Error, declined transaction. WirecardMerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
                amount = {$amount}, currency = {$currency})");
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
            $message = "WirecardMerchantController::cancelOriginalCredit action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            WirecardErrorHelper::wirecardError($message, $message);
            WirecardErrorHelper::wirecardDeclinedTransactionsLog("Error, declined transaction. WirecardMerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
                amount = {$amount}, currency = {$currency})");
			exit(NOK);
		}
	}
}
