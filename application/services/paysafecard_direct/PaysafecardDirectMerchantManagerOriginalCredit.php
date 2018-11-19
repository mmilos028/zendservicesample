<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'PaysafecardDirectErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';

/**
 * THIS IS FOR MERCHANT PAYOUT OF PLAYERS (PAYOUT PLAYER TO HIS ACCOUNT)
 * Merchant manager to perform transaction processing from Wirecard payment provider ...
 *
 */
class PaysafecardDirectMerchantManagerOriginalCredit {

    private $DEBUG = false;

	/**
	* Sends Paysafecard original credit command
	* This will make player payout from Wirecard
	* it was not confirmed in database
	* return true if successfull or false if there was an error
	* currency code = ex. 978
	* currency text = ex. EUR
	* returns true if command was send to wirecard to void credit
	* returns false if there was an error
	 * @param string $backoffice_session_id
	 * @param string $transaction_id_old
	 * @param string $db_transaction_id
	 * @param string $paysafecard_transaction_id
	 * @param string $oref_transaction_id
	 * @param string $player_id
	 * @param float $amount
	 * @param string $currency_text
	 * @param string $currency_code
	 * @param string $payment_method
	 * @param float $fee_amount
     * @param string $token_id
	 * @return mixed
	 */
	public function sendPaysafecardDirectOriginalCredit($backoffice_session_id, $transaction_id_old, $db_transaction_id, $paysafecard_transaction_id,
    $oref_transaction_id, $player_id, $amount, $currency_text, $currency_code, $payment_method, $fee_amount, $token_id){
		$backoffice_session_id = trim(strip_tags($backoffice_session_id));
		$transaction_id_old = trim(strip_tags($transaction_id_old));
		$db_transaction_id = trim(strip_tags($db_transaction_id));
		$paysafecard_transaction_id = trim(strip_tags($paysafecard_transaction_id));
		$oref_transaction_id = trim(strip_tags($oref_transaction_id));
		$player_id = trim(strip_tags($player_id));
		$amount = doubleval(trim(strip_tags($amount)));
		$currency_text = trim(strip_tags($currency_text));
		$currency_code = trim(strip_tags($currency_code));
		$payment_method = trim(strip_tags($payment_method));
		$fee_amount = doubleval(trim(strip_tags($fee_amount)));
        $token_id = trim(strip_tags($token_id));
		try{

            if($this->DEBUG) {
                //DEBUG THIS PART OF CODE - CALLED BY BACKOFFICE FOR PAYOUT PLAYER
                $message = "PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id},
			             paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text},
	            currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id = {$token_id})";
                PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
            }

            require_once MODELS_DIR . DS . 'PlayerModel.php';
            $modelPlayer = new PlayerModel();
            $playerDetails = $modelPlayer->getPlayerDetailsMalta(null, $player_id);
            $playerDetails = $playerDetails['details'];

            //require_once MODELS_DIR . DS . 'MerchantModel.php';
            //$modelMerchant = new MerchantModel();
            //$site_settings = $modelMerchant->findSiteSettings($player_id);

            $config = Zend_Registry::get('config');
            if($config->paysafecardDirectTestMode == "true") {
                $paysafecard_payout_rest_url = $config->paysafecardDirectUrl . "payout.php?paysafecard_transaction_id={$paysafecard_transaction_id}&action=capture";
            }else{
                $paysafecard_payout_rest_url = $config->paysafecardDirectUrl . "payouts/" . $paysafecard_transaction_id . "/capture";
            }

