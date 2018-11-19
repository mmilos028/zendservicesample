<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'WirecardErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'WirecardMerchantHelper.php';

/**
 *
 * Merchant manager to perform transaction processing of payouts from Wirecard payment provider ...
 *
 */
class WirecardMerchantManagerPayout {

    private $DEBUG = false;
	/**
	 *
	 * test if communication is done through white listed ip address range ...
	 */
	private function isSecureConnection(){
		$config = Zend_Registry::get('config');
		//will check site ip address caller if true
		if($config->checkWirecardIpAddress == 'true'){
			$ip_addresses = explode(' ', $config->wirecardIpAddress);
			$host_ip_address = IPHelper::getRealIPAddress();
			$status = in_array($host_ip_address, $ip_addresses);
			if(!$status){
				$message = "WirecardMerchantManagerPayout::isSecureConnection service: Host with blacklisted ip address {$host_ip_address} is trying to connect to merchant paying / payout web service.";
				WirecardErrorHelper::wirecardError($message, $message);
			}
			return $status;
		} else {
            return true;
        }
	}

    /**
     * @param $resultsArray
     * @param $unpackedWirecardData
     * @return array
     * @throws Zend_Exception
     */
    private function processOkOrPendingTransaction($resultsArray, $unpackedWirecardData){
        if($unpackedWirecardData['status'] == OK){
            //try to confirm transaction in database
            //if charge fee success then process player withdraw payment
            require_once MODELS_DIR . DS . 'MerchantModel.php';
		    $modelMerchant = new MerchantModel();
            $transactionResult = $modelMerchant->apcoWithdrawArrivedFromApco($unpackedWirecardData['withdraw_request_id'], YES, $unpackedWirecardData['payment_method']);
            if($transactionResult['status'] == OK && $transactionResult['verification_status'] == "1"){
                return array("status"=>OK, "result"=>$resultsArray);
            }else{
                //this transaction is not confirmed in database
                //transaction is written in bank but not in our database
                //send signal to WIRECARD service to void purchase
                require_once SERVICES_DIR . DS . 'wirecard_merchant' . DS . 'WirecardMerchantManagerVoidOperation.php';
                $wirecardMerchantManagerVoidOperation = new WirecardMerchantManagerVoidOperation();
                $wirecardMerchantManagerVoidOperation->sendWirecardVoidCredit($resultsArray);
                //send error mail to player and administrator, write error log for service
                return array("status"=>NOK, "result"=>$resultsArray);
            }
            /*if($feeResult['status'] == OK){
                //IF TRANSACTION WAS SUCCESS NOTHING WILL HAPPEN
                //this transaction if confirmed in database
                //transaction is written in bank and in our database
                return array("status"=>OK, "result"=>$xmlFastPayObject);
            }else{
                //this transaction is not confirmed in database
                //transaction is written in bank but not in our database
                //send signal to apco service to void purchase
                require_once SERVICES_DIR . DS . 'MerchantManagerVoidOperation.php';
                $merchantManagerVoidOperation = new MerchantManagerVoidOperation();
                $merchantManagerVoidOperation->sendApcoVoidCredit($validResponseResult);
                //send error mail to player and administrator, write error log for service
                return array("status"=>NOK, "result"=>$xmlFastPayObject);
            }*/
        }else{
            //TRANSACTION NOT OK ????
            require_once MODELS_DIR . DS . 'MerchantModel.php';
            $modelMerchant = new MerchantModel();
            $modelMerchant->apcoWithdrawArrivedFromApco($unpackedWirecardData['withdraw_request_id'], NO, $unpackedWirecardData['payment_method']);
            $message = '<br/><strong>NOTOK</strong>: The transaction was not successful. <br /> Wirecard Transaction ID: ' . (string)$unpackedWirecardData['wirecard_transaction_id'];
            $message = "WirecardMerchantManagerPayout::payout<br />Wirecard payout: {$message}";
            WirecardErrorHelper::wirecardErrorLog($message);
            return array("status"=>NOK, "result"=>$resultsArray);
        }
    }

