<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'ApcoErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'ApcoMerchantHelper.php';

/**
 *
 * Merchant manager to perform transaction processing from Apco Limited payment processor ...
 *
 */
class MerchantManagerPayout {

    private $DEBUG = false;
	/**
	 *
	 * test if communication is done through white listed ip address range ...
	 */
	private function isSecureConnection(){
		$config = Zend_Registry::get('config');
		//will check site ip address caller if true
		if($config->checkApcoIpAddress == 'true'){
			$ip_addresses = explode(' ', $config->apcoIpAddress);
			$host_ip_address = IPHelper::getRealIPAddress();
			$status = in_array($host_ip_address, $ip_addresses);
			if(!$status){
				$message = "MerchantManagerPayout::isSecureConnection service: Host with blacklisted ip address {$host_ip_address} is trying to connect to merchant payin / payout web service.";
				ApcoErrorHelper::apcoIntegrationError($message, $message);
			}
			return $status;
		} else {
            return true;
        }
	}

    /**
     * @param array $validResponseResult
     * @param object $xmlFastPayObject
     * @return array
     */
    private function processOkOrPendingTransaction($validResponseResult, $xmlFastPayObject){
        if($this->DEBUG){
            $message = "MerchantManagerPayout::payout method called validateResponseWithToolPayout";
            ApcoErrorHelper::apcoIntegrationAccess($message, $message);
        }
        if($validResponseResult['status'] == OK){
            //try to confirm transaction in database
            //if charge fee success then process player withdraw payment
            require_once MODELS_DIR . DS . 'MerchantModel.php';
		    $modelMerchant = new MerchantModel();
            $transactionResult = $modelMerchant->apcoWithdrawArrivedFromApco($validResponseResult['withdraw_request_id'], YES, $validResponseResult['payment_method']);
            if($transactionResult['status'] == OK && $transactionResult['verification_status'] == "1"){
                return array("status"=>OK, "result"=>$xmlFastPayObject);
            }else{
                //this transaction is not confirmed in database
                //transaction is written in bank but not in our database
                //send signal to apco service to void purchase
                require_once SERVICES_DIR . DS . 'apco_merchant' . DS . 'MerchantManagerVoidOperation.php';
                $merchantManagerVoidOperation = new MerchantManagerVoidOperation();
                $merchantManagerVoidOperation->sendApcoVoidCredit($validResponseResult);
                //send error mail to player and administrator, write error log for service
                return array("status"=>NOK, "result"=>$xmlFastPayObject);
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
            $modelMerchant->apcoWithdrawArrivedFromApco($validResponseResult['withdraw_request_id'], NO, $validResponseResult['payment_method']);
            $message = '<br/><strong>NOTOK</strong>: The transaction was not successful. <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
            $message = "MerchantManagerPayout::payout<br />Apco payout: {$message}";
            ApcoErrorHelper::apcoIntegrationErrorLog($message);
            return array("status"=>NOK, "result"=>$xmlFastPayObject);
        }
    }

	/**
	 * This is called by Apco payment service for payout so we can
	 * verify that transaction was successfull on Apco and on our side
	 * returns OK, NOK, NOK_EXCEPTION
     * received parameters from url address
	 * @param string $xmlFastPayString
	 * @return mixed
	 */
	public function payout($xmlFastPayString){
        if($this->DEBUG){
            $message = "MerchantManagerPayout::payout(xmlFastPayString={$xmlFastPayString})";
            ApcoErrorHelper::apcoIntegrationAccess($message, $message);
        }
        $xmlFastPayStringResult = ApcoMerchantHelper::getParamsAndConvertToXmlObject($xmlFastPayString);
        //check if transaction is coming from our payment processor
        //based on their ip address
        $xmlFastPayObject = $xmlFastPayStringResult["result"];
        if (!$this->isSecureConnection()){
            return array("status"=>NOK, "result"=>$xmlFastPayObject);
        }
        try{
			if($xmlFastPayStringResult["status"] != OK) {
                return array("status" => NOK, "result" => $xmlFastPayObject);
            }
            if($this->DEBUG){
                $message = "MerchantManagerPayout::payout method has xml parametar values: {$xmlFastPayString}";
                ApcoErrorHelper::apcoIntegrationAccess($message, $message);
            }
			//get secret word for soap web service
			require_once MODELS_DIR . DS . 'MerchantModel.php';
			$modelMerchant = new MerchantModel();
			//get secret words given by apco payment service
            $secretWords = $modelMerchant->getSecretWords(null);
            if($secretWords["status"] == NOK){
                return array("status"=>NOK, "result"=>$xmlFastPayObject);
            }
            if($this->DEBUG){
                $message = "MerchantManagerPayout::payout method called secret words";
                ApcoErrorHelper::apcoIntegrationAccess($message, $message);
            }
            $profile_id = $secretWords['profile_id'];
            $secret_word = $secretWords['secret_word'];
            //get merchant code and merchant password from database
            $merchantCodes = $modelMerchant->getMerchantCodes(null);
            if($merchantCodes["status"] == NOK){
                return array("status"=>NOK, "result"=>$xmlFastPayObject);
            }
            $merchant_code = $merchantCodes['merchant_code'];
            $merchant_password = $merchantCodes['merchant_password'];
            if($this->DEBUG){
                $message = "MerchantManagerPayout::payout method called merchant words";
                ApcoErrorHelper::apcoIntegrationAccess($message, $message);
            }
			//check if message's hash value is correct - check if message is sent by apco service
            $res = ApcoMerchantHelper::reCheckMd5ValidationOnFastPayXMLPayout($xmlFastPayObject, $secret_word);
			if ($res["status"] == OK) {
				//MDF successfully matched
				//send exception message on mail to administrators
                $resultValue = $res["result"];
                //validate response from apco soap web service
                //call apco soap web service and validate this transaction
                $validResponseResult = ApcoMerchantHelper::validateResponseWithToolPayout($xmlFastPayString, $xmlFastPayObject, $merchant_code, $merchant_password);
				switch (strtoupper($resultValue)) {
					case BANK_OK:
                        return $this->processOkOrPendingTransaction($validResponseResult, $xmlFastPayObject);
                    break;
                    case BANK_PENDING:
                        //still pending transaction
                        $payment_method = $validResponseResult['payment_method'];
                        if($payment_method == ENTERCASH){
                            //for entercash if pending process as if OK transaction
                            return $this->processOkOrPendingTransaction($validResponseResult, $xmlFastPayObject);
                        }else {
                            require_once MODELS_DIR . DS . 'MerchantModel.php';
                            $modelMerchant = new MerchantModel();
                            $modelMerchant->apcoWithdrawArrivedFromApco($validResponseResult['withdraw_request_id'], NO, $validResponseResult['payment_method']);
                            $message = '<br/><strong>PENDING</strong>: The transaction is still pending. <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
                            $message = "MerchantManagerPayout::payout <br />Apco payout: {$message}";
                            ApcoErrorHelper::apcoIntegrationErrorLog($message);
                            return array("status"=>NOK, "result"=>$xmlFastPayObject);
                        }
                    break;
                    case BANK_NOK:
                        //transaction was not successfull error
                        require_once MODELS_DIR . DS . 'MerchantModel.php';
                        $modelMerchant = new MerchantModel();
                        $modelMerchant->apcoWithdrawArrivedFromApco($validResponseResult['withdraw_request_id'], NO, $validResponseResult['payment_method']);

                        $message = '<br/><strong>NOTOK</strong>: The transaction was not successful. <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
                        $message = "MerchantManagerPayout::payout <br />Apco payout: {$message}";
                        ApcoErrorHelper::apcoIntegrationErrorLog($message);
                        return array("status"=>NOK, "result"=>$xmlFastPayObject);
                    break;
                    case BANK_DECLINED:
                        //bank has declined transaction
                        require_once MODELS_DIR . DS . 'MerchantModel.php';
                        $modelMerchant = new MerchantModel();
                        $modelMerchant->apcoWithdrawArrivedFromApco($validResponseResult['withdraw_request_id'], NO, $validResponseResult['payment_method']);

                        $message = '<br/><strong>DECLINED</strong>: The transaction was declined. <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
                        $message = "MerchantManagerPayout::payout <br />Apco payout: {$message}";
                        ApcoErrorHelper::apcoIntegrationErrorLog($message);
                        return array("status"=>NOK, "result"=>$xmlFastPayObject);
                    break;
                    case BANK_CANCEL:
                        //transaction was canceled
                        require_once MODELS_DIR . DS . 'MerchantModel.php';
                        $modelMerchant = new MerchantModel();
                        $modelMerchant->apcoWithdrawArrivedFromApco($validResponseResult['withdraw_request_id'], NO, $validResponseResult['payment_method']);

                        $message = '<br/><strong>CANCEL</strong>: The transaction is cancelled. <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
                        $message = "MerchantManagerPayout::payout <br />Apco payout: {$message}";
                        ApcoErrorHelper::apcoIntegrationErrorLog($message);
                        return array("status"=>NOK, "result"=>$xmlFastPayObject);
                    break;
                    default:
                        //unknown error
                        require_once MODELS_DIR . DS . 'MerchantModel.php';
                        $modelMerchant = new MerchantModel();
                        $modelMerchant->apcoWithdrawArrivedFromApco($validResponseResult['withdraw_request_id'], NO, $validResponseResult['payment_method']);

                        $message = '<br/><strong>OTHER RESULT</strong>: ' . strtoupper($resultValue) . ' <br /> Merchant Order Reference Number: ' . (string)$xmlFastPayObject->ORef;
                        $message = "MerchantManagerPayout::payout <br />Apco payout: {$message}";
                        ApcoErrorHelper::apcoIntegrationErrorLog($message);
                        return array("status"=>NOK, "result"=>$xmlFastPayObject);
                    break;
				}
            } else {
                //ERROR: invalid hash
                $message = "<br /><strong>PAYOUT - Invalid hash value. No match!!</strong>";
                $message = "MerchantManagerPayout::payout <br />Apco payout: {$message}";
                ApcoErrorHelper::apcoIntegrationErrorLog($message);
                return array("status"=>NOK, "result"=>$xmlFastPayObject);
            }
        }catch(Zend_Exception $ex){
            //returns exception to web site
            //send mail with exception message to administrators
            $message = "MerchantManagerPayout::payout method: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            ApcoErrorHelper::apcoIntegrationErrorLog($message);
            return  array("status"=>NOK, "result"=>$xmlFastPayObject);
		}
	}
}
