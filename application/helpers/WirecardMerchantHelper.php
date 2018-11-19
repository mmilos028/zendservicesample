<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WirecardErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';


class WirecardMerchantHelper {


    public static function isSecureCard($payment_method){
        $is_3d_secure = false;
        if(in_array(strtoupper($payment_method), array("VISA3D", "MASTERCARDSECURE", "MAESTROSECURE", "MAESTRO"))){
            $is_3d_secure = true;
        }else{
            $is_3d_secure = false;
        }
        return $is_3d_secure;
    }

    public static function returnCardType($payment_method){
        $payment_method = strtoupper($payment_method);
        $card_type = "VISA";
        if($payment_method == "VISA3D"){
            $card_type = "VISA";
        }else if($payment_method == "VISA"){
            $card_type = "VISA";
        }
        else if($payment_method == "MASTERCARD"){
            $card_type = "MASTERCARD";
        }
        else if($payment_method == "MASTERCARDSECURE"){
            $card_type = "MASTERCARD";
        }
        else if($payment_method == "MAESTRO"){
            $card_type = "MAESTRO";
        }
        else if($payment_method == "MAESTROSECURE"){
            $card_type = "MAESTRO";
        }
        return strtolower($card_type);
    }

    /**
     * Test if Wirecard message was tempered by using response_signature field
     * @param $resultsArray
     * @param $resultsString
     * @return bool
     * @throws Zend_Exception
     */
    public static function testValidMessage($resultsArray, $resultsString){
        $response_signature = $resultsArray['response_signature'];

        $unpackedWirecardData = self::validatePurchaseResponse($resultsArray, $resultsString);
        $account = self::getAccountSpecificDataPerPaymentMethod($unpackedWirecardData['payment_method_code']);

        if(strlen($resultsArray['merchant_account_id']) > 0){
            $merchant_account_id = trim($resultsArray['merchant_account_id']);
        }else{
            $merchant_account_id = "";
        }

        if(strlen($resultsArray['transaction_id']) > 0){
            $transaction_id = trim($resultsArray['transaction_id']);
        }else{
            $transaction_id = "";
        }

        if(strlen($resultsArray['request_id']) > 0){
            $request_id = trim($resultsArray['request_id']);
        }else{
            $request_id = "";
        }

        if(strlen($resultsArray['transaction_type']) > 0){
            $transaction_type = trim($resultsArray['transaction_type']);
        }else{
            $transaction_type = "";
        }

        if(strlen($resultsArray['transaction_state']) > 0){
            $transaction_state = trim($resultsArray['transaction_state']);
        }else{
            $transaction_state = "";
        }

        if(strlen($resultsArray['completion_time_stamp']) > 0){
            $completion_time_stamp = trim($resultsArray['completion_time_stamp']);
        }else{
            $completion_time_stamp = "";
        }

        if(strlen($resultsArray['token_id']) > 0){
            $token_id = trim($resultsArray['token_id']);
        }else{
            $token_id = "";
        }

        if(strlen($resultsArray['masked_account_number']) > 0){
            $masked_account_number = trim($resultsArray['masked_account_number']);
        }else{
            $masked_account_number = "";
        }

        if(strlen($resultsArray['ip_address']) > 0){
            $ip_address = trim($resultsArray['ip_address']);
        }else{
            $ip_address = "";
        }

        if(strlen($resultsArray['authorization_code']) > 0){
            $authorization_code = trim($resultsArray['authorization_code']);
        }else{
            $authorization_code = "";
        }
		
		$received_account = self::getAccountSpecificDataFromMerchantAccountId($merchant_account_id);

        $our_response_signature = hash('sha256', trim(
            $merchant_account_id .
            $transaction_id .
            $request_id .
            $transaction_type .
            $transaction_state .
            $completion_time_stamp .
            $token_id .
            $masked_account_number .
            $ip_address .
            $authorization_code .
            $received_account['secret_key']
        ));

        $test = ($response_signature == $our_response_signature);

        $wirecard_response_signature_content = print_r(array(
            "merchant_account_id"=>$merchant_account_id,
            "transaction_id"=>$transaction_id,
            "request_id"=>$request_id,
            "transaction_type"=>$transaction_type,
            "transaction_state"=>$transaction_state,
            "completion_time_stamp"=>$completion_time_stamp,
            "token_id"=>$token_id,
            "masked_account_number"=>$masked_account_number,
            "ip_address"=>$ip_address,
            "authorization_code"=>$authorization_code,
            "secret_key"=>$received_account['secret_key']
        ), true);

        $our_response_signature_content = print_r(array(
            "merchant_account_id"=>$merchant_account_id,
            "transaction_id"=>$resultsArray['transaction_id'],
            "request_id"=>$resultsArray['request_id'],
            "transaction_type"=>$resultsArray['transaction_type'],
            "transaction_state"=>$resultsArray['transaction_state'],
            "completion_time_stamp"=>$resultsArray['completion_time_stamp'],
            "token_id"=>$resultsArray['token_id'],
            "masked_account_number"=>$resultsArray['masked_account_number'],
            "ip_address"=>$resultsArray['ip_address'],
            "authorization_code"=>$resultsArray['authorization_code'],
            "secret_key"=>$received_account['secret_key']
        ), true);

        if(!$test){
            WirecardErrorHelper::wirecardErrorLog("WirecardMerchantManagerHelper::testValidMessage, response signature does not match! <br /> response_signature = {$response_signature} our_response_signature = {$our_response_signature} <br /> our response_signature content: {$our_response_signature_content} <br /> wirecard response_signature content: {$wirecard_response_signature_content}");
        }

        return $test;
    }

