<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'PaysafecardDirectErrorHelper.php';
require_once HELPERS_DIR . DS . 'PaysafecardDirectMerchantHelper.php';
require_once SERVICES_DIR . DS . 'paysafecard_direct' . DS . 'PaysafecardDirectMerchantManagerPurchase.php';

class PaysafecardDirectMerchantController extends Zend_Controller_Action{

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

  public function testAction(){
		die("test");
	}
	//
	//  This url is targeted by paysafecard direct service
	//  when paysafecard direct transaction payment is done successfully
	//  It is expected to send in url address params with xml structure
    //
    //       RETRIEVE PAYMENT DETAILS
    //       RESPONSE CAPTURE PAYMENT 1 SUCCESS
    //       REPSONSE CAPTURE PAYMENT 1
	//
	public function purchaseAction(){

        $udf1 = urldecode($_GET['udf1']);
        $udf2 = urldecode($_GET['udf2']);
        $udf3 = urldecode($_GET['udf3']);

        $request_string = file_get_contents('php://input');

        //urlencoded values
        //mtid=pay_1090004809_KQVQg7vTypLxhnYhlcdM0C20ZEYRICu4_EUR&
        //eventType=ASSIGN_CARDS&
        //serialNumbers=8531639374503967%3BEUR%3B0.02%3BXX00044%3B


        $mtid = urldecode($_POST['mtid']); // pay_1090004809_KQVQg7vTypLxhnYhlcdM0C20ZEYRICu4_EUR     ---PAYMENT_ID value
        $event_type = urldecode($_POST['eventType']); // ASSIGN_CARDS
        $serial_numbers = urldecode($_POST['serialNumbers']);  //8531639374503967;EUR;0.02;XX00044;    --- SERIAL NUMBER | CURRENCY | AMOUNT | COUNTRYCODE (XX) TYPE (00044)

        $serial_numbers_array = explode(';', $serial_numbers);

        if($this->DEBUG_PURCHASE) {
            //DEBUG THIS PART OF CODE
            //$errorHelper->sendMail("PaysafecardDirectMerchantController::purchase(udf1 = {$udf1}, udf2 = {$udf2}, udf3 = {$udf3}) <br /> {$request_string}");
            PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantController::purchase(udf1 = {$udf1}, udf2 = {$udf2}, udf3 = {$udf3}) {$request_string}");
        }

        //RETRIEVE PAYMENT DETAILS FROM PAYSAFECARD

        $config = Zend_Registry::get('config');

        if($config->paysafecardDirectTestMode == "true") {
            $get_payment_details_url = $config->paysafecardDirectUrl . "paysafecard_payment_details.php?mtid=" . $mtid;
        }else{
            $get_payment_details_url = $config->paysafecardDirectUrl . "payments/" . $mtid;
        }

        $http_user = base64_encode($config->paysafecardDirectHttpUser);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $get_payment_details_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        if ($config->paysafecardVerifyCertificate == "true") {
            curl_setopt($ch, CURLOPT_CAINFO, APP_DIR . "/configs/paysafecard_certificates/" . $config->paysafecardCertificateFileName);
        }else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "Authorization: Basic {$http_user}"
        ));
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            //there was an error sending post to paysafecard to check payment
            $error_message = curl_error($ch);
            curl_close($ch);
            $message = "PaysafecardDirectMerchantController::purchase(mtid={$mtid}, eventType={$event_type}, serialNumbers={$serial_numbers}) ERROR MESSAGE = " . $error_message;
            PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);

            $paysafecard_direct_transaction_id = $mtid;

            $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactBeginPurchaseResponse($serial_numbers_array[2], $paysafecard_direct_transaction_id, $udf1, $udf2, $udf3);
            $this->notifyFailedTransaction($unpackedPaysafecardDirectData);
            exit(NOK);
        }
        curl_close($ch);

        if($this->DEBUG_PURCHASE) {
            //DEBUG THIS PART OF CODE
            $message = $response;
            //$errorHelper->sendMail("PaysafecardDirectMerchantController::purchase RETRIEVE PAYMENT DETAILS <br /> {$message}");
            PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantController::purchase RETRIEVE PAYMENT DETAILS {$message}");
        }

        $responsePaysafecardDirectRetrievePaymentDetailsJsonObject = json_decode($response, true);

        $object = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['object'];
        $id = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['id'];
        $created = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['created'];
        $updated = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['updated'];
        $amount = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['amount'];
        $currency = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['currency'];
        $status =   $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['status'];
        $type = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['type'];
        $redirect_success_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'];
        $redirect_failure_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['failure_url'];
        $redirect_auth_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['auth_url'];
        $customer_id = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['customer']['id'];
        $customer_ip = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['customer']['ip'];
        $notification_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'];
        $card_details_serial = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['serial'];
        $card_details_type = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['type'];
        $card_details_country = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['country'];
        $card_details_currency = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['currency'];
        $card_details_amount = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['amount'];

        switch ($status){
            case "AUTHORIZED":
                if($config->paysafecardDirectTestMode == "true") {
                    $capture_url = $config->paysafecardDirectUrl . "capture_purchase.php?mtid=" . $id;
                }else{
                    $capture_url = $config->paysafecardDirectUrl . "payments/" . $id . "/capture";
                }

                if($this->DEBUG_PURCHASE) {
                    //$errorHelper->sendMail("PaysafecardDirectMerchantController::purchase Authorized CAPTURE PAYMENT ON URL <br /> {$capture_url}");
                    PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantController::purchase Authorized CAPTURE PAYMENT ON URL {$capture_url}");
                }
                //send CAPTURE PAYMENT
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $capture_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                if ($config->paysafecardVerifyCertificate == "true") {
                    curl_setopt($ch, CURLOPT_CAINFO, APP_DIR . "/configs/paysafecard_certificates/" . $config->paysafecardCertificateFileName);
                }else {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                  "Content-Type: application/json",
                  "Authorization: Basic {$http_user}"
                ));
                $response = curl_exec($ch);
                if(curl_errno($ch)){
                    //there was an error sending post to PAYSAFECARD to capture payment
                    $error_message = curl_error($ch);
                    curl_close($ch);
                    $message = "PaysafecardDirectMerchantController::purchase(mtid={$mtid}, eventType={$event_type}, serialNumbers={$serial_numbers}) CAPTURE PAYMENT ERROR MESSAGE = " . $error_message;
                    PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);

                    $paysafecard_direct_transaction_id = $id;
                    $player_id = $customer_id;
                    $ip_address = $customer_ip;
                    $card_number = $card_details_serial;
                    $card_country = $card_details_country;
                    $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                    $this->notifyFailedTransaction($unpackedPaysafecardDirectData);
                    exit(NOK);
                }else{
                    //returns SUCCESS or AUTHORIZE
                    curl_close($ch);
                    $responsePaysafecardDirectCapturePaymentDetailsJsonObject = json_decode($response, true);
                    $object = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['object'];
                    $id = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['id'];
                    $created = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['created'];
                    $updated = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['updated'];
                    $amount = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['amount'];
                    $currency = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['currency'];
                    $status = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['status'];
                    $type = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['type'];
                    $redirect_success_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['redirect']['success_url'];
                    $redirect_failure_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['redirect']['failure_url'];
                    $redirect_auth_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['redirect']['auth_url'];
                    $customer_id = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['customer']['id'];
                    $customer_ip = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['customer']['ip'];
                    $notification_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['notification_url'];
                    $card_details_serial = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['serial'];
                    $card_details_type = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['type'];
                    $card_details_country = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['country'];
                    $card_details_currency = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['currency'];
                    $card_details_amount = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['amount'];

                    if($this->DEBUG_PURCHASE) {
                        //DEBUG THIS PART OF CODE
                        $message = $response;
                        //$errorHelper->sendMail("PaysafecardDirectMerchantController::purchase REPSONSE CAPTURE PAYMENT 1 <br /> {$message}");
                        PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantController::purchase RESPONSE CAPTURE PAYMENT 1 {$message}");
                    }

                    switch($status){
                        case "AUTHORIZED":
                            //send CAPTURE PAYMENT
                            if($config->paysafecardDirectTestMode == "true") {
                                $capture_url = $config->paysafecardDirectUrl . "capture_purchase.php?mtid=" . $id;
                            }else{
                                $capture_url = $config->paysafecardDirectUrl . "payments/" . $id . "/capture";
                            }

                            if($this->DEBUG_PURCHASE) {
                                //$errorHelper->sendMail("PaysafecardDirectMerchantController::purchase CAPTURE PAYMENT ON URL <br /> {$capture_url}");
                                PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantController::purchase CAPTURE PAYMENT ON URL {$capture_url}");
                            }
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $capture_url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HEADER, FALSE);
                            curl_setopt($ch, CURLOPT_POST, TRUE);
                            if ($config->paysafecardVerifyCertificate == "true") {
                                curl_setopt($ch, CURLOPT_CAINFO, APP_DIR . "/configs/paysafecard_certificates/" . $config->paysafecardCertificateFileName);
                            }else {
                                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            }
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                              "Content-Type: application/json",
                              "Authorization: Basic {$http_user}"
                            ));
                            $response = curl_exec($ch);
                            if(curl_errno($ch)){
                                //there was an error sending post to PAYSAFECARD to capture payment
                                $error_message = curl_error($ch);
                                curl_close($ch);
                                $message = "PaysafecardDirectMerchantController::purchase(mtid={$mtid}, eventType={$event_type}, serialNumbers={$serial_numbers}) CAPTURE PAYMENT 2 ERROR MESSAGE = " . $error_message;
                                PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);

                                $paysafecard_direct_transaction_id = $id;
                                $player_id = $customer_id;
                                $ip_address = $customer_ip;
                                $card_number = $card_details_serial;
                                $card_country = $card_details_country;
                                $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                                $this->notifyFailedTransaction($unpackedPaysafecardDirectData);
                                exit(NOK);
                            }else{
                                //returns SUCCESS or AUTHORIZE
                                curl_close($ch);
                                $responsePaysafecardDirectCapturePaymentDetailsJsonObject = json_decode($response, true);
                                $object = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['object'];
                                $id = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['id'];
                                $created = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['created'];
                                $updated = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['updated'];
                                $amount = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['amount'];
                                $currency = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['currency'];
                                $status = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['status'];
                                $type = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['type'];
                                $redirect_success_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['redirect']['success_url'];
                                $redirect_failure_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['redirect']['failure_url'];
                                $redirect_auth_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['redirect']['auth_url'];
                                $customer_id = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['customer']['id'];
                                $customer_ip = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['customer']['ip'];
                                $notification_url = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['notification_url'];
                                $card_details_serial = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['serial'];
                                $card_details_type = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['type'];
                                $card_details_country = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['country'];
                                $card_details_currency = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['currency'];
                                $card_details_amount = $responsePaysafecardDirectCapturePaymentDetailsJsonObject['card_details']['amount'];

                                if($this->DEBUG_PURCHASE) {
                                    //DEBUG THIS PART OF CODE
                                    $message = $response;
                                    // $errorHelper->sendMail("PaysafecardDirectMerchantController::purchase RESPONSE CAPTURE PAYMENT 2 <br /> {$message}");
                                    PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantController::purchase RESPONSE CAPTURE PAYMENT 2 {$message}");
                                }

                                switch ($status){
                                    case "SUCCESS":
                                        //do deposit action
                                        if($this->DEBUG_PURCHASE) {
                                            //DEBUG THIS PART OF CODE
                                            $message = $response;
                                            //$errorHelper->sendMail("PaysafecardDirectMerchantController::purchase RESPONSE CAPTURE PAYMENT 2 SUCCESS <br /> {$message}");
                                            PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantController::purchase RESPONSE CAPTURE PAYMENT 2 SUCCESS {$message}");
                                        }
                                        $paysafecard_direct_transaction_id = $id;
                                        $player_id = $customer_id;
                                        $ip_address = $customer_ip;
                                        $card_number = $card_details_serial;
                                        $card_country = $card_details_country;
                                        $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                                        $paysafecardDirectMerchantManagerPurchase = new PaysafecardDirectMerchantManagerPurchase();
                                        $transactionResult = $paysafecardDirectMerchantManagerPurchase->processOkOrPendingTransaction($unpackedPaysafecardDirectData);
                                        if($transactionResult['status'] == OK){
                                            exit(OK);
                                        }else{
                                            $paysafecard_direct_transaction_id = $id;
                                            $player_id = $customer_id;
                                            $ip_address = $customer_ip;
                                            $card_number = $card_details_serial;
                                            $card_country = $card_details_country;
                                            $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                                            $this->notifyFailedTransaction($unpackedPaysafecardDirectData);
                                            exit(NOK);
                                        }
                                        break;
                                    default:
                                        $message = "PaysafecardDirectMerchantController::purchase(mtid={$mtid}, eventType={$event_type}, serialNumbers={$serial_numbers}) CAPTURE PAYMENT 2 ERROR {$status} PAYSAFECARD RECEIVED MESSAGE {$response} ";
                                        PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);

                                        $paysafecard_direct_transaction_id = $id;
                                        $player_id = $customer_id;
                                        $ip_address = $customer_ip;
                                        $card_number = $card_details_serial;
                                        $card_country = $card_details_country;
                                        $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                                        $this->notifyFailedTransaction($unpackedPaysafecardDirectData);
                                        exit(NOK);
                                }
                            }
                            break;
                        case "SUCCESS":
                            //do deposit credits action
                            if($this->DEBUG_PURCHASE) {
                                //DEBUG THIS PART OF CODE
                                $message = $response;
                                PaysafecardDirectErrorHelper::sendMail("PaysafecardDirectMerchantController::purchase RESPONSE CAPTURE PAYMENT 1 SUCCESS <br /> {$message}");
                                PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantController::purchase RESPONSE CAPTURE PAYMENT 1 SUCCESS {$message}");
                            }
                            $paysafecard_direct_transaction_id = $id;
                            $player_id = $customer_id;
                            $ip_address = $customer_ip;
                            $card_number = $card_details_serial;
                            $card_country = $card_details_country;
                            $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                            $paysafecardDirectMerchantManagerPurchase = new PaysafecardDirectMerchantManagerPurchase();
                            $transactionResult = $paysafecardDirectMerchantManagerPurchase->processOkOrPendingTransaction($unpackedPaysafecardDirectData);
                            if($transactionResult['status'] == OK){
                                exit(OK);
                            }else{
                                $paysafecard_direct_transaction_id = $id;
                                $player_id = $customer_id;
                                $ip_address = $customer_ip;
                                $card_number = $card_details_serial;
                                $card_country = $card_details_country;
                                $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                                $this->notifyFailedTransaction($unpackedPaysafecardDirectData);
                                exit(NOK);
                            }
                            break;
                        default:
                            $message = "PaysafecardDirectMerchantController::purchase(mtid={$mtid}, eventType={$event_type}, serialNumbers={$serial_numbers}) CAPTURE PAYMENT 2 ERROR {$status} PAYSAFECARD RECEIVED MESSAGE {$response} ";
                            PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);

                            $paysafecard_direct_transaction_id = $id;
                            $player_id = $customer_id;
                            $ip_address = $customer_ip;
                            $card_number = $card_details_serial;
                            $card_country = $card_details_country;
                            $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                            $this->notifyFailedTransaction($unpackedPaysafecardDirectData);
                            exit(NOK);
                    }
                }
                break;
            case "SUCCESS":
                //do deposit credits action
                if($this->DEBUG_PURCHASE) {
                    //DEBUG THIS PART OF CODE
                    $message = $response;
                    //$errorHelper->sendMail("PaysafecardDirectMerchantController::purchase RESPONSE RETRIEVE PAYMENT DETAILS SUCCESS <br /> {$message}");
                    PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantController::purchase RESPONSE RETRIEVE PAYMENT DETAILS SUCCESS {$message}");
                }

                $paysafecard_direct_transaction_id = $id;
                $player_id = $customer_id;
                $ip_address = $customer_ip;
                $card_number = $card_details_serial;
                $card_country = $card_details_country;
                $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                $paysafecardDirectMerchantManagerPurchase = new PaysafecardDirectMerchantManagerPurchase();
                $transactionResult = $paysafecardDirectMerchantManagerPurchase->processOkOrPendingTransaction($unpackedPaysafecardDirectData);
                if($transactionResult['status'] == OK){
                    exit(OK);
                }else{
                    $paysafecard_direct_transaction_id = $id;
                    $player_id = $customer_id;
                    $ip_address = $customer_ip;
                    $card_number = $card_details_serial;
                    $card_country = $card_details_country;
                    $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                    $this->notifyFailedTransaction($unpackedPaysafecardDirectData);
                    exit(NOK);
                }
                break;
            default:
                //unknown status or not authorized and not success
                $message = "PaysafecardDirectMerchantController::purchase(mtid={$mtid}, eventType={$event_type}, serialNumbers={$serial_numbers}) RETRIEVE PAYMENT DETAILS {$status} ";
                PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                $paysafecard_direct_transaction_id = $id;
                $player_id = $customer_id;
                $ip_address = $customer_ip;
                $card_number = $card_details_serial;
                $card_country = $card_details_country;
                $unpackedPaysafecardDirectData = PaysafecardDirectMerchantHelper::compactPurchaseResponse($amount, $currency, $paysafecard_direct_transaction_id, $player_id, $ip_address, $card_number, $card_country, $udf1, $udf2, $udf3);
                $this->notifyFailedTransaction($unpackedPaysafecardDirectData);
                exit(NOK);
        }
	}

	//
    // This url is targeted by backoffice application to payout
    // player to his paysafecard account
    //
    public function originalCreditAction(){
        $loginSuccessful = false;
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
            $config = Zend_Registry::get('config');
            $paysafecard_direct_http_user = $config->localPaysafecardDirectHttpUser;
            $paysafecard_direct_http_password = $config->localPaysafecardDirectHttpPassword;
            if ($username == $paysafecard_direct_http_user && $password == $paysafecard_direct_http_password){
                $loginSuccessful = true;
            }
        }
        if (!$loginSuccessful){
            $ip_address = IPHelper::getRealIPAddress();
            $message = "PaysafecardDirectMerchantController::originalCreditAction authentication failed! <br /> Detected IP Address={$ip_address}";
            PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);

            header('WWW-Authenticate: Basic realm="Secret page"');
            header('HTTP/1.0 401 Unauthorized');
            exit("Login failed!\n");
        }

        $backoffice_session_id = trim(strip_tags(urldecode($this->_getParam('backoffice_session_id'))));
        $transaction_id_old = trim(strip_tags(urldecode($this->_getParam('transaction_id_old'))));
        $db_transaction_id = trim(strip_tags(urldecode($this->_getParam('db_transaction_id'))));
        $paysafecard_transaction_id = trim(strip_tags(urldecode($this->_getParam('paysafecard_transaction_id'))));
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
                PaysafecardDirectErrorHelper::sendMail("PaysafecardDirectMerchantController::originalCreditAction method: " .
                "<br /> Backoffice Session_id = {$backoffice_session_id} <br /> Transaction Id Old = {$transaction_id_old} <br /> DB Transaction ID = {$db_transaction_id}
                 <br /> Paysafecard_transaction_id = {$paysafecard_transaction_id}" . "<br /> OREF_TRANSACTION_ID = {$oref_transaction_id} <br /> Player ID = {$player_id}
                 <br /> Amount = {$amount} <br /> Currency = {$currency_text} <br /> Currency Code = {$currency_code} <br /> Payment Method = {$payment_method} <br /> Fee Amount = {$fee_amount}
                 <br /> Token ID = {$token_id}");
                PaysafecardDirectErrorHelper::paysafecardDirectErrorLog("PaysafecardDirectMerchantController::originalCreditAction method:
                Backoffice Session_id = {$backoffice_session_id} Transaction Id Old = {$transaction_id_old} DB Transaction ID = {$db_transaction_id}
                Paysafecard_transaction_id = {$paysafecard_transaction_id} OREF_TRANSACTION_ID = {$oref_transaction_id} Player ID = {$player_id}
                Amount = {$amount} Currency = {$currency_text} Currency Code = {$currency_code} Payment Method = {$payment_method} Fee Amount = {$fee_amount} Token ID = {$token_id}");
            }

			require_once SERVICES_DIR . DS . 'paysafecard_direct' . DS . 'PaysafecardDirectMerchantManagerOriginalCredit.php';
			$paysafecardDirectMerchantManagerOriginalCredit = new PaysafecardDirectMerchantManagerOriginalCredit();
			$resArray = $paysafecardDirectMerchantManagerOriginalCredit->sendPaysafecardDirectOriginalCredit($backoffice_session_id, $transaction_id_old, $db_transaction_id, $paysafecard_transaction_id,
                $oref_transaction_id, $player_id, $amount, $currency_text, $currency_code, $payment_method, $fee_amount, $token_id
            );

			if($resArray['status'] == OK){
				//returns true if success prints OK
				exit(Zend_Json::encode($resArray));
			}else{
                require_once MODELS_DIR . DS . 'MerchantModel.php';
                $modelMerchant = new MerchantModel();
                $modelMerchant->transactionApiResponse(
                    $db_transaction_id,
                    NOK,
                    $resArray['error_message'],
                    "",
                    $player_id,
                    -1,
                    $payment_method,
                    PAYSAFECARD_DIRECT_PAYMENT_PROVIDER,
                    $amount,
                    $currency_text
                );

                $bo_session_id = null;
                require_once MODELS_DIR . DS . 'PlayerModel.php';
                $modelPlayer = new PlayerModel();
                $playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
                if($playerDetails['status'] != OK){
                    $message = "WirecardMerchantController::payoutAction(). PaysafecardDirectMerchantController::originalCreditAction(backoffice_session_id = {$backoffice_session_id}, transaction_id_old = {$transaction_id_old},
                        db_transaction_id = {$db_transaction_id}, paysafecard_transaction_id = {$paysafecard_transaction_id}, oref_transaction_id = {$oref_transaction_id}, player_id = {$player_id}, amount = {$amount},
                        currency_text = {$currency_text}, currency_code = {$currency_code}, payment_method = {$payment_method}, fee_amount = {$fee_amount}, token_id = {$token_id})";
                    PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog($message);
                    exit(NOK);
                }
                $details = $playerDetails['details'];
                //get site settings for player with player_id
                $site_settings = $modelMerchant->findSiteSettings($player_id);
                if($site_settings['status'] != OK){
                    $message = "WirecardMerchantController::payoutAction(). PaysafecardDirectMerchantController::originalCreditAction(backoffice_session_id = {$backoffice_session_id}, transaction_id_old = {$transaction_id_old},
                        db_transaction_id = {$db_transaction_id}, paysafecard_transaction_id = {$paysafecard_transaction_id}, oref_transaction_id = {$oref_transaction_id}, player_id = {$player_id}, amount = {$amount},
                        currency_text = {$currency_text}, currency_code = {$currency_code}, payment_method = {$payment_method}, fee_amount = {$fee_amount}, token_id = {$token_id})";
                    PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog($message);
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
                    $playerMailRes = WebSiteEmailHelper::getPayoutDeclinedFromApcoBackofficeContent($player_username, $db_transaction_id, $amount, $fee_amount, $player_currency,
                        $site_images_location, $casino_name, $site_url_link, $contact_url_link, $support_url_link, $terms_url_link, $language_settings);
                    $title = $playerMailRes['mail_title'];
                    $content = $playerMailRes['mail_message'];
                    $logger_message =  "Player with mail address: {$player_mail_address} username: {$player_username}, has not been notified for failed credit transfer payout through Wirecard payment provider via email.";
                    WebSiteEmailHelper::sendMailToPlayer($player_mail_send_from, $player_mail_address, $player_smtp_server, $title, $content, $title, $title, $logger_message, $site_images_location);
                }
                if($resArray['status'] != OK){
                    $message = "WirecardMerchantController::payoutAction(). PaysafecardDirectMerchantController::originalCreditAction(backoffice_session_id = {$backoffice_session_id}, transaction_id_old = {$transaction_id_old},
                        db_transaction_id = {$db_transaction_id}, paysafecard_transaction_id = {$paysafecard_transaction_id}, oref_transaction_id = {$oref_transaction_id}, player_id = {$player_id}, amount = {$amount},
                        currency_text = {$currency_text}, currency_code = {$currency_code}, payment_method = {$payment_method}, fee_amount = {$fee_amount}, token_id = {$token_id})";
                    PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog($message);
                }

				//returns false if not success prints NOK
                PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog("Error, declined transaction. PaysafecardDirectMerchantController::originalCreditAction(backoffice_session_id = {$backoffice_session_id}, transaction_id_old = {$transaction_id_old},
                db_transaction_id = {$db_transaction_id}, paysafecard_transaction_id = {$paysafecard_transaction_id}, oref_transaction_id = {$oref_transaction_id}, player_id = {$player_id}, amount = {$amount},
                currency_text = {$currency_text}, currency_code = {$currency_code}, payment_method = {$payment_method}, fee_amount = {$fee_amount}, token_id = {$token_id})");
				exit(Zend_Json::encode($resArray));
			}
		}catch(Zend_Exception $ex){
            $message = "PaysafecardDirectMerchantController::originalCreditAction exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
            PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog("Error, declined transaction. PaysafecardDirectMerchantController::originalCreditAction(backoffice_session_id = {$backoffice_session_id}, transaction_id_old = {$transaction_id_old},
            db_transaction_id = {$db_transaction_id}, paysafecard_transaction_id = {$paysafecard_transaction_id}, oref_transaction_id = {$oref_transaction_id}, player_id = {$player_id}, amount = {$amount},
            currency_text = {$currency_text}, currency_code = {$currency_code}, payment_method = {$payment_method}, fee_amount = {$fee_amount}, token_id = {$token_id})");
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
            $paysafecard_direct_http_user = $config->localPaysafecardDirectHttpUser;
            $paysafecard_direct_http_password = $config->localPaysafecardDirectHttpPassword;
            if ($username == $paysafecard_direct_http_user && $password == $paysafecard_direct_http_password){
                $loginSuccessful = true;
            }
        }
        if (!$loginSuccessful){
            $ip_address = IPHelper::getRealIPAddress();
            $message = "PaysafecardDirectMerchantController::cancelOriginalCreditAction HTTP authentication failed! <br /> Detected IP Address={$ip_address}";
            PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);

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
                PaysafecardDirectErrorHelper::sendMail("paysafecardDirectMerchantController::cancelOriginalCredit method. <br /> Transaction id = {$transaction_id} <br /> Player id = {$player_id} <br /> Currency = {$currency} <br /> Amount = {$amount}");
                PaysafecardDirectErrorHelper::paysafecardDirectErrorLog("PaysafecardDirectMerchantController::cancelOriginalCredit method. Transaction id = {$transaction_id} Player id = {$player_id} <br /> Currency = {$currency} <br /> Amount = {$amount}");
            }
            $bo_session_id = null;
			require_once MODELS_DIR . DS . 'PlayerModel.php';
			$modelPlayer = new PlayerModel();
			//retrieve player details - required for player email addres
			$playerDetails = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
			if($playerDetails['status'] != OK){
        PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog("Error, declined transaction. PaysafecardDirectMerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
        amount = {$amount}, currency = {$currency})");
				exit(NOK);
			}
			$details = $playerDetails['details'];
			//get site settings for player with player_id
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			$siteSettings = $modelMerchant->findSiteSettings($player_id);
			if($siteSettings['status'] != OK){
        PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog("Error, declined transaction. PaysafecardDirectMerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
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
        PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog("Error, declined transaction. PaysafecardDirectMerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
        amount = {$amount}, currency = {$currency})");
				exit(NOK);
			}
		}catch(Zend_Exception $ex){
            $message = "PaysafecardDirectMerchantController::cancelOriginalCredit action exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
            PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog("Error, declined transaction. PaysafecardDirectMerchantController::cancelOriginalCreditAction(transaction_id = {$transaction_id}, player_id = {$player_id},
                amount = {$amount}, currency = {$currency})");
			exit(NOK);
		}
	}

    private function notifyFailedTransaction($unpackedPaysafecardDirect){
        $player_id = $unpackedPaysafecardDirect['player_id'];
        $received_data = print_r($unpackedPaysafecardDirect, true);
        $bo_session_id = null;
        require_once MODELS_DIR . DS . 'PlayerModel.php';
        $modelPlayer = new PlayerModel();
        //retrieve player details - required for player email addres
        $player_details = $modelPlayer->getPlayerDetailsMalta($bo_session_id, $player_id);
        if($player_details['status'] != OK){
            $message = "PaysafecardDirectMerchantController::purchase() <br /> {$received_data}";
            PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog($message);
            exit(NOK);
        }
        $details = $player_details['details'];
        //get site settings for player with player_id
        require_once MODELS_DIR . DS . 'MerchantModel.php';
        $modelMerchant = new MerchantModel();

        $modelMerchant->transactionApiResponse(
            $unpackedPaysafecardDirect['db_transaction_id'],
            NOK,
            print_r($unpackedPaysafecardDirect, true),
            "",
            $player_id,
            1,
            $unpackedPaysafecardDirect['payment_method_code'],
            PAYSAFECARD_DIRECT_PAYMENT_PROVIDER,
            $unpackedPaysafecardDirect['player_basic_deposit_amount'],
            $unpackedPaysafecardDirect['currency_text']
        );

        $site_settings = $modelMerchant->findSiteSettings($player_id);
        if($site_settings['status'] != OK){
            if($this->DEBUG_PURCHASE) {
                //DEBUG THIS CODE
                PaysafecardDirectErrorHelper::sendMail("PaysafecardDirectMerchantController::purchase action failed, can not get web site settings. <br /> {$received_data}");
                PaysafecardDirectErrorHelper::paysafecardDirectErrorLog("PaysafecardDirectMerchantController::purchase action failed, can not get web site settings. <br /> {$received_data}");
            }
            $message = "PaysafecardDirectMerchantController::purchaseAction(). resultsString = {$received_data}";
            PaysafecardDirectErrorHelper::paysafecardDirectDeclinedTransactionsLog($message);
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
        $deposit_amount = $unpackedPaysafecardDirect['player_basic_deposit_amount'];
        $fee_amount = $unpackedPaysafecardDirect['fee_amount'];
        $currency_text = $unpackedPaysafecardDirect['currency_text'];
        $db_transaction_id = $unpackedPaysafecardDirect['db_transaction_id'];
        $payment_method = $unpackedPaysafecardDirect['payment_method_code'];
        if(strlen($player_email_address) != 0){
            $playerMailRes = WebSiteEmailHelper::getPurchaseFailedContent($deposit_amount, $fee_amount, $currency_text, $db_transaction_id, $payment_method,
            $player_username, $site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings);
            $title = $playerMailRes['mail_title'];
            $content = $playerMailRes['mail_message'];
            $logger_message =  "Player with mail address: {$player_email_address} username: {$player_username}, has not been notified for failed credit transfer payment through Paysafecard Direct payment processor via email.";
            WebSiteEmailHelper::sendMailToPlayer($player_email_send_from, $player_email_address, $player_smtp_server, $title, $content,
                $title, $title, $logger_message, $site_images_location);
        }
    }

    private function getPaysafecardPaymentDetails($payment_id){
        if($this->DEBUG_PURCHASE) {
            //DEBUG THIS
            $message = "PaysafecardDirectMerchantController::getPaysafecardPaymentDetails(payment_id = {$payment_id})";
            PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
        }
		if(strlen($payment_id)==0){
            $message = "PaysafecardDirectMerchantController::getPaysafecardPaymentDetails(payment_id = {$payment_id})";
            PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
        try{
            $config = Zend_Registry::get('config');

            if($config->paysafecardDirectTestMode == "true"){
                $paysafecard_direct_details_url = $config->paysafecardDirectUrl . "paysafecard_payment_details.php?mtid=" . $payment_id;
            }else{
                $paysafecard_direct_details_url = $config->paysafecardDirectUrl . "payments/" . $payment_id;
            }

            $http_user = base64_encode($config->paysafecardDirectHttpUser);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $paysafecard_direct_details_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            if ($config->paysafecardVerifyCertificate == "true") {
                curl_setopt($ch, CURLOPT_CAINFO, APP_DIR . "/configs/paysafecard_certificates/" . $config->paysafecardCertificateFileName);
            }else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              "Content-Type: application/json",
              "Authorization: Basic {$http_user}"
            ));
            $response = curl_exec($ch);
            if(curl_errno($ch)){
                //there was an error sending post to paysafecard to check payment
                $error_message = curl_error($ch);
                curl_close($ch);
                $message = "PaysafecardDirectMerchantController::getPaysafecardPaymentDetails(payment_id={$payment_id}) ERROR MESSAGE = " . $error_message;
                PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
            curl_close($ch);

            if($this->DEBUG_PURCHASE) {
                //DEBUG THIS PART OF CODE
                $message = $response;
                PaysafecardDirectErrorHelper::sendMail("PaysafecardDirectMerchantController::getPaysafecardPaymentDetails({$payment_id}) RETRIEVE PAYMENT DETAILS <br /> {$message}");
                PaysafecardDirectErrorHelper::paysafecardDirectAccessLog("PaysafecardDirectMerchantController::getPaysafecardPaymentDetails({$payment_id}) RETRIEVE PAYMENT DETAILS {$message}");
            }

            $responsePaysafecardDirectRetrievePaymentDetailsJsonObject = json_decode($response, true);

            $object = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['object'];
            $id = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['id'];
            $created = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['created'];
            $updated = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['updated'];
            $amount = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['amount'];
            $currency = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['currency'];
            $status = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['status'];
            $type = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['type'];
            $redirect_success_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['success_url'];
            $redirect_failure_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['failure_url'];
            $redirect_auth_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['redirect']['auth_url'];
            $customer_id = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['customer']['id'];
            $customer_ip = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['customer']['ip'];
            $notification_url = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['notification_url'];
            $card_details_serial = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['serial'];
            $card_details_type = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['type'];
            $card_details_country = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['country'];
            $card_details_currency = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['currency'];
            $card_details_amount = $responsePaysafecardDirectRetrievePaymentDetailsJsonObject['card_details']['amount'];

            return array("status"=>OK, "result"=>$responsePaysafecardDirectRetrievePaymentDetailsJsonObject);

        }catch(Zend_Exception $ex){
            $error_message = $ex->getMessage();
            $message = "PaysafecardDirectMerchantController::getPaysafecardPaymentDetails(payment_id={$payment_id}) ERROR MESSAGE = " . $error_message;
            PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }
}
