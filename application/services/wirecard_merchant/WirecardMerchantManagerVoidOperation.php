<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'WirecardErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Merchant manager to perform transaction processing from Wirecard payment provider ...
 * Cancels purchase and original credit (payout) of player transactions
 */
class WirecardMerchantManagerVoidOperation {

    private $DEBUG_VOID_PURCHASE = false;
    private $DEBUG_VOID_CREDIT = false;

    /**
     * @param $resultsArray
     * @return array
     * @throws Zend_Exception
     */
	public function sendWirecardVoidPurchase($resultsArray){
        $resultsString = print_r($resultsArray, true);
        $config = Zend_Registry::get("config");
        if($config->wirecardDoVoidPurchaseOperation == "true") {
            try {

                $unpackedWirecardData = WirecardMerchantHelper::validatePurchaseResponse($resultsArray, $resultsString);

                $amount = $unpackedWirecardData['amount'];
                $currency_text = $unpackedWirecardData['currency_text'];
                $currency_code = $unpackedWirecardData['currency_code'];
                $casino_name = $unpackedWirecardData['site_domain'];
                $wirecard_transaction_id = $unpackedWirecardData['wirecard_transaction_id'];

                $is_3d_card = WirecardMerchantHelper::isSecureCard($unpackedWirecardData['payment_method_code']);

                $account = WirecardMerchantHelper::getAccountSpecificDataPerPaymentMethod(in_array($unpackedWirecardData['payment_method_code'], $is_3d_card));
                $wirecard_payment_rest_url = $account['wirecard_payment_rest_url'];
                $wirecard_payment_rest_username = $account['wirecard_payment_rest_username'];
                $wirecard_payment_rest_password = $account['wirecard_payment_rest_password'];

                $merchant_account_id = $account['merchant_account_id'];

                date_default_timezone_set("UTC");
                $request_time_stamp = gmdate("Y") . gmdate("m") . gmdate("d") . gmdate("H") . gmdate("i") . gmdate("s");
                $request_id = substr(hash('sha256', trim($unpackedWirecardData['merchant_order_ref_number'] . $request_time_stamp)), 0, 32);
                $transaction_type = 'void-purchase';
                $parent_transaction_id = $unpackedWirecardData['wirecard_transaction_id'];
                $ip_address = $unpackedWirecardData['ip_address'];

                $fields = array(
                    'merchant_account_id'=> urlencode($merchant_account_id),
                    'request_id'=>urlencode($request_id),
                    'transaction_type'=>urlencode($transaction_type),
                    'parent_transaction_id'=>urlencode($parent_transaction_id),
                    'payment_ip_address'=>urlencode($ip_address)
                );
                $fields_string = "";
                foreach ($fields as $key => $value) {
                    $fields_string .= $key . '=' . $value . '&';
                }
                rtrim($fields_string, '&');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $wirecard_payment_rest_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                curl_setopt($ch, CURLOPT_POST, count($fields));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                //disable ssl verification
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, "$wirecard_payment_rest_username:$wirecard_payment_rest_password");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Connection: keep-alive'
                ));
                $data = curl_exec($ch);
                if (curl_errno($ch)) {
                    //there was an error sending post to void purchase player's transaction
                    $error_message = curl_error($ch);
                    $message = "WirecardMerchantManagerVoidOperation::sendWirecardVoidPurchase - send Wirecard void purchase method. <br />
                    Error in canceling purchase command for Wirecard transaction. <br />
                    Must be canceled manually through Wirecard. <br />
                    Transaction was not confirmed in database. <br />
                    AMOUNT: {$amount} <br />
                    CURRENCY TEXT: {$currency_text} <br />
                    CURRENCY CODE: {$currency_code} <br />
                    CASINO NAME: {$casino_name} <br />
                    WIRECARD TRANSACTION ID: {$wirecard_transaction_id} <br />
                    Exception message: <br /> {$error_message}";
                    WirecardErrorHelper::wirecardError($message, $message);
                    return array("status" => NOK);
                } else {
                    //players void purchase was success
                    curl_close($ch);
                    $message = "WirecardMerchantManagerVoidOperation::sendWirecardVoidPurchase - send Wirecard void purchase method. <br />
                    Purchase Wirecard transaction from player was successfully aborted. <br />
                    Player's transaction was not confirmed in database and his Wirecard purchase is revoked. <br />
                    This aborted transaction must be verfied with Wirecard to have been revoked. <br />
                    AMOUNT: {$amount} <br />
                    CURRENCY TEXT: {$currency_text} <br />
                    CURRENCY CODE: {$currency_code} <br />
                    CASINO NAME: {$casino_name} <br />
                    WIRECARD TRANSACTION ID: {$wirecard_transaction_id}";
                    WirecardErrorHelper::wirecardAccess($message, $message);
                    return array("status" => OK);
                }
            } catch (Zend_Exception $ex) {
                //there was an error in players void purchase transaction
                $message = "WirecardMerchantManagerVoidOperation::sendWirecardVoidPurchase - send Wirecard void purchase method. <br />
                Exception occured in canceling purchase command for Wirecard transaction. <br />
                Must be canceled manually through Wirecard. <br />
                Transaction was not confirmed in database. <br />
                resultString = {$resultsString} <br />
                Exception message: <br /> {$ex->getMessage()}";
                WirecardErrorHelper::wirecardError($message, $message);
                return array("status" => NOK);
            }
        }else{
            if($this->DEBUG_VOID_PURCHASE){
                //DEBUG HERE
                $message = $resultsString;
                WirecardErrorHelper::wirecardAccess($message, $message);
            }
            return array("status" => OK);
        }
	}

    /**
     * @param $resultsArray
     * @return array
     * @throws Zend_Exception
     */
	public function sendWirecardVoidCredit($resultsArray){
        //return array("status" => NOK, "error_message" => "Cannot obtain void credit XML message !");
		$backoffice_session_id = trim(strip_tags($resultsArray['backoffice_session_id']));
		$amount = trim(strip_tags($resultsArray['amount']));
		$currency_text = trim(strip_tags($resultsArray['currency_text']));
		$currency_code = trim(strip_tags($resultsArray['currency_code']));
		$oref_transaction_id = trim(strip_tags($resultsArray['oref_transaction_id']));
		$wirecard_transaction_id = trim(strip_tags($resultsArray['wirecard_transaction_id']));
        $payment_method = trim(strip_tags($resultsArray['payment_method']));
        $db_transaction_id = trim(strip_tags($resultsArray['db_transaction_id']));
        $transaction_type = "void-credit";
        $payment_ip_address = trim(strip_tags($resultsArray['ip_address']));
        $config = Zend_Registry::get("config");
        if($config->wirecardDoVoidCreditOperation == "true") {
            try {
                $account = WirecardMerchantHelper::getAccountSpecificDataPerPaymentMethod($payment_method);
                $wirecard_payment_rest_url = $account['wirecard_payment_rest_url'];
                $wirecard_payment_rest_username = $account['wirecard_payment_rest_username'];
                $wirecard_payment_rest_password = $account['wirecard_payment_rest_password'];
                $merchant_account_id = $account['merchant_account_id'];

                date_default_timezone_set("UTC");
                $request_time_stamp = gmdate("Y") . gmdate("m") . gmdate("d") . gmdate("H") . gmdate("i") . gmdate("s");
                $request_id = substr(hash('sha256', trim($db_transaction_id . $request_time_stamp)), 0, 32);

                $fields = array(
                    'merchant_account_id' => urlencode($merchant_account_id),
                    'request_id' => urlencode($request_id),
                    'transaction_type' => urlencode($transaction_type),
                    'parent_transaction_id' => urlencode($wirecard_transaction_id),
                    'payment_ip_address' => urlencode($payment_ip_address),
                );
                $fields_string = "";
                foreach ($fields as $key => $value) {
                    $fields_string .= $key . '=' . $value . '&';
                }
                rtrim($fields_string, '&');
                //start post init to WIRECARD payment page
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $wirecard_payment_rest_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                curl_setopt($ch, CURLOPT_POST, count($fields));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                //disable ssl verification
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, "$wirecard_payment_rest_username:$wirecard_payment_rest_password");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Connection: keep-alive'
                ));
                $data = curl_exec($ch);
                if (curl_errno($ch)) {
                    //there was an error sending post to void credit player's transaction
                    $error_message = curl_error($ch);
                    $message = "WirecardMerchantManagerVoidOperation::sendWirecardVoidCredit - send Wirecard void credit method. Error in canceling payout command for Wirecard transaction.
                    <br />Transaction was not confirmed in database.
                    <br />AMOUNT: {$amount}
                    <br />CURRENCY TEXT: {$currency_text}
                    <br />CURRENCY CODE: {$currency_code}
                    <br />OREF TRANSACTION ID: {$oref_transaction_id}
                    <br />WIRECARD TRANSACTION ID: {$wirecard_transaction_id}
                    <br />Exception message: <br /> {$error_message}";
                    WirecardErrorHelper::wirecardError($message, $message);
                    return array("status" => NOK);
                } else {
                    //players void credit was success
                    curl_close($ch);
                    $message = "WirecardMerchantManagerVoidOperation::sendWirecardVoidCredit - send Wirecard void credit method.
                    <br />Player's transaction was not confirmed in database and his Wirecard payout is revoked.
                    <br />AMOUNT: {$amount}
                    <br />CURRENCY TEXT: {$currency_text}
                    <br />CURRENCY CODE: {$currency_code}
                    <br />OREF TRANSACTION ID: {$oref_transaction_id}
                    <br />WIRECARD TRANSACTION ID: {$wirecard_transaction_id}";
                    WirecardErrorHelper::wirecardAccessLog($message);
                    return array("status" => OK);
                }
            } catch (Zend_Exception $ex) {
                //there was an error in players void credit transaction
                $message = "WirecardMerchantManagerVoidOperation::sendWirecardVoidCredit - send Wirecard void credit method.
                <br />Exception occured in canceling payout command for Wirecard transaction.
                <br />Transaction was not confirmed in database.
                <br />AMOUNT: {$amount}
                <br />CURRENCY TEXT: {$currency_text}
                <br />CURRENCY CODE: {$currency_code}
                <br />OREF TRANSACTION ID: {$oref_transaction_id}
                <br />WIRECARD TRANSACTION ID: {$wirecard_transaction_id}
                <br />Exception message:
                <br /> {$ex->getMessage()}";
                WirecardErrorHelper::wirecardError($message, $message);
                return array("status" => NOK);
            }
        }else{
            if($this->DEBUG_VOID_CREDIT){
                //DEBUG HERE
                $message = "WirecardMerchantManagerVoidOperation::sendApcoVoidCredit(backoffice_session_id = {$backoffice_session_id}, amount = {$amount}, currency_text={$currency_text},
                currency_code = {$currency_code}, oref_transaction_id = {$oref_transaction_id}, wirecard_transaction_id = {$wirecard_transaction_id})";
                WirecardErrorHelper::wirecardAccess($message, $message);
            }
            return array("status" => OK);
        }
	}
}