    /**
     * @param $payment_method
     * @return array
     * @throws Zend_Exception
     */
    public static function getAccountSpecificDataPerPaymentMethod($payment_method){
        $is_3d_card = self::isSecureCard($payment_method);

        $config = Zend_Registry::get('config');
        if($config->wirecardTestMode == "true"){
            switch(strtolower($payment_method)){
                case 'ideal':
                    $merchant_account_id = "adb45327-170a-460b-9810-9008e9772f5f";
                    $secret_key = "1b9e63b4-c132-42c3-bcbd-2d2e47ae7154";
                    $transaction_type = "debit";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "ideal";

                    break;
                case 'p24':
                case 'przelewy':
                case 'przlewy':
                case 'przelewy24':
                    $merchant_account_id = "27183130-2a8e-47ff-84ab-25d12362e843";
                    $secret_key = "9a03a3ef-2575-4b29-a715-358106a904f4";
                    $transaction_type = "debit";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "p24";
                    break;
                case 'skrill':
                case 'mbkr':
                    $merchant_account_id = "45491d10-15c7-4f4c-b95f-d54b0fb7e7a3";
                    $secret_key = "e9531d9d-fa88-47f9-aa5d-bcfd5f6bbc9e";
                    $transaction_type = "debit";
                    $descriptor = "test";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "skrill";
                    break;
                case 'sofortbanking':
                case 'sofort':
                    $merchant_account_id = "f19d17a2-01ae-11e2-9085-005056a96a54";
                    $secret_key = "ad39d9d9-2712-4abd-9016-cdeb60dc3c8f";
                    $transaction_type = "debit";
                    $descriptor = "test";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "sofortbanking";
                    break;
                case 'trustly':
                    $merchant_account_id = "pending";
                    $secret_key = "";
                    $transaction_type = "purchase";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "trustly";
                    break;
                case 'trustpay':
                    $merchant_account_id = "fe6c560b-5f28-4e0a-9bde-cee067f97ed6";
                    $secret_key = "79030da5-284e-4924-9576-a1ae3c8b4aad";
                    $transaction_type = "debit";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "trustpay";
                    break;
                case 'paysafecard':
                case 'psc':
                    $merchant_account_id = "4c0de18e-4c20-40a7-a5d8-5178f0fe95bd";
                    $secret_key = "bb1f2975-827b-4aa8-bec6-405191d85fa5";
                    $transaction_type = "debit";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "paysafecard";
                    break;
                case 'creditcard': case 'visa': case 'visa3d': case 'maestro': case 'mastercard': case 'mastercardsecure': case 'maestrosecure':
                    if($is_3d_card == false) {
                        $merchant_account_id = "1b3be510-a992-48aa-8af9-6ba4c368a0ac";
                        $secret_key = "33a67608-9822-43c2-acc1-faf2947b1be5";
                        //$merchant_account_id = "33f6d473-3036-4ca5-acb5-8c64dac862d1";
                        //$secret_key = "9e0130f6-2e1e-4185-b0d5-dc69079c75cc";
                        $transaction_type = "purchase";
                        $descriptor = "";
                        $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                        $wirecard_payment_rest_username = "70000-APIDEMO-CARD";
                        $wirecard_payment_rest_password = "ohysS0-dvfMx";
                        $wirecard_payment_method = "creditcard";
                    }else{
                        $merchant_account_id = "33f6d473-3036-4ca5-acb5-8c64dac862d1";
                        $secret_key = "9e0130f6-2e1e-4185-b0d5-dc69079c75cc";
                        $transaction_type = "purchase";
                        $descriptor = "";
                        $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                        $wirecard_payment_rest_username = "70000-APILUHN-CARD";
                        $wirecard_payment_rest_password = "8mhwavKVb91T";
                        $wirecard_payment_method = "creditcard";
                    }
                    break;
                default:
                    if($is_3d_card == false) {
                        $merchant_account_id = "1b3be510-a992-48aa-8af9-6ba4c368a0ac";
                        $secret_key = "33a67608-9822-43c2-acc1-faf2947b1be5";
                        //$merchant_account_id = "33f6d473-3036-4ca5-acb5-8c64dac862d1";
                        //$secret_key = "9e0130f6-2e1e-4185-b0d5-dc69079c75cc";
                        $transaction_type = "purchase";
                        $descriptor = "";
                        $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                        $wirecard_payment_rest_username = "70000-APIDEMO-CARD";
                        $wirecard_payment_rest_password = "ohysS0-dvfMx";
                        $wirecard_payment_method = "creditcard";
                    }else{
                        $merchant_account_id = "33f6d473-3036-4ca5-acb5-8c64dac862d1";
                        $secret_key = "9e0130f6-2e1e-4185-b0d5-dc69079c75cc";
                        $transaction_type = "purchase";
                        $descriptor = "";
                        $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                        $wirecard_payment_rest_username = "70000-APILUHN-CARD";
                        $wirecard_payment_rest_password = "8mhwavKVb91T";
                        $wirecard_payment_method = "creditcard";
                    }
            }

        }else {
            switch(strtolower($payment_method)){
                case 'sofortbanking':
                case 'sofort':
                    $merchant_account_id = $config->wirecardMerchantAccountIdSofort;
                    $secret_key = $config->wirecardSecretKeySofort;
                    $transaction_type = "debit";
                    $descriptor = "test";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "sofortbanking";
                    break;
                case 'creditcard': case 'visa': case 'visa3d': case 'maestro': case 'mastercard': case 'mastercardsecure': case 'maestrosecure':
                    if($is_3d_card == false) {
                        $merchant_account_id = $config->wirecardMerchantAccountIdWdbEE;
                        $secret_key = $config->wirecardSecretKeyWdbEE;
                        $transaction_type = "purchase";
                        $descriptor = "";
                        $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                        $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                        $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                        $wirecard_payment_method = "creditcard";
                    }else{
                        $merchant_account_id = $config->wirecardMerchantAccountIdWdb3DEE;
                        $secret_key = $config->wirecardSecretKeyWdb3DEE;
                        $transaction_type = "purchase";
                        $descriptor = "";
                        $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                        $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                        $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                        $wirecard_payment_method = "creditcard";
                    }
                    break;
                default:
                    if($is_3d_card == false) {
                        $merchant_account_id = $config->wirecardMerchantAccountIdWdbEE;
                        $secret_key = $config->wirecardSecretKeyWdbEE;
                        $transaction_type = "purchase";
                        $descriptor = "";
                        $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                        $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                        $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                        $wirecard_payment_method = "creditcard";
                    }else{
                        $merchant_account_id = $config->wirecardMerchantAccountIdWdb3DEE;
                        $secret_key = $config->wirecardSecretKeyWdb3DEE;
                        $transaction_type = "purchase";
                        $descriptor = "";
                        $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                        $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                        $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                        $wirecard_payment_method = "creditcard";
                    }
            }
        }
        return array(
            "merchant_account_id"=>$merchant_account_id,
            "secret_key"=>$secret_key,
            "transaction_type"=>$transaction_type,
            "descriptor"=>$descriptor,
            "wirecard_payment_rest_url"=>$wirecard_payment_rest_url,
            "wirecard_payment_rest_username"=>$wirecard_payment_rest_username,
            "wirecard_payment_rest_password"=>$wirecard_payment_rest_password,
            "wirecard_payment_method"=>$wirecard_payment_method
        );
    }