            if($this->DEBUG) {
                //DEBUG THIS CODE
                $message = "PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old},
                db_transaction_id={$db_transaction_id}, paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method},
	            fee_amount={$fee_amount}, token_id={$token_id}) <br />
	            Original Credit URL: {$paysafecard_payout_rest_url}";
                PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
            }

            if($config->paysafecardDirectTestMode == "true"){
                $http_user = "cHNjX0R4dThqSnI1LVdPYXhLWnpjOXdyMUtNLXd1Y3dZMXg=";
                /*
                $paysafecard_id = "merchantclientid5HzDvoZSodKDJ7X7VQKrtestAutomation";
                $paysafecard_email = "psc.mypins+9000001500_xZteDVTw@gmail.com";
                $paysafecard_first_name = "SuAeRHtjkNJSoraWHZAERgaRdA";
                $paysafecard_last_name = "VgObhlCPEXNexGsXqSuIWhzDtt";
                $paysafecard_date_of_birth = "1986-06-28";
                $paysafecard_ip = $playerDetails['ip_address'];
                */
                $paysafecard_id = $player_id;
                $paysafecard_email = $playerDetails['email'];
                $paysafecard_first_name = $playerDetails['first_name'];
                $paysafecard_last_name = $playerDetails['last_name'];
                $paysafecard_date_of_birth = date("Y-m-d", strtotime($playerDetails['birthday']));
                $paysafecard_ip = $playerDetails['ip_address'];
            }else{
                $http_user = base64_encode($config->paysafecardDirectHttpUserPayout);
                $paysafecard_id = $player_id;
                $paysafecard_email = $playerDetails['email'];
                $paysafecard_first_name = $playerDetails['first_name'];
                $paysafecard_last_name = $playerDetails['last_name'];
                $paysafecard_date_of_birth = date("Y-m-d", strtotime($playerDetails['birthday']));
                $paysafecard_ip = $playerDetails['ip_address'];

            }

            $jsonarray = array(
                "amount"   => $amount,
                "currency" => $currency_text,
                "type"     => "PAYSAFECARD",
                "customer" => array(
                    "id"            => $paysafecard_id,
                    "email"         => $paysafecard_email,
                    "first_name"    => $paysafecard_first_name,
                    "last_name"     => $paysafecard_last_name,
                    "date_of_birth" => $paysafecard_date_of_birth,
                    "ip"            => $paysafecard_ip,
                ),
                "capture"  => "true",
            );

			//start post init to paysafecard direct rest payments URL
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $paysafecard_payout_rest_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            if($config->paysafecardDirectTestMode == "true") {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonarray));
            }
            curl_setopt($ch, CURLOPT_POST, TRUE);
            if ($config->paysafecardVerifyCertificate == "true") {
                curl_setopt($ch, CURLOPT_CAINFO, APP_DIR . "/configs/paysafecard_certificates/" . $config->paysafecardCertificateFileName);
            }else {
                //disable ssl verification
                if ($config->paysafecardDirectTestMode != "true") {
                    curl_setopt($ch, CURLOPT_PORT, 443);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              "Content-Type: application/json",
              "Authorization: Basic {$http_user}"
            ));
			$response = curl_exec($ch);
            $info = curl_getinfo($ch);
			if(curl_errno($ch)){
				//there was an error sending post to original credit player's transaction (error in player withdraw with PAYSAFECARD DIRECT)
				$error_message = curl_error($ch);
				$message = "PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id},
                transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id}, paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id={$token_id})
				<br /> Transaction was confirmed in database. <br />Exception message: <br /> {$error_message}";
				PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
				return array("status"=>NOK, "error_message"=>"Error while sending player payout to PAYSAFECARD. {$message}");
			}else{
                if($this->DEBUG) {
                    //DEBUG THIS CODE
                    $message = "PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old},
                    db_transaction_id={$db_transaction_id}, paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
                    player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method},
                    fee_amount={$fee_amount}, token_id={$token_id}) <br />
                    Original Credit URL: {$paysafecard_payout_rest_url} <br />";
                    PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
                }
				//player payout was success
				curl_close($ch);

                if($this->DEBUG) {
                    //DEBUG THIS CODE
                    $info_string = print_r($info, true);
                    $message = "
                    Http User {$http_user} <br />
                    PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old},
                    db_transaction_id={$db_transaction_id}, paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
                    player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method},
                    fee_amount={$fee_amount}, token_id={$token_id}) <br />
                    Paysafecard Response: {$response} <br />
                    Paysafecard connection info: {$info_string} <br />
                    Original Credit URL: {$paysafecard_payout_rest_url} <br />";
                    PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
                }

                $responsePaysafecardDirectPayoutCaptureJsonObject = json_decode($response, true);
                $code = $responsePaysafecardDirectPayoutCaptureJsonObject['code'];
                $info_message = $responsePaysafecardDirectPayoutCaptureJsonObject['message'];
                $info_message_number = $responsePaysafecardDirectPayoutCaptureJsonObject['number'];
                $param = $responsePaysafecardDirectPayoutCaptureJsonObject['param'];

                if($info['http_code'] == 400){

                    if($this->DEBUG){
                        $message = "PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id}, transaction_id_old={$transaction_id_old},
                        db_transaction_id={$db_transaction_id}, paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
                        player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method},
                        fee_amount={$fee_amount}, token_id={$token_id}) <br />
                        Paysafecard Response: code = {$code} message = {$info_message} message_number = {$info_message_number} param = {$param} <br />
                        Original Credit URL: {$paysafecard_payout_rest_url} <br />";
                        PaysafecardDirectErrorHelper::paysafecardDirectAccess($message, $message);
                    }

                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $modelMerchant->apcoWithdrawArrivedFromApco($db_transaction_id, NO, $payment_method);
                    $message = "PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id},
                    transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id},paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
                    player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id={$token_id}) <br />
                    Player's payout request was confirmed in database and his Paysafecard Direct payout is not executed without errors. <br />
                    Paysafecard Response: code = {$code} message = {$info_message} message_number = {$info_message_number} param = {$param} <br />
                    Original Credit URL: {$paysafecard_payout_rest_url} <br />";
                    PaysafecardDirectErrorHelper::paysafecardDirectAccessLog($message);
                    return array("status"=>NOK, "error_message"=>"Error while sending player payout to PAYSAFECARD. {$message}");

                }else if($info['http_code'] == 200){
                    if($responsePaysafecardDirectPayoutCaptureJsonObject['status'] == "SUCCESS"){

                        $returnRes = $this->processOkOrPendingTransaction($db_transaction_id, $payment_method);

                        if($returnRes['status'] == OK) {
                            $message = "PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id},
                            transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id},paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
                            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id={$token_id}) <br />
                            Player's payout request was confirmed in database and his Paysafecard Direct payout is not executed without errors. <br />
                            Paysafecard Response: code = {$code} message = {$info_message} message_number = {$info_message_number} param = {$param} <br />
                            Original Credit URL: {$paysafecard_payout_rest_url} <br />";
                            PaysafecardDirectErrorHelper::paysafecardDirectAccessLog($message);
                            return array("status" => OK);
                        }else{
                            require_once MODELS_DIR . DS . 'MerchantModel.php';
                            $modelMerchant = new MerchantModel();
                            $modelMerchant->apcoWithdrawArrivedFromApco($db_transaction_id, NO, $payment_method);
                            $message = "PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id},
                            transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id},paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
                            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id={$token_id}) <br />
                            Player's payout request was confirmed in database and his Paysafecard Direct payout is not executed without errors. <br />
                            Paysafecard Response: code = {$code} message = {$info_message} message_number = {$info_message_number} param = {$param} <br />
                            Original Credit URL: {$paysafecard_payout_rest_url} <br />";
                            PaysafecardDirectErrorHelper::paysafecardDirectAccessLog($message);
                            return array("status"=>NOK, "error_message"=>"Error while sending player payout to PAYSAFECARD. {$message}");
                        }
                    }else{
                        require_once MODELS_DIR . DS . 'MerchantModel.php';
                        $modelMerchant = new MerchantModel();
                        $modelMerchant->apcoWithdrawArrivedFromApco($db_transaction_id, NO, $payment_method);
                        $message = "PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id},
                        transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id},paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
                        player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id={$token_id}) <br />
                        Player's payout request was confirmed in database and his Paysafecard Direct payout is not executed without errors. <br />
                        Paysafecard Response: code = {$code} message = {$info_message} message_number = {$info_message_number} param = {$param} <br />
                        Original Credit URL: {$paysafecard_payout_rest_url} <br />";
                        PaysafecardDirectErrorHelper::paysafecardDirectAccessLog($message);
                        return array("status"=>NOK, "error_message"=>"Error while sending player payout to PAYSAFECARD. {$message}");
                    }
                }else {
                    require_once MODELS_DIR . DS . 'MerchantModel.php';
                    $modelMerchant = new MerchantModel();
                    $modelMerchant->apcoWithdrawArrivedFromApco($db_transaction_id, NO, $payment_method);
                    $message = "PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id},
                    transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id},paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
                    player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id={$token_id}) <br />
                    Player's payout request was confirmed in database and his Paysafecard Direct payout is not executed without errors. <br />
                    Paysafecard Response: code = {$code} message = {$info_message} message_number = {$info_message_number} param = {$param} <br />
                    Original Credit URL: {$paysafecard_payout_rest_url} <br />";
                    PaysafecardDirectErrorHelper::paysafecardDirectAccessLog($message);
                    return array("status" => NOK, "error_message" => "Error while sending player payout to PAYSAFECARD. {$message}");
                }

			}
		}catch(Zend_Exception $ex){
			//there was an error in player payout transaction
			$message = "PaysafecardDirectMerchantManagerOriginalCredit::sendPaysafecardDirectOriginalCredit(backoffice_session_id={$backoffice_session_id},
                transaction_id_old={$transaction_id_old}, db_transaction_id={$db_transaction_id}, paysafecard_transaction_id={$paysafecard_transaction_id}, oref_transaction_id={$oref_transaction_id},
	            player_id={$player_id}, amount={$amount}, currency_text={$currency_text}, currency_code={$currency_code}, payment_method={$payment_method}, fee_amount={$fee_amount}, token_id={$token_id})
	            <br /> Exception occured in making payout command for Paysafecard transaction.
	            <br />Transaction was confirmed in database, but there was no payout with Paysafecard.
	            <br />Exception message: <br /> {$ex->getMessage()}";
			PaysafecardDirectErrorHelper::paysafecardDirectError($message, $message);
			return array("status"=>NOK, "error_message"=>"There was an error while making payout for Paysafecard transaction.
                <br />Transaction was confirmed in database, but there was no payout with Paysafecard.
                <br />AMOUNT: {$amount}
                <br />CURRENCY_TEXT: {$currency_text}
                <br />CURRENCY_CODE: {$currency_code}
                <br />OREF_TRANSACTION_ID: {$oref_transaction_id}
                <br />PAYSAFECARD_TRANSACTION_ID: {$paysafecard_transaction_id}
                <br />TOKEN_ID: {$token_id}"
            );
		}
	}

    /**
     * @param $withdraw_request_id
     * @param $payment_method
     * @return array
     * @throws Zend_Exception
     */
    private function processOkOrPendingTransaction($withdraw_request_id, $payment_method){
        //try to confirm transaction in database
        //if charge fee success then process player withdraw payment
        require_once MODELS_DIR . DS . 'MerchantModel.php';
        $modelMerchant = new MerchantModel();
        $transactionResult = $modelMerchant->apcoWithdrawArrivedFromApco($withdraw_request_id, YES, $payment_method);
        if($transactionResult['status'] == OK && $transactionResult['verification_status'] == "1"){
            return array("status"=>OK);
        }else{
            return array("status"=>NOK);
        }
    }
}