	/**
	 * This is called by Wirecard payment provider for payout so we can
	 * verify that transaction was successfull on Wirecard and on our side
	 * returns OK, NOK, NOK_EXCEPTION
     * received parameters from url address
	 * @param array $resultsArray
     * @param string $resultsString
	 * @return mixed
	 */
	public function payout($resultsArray, $resultsString){
        if($this->DEBUG){
            $message = "WirecardMerchantManagerPayout::payout(resultsArray, resultsString={$resultsString})";
            WirecardErrorHelper::wirecardAccess($message, $message);
        }
        //check if transaction is coming from our payment processor
        //based on their ip address
        if (!$this->isSecureConnection()){
            return array("status"=>NOK, "result"=>$resultsArray);
        }
        try{
			/*if($resultsArray["status"] != OK) {
                return array("status" => NOK, "result" => $resultsArray);
            }*/
            if($this->DEBUG){
                $message = "WirecardMerchantManagerPayout::payout() method has parametar values: <br /> resultsString = {$resultsString}";
                WirecardErrorHelper::wirecardAccess($message, $message);
            }

            $unpackedWirecardData = WirecardMerchantHelper::validatePayoutResponse($resultsArray, $resultsString);
            if (WirecardMerchantHelper::testValidMessage($resultsArray, $resultsString)) {
            //if(true){
                switch (strtoupper($resultsArray["transaction_state"])) {
                    case "SUCCESS":
                        return $this->processOkOrPendingTransaction($resultsArray, $unpackedWirecardData);
                        break;
                    case "IN-PROGRESS":
                        //still pending transaction error
                        require_once MODELS_DIR . DS . 'MerchantModel.php';
                        $modelMerchant = new MerchantModel();
                        $modelMerchant->apcoWithdrawArrivedFromApco($unpackedWirecardData['withdraw_request_id'], NO, $unpackedWirecardData['payment_method']);
                        $message = "<br/><strong>WirecardMerchantManagerPayout::payout() IN PROGRESS</strong>: The transaction is still in progress. <br />Wirecard Transaction ID: {$unpackedWirecardData['wirecard_transaction_id']} <br />Status Code: {$resultsArray['status_code_1']} <br />Status Description: {$resultsArray['status_description_1']}";
                        $message = "<br />WireCard payout: {$message}";
                        WirecardErrorHelper::wirecardErrorLog($message);
                        return array("status" => NOK, "result"=>$resultsArray);
                        break;
                    case "FAILED":
                        //still pending transaction
                        require_once MODELS_DIR . DS . 'MerchantModel.php';
                        $modelMerchant = new MerchantModel();
                        $modelMerchant->apcoWithdrawArrivedFromApco($unpackedWirecardData['withdraw_request_id'], NO, $unpackedWirecardData['payment_method']);
                        $message = "<br/><strong>WirecardMerchantManagerPayout::payout() FAILED</strong>: The transaction has failed. <br />Wirecard Transaction ID: {$unpackedWirecardData['wirecard_transaction_id']} <br />Status Code: {$resultsArray['status_code_1']} <br />Status Description: {$resultsArray['status_description_1']}";
                        $message = "<br />Wirecard payout: {$message}";
                        WirecardErrorHelper::wirecardErrorLog($message);
                        return array("status"=>NOK, "result"=>$resultsArray);
                        break;
                    default:
                        //unknown error
                        require_once MODELS_DIR . DS . 'MerchantModel.php';
                        $modelMerchant = new MerchantModel();
                        $modelMerchant->apcoWithdrawArrivedFromApco($unpackedWirecardData['withdraw_request_id'], NO, $unpackedWirecardData['payment_method']);
                        $message = "<br/><strong>WirecardMerchantManagerPayout::payout() OTHER RESULT</strong>: " . strtoupper($unpackedWirecardData['transaction_state']) . "<br />Wirecard Transaction ID: {$unpackedWirecardData['wirecard_transaction_id']} <br />Status Code: {$resultsArray['status_code_1']} <br />Status Description: {$resultsArray['status_description_1']}";
                        $message = "WirecardMerchantManagerPayout::payout <br />Wirecard payout: {$message}";
                        WirecardErrorHelper::wirecardErrorLog($message);
                        return array("status" => NOK, "result"=>$resultsArray);
                }
            } else {
				//ERROR: invalid response signature
				$message = "<br /><strong>WirecardMerchantManagerPayout::payout() PAYOUT - Invalid Response Signature. Transaction is invalid, because message was tempered and response signature is invalid!</strong> <br />Wirecard Transaction ID: {$unpackedWirecardData['wirecard_transaction_id']} <br />Status Code: {$resultsArray['status_code_1']} <br />Status Description: {$resultsArray['status_description_1']}";
				WirecardErrorHelper::wirecardErrorLog($message);
				return array("status"=>NOK, "result"=>$resultsArray);
			}
        }catch(Zend_Exception $ex){
            //returns exception to web site
            //send mail with exception message to administrators
            $message = "WirecardMerchantManagerPayout::payout() method: " . CursorToArrayHelper::getExceptionTraceAsString($ex) . "<br />Status Code: {$resultsArray['status_code_1']} <br />Status Description: {$resultsArray['status_description_1']}";
            WirecardErrorHelper::wirecardErrorLog($message);
            return  array("status"=>NOK, "result"=>$resultsArray);
		}
	}
}