    public static function getAccountSpecificDataFromMerchantAccountId($test_merchant_account_id){
        $config = Zend_Registry::get('config');
        if($config->wirecardTestMode == "true"){
            switch($test_merchant_account_id){
                case "adb45327-170a-460b-9810-9008e9772f5f":
                    $merchant_account_id = "adb45327-170a-460b-9810-9008e9772f5f";
                    $secret_key = "1b9e63b4-c132-42c3-bcbd-2d2e47ae7154";
                    $transaction_type = "debit";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "ideal";

                    break;
                case "27183130-2a8e-47ff-84ab-25d12362e843":
                    $merchant_account_id = "27183130-2a8e-47ff-84ab-25d12362e843";
                    $secret_key = "9a03a3ef-2575-4b29-a715-358106a904f4";
                    $transaction_type = "debit";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "p24";
                    break;
                case "45491d10-15c7-4f4c-b95f-d54b0fb7e7a3":
                    $merchant_account_id = "45491d10-15c7-4f4c-b95f-d54b0fb7e7a3";
                    $secret_key = "e9531d9d-fa88-47f9-aa5d-bcfd5f6bbc9e";
                    $transaction_type = "debit";
                    $descriptor = "test";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "skrill";
                    break;
                case "f19d17a2-01ae-11e2-9085-005056a96a54":
                    $merchant_account_id = "f19d17a2-01ae-11e2-9085-005056a96a54";
                    $secret_key = "ad39d9d9-2712-4abd-9016-cdeb60dc3c8f";
                    $transaction_type = "debit";
                    $descriptor = "test";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "sofortbanking";
                    break;
                case "pending":
                    $merchant_account_id = "pending";
                    $secret_key = "";
                    $transaction_type = "purchase";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "trustly";
                    break;
                case "fe6c560b-5f28-4e0a-9bde-cee067f97ed6":
                    $merchant_account_id = "fe6c560b-5f28-4e0a-9bde-cee067f97ed6";
                    $secret_key = "79030da5-284e-4924-9576-a1ae3c8b4aad";
                    $transaction_type = "debit";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "trustpay";
                    break;
                case "4c0de18e-4c20-40a7-a5d8-5178f0fe95bd":
                    $merchant_account_id = "4c0de18e-4c20-40a7-a5d8-5178f0fe95bd";
                    $secret_key = "bb1f2975-827b-4aa8-bec6-405191d85fa5";
                    $transaction_type = "debit";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "paysafecard";
                    break;
                case "1b3be510-a992-48aa-8af9-6ba4c368a0ac":
                    $merchant_account_id = "1b3be510-a992-48aa-8af9-6ba4c368a0ac";
                    $secret_key = "33a67608-9822-43c2-acc1-faf2947b1be5";
                    //$merchant_account_id = "33f6d473-3036-4ca5-acb5-8c64dac862d1";
                    //$secret_key = "9e0130f6-2e1e-4185-b0d5-dc69079c75cc";
                    $transaction_type = "purchase";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = "70000-APIDEMO-CARD";
                    $wirecard_payment_rest_password = "ohysS0-dvfMx";
                    $wirecard_payment_method = "creditcard";
                    break;
                case "33f6d473-3036-4ca5-acb5-8c64dac862d1":
                    $merchant_account_id = "33f6d473-3036-4ca5-acb5-8c64dac862d1";
                    $secret_key = "9e0130f6-2e1e-4185-b0d5-dc69079c75cc";
                    $transaction_type = "purchase";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = "70000-APILUHN-CARD";
                    $wirecard_payment_rest_password = "8mhwavKVb91T";
                    $wirecard_payment_method = "creditcard";
                    break;
                default:
                    $merchant_account_id = "1b3be510-a992-48aa-8af9-6ba4c368a0ac";
                    $secret_key = "33a67608-9822-43c2-acc1-faf2947b1be5";
                    //$merchant_account_id = "33f6d473-3036-4ca5-acb5-8c64dac862d1";
                    //$secret_key = "9e0130f6-2e1e-4185-b0d5-dc69079c75cc";
                    $transaction_type = "purchase";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = "70000-APIDEMO-CARD";
                    $wirecard_payment_rest_password = "ohysS0-dvfMx";
                    $wirecard_payment_method = "creditcard";

            }

        }else {
            switch($test_merchant_account_id){
                case $config->wirecardMerchantAccountIdSofort:
                    $merchant_account_id = $config->wirecardMerchantAccountIdSofort;
                    $secret_key = $config->wirecardSecretKeySofort;
                    $transaction_type = "debit";
                    $descriptor = "test";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "sofortbanking";
                    break;
                case $config->wirecardMerchantAccountIdWdbEE:
                    $merchant_account_id = $config->wirecardMerchantAccountIdWdbEE;
                    $secret_key = $config->wirecardSecretKeyWdbEE;
                    $transaction_type = "purchase";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "creditcard";
                    break;
                case $config->wirecardMerchantAccountIdWdb3DEE:
                    $merchant_account_id = $config->wirecardMerchantAccountIdWdb3DEE;
                    $secret_key = $config->wirecardSecretKeyWdb3DEE;
                    $transaction_type = "purchase";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "creditcard";
                    break;
                default:
                    $merchant_account_id = $config->wirecardMerchantAccountIdWdbEE;
                    $secret_key = $config->wirecardSecretKeyWdbEE;
                    $transaction_type = "purchase";
                    $descriptor = "";
                    $wirecard_payment_rest_url = $config->wirecardPaymentRestUrl;
                    $wirecard_payment_rest_username = $config->wirecardPaymentRestUsername;
                    $wirecard_payment_rest_password = $config->wirecardPaymentRestPassword;
                    $wirecard_payment_method = "creditcard";
            }
        }
        return array(
            "merchant_account_id"=>$merchant_account_id,
            "secret_key"=>$secret_key,
            "transaction_type"=>$transaction_type,
            "descriptor"=>$descriptor,
            "wirecard_payment_rest_url"=>$wirecard_payment_rest_url,
            "wirecard_payment_rest_username"=>$wirecard_payment_rest_username,
            "wirecard_payment_rest_password"=>$wirecard_payment_rest_password,
            "wirecard_payment_method"=>$wirecard_payment_method
        );
    }

    /**
	* extract data from received array for Wirecard payment provider
    * @param array $resultsArray
    * @param string $resultsString
	* @return mixed
	*/
	public static function validatePurchaseResponse($resultsArray, $resultsString) {
		try {
			$amount = doubleval(trim(number_format((string)$resultsArray['requested_amount'], 2, '.', '')));
            $merchant_account_id = trim($resultsArray['merchant_account_id']);
			$wirecard_transaction_id = trim($resultsArray['transaction_id']);
			// returns currency code 978 instead of currency text EUR
			$currency_text = trim($resultsArray['requested_amount_currency']);
			//authorization code TEST in test reg.
			$authorization_code = trim($resultsArray['authorization_code']);
			//EXTENDED DATA
			//credit card number 444444,4444
			$masked_account_number = trim($resultsArray['masked_account_number']);
			//credit card expiry date, 12/2013
			$card_expiry_date = '';
			//first name and last name of client
			$card_holder_name = $resultsArray['first_name'] . ' ' . $resultsArray['last_name'];
            $email_address = $resultsArray['email'];
			//Payment method code, PTEST
            $card_type = '';
            //Reccuring token id  - 4242796444090018
            $token_id = $resultsArray['token_id'];
            //completion timestamp - 20170623090600
            $completion_timestamp = $resultsArray['completion_time_stamp'];
            //used locale - en
            $locale = $resultsArray['locale'];
            //first name
            $first_name = $resultsArray['first_name'];
            $last_name = $resultsArray['last_name'];
            $ip_address = $resultsArray['ip_address'];
            $transaction_type = $resultsArray['transaction_type'];
            $transaction_state = $resultsArray['transaction_state'];
            $status_code_1 = $resultsArray['status_code_1'];
            $status_description_1 = $resultsArray['status_description_1'];
            $status_code_2 = $resultsArray['status_code_2'];
            $status_description_2 = $resultsArray['status_description_2'];
            $status_code_3 = $resultsArray['status_code_2'];
            $status_description_3 = $resultsArray['status_description_2'];


            $udf1 = trim($resultsArray['field_value_1']);
            if($resultsArray['field_name_1'] == "field_value_1"){
                $udf1 = trim($resultsArray['field_value_1']);
            }else if($resultsArray['field_name_1'] == "field_value_2"){
                $udf1 = trim($resultsArray['field_value_2']);
            }else if($resultsArray['field_name_1'] == "field_value_3"){
                $udf1 = trim($resultsArray['field_value_3']);
            }else if($resultsArray['field_name_1'] == "field_value_4"){
                $udf1 = trim($resultsArray['field_value_4']);
            }

            $udf2 = trim($resultsArray['field_value_2']);
            if($resultsArray['field_name_2'] == "field_value_1"){
                $udf2 = trim($resultsArray['field_value_1']);
            }else if($resultsArray['field_name_2'] == "field_value_2"){
                $udf2 = trim($resultsArray['field_value_2']);
            }else if($resultsArray['field_name_2'] == "field_value_3"){
                $udf2 = trim($resultsArray['field_value_3']);
            }else if($resultsArray['field_name_2'] == "field_value_4"){
                $udf2 = trim($resultsArray['field_value_4']);
            }

            $udf3 = trim($resultsArray['field_value_3']);
            if($resultsArray['field_name_3'] == "field_value_1"){
                $udf3 = trim($resultsArray['field_value_1']);
            }else if($resultsArray['field_name_3'] == "field_value_2"){
                $udf3 = trim($resultsArray['field_value_2']);
            }else if($resultsArray['field_name_3'] == "field_value_3"){
                $udf3 = trim($resultsArray['field_value_3']);
            }else if($resultsArray['field_name_3'] == "field_value_4"){
                $udf3 = trim($resultsArray['field_value_4']);
            }

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
            $currency = (string)$csv_array_1_3[1];
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

            $card_country = "";
			$transaction_date = substr($completion_timestamp, 0, 4) . "-" . substr($completion_timestamp, 4, 2) . "-" . substr($completion_timestamp, 6, 2) . " " . substr($completion_timestamp, 8, 2) . ":" . substr($completion_timestamp, 10, 2) . ":" . substr($completion_timestamp, 12, 2);
			$bank_code = "";
			$card_issuer_bank = "";
			$card_country_ip = "";

			//END EXTENDED DATA
			//get currency text from currency code (from 978 to get EUR)
			require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
			$helperCurrencyList = new CurrencyListHelper();
            $currency_code = $helperCurrencyList->getCurrencyCode($currency_text);

			$access_message = "WIRECARD PURCHASE RESPONSE> WIRECARD TRANSACTION ID: {$wirecard_transaction_id} AMOUNT: {$amount} CURRENCY CODE: {$currency_code}
			 CURRENCY TEXT: {$currency_text} MASKED CARD NUMBER: {$masked_account_number} CARD EXPIRY DATE: {$card_expiry_date}
			 CARD HOLDER NAME: {$card_holder_name} PAYMENT METHOD CODE: {$payment_method_code} CARD COUNTRY: {$card_country} CARD TYPE: {$card_type}
			 WEB SITE DOMAIN: {$site_domain} PC SESSION ID (UDF1): {$pc_session_id}
			 OREF TRANSACTION ID (UDF2): {$oref_transaction_id} TRANSACTION LIMIT (UDF2): {$over_limit} PLAYER ID (UDF2): {$player_id} BONUS CODE (UDF2): {$bonus_code}
			 TRANSACTION ID (UDF3): {$transaction_id} FEE AMOUNT (UDF3): {$fee_amount} DEPOSIT AMOUNT (UDF3): {$deposit_amount} DB TRANSACTION ID (UDF3): {$db_transaction_id}
			 MERCHANT ACCOUNT ID: {$merchant_account_id} TOKEN_ID: {$token_id} TRANSACTION TYPE: {$transaction_type} TRANSACTION STATE: {$transaction_state}
			 LOCALE: {$locale} FIRST NAME: {$first_name} LAST NAME: {$last_name} STATUS_CODE_1: {$status_code_1} STATUS_DESCRIPTION_1: {$status_description_1}
			 STATUS_CODE_2: {$status_code_2} STATUS_DESCRIPTION_2: {$status_description_2} STATUS_CODE_3: {$status_code_3} STATUS_DESCRIPTION_3: {$status_description_3}
			 <END STATUS URL PARAMS PURCHASE";
       WirecardErrorHelper::wirecardAccessLog("WirecardMerchantManagerHelper::validatePurchaseResponse: {$resultsString}");
			 WirecardErrorHelper::wirecardAccessLog("WirecardMerchantManagerHelper::validatePurchaseResponse: {$access_message}");

			//pass data required to confirm transaction in database
			return array(
                "status"=>OK,
                "pc_session_id"=>$pc_session_id,
                "transaction_id"=>$transaction_id,
                "amount"=>$amount,
			    "wirecard_transaction_id"=>$wirecard_transaction_id,
                "currency_text"=>$currency_text,
                "currency_code"=>$currency_code,
                "credit_card_number"=>$masked_account_number,
			    "credit_card_date_expires"=>$card_expiry_date,
                "credit_card_holder"=>$card_holder_name,
                "credit_card_country"=>$card_country,
			    "credit_card_type"=>$card_type,
                "start_time"=>$transaction_date,
                "bank_code"=>$bank_code,
                "ip_address"=>$ip_address,
			    "card_issuer_bank"=>$card_issuer_bank,
                "card_country_ip"=>$card_country_ip,
                "client_email"=>$email_address,
                "over_limit"=>$over_limit,
			    "bank_auth_code"=>$authorization_code,
                "payment_method_code"=>$payment_method_code,
                "payment_method_id"=>$payment_method_id,
                "merchant_order_ref_number"=>$oref_transaction_id,
                "site_domain"=>$site_domain,
			    "bonus_code"=>$bonus_code,
                "fee_amount"=>$fee_amount,
                "player_basic_deposit_amount"=>$deposit_amount,
                "player_id"=>$player_id,
                "db_transaction_id"=>$db_transaction_id,
                "merchant_account_id"=>$merchant_account_id,
                "token_id"=>$token_id,

                "locale" => $locale,
                "first_name" => $first_name,
                "last_name" => $last_name,
                "transaction_type" => $transaction_type,
                "transaction_state" => $transaction_state,
                "status_code_1" => $status_code_1,
                "status_description_1" => $status_description_1,
                "status_code_2" => $status_code_2,
                "status_description_2" => $status_description_2,
                "status_code_3" => $status_code_3,
                "status_description_3" => $status_description_3
            );
		} catch (Zend_Exception $ex) {
			$message = "WirecardMerchantHelper::validatePurchaseResponse method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
		    WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	* extract data from received array for Wirecard payment provider
    * @param array $resultsArray
    * @param string $resultsString
	* @return mixed
	*/
	public static function validatePayoutResponse($resultsArray, $resultsString) {
		try {
			$amount = doubleval(trim(number_format((string)$resultsArray['requested_amount'], 2, '.', '')));
            $merchant_account_id = trim($resultsArray['merchant_account_id']);
			$wirecard_transaction_id = trim($resultsArray['transaction_id']);
			// returns currency code 978 instead of currency text EUR
			$currency_text = trim($resultsArray['requested_amount_currency']);
			//authorization code TEST in test reg.
			$authorization_code = trim($resultsArray['authorization_code']);
			//EXTENDED DATA
			//credit card number 444444,4444
			$masked_account_number = trim($resultsArray['masked_account_number']);
			//credit card expiry date, 12/2013
			$card_expiry_date = '';
			//first name and last name of client
			$card_holder_name = $resultsArray['first_name'] . ' ' . $resultsArray['last_name'];
            $email_address = $resultsArray['email'];
			//acquirer code, PTEST
			$acquirer_code = '';
			//Payment method code, PTEST
            $card_type = '';

            $udf1 = trim($resultsArray['field_value_1']);
            if($resultsArray['field_name_1'] == "field_value_1"){
                $udf1 = trim($resultsArray['field_value_1']);
            }else if($resultsArray['field_name_1'] == "field_value_2"){
                $udf1 = trim($resultsArray['field_value_2']);
            }else if($resultsArray['field_name_1'] == "field_value_3"){
                $udf1 = trim($resultsArray['field_value_3']);
            }else if($resultsArray['field_name_1'] == "field_value_4"){
                $udf1 = trim($resultsArray['field_value_4']);
            }

            $udf2 = trim($resultsArray['field_value_2']);
            if($resultsArray['field_name_2'] == "field_value_1"){
                $udf2 = trim($resultsArray['field_value_1']);
            }else if($resultsArray['field_name_2'] == "field_value_2"){
                $udf2 = trim($resultsArray['field_value_2']);
            }else if($resultsArray['field_name_2'] == "field_value_3"){
                $udf2 = trim($resultsArray['field_value_3']);
            }else if($resultsArray['field_name_2'] == "field_value_4"){
                $udf2 = trim($resultsArray['field_value_4']);
            }
			
			$udf3 = trim($resultsArray['field_value_3']);
            if($resultsArray['field_name_3'] == "field_value_1"){
                $udf3 = trim($resultsArray['field_value_1']);
            }else if($resultsArray['field_name_3'] == "field_value_2"){
                $udf3 = trim($resultsArray['field_value_2']);
            }else if($resultsArray['field_name_3'] == "field_value_3"){
                $udf3 = trim($resultsArray['field_value_3']);
            }else if($resultsArray['field_name_3'] == "field_value_4"){
                $udf3 = trim($resultsArray['field_value_4']);
            }

			//EXTRACT UDF1
			//contains backoffice_session_id, currency as text, casino_name
			$csv_array1 = explode(";", $udf1);
            //extract backoffice_session_id_
            $csv_array_1_0 = explode("=", $csv_array1[0]);      //BACKOFFICE_SESSION_ID
			$backoffice_session_id = (string)$csv_array_1_0[1];
            //extract currency
			$csv_array_1_1 = explode("=", $csv_array1[1]);      //CURRENCY
			$currency = (int)$csv_array_1_1[1];
            //extract casino_name
			$csv_array_1_2 = explode("=", $csv_array1[2]);      //CASINO_NAME
			$casino_name = (string)$csv_array_1_2[1];


			//EXTRACT UDF2
			//contains csv array of values: transaction limit if is checked here AND player_id AND merchant order reference number site domain AND bonus campaign code separated with ;
			$csv_array2 = explode(";", $udf2);
			//withdraw_request_id
			$csv_array_2_0 = explode("=", $csv_array2[0]); //WITHDRAW_REQUEST_ID
			$withdraw_request_id = $csv_array_2_0[1];
			//unique player id in our system
			$csv_array_2_1 = explode("=", $csv_array2[1]); //PLAYER_ID
			$player_id = $csv_array_2_1[1];
			//transaction_id_old
			$csv_array_2_2 = explode("=", $csv_array2[2]); //TRANSACTION ID OLD
			$transaction_id_old = $csv_array_2_2[1];
			//payment_method
			$csv_array_2_3 = explode("=", $csv_array2[3]); //PAYMENT METHOD
			$payment_method = $csv_array_2_3[1];
            //oref_transaction_id
			$csv_array_2_4 = explode("=", $csv_array2[4]); //OREF_TRANSACTION_ID
			$oref_transaction_id = $csv_array_2_4[1];
            //fee_amount
			$csv_array_2_5 = explode("=", $csv_array2[5]); //FEE_AMOUNT
			$fee_amount = $csv_array_2_5[1];
			
			
			//EXTRACT UDF3
			//contains backoffice_session_id, currency as text, casino_name
			$csv_array3 = explode(";", $udf3);
            //extract player username
            $csv_array_3_0 = explode("=", $csv_array3[0]);      //PLAYER USERNAME
			$player_username = (string)$csv_array_3_0[1];
            //extract casino_name
			$csv_array_3_1 = explode("=", $csv_array3[1]);      //CASINO NAME
			$casino_name2 = (int)$csv_array_3_1[1];
            //extract player_id
			$csv_array_3_2 = explode("=", $csv_array3[2]);      //PLAYER ID
			$player_id2 = (string)$csv_array_3_2[1];
			//extract player_first_name
			$csv_array_3_3 = explode("=", $csv_array3[3]);      //PLAYER FIRST NAME
			$player_first_name = (string)$csv_array_3_3[1];
			//extract player_last_name
			$csv_array_3_4 = explode("=", $csv_array3[4]);      //PLAYER LAST NAME
			$player_last_name = (string)$csv_array_3_4[1];

			//END EXTENDED DATA
			//get currency text from currency code (from 978 to get EUR)
			require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
			$helperCurrencyList = new CurrencyListHelper();
            $currency_code = $helperCurrencyList->getCurrencyCode($currency_text);

			$access_message = "STATUS URL PARAMS PAYOUT> WIRECARD TRANSACTION ID: {$wirecard_transaction_id} OREF TRANSACTION ID: {$oref_transaction_id}
	        Backoffice_Session_ID, Currency, Casino_Name (csv values inside UDF1): {$udf1} Withdraw_Request_Id, Player Id, Transaction_Id_Old, Payment_Method, Oref_Transaction_Id, Fee Amount (csv values inside UDF2): {$udf2}
			Player Username, Casino Name, Player ID, Player First Name, Player Last Name (csv values inside UDF3): {$udf3}
	        AMOUNT: {$amount} CURRENCY_CODE: {$currency_code} CURRENCY_TEXT: {$currency_text} <END STATUS URL PARAMS PAYOUT";
			WirecardErrorHelper::wirecardAccessLog("WirecardMerchantManagerPayout::validatePayoutResponse resultsString: {$resultsString}");
			WirecardErrorHelper::wirecardAccessLog("WirecardMerchantManagerPayout::validatePayoutResponse PARSED RESPONSE: {$access_message}");

            $completion_time_stamp = $resultsArray['completion_time_stamp']; //20170616083744
            $transaction_date = $completion_time_stamp;
            $bank_code = "";
            $user_ip_address = $resultsArray['ip_address'];
            $card_issuer_bank = "";
            $card_country_ip = "";

			//pass data required to confirm transaction in database
            return array("status"=>OK,
                "backoffice_session_id"=>$backoffice_session_id,
                "transaction_id_old"=>$transaction_id_old,
                "amount"=>$amount,
				"wirecard_transaction_id"=>$wirecard_transaction_id,
                "currency_code"=>$currency_code,
                "currency_text"=>$currency_text,
                "credit_card_number"=>$masked_account_number,
				"credit_card_date_expires"=>$card_expiry_date,
                "credit_card_holder"=>$card_holder_name,
                "credit_card_country"=>"",
				"credit_card_type"=>$card_type,
                "start_time"=>$transaction_date,
                "bank_code"=>$bank_code,
                "ip_address"=>$user_ip_address,
				"card_issuer_bank"=>$card_issuer_bank,
                "card_country_ip"=>$card_country_ip,
                "client_email"=>$email_address,
                "withdraw_request_id"=>$withdraw_request_id,
                "bank_auth_code"=>$authorization_code,
				"merchant_order_ref_number"=>$oref_transaction_id,
                "player_id"=>$player_id,
                "payment_method"=>$payment_method,
                "casino_name"=>$casino_name,
                "fee_amount"=>doubleval($fee_amount),
                "merchant_account_id"=>$merchant_account_id
            );
		} catch (Zend_Exception $ex) {
			$message = "MerchantManagerPurchase::validatePurchaseResponse method exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
      WirecardErrorHelper::wirecardError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
}
