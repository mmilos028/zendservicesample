<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
class MerchantModel{
	public function __construct(){
	}

    private $DEBUG = false;

    private $DEBUG_DEPOSIT = false;

    private $DEBUG_PAYOUT = false;

    /**
     * @param $transaction_id
     * @param $status
     * @param $message
     * @param $message_id
     * @param $user_id
     * @param $transaction_sign
     * @param $payment_method
     * @param $payment_provider_id
     * @param $amount
     * @param $currency
     * @return array
     * @throws Zend_Exception
     */
    public function transactionApiResponse($transaction_id, $status, $message, $message_id, $user_id, $transaction_sign, $payment_method,
            $payment_provider_id, $amount, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        if($this->DEBUG) {
            //DEBUG THIS PART OF CODE - PAYOUT RECEIVED PARAMETERS TO DB
            $errorHelper = new ErrorHelper();
            $errorHelper->sendMail("MerchantModel::transactionApiResponse <br /> CREDIT_TRANSFER.i_transaction_api_response(:p_transaction_id_in = {$transaction_id},
                :p_status = {$status}, :p_message = {$message}, :p_message_id = {$message_id}, :p_user_id = {$user_id}, :p_transaction_sign = {$transaction_sign},
			    :p_payment_method = {$payment_method}, :p_payment_provider_id = {$payment_provider_id}, :p_amount = {$amount}, :p_currency = {$currency}, :p_status_out)");
        }
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.i_transaction_api_response(:p_transaction_id_in, :p_status, :p_message, :p_message_id, :p_user_id, :p_transaction_sign, :p_payment_method, :p_payment_provider_id, :p_amount, :p_currency, :p_status_out)');
			$stmt->bindParam(':p_transaction_id_in', $transaction_id);
			$stmt->bindParam(':p_status', $status);
			$stmt->bindParam(':p_message', $message);
            $stmt->bindParam(':p_message_id', $message_id);
            $stmt->bindParam(':p_user_id', $user_id);
            $stmt->bindParam(':p_transaction_sign', $transaction_sign);
            $stmt->bindParam(':p_payment_method', $payment_method);
            $stmt->bindParam(':p_payment_provider_id', $payment_provider_id);
            $stmt->bindParam(':p_amount', $amount);
            $stmt->bindParam(':p_currency', $currency);
            $status_out = "";
			$stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            if($this->DEBUG) {
                $errorHelper = new ErrorHelper();
                $errorHelper->sendMail("MerchantModel::transactionApiResponse <br /> CREDIT_TRANSFER.i_transaction_api_response(:p_transaction_id_in = {$transaction_id},
                :p_status = {$status}, :p_message = {$message}, :p_message_id = {$message_id}, :p_user_id = {$user_id}, :p_transaction_sign = {$transaction_sign},
			    :p_payment_method = {$payment_method}, :p_payment_provider_id = {$payment_provider_id}, :p_amount = {$amount}, :p_currency = {$currency}, :p_status_out)");
            }
			return array("status"=>OK, "status_out"=>$status_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
            $errorHelper = new ErrorHelper();
            $message = "MerchantModel::transactionApiResponse <br /> CREDIT_TRANSFER.i_transaction_api_response(:p_transaction_id_in = {$transaction_id},
                :p_status = {$status}, :p_message = {$message}, :p_message_id = {$message_id}, :p_user_id = {$user_id}, :p_transaction_sign = {$transaction_sign},
			    :p_payment_method = {$payment_method}, :p_payment_provider_id = {$payment_provider_id}, :p_amount = {$amount}, :p_currency = {$currency}, :p_status_out)
			     <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "code"=>$code, "message"=>CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
    }

    /**
     * @param $transaction_id
     * @param $verified_y_n_status
     * @param $payment_method_code
     * @return array
     * @throws Zend_Exception
     */
    public function apcoWithdrawArrivedFromApco($transaction_id, $verified_y_n_status, $payment_method_code){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        if($this->DEBUG_PAYOUT) {
            //DEBUG THIS PART OF CODE - PAYOUT RECEIVED PARAMETERS TO DB
            $errorHelper = new ErrorHelper();
            $errorHelper->sendMail("MerchantModel::apcoWithdrawArrivedFromApco <br /> CREDIT_TRANSFER.pay_out_verified_apco(
                :p_tranasaction_id_in = {$transaction_id}, :p_verified_y_n = {$verified_y_n_status}, :p_payment_method_code_in = {$payment_method_code}, :p_verification_status_out)");
        }
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.pay_out_verified_apco(:p_transaction_id_in, :p_verified_y_n, :p_payment_method_code_in, :p_verification_status_out)');
			$stmt->bindParam(':p_transaction_id_in', $transaction_id);
			$stmt->bindParam(':p_verified_y_n', $verified_y_n_status);
			$stmt->bindParam(':p_payment_method_code_in', $payment_method_code);
            $verification_status = "1";
			$stmt->bindParam(':p_verification_status_out', $verification_status, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            if($this->DEBUG_PAYOUT) {
                $errorHelper = new ErrorHelper();
                $errorHelper->sendMail("MerchantModel::apcoWithdrawArrivedFromApco <br /> CREDIT_TRANSFER.pay_out_verified_apco(
			        :p_tranasaction_id_in = {$transaction_id}, :p_verified_y_n = {$verified_y_n_status}, :p_payment_method_code_in = {$payment_method_code},
			        :p_verification_status_out = {$verification_status})");
            }
			return array("status"=>OK, "verification_status"=>$verification_status);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
            $errorHelper = new ErrorHelper();
            $message = "MerchantModel::apcoPayOut <br /> CREDIT_TRANSFER.pay_out_verified_apco(:p_tranasaction_id_in = {$transaction_id}, :p_verified_y_n = {$verified_y_n_status},
            :p_payment_method_code_in = {$payment_method_code}, :p_verification_status_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "code"=>$code, "message"=>CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
    }

	//Returns currency for player's pc session id
    /**
     * @param $pc_session_id
     * @return array
     * @throws Zend_Exception
     */
	public function currencyCodeForSession($pc_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.CURRENCY_CODE_FOR_SESSION(:p_session_id_in, :p_currency_name_out, :p_currency_code_out)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$currency_name = "";
			$stmt->bindParam(':p_currency_name_out', $currency_name, SQLT_CHR, 255);
			$currency_code = "";
			$stmt->bindParam(':p_currency_code_out', $currency_code, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            if($this->DEBUG) {
                //DEBUG THIS CODE
                $errorHelper = new ErrorHelper();
                $mail_message = $log_message = "MerchantModel::currencyCodeForSession CALL WEB_REPORTS.CURRENCY_CODE_FOR_SESSION(:p_session_id_in = {$pc_session_id}, :p_currency_name_out = {$currency_name}, :p_currency_code_out = {$currency_code})";
                $errorHelper->merchantError($mail_message, $log_message);
            }
			if(strlen($currency_name) == 0 || strlen($currency_code) == 0){
				$errorHelper = new ErrorHelper();
				$message = "MerchantModel::currencyCodeForSession - WEB_REPORTS.CURRENCY_CODE_FOR_SESSION(:p_session_id_in = {$pc_session_id}, :p_currency_name_out = {$currency_name}, :p_currency_code_out = {$currency_code})";
				$errorHelper->merchantError($message, $message);
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			return array("status"=>OK, "pc_session_id"=>$pc_session_id, "currency_text"=>$currency_name, "currency_code"=>$currency_code);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::currencyCodeForSession <br /> WEB_REPORTS.CURRENCY_CODE_FOR_SESSION(p_session_id_in = {$pc_session_id}, p_currency_name_out, p_currency_code_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

	//Cancels player's withdraw request, cancels all withdraw requests made by player
    /**
     * @param $site_session_id
     * @param $transaction_id
     * @param $withdraw_amount
     * @return array
     * @throws Zend_Exception
     */
	public function cancelWithdraw($site_session_id, $transaction_id, $withdraw_amount){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        if($this->DEBUG) {
            //DEBUG HERE
            $errorHelper = new ErrorHelper();
            $message = "MerchantModel::cancelWithdraw <br /> CREDIT_TRANSFER.CANCEL_WITHDRAW(:p_session_id_in = {$site_session_id}, :p_withdraw_transaction_id_in = {$transaction_id}, :p_withdraw_amount = {$withdraw_amount}, p_cancel_status_out =)";
            $errorHelper->merchantAccess($message, $message);
        }
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.CANCEL_WITHDRAW(:p_session_id_in, :p_withdraw_transaction_id_in, :p_withdraw_amount, :p_cancel_status_out)');
			$stmt->bindParam(':p_session_id_in', $site_session_id);
            $stmt->bindParam(':p_withdraw_transaction_id_in', $transaction_id);
			$stmt->bindParam(':p_withdraw_amount', $withdraw_amount);
            $cancel_status = "";
            $stmt->bindParam(':p_cancel_status_out', $cancel_status, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            if($cancel_status == "1") {
                return array("status" => OK, "transaction_id" => $transaction_id, "cancel_status" => $cancel_status);
            }else{
                return array("status"=> NOK, "message"=>NOK_EXCEPTION);
            }
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "MerchantModel::cancelWithdraw <br /> CREDIT_TRANSFER.CANCEL_WITHDRAW(:p_session_id_in = {$site_session_id}, :p_withdraw_transaction_id_in = {$transaction_id}, :p_withdraw_amount = {$withdraw_amount}, p_cancel_status_out =) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
	
	//find site settings, url links for player with id
    /**
     * @param $player_id
     * @return array
     * @throws Zend_Exception
     */
	public function findSiteSettings($player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$FIND_SITE_SETINGS(:p_player_id_in, :p_smtp_server_in, :p_mail_server_in,
			:p_site_link_in, :p_player_activation_link_in, :p_forgot_pass_link, :p_site_image_location_in, :p_casino_name,
			:p_contact_url, :p_support_url, :p_terms_url, :p_UNLOCK_LINK_OUT, :p_default_language, :p_privacy_policy_url, :cur_result_out)');
			$stmt->bindParam(':p_player_id_in', $player_id);
            $smtp_server = "";
			$stmt->bindParam(':p_smtp_server_in', $smtp_server, SQLT_CHR, 255);
			$mail_address_from = "";
			$stmt->bindParam(':p_mail_server_in', $mail_address_from, SQLT_CHR, 255);
			$site_link = "";
			$stmt->bindParam(':p_site_link_in', $site_link, SQLT_CHR, 255);
			$player_activation_link = "";
			$stmt->bindParam(':p_player_activation_link_in', $player_activation_link, SQLT_CHR, 255);
			$forgot_pass_link = "";
			$stmt->bindParam(':p_forgot_pass_link', $forgot_pass_link, SQLT_CHR, 255);
			$site_image_location = "";
			$stmt->bindParam(':p_site_image_location_in', $site_image_location, SQLT_CHR, 255);
			$casino_name = "";
			$stmt->bindParam(':p_casino_name', $casino_name, SQLT_CHR, 255);
			//$apco_purchase_link = "";
			//purchase status url
			//$stmt->bindParam(':p_apco_purchase_link_in', $apco_purchase_link, SQLT_CHR, 255);
			//$apco_payout_link = "";
			//original credit (payout) status url
			//$stmt->bindParam(':p_apco_payout_link_in', $apco_payout_link, SQLT_CHR, 255);
			$redirection_site_success = "";
			//$stmt->bindParam(':p_redirection_site_suc_in', $redirection_site_success, SQLT_CHR, 255);
			$redirection_site_failed = "";
			//$stmt->bindParam(':p_redirection_site_nosuc_in', $redirection_site_failed, SQLT_CHR, 255);
			$contact_url = "";
			$stmt->bindParam(':p_contact_url', $contact_url, SQLT_CHR, 255);
			$support_url = "";
			$stmt->bindParam(':p_support_url', $support_url, SQLT_CHR, 255);
			$terms_url = "";
			$stmt->bindParam(':p_terms_url', $terms_url, SQLT_CHR, 255);
			$unlock_url = "";
			$stmt->bindParam(':p_UNLOCK_LINK_OUT', $unlock_url, SQLT_CHR, 255);
            $default_language = "";
			$stmt->bindParam(':p_default_language', $default_language, SQLT_CHR, 255);
            $privacy_policy_link = "";
			$stmt->bindParam(':p_privacy_policy_url', $privacy_policy_link, SQLT_CHR, 255);

            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':cur_result_out', $cursor);

			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();

            $apco_purchase_link = "";
            $apco_payout_link = "";
            $apco_redirection_site_failed_link = "";
            $apco_redirection_site_success_link = "";

            $wirecard_purchase_link = "";
            $wirecard_payout_link = "";
            $wirecard_redirection_site_success_link = "";
            $wirecard_redirection_site_failed_link = "";

            $paysafecard_direct_purchase_link = "";
            $paysafecard_direct_payout_link = "";
            $paysafecard_direct_redirection_site_success_link = "";
            $paysafecard_direct_redirection_site_failed_link = "";

            foreach($cursor as $cur){
                switch($cur['payment_provider_id']){
                    case APCO_PAYMENT_PROVIDER:
                        $apco_purchase_link = $cur['purchase_url'];
                        $apco_payout_link = $cur['payout_url'];
                        $apco_redirection_site_success_link = $cur['payout_success'];
                        $apco_redirection_site_failed_link = $cur['payout_fail'];
                        break;
                    case WIRECARD_PAYMENT_PROVIDER:
                        $wirecard_purchase_link = $cur['purchase_url'];
                        $wirecard_payout_link = $cur['payout_url'];
                        $wirecard_redirection_site_success_link = $cur['payout_success'];
                        $wirecard_redirection_site_failed_link = $cur['payout_fail'];
                        break;
                    case PAYSAFECARD_DIRECT_PAYMENT_PROVIDER:
                        $paysafecard_direct_purchase_link = $cur['purchase_url'];
                        $paysafecard_direct_payout_link = $cur['payout_url'];
                        $paysafecard_direct_redirection_site_success_link = $cur['payout_success'];
                        $paysafecard_direct_redirection_site_failed_link = $cur['payout_fail'];
                        break;
                    default:
                        $apco_purchase_link = $cur['purchase_url'];
                        $apco_payout_link = $cur['payout_url'];
                        $apco_redirection_site_success_link = $cur['payout_success'];
                        $apco_redirection_site_failed_link = $cur['payout_fail'];
                }
            }

			return array("status"=>OK, "player_id"=>$player_id, "smtp_server_ip"=>$smtp_server, "mail_address_from"=>$mail_address_from,
                "site_link"=>$site_link, "player_activation_link"=>$player_activation_link, "forgot_pass_link"=>$forgot_pass_link,
                "site_image_location"=>$site_image_location, "casino_name"=>$casino_name, "contact_url_link"=>$contact_url, "privacy_policy_link"=>$privacy_policy_link,
                "support_url_link"=>$support_url, "terms_url_link"=>$terms_url, "unlock_url_link"=>$unlock_url, "language_settings"=>$default_language,

                "apco_purchase_link"=>$apco_purchase_link,
                "apco_payout_link"=>$apco_payout_link,
                "apco_redirection_site_success_link"=>$apco_redirection_site_success_link,
                "apco_redirection_site_failed_link"=>$apco_redirection_site_failed_link,

                "wirecard_purchase_link"=>$wirecard_purchase_link,
                "wirecard_payout_link"=>$wirecard_payout_link,
                "wirecard_redirection_site_success_link"=>$wirecard_redirection_site_success_link,
                "wirecard_redirection_site_failed_link"=>$wirecard_redirection_site_failed_link,

                "paysafecard_direct_purchase_link"=>$paysafecard_direct_purchase_link,
                "paysafecard_direct_payout_link"=>$paysafecard_direct_payout_link,
                "paysafecard_direct_redirection_site_success_link"=>$paysafecard_direct_redirection_site_success_link,
                "paysafecard_direct_redirection_site_failed_link"=>$paysafecard_direct_redirection_site_failed_link,
            );
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::findSiteSettings <br /> WEB_REPORTS.M\$FIND_SITE_SETINGS(:p_player_id_in = {$player_id}, :p_smtp_server_in =, :p_mail_server_in =,
			:p_site_link_in, :p_player_activation_link_in, :p_forgot_pass_link, :p_site_image_location_in, :p_casino_name,
			:p_contact_url, :p_support_url, :p_terms_url, :p_UNLOCK_LINK_OUT, :p_default_language, :p_privacy_policy_url) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}	

	//List All payment methods that we support
    /**
     * @return array
     * @throws Zend_Exception
     */
	public function listAllPaymentMethods(){
	    $config = Zend_Registry::get('config');

	    if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }

	    if($config->db->enable_cache == "true"){
            $cacheObj = Zend_Registry::get('db_cache');
	        $affiliate_id = null;
	        $session_id = -1;
			$cache_key_name = "WEB_REPORTS__M_LIST_ALL_PAYMENT_METHOD";
			$cache_key_name = str_replace(array("."), "_", $cache_key_name);
		    $result = unserialize($cacheObj->load($cache_key_name) );
		    if(!isset($result) || $result == null || !$result) {
		         /* @var $dbAdapter Zend_Db_Adapter_Oracle */
                $dbAdapter = Zend_Registry::get('db_auth');
                $dbAdapter->beginTransaction();
                try {
                    $stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$LIST_ALL_PAYMENT_METHOD(:p_PAYMENT_METHOD_out)');
                    $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
                    $stmt->bindCursor(":p_PAYMENT_METHOD_out", $cursor);
                    $stmt->execute(null, false);
                    $dbAdapter->commit();
                    $cursor->execute();
                    $cursor->free();
                    $dbAdapter->closeConnection();

                    $result = array("status" => OK, "payment_methods" => $cursor);

                    $cache_key_name = "WEB_REPORTS__M_LIST_ALL_PAYMENT_METHOD";
					$cache_key_name = str_replace(array("."), "_", $cache_key_name);
                    $cacheObj->save(serialize($result), $cache_key_name);

                    if($config->measureSpeedPerformance == "true") {
                        $after_time = microtime(true);
                        $difference_time = number_format(($after_time-$before_time), 4);
                        $errorHelper = new ErrorHelper();
                        $measure_time_message = "WEB_REPORTS.M\$LIST_ALL_PAYMENT_METHOD(:p_PAYMENT_METHOD_out)";
                        $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                        $errorHelper->siteAccessLog($measure_time_message);
                    }

                    return $result;
                } catch (Zend_Exception $ex) {
                    $dbAdapter->rollBack();
                    $dbAdapter->closeConnection();
                    $errorHelper = new ErrorHelper();
                    $message = "MerchantModel::listAllPaymentMethods <br /> WEB_REPORTS.M\$LIST_ALL_PAYMENT_METHOD(p_PAYMENT_METHOD_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                    $errorHelper->merchantError($message, $message);
                    return array("status" => NOK, "message" => NOK_EXCEPTION);
                }
            }else{
		        return $result;
            }
        }else {
            /* @var $dbAdapter Zend_Db_Adapter_Oracle */
            $dbAdapter = Zend_Registry::get('db_auth');
            $dbAdapter->beginTransaction();
            try {
                $stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$LIST_ALL_PAYMENT_METHOD(:p_PAYMENT_METHOD_out)');
                $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
                $stmt->bindCursor(":p_PAYMENT_METHOD_out", $cursor);
                $stmt->execute(null, false);
                $dbAdapter->commit();
                $cursor->execute();
                $cursor->free();
                $dbAdapter->closeConnection();

                if($config->measureSpeedPerformance == "true") {
                    $after_time = microtime(true);
                    $difference_time = number_format(($after_time-$before_time), 4);
                    $errorHelper = new ErrorHelper();
                    $measure_time_message = "WEB_REPORTS.M\$LIST_ALL_PAYMENT_METHOD(:p_PAYMENT_METHOD_out)";
                    $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                    $errorHelper->siteAccessLog($measure_time_message);
                }

                return array("status" => OK, "payment_methods" => $cursor);
            } catch (Zend_Exception $ex) {
                $dbAdapter->rollBack();
                $dbAdapter->closeConnection();
                $errorHelper = new ErrorHelper();
                $message = "MerchantModel::listAllPaymentMethods <br /> WEB_REPORTS.M\$LIST_ALL_PAYMENT_METHOD(p_PAYMENT_METHOD_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->merchantError($message, $message);
                return array("status" => NOK, "message" => NOK_EXCEPTION);
            }
        }
	}	
	
	//Process payout received from Apco, after operator confirmed player's payout in our system and also confirmed in Apco
    /**
     * @param $backoffice_session_id
     * @param $transaction_id_old
     * @param $amount
     * @param $apco_transaction_id
     * @param $currency
     * @param $credit_card_number
     * @param $credit_card_date_expires
     * @param $credit_card_holder
     * @param $credit_card_country
     * @param $credit_card_type
     * @param $start_time
     * @param $bank_code
     * @param $ip_address
     * @param $card_issuer_bank
     * @param $card_country
     * @param $client_email
     * @param $transaction_id_hang
     * @param $bank_auth_code
     * @param $source
     * @param $player_id
     * @param $site_domain
     * @return array
     * @throws Zend_Exception
     */
	public function apcoPayOutVerified($backoffice_session_id, $transaction_id_old, $amount, $apco_transaction_id,
	$currency, $credit_card_number, $credit_card_date_expires, $credit_card_holder, $credit_card_country,
	$credit_card_type, $start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, 
	$transaction_id_hang, $bank_auth_code, $source, $player_id, $site_domain){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$over_limit = null;
        if($this->DEBUG_PAYOUT) {
            //DEBUG THIS PART OF CODE - PAYOUT RECEIVED PARAMETERS TO DB
            $errorHelper = new ErrorHelper();
            $errorHelper->sendMail("MerchantModel::apcoPayOutVerified <br /> CREDIT_TRANSFER.APCO_PAY_OUT_V <br /> Backoffice session ID: {$backoffice_session_id} <br /> Transaction ID: {$transaction_id_old}
                <br /> Amount: {$amount} <br /> Apco transaction id: {$apco_transaction_id} <br /> Currency: {$currency}
                <br /> Credit Card number: {$credit_card_number} <br /> Credit card date expired: {$credit_card_date_expires}
                <br /> Credit card Holder: {$credit_card_holder} <br /> Credit card country: {$credit_card_country}
                <br /> Credit Card Type: {$credit_card_type} <br /> Start time: {$start_time} <br /> Bank code: {$bank_code}
                <br /> IP address: {$ip_address} <br /> Card issuer bank: {$card_issuer_bank} <br /> Card country IP: {$card_country}
                <br /> Client email: {$client_email} <br /> Over limit: {$over_limit} <br /> Transaction_id_hang: {$transaction_id_hang}
                <br /> Bank auth code: {$bank_auth_code} <br /> Source (Payment method): {$source} <br /> Player ID: {$player_id} <br /> Site domain: {$site_domain}");
        }
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.APCO_PAY_OUT_V(:p_session_id_in, :p_transaction_id_in, :p_amount_in, :p_apco_transaction_id, :p_currency_in, :p_credit_card_number_in,
				:p_credit_card_date_expiried_in, :p_credit_card_holder_in, :p_credit_card_country_in, :p_credit_card_type_in, :p_start_time_in, :p_bank_code_in, :p_ip_address_in, :p_CARD_ISSUER_BANK_in, 
				:p_card_country_ip_in, :p_client_email_in, :p_over_limit_in, :p_BANK_AUTH_CODE, :p_source_in, :p_transaction_id_hang_in, :p_player_id_in, :p_site_domen_in, :p_transaction_id_out)');
			$stmt->bindParam(':p_session_id_in', $backoffice_session_id);
			$stmt->bindParam(':p_transaction_id_in', $transaction_id_old);
			$stmt->bindParam(':p_amount_in', $amount);
			$stmt->bindParam(':p_apco_transaction_id', $apco_transaction_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_credit_card_number_in', $credit_card_number);
			$stmt->bindParam(':p_credit_card_date_expiried_in', $credit_card_date_expires);
			$stmt->bindParam(':p_credit_card_holder_in', $credit_card_holder);
			$stmt->bindParam(':p_credit_card_country_in', $credit_card_country);
			$stmt->bindParam(':p_credit_card_type_in', $credit_card_type);
			$stmt->bindParam(':p_start_time_in', $start_time);
			$stmt->bindParam(':p_bank_code_in', $bank_code);
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$stmt->bindParam(':p_CARD_ISSUER_BANK_in', $card_issuer_bank);
			$stmt->bindParam(':p_card_country_ip_in', $card_country);
			$stmt->bindParam(':p_client_email_in', $client_email);
			$stmt->bindParam(':p_over_limit_in', $over_limit);
			$stmt->bindParam(':p_BANK_AUTH_CODE', $bank_auth_code);
			$stmt->bindParam(':p_source_in', $source);			
			$stmt->bindParam(':p_transaction_id_hang_in', $transaction_id_hang);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_site_domen_in', $site_domain);
			$transaction_id_out = "-10";
			$stmt->bindParam(':p_transaction_id_out', $transaction_id_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            if($this->DEBUG_PAYOUT) {
                //DEBUG THIS PART OF CODE - RETURNS PARAMETAR VALUES
                $errorHelper = new ErrorHelper();
                $errorHelper->sendMail("PROCEDURE CREDIT_TRANSFER.APCO_PAY_OUT_V RETURNS P_TRANSACTION_ID_OUT: {$transaction_id_out}");
            }
			return array("status"=>OK, "transaction_id_out"=>$transaction_id_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();		
			$errorHelper = new ErrorHelper();
            $code = $ex->getCode();
			$message = "MerchantModel::apcoPayOutVerified <br /> CREDIT_TRANSFER.APCO_PAY_OUT_V(:p_session_id_in = {$backoffice_session_id}, :p_transaction_id_in = {$transaction_id_old}, :p_amount_in = {$amount},
            :p_apco_transaction_id = {$apco_transaction_id}, :p_currency_in={$currency}, :p_credit_card_number_in={$credit_card_number},
            :p_credit_card_date_expiried_in = {$credit_card_date_expires}, :p_credit_card_holder_in = {$credit_card_holder}, :p_credit_card_country_in = {$credit_card_country},
            :p_credit_card_type_in = {$credit_card_type}, :p_start_time_in = {$start_time}, :p_bank_code_in = {$bank_code}, :p_ip_address_in = {$ip_address}, :p_CARD_ISSUER_BANK_in = {$card_issuer_bank},
            :p_card_country_ip_in = {$card_country}, :p_client_email_in = {$client_email}, :p_over_limit_in = {$over_limit}, :p_BANK_AUTH_CODE = {$bank_auth_code}, :p_source_in = {$source},
            :p_transaction_id_hang_in = {$transaction_id_hang}, :p_player_id_in = {$player_id}, :p_site_domen_in = {$site_domain}, :p_transaction_id_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "code"=>$code);
		}
	}	

	//List all payment methods for site session id (for player)
	/*public function listPaymentMethods($site_session_id){
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.M$LIST_PAYMENT_METHOD(:p_session_id_in, :p_PAYMENT_METHOD_out, :p_game_session_is_open_out)');
			$stmt->bindParam(':p_session_id_in', $site_session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_PAYMENT_METHOD_out", $cursor);
			$game_session_is_open_out = "";
			$stmt->bindParam(':p_game_session_is_open_out', $game_session_is_open_out);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "payment_methods"=>$cursor, "game_session_is_open"=>$game_session_is_open_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::listPaymentMethods <br /> SITE_LOGIN.M\$LIST_PAYMENT_METHOD(:p_session_id_in = {$site_session_id}, :p_PAYMENT_METHOD_out, :p_game_session_is_open_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}*/
	
	//sent from our site player to our database his payout request
    /**
     * @param $pc_session_id
     * @param $transaction_id
     * @param $amount
     * @param $apco_transaction_id
     * @param $currency
     * @param $credit_card_number
     * @param $credit_card_date_expires
     * @param $credit_card_holder
     * @param $credit_card_country
     * @param $credit_card_type
     * @param $start_time
     * @param $bank_code
     * @param $ip_address
     * @param $card_issuer_bank
     * @param $card_country
     * @param $client_email
     * @param $over_limit
     * @param $bank_auth_code
     * @param string $source
     * @param $site_domain
     * @param $fee_transaction_id
     * @param $payment_provider
     * @param $token_id
     * @return array
     * @throws Zend_Exception
     */
	public function paymentProviderWithdrawRequest($pc_session_id, $transaction_id, $amount, $apco_transaction_id,
	$currency, $credit_card_number, $credit_card_date_expires, $credit_card_holder, $credit_card_country,
	$credit_card_type, $start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, $over_limit, $bank_auth_code, $source = '',
    $site_domain, $fee_transaction_id, $payment_provider, $token_id = ''){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        if($this->DEBUG) {
            //DEBUG THIS PART OF CODE - PAYOUT RECEIVED PARAMETERS TO DB
            $errorHelper = new ErrorHelper();
            $errorHelper->sendMail("MerchantModel::paymentProviderWithdrawRequest <br /> CREDIT_TRANSFER.APCO_PAY_OUT <br /> PC session ID: {$pc_session_id} <br /> Transaction ID: {$transaction_id}
			<br /> Amount: {$amount} <br /> Apco transaction id: {$apco_transaction_id} <br /> Currency: {$currency} 
			<br /> Credit Card number: {$credit_card_number} <br /> Credit card date expired: {$credit_card_date_expires} 
			<br /> Credit card Holder: {$credit_card_holder} <br /> Credit card country: {$credit_card_country} 
			<br /> Credit Card Type: {$credit_card_type} <br /> Start time: {$start_time} <br /> Bank code: {$bank_code} 
			<br /> IP address: {$ip_address} <br /> Card issuer bank: {$card_issuer_bank} <br /> Card country IP: {$card_country} 
			<br /> Client email: {$client_email} <br /> Over limit: {$over_limit} <br /> Bank auth code: {$bank_auth_code} <br /> Site Domain: {$site_domain}
		    <br /> Fee Transaction Id {$fee_transaction_id} <br /> Payment Provider = {$payment_provider} <br /> Token ID = {$token_id}");
        }
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.APCO_PAY_OUT(:p_session_id_in, :p_transaction_id_in, :p_amount_in, :p_apco_transaction_id, :p_currency_in, :p_credit_card_number_in,
			:p_credit_card_date_expiried_in, :p_credit_card_holder_in, :p_credit_card_country_in, :p_credit_card_type_in, :p_start_time_in, :p_bank_code_in, :p_ip_address_in, :p_CARD_ISSUER_BANK_in, 
			:p_card_country_ip_in, :p_client_email_in, :p_over_limit_in, :p_BANK_AUTH_CODE, :p_source_in, :p_site_domen_in, :p_fee_transaction_id_in, :p_payment_provider_in, :p_token_in, :p_transaction_id_out,
			:p_db_transactions_id_out, :p_verifyed_player_out)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$stmt->bindParam(':p_transaction_id_in', $transaction_id);
			$stmt->bindParam(':p_amount_in', $amount);
			$stmt->bindParam(':p_apco_transaction_id', $apco_transaction_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_credit_card_number_in', $credit_card_number);
			$stmt->bindParam(':p_credit_card_date_expiried_in', $credit_card_date_expires);
			$stmt->bindParam(':p_credit_card_holder_in', $credit_card_holder);
			$stmt->bindParam(':p_credit_card_country_in', $credit_card_country);
			$stmt->bindParam(':p_credit_card_type_in', $credit_card_type);
			$stmt->bindParam(':p_start_time_in', $start_time);
			$stmt->bindParam(':p_bank_code_in', $bank_code);
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$stmt->bindParam(':p_CARD_ISSUER_BANK_in', $card_issuer_bank);
			$stmt->bindParam(':p_card_country_ip_in', $card_country);
			$stmt->bindParam(':p_client_email_in', $client_email);
			$stmt->bindParam(':p_over_limit_in', $over_limit);
			$stmt->bindParam(':p_BANK_AUTH_CODE', $bank_auth_code);
			$stmt->bindParam(':p_source_in', $source);
			$stmt->bindParam(':p_site_domen_in', $site_domain);
            $stmt->bindParam(':p_fee_transaction_id_in', $fee_transaction_id);
            $stmt->bindParam(':p_payment_provider_in', $payment_provider);
            $stmt->bindParam(':p_token_in', $token_id);
			$transaction_id_out = "-10";
			$stmt->bindParam(':p_transaction_id_out', $transaction_id_out, SQLT_CHR, 255);
			$db_transaction_id_out = ""; //OREF
			$stmt->bindParam(':p_db_transactions_id_out', $db_transaction_id_out, SQLT_CHR, 255);
			$verified_player_out = ""; //OREF
			$stmt->bindParam(':p_verifyed_player_out', $verified_player_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            if($this->DEBUG) {
                //DEBUG THIS PART OF CODE - RETURNS PARAMETAR VALUES
                $errorHelper = new ErrorHelper();
                $errorHelper->sendMail("MerchantModel::paymentProviderWithdrawRequest CREDIT_TRANSFER.APCO_PAY_OUT PROCEDURE RETURNS P_TRANSACTION_ID_OUT: {$transaction_id_out}");
            }
			return array("status"=>OK, "transaction_id_out"=>$transaction_id_out, "db_transaction_id"=>$db_transaction_id_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			//exception -20101,'Game session is open !!!!'
			$code = $ex->getCode();
            $errorHelper = new ErrorHelper();
            $message = "MerchantModel::paymentProviderWithdrawRequest <br /> CREDIT_TRANSFER.APCO_PAY_OUT(:p_session_id_in={$pc_session_id}, :p_transaction_id_in={$transaction_id}, :p_amount_in={$amount}, :p_apco_transaction_id = {$apco_transaction_id},
            :p_currency_in = {$currency}, :p_credit_card_number_in = {$credit_card_number}, :p_credit_card_date_expiried_in = {$credit_card_date_expires}, :p_credit_card_holder_in = {$credit_card_holder},
			:p_credit_card_country_in = {$credit_card_country}, :p_credit_card_type_in = {$credit_card_type}, :p_start_time_in = {$start_time}, :p_bank_code_in = {$bank_code},
			:p_ip_address_in = {$ip_address}, :p_CARD_ISSUER_BANK_in = {$card_issuer_bank}, :p_card_country_ip_in = {$card_country}, :p_client_email_in = {$client_email},
			:p_over_limit_in = {$over_limit}, :p_BANK_AUTH_CODE = {$bank_auth_code}, :p_source_in = {$source}, :p_site_domen_in = {$site_domain}, :p_fee_transaction_id_in = {$fee_transaction_id},
			:p_payment_provider_in = {$payment_provider}, :p_token_in = {$token_id}
			:p_transaction_id_out, :p_db_transactions_id_out, :p_verifyed_player_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "code"=>$code, "message"=>$message);
		}
	}
	
	//Check open game session for player with site session id * throws exception if there is an game opened
    /**
     * @param $site_session_id
     * @return array
     * @throws Zend_Exception
     */
	public function checkOpenGameSession($site_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.M$CHECK_OPEN_GAME_SESSION(:p_session_id_in)');
			$stmt->bindParam(':p_session_id_in', $site_session_id);
			$stmt->execute();
			$dbAdapter->commit();			
			$dbAdapter->closeConnection();
			return array("status"=>OK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::apcoPayOutVerified <br /> SITE_LOGIN.M\$CHECK_OPEN_GAME_SESSION(p_session_id_in = {$site_session_id}) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			//$errorHelper->merchantError($message, $message);
            $errorHelper->merchantErrorLog($message);
			return array("status"=>NOK);
		}
	}
	
	// return transaction limit to payin credits through credit card or return player status if transaction limit is controlled
    /**
     * @param $site_session_id
     * @param $in_out_transaction
     * @param $payment_method
     * @param $amount
     * @return array
     * @throws Zend_Exception
     */
	public function getTransactionLimit($site_session_id, $in_out_transaction, $payment_method, $amount){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.TRANSACTIONS_LIMIT(:p_session_id_in, :p_in_out_transaction_in, :p_payment_method_in, :p_payment_amount_in, :p_transaction_limit_out, :p_player_status_out, :p_player_system_limit_out, :p_withdraw_fee_out)');
			$stmt->bindParam(':p_session_id_in', $site_session_id);
			$stmt->bindParam(':p_in_out_transaction_in', $in_out_transaction);
			$stmt->bindParam(':p_payment_method_in', $payment_method);
			$stmt->bindParam(':p_payment_amount_in', $amount);
			//return transaction limit for credit card transaction
			$transaction_limit_out = ""; //number
			$stmt->bindParam(':p_transaction_limit_out', $transaction_limit_out, SQLT_CHR, 255);
			//return if transaction limit for credit card will be controlled
			$player_status_out = YES; // Y | N
			$stmt->bindParam(':p_player_status_out', $player_status_out, SQLT_CHR, 255);
			$player_system_limit_out = "";
			$stmt->bindParam(':p_player_system_limit_out', $player_system_limit_out, SQLT_CHR, 255);
            $withdraw_fee_out = "";
			$stmt->bindParam(':p_withdraw_fee_out', $withdraw_fee_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();

            if($this->DEBUG) {
                //DEBUG THIS PART OF CODE - TEST TRANSACTION LIMIT
                $errorHelper = new ErrorHelper();
                $message = "MerchantModel::getTransactionLimit - APCO TEST TRANSACTION LIMIT
                SITE_LOGIN.TRANSACTIONS_LIMIT(:p_session_id_in = {$site_session_id}, :p_in_out_transaction_in = {$in_out_transaction}, :p_payment_method_in = {$payment_method}, :p_payment_amount_in = {$amount},
                :p_transaction_limit_out = {$transaction_limit_out}, :p_player_status_out = {$player_status_out}, :p_player_system_limit_out = {$player_system_limit_out}, :p_withdraw_fee_out = {$withdraw_fee_out})";
                $errorHelper->merchantError($message, $message);
            }
			return array("status"=>OK, "transaction_limit_out"=>$transaction_limit_out, 
			"player_status_out"=>$player_status_out, "player_system_limit_out"=>$player_system_limit_out, "withdraw_fee_out"=>$withdraw_fee_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $code = $ex->getCode();
            $errorHelper = new ErrorHelper();
            $message = "MerchantModel::getTransactionLimit <br /> SITE_LOGIN.TRANSACTIONS_LIMIT(:p_session_id_in = {$site_session_id}, :p_in_out_transaction_in = {$in_out_transaction}, :p_payment_method_in = {$payment_method}, :p_payment_amount_in = {$amount}, :p_transaction_limit_out, :p_player_status_out, :p_player_system_limit_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            if($code == "20121"){
                //EMV player
                return array("status"=>OK, "transaction_limit_out"=>500, "player_status_out"=>NO, "player_system_limit_out"=>"S", "withdraw_fee_out"=>0);
            }else if($code == "20309"){
                return array("status"=>NOK, "message"=>"Fee percent and Fee fix amount can not be defined both at the same time!");
            }else if($code == "20310"){
                return array("status"=>NOK, "message"=>"Fee percent and Fee fix amount are both empty! It must be defined eather Fee percent or Fee fix amount!");
            }else if($code == "20311") {
                return array("status"=>NOK, "message"=>"There are not enough funds on account to charge Withdraw and Withdraw Fee.");
            }else if($code == "20312"){
                return array("status"=>NOK, "message"=>"There are not enough funds on account to charge Withdraw Fee.");
            }else if($code == "20313"){
                return array("status"=>NOK, "message"=>"There are more then one Withdraw Fee profiles for WL for this currency!");
            }else{
                $errorHelper->merchantError($message, $message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
		}
	}
	
	// confirm transaction for purchase action in apco payment
    /**
     * @param $pc_session_id
     * @param $transaction_id
     * @param $amount
     * @param $apco_transaction_id
     * @param $currency
     * @param $credit_card_number
     * @param $credit_card_date_expires
     * @param $credit_card_holder
     * @param $credit_card_country
     * @param $credit_card_type
     * @param $start_time
     * @param $bank_code
     * @param $ip_address
     * @param $card_issuer_bank
     * @param $card_country
     * @param $client_email
     * @param $over_limit
     * @param $bank_auth_code
     * @param $payment_method_code
     * @param $merchant_order_reference_number
     * @param $site_domain
     * @param $payment_provider
     * @param string $token_id
     * @return array
     * @throws Zend_Exception
     */
	public function confirmPurchaseForPaymentProvider($pc_session_id, $transaction_id, $amount, $apco_transaction_id,
	$currency, $credit_card_number, $credit_card_date_expires, $credit_card_holder, $credit_card_country,
	$credit_card_type, $start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, 
	$over_limit, $bank_auth_code, $payment_method_code, $merchant_order_reference_number, $site_domain, $payment_provider, $token_id = ''){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        if($this->DEBUG_DEPOSIT) {
            //DEBUG THIS PART OF CODE - PAYIN RECEIVED PARAMETERS TO DB
            $errorHelper = new ErrorHelper();
            $errorHelper->sendMail("MerchantModel::confirmPurchaseForPaymentProvider - CREDIT_TRANSFER.APCO_PAY_IN <br /> PC session ID (p_session_id_in): {$pc_session_id} <br /> Transaction ID (p_transaction_id_in): {$transaction_id}" .
            "<br /> Amount (p_amount_in): {$amount} <br /> Apco transaction id (p_apco_transaction_id): {$apco_transaction_id} <br /> Currency (p_currency_in): {$currency}" .
            "<br /> Credit Card number (p_credit_card_number_in): {$credit_card_number} <br /> Credit card date expired (p_credit_card_date_expiried_in): {$credit_card_date_expires}" .
            "<br /> Credit card Holder (p_credit_card_holder_in): {$credit_card_holder} <br /> Credit card country (p_credit_card_country_in): {$credit_card_country}" .
            "<br /> Credit Card Type (p_credit_card_type_in): {$credit_card_type} <br /> Start time (p_start_time_in): {$start_time} <br /> Bank code (p_bank_code_in): {$bank_code} " .
            "<br /> IP address (p_ip_address_in): {$ip_address} <br /> Card issuer bank (p_card_issuer_bank_in): {$card_issuer_bank} <br /> Card country IP (p_card_country_ip_in): {$card_country}" .
            "<br /> Client email (p_client_email_in): {$client_email} <br /> Over limit (p_over_limit_in): {$over_limit} <br /> Bank auth code (p_bank_auth_code): {$bank_auth_code}" .
            "<br /> Merchant order ref number (p_apco_sequence_in): {$merchant_order_reference_number} <br /> Payment method code (p_source_in): {$payment_method_code}" .
            "<br /> Site domain (p_site_domen_in): {$site_domain}" .
            "<br /> Payment provider (p_payment_provider_in): {$payment_provider}" .
            "<br /> Token ID (p_token_in): {$token_id}"
            );
        }
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.APCO_PAY_IN(:p_session_id_in, :p_transaction_id_in, :p_amount_in, :p_apco_transaction_id, :p_currency_in,
			:p_credit_card_number_in, :p_credit_card_date_expiried_in, :p_credit_card_holder_in, :p_credit_card_country_in, :p_credit_card_type_in, :p_start_time_in,
			:p_bank_code_in, :p_ip_address_in, :p_CARD_ISSUER_BANK_in, :p_card_country_ip_in, :p_client_email_in, :p_over_limit_in,
			:p_BANK_AUTH_CODE, :p_apco_sequence_in, :p_source_in, :p_site_domen_in, :p_payment_provider_in, :p_token_in, :p_transaction_id_out)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$stmt->bindParam(':p_transaction_id_in', $transaction_id);
			$stmt->bindParam(':p_amount_in', $amount);
			$stmt->bindParam(':p_apco_transaction_id', $apco_transaction_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_credit_card_number_in', $credit_card_number);
			$stmt->bindParam(':p_credit_card_date_expiried_in', $credit_card_date_expires);
			$stmt->bindParam(':p_credit_card_holder_in', $credit_card_holder);
			$stmt->bindParam(':p_credit_card_country_in', $credit_card_country);
			$stmt->bindParam(':p_credit_card_type_in', $credit_card_type);
			$stmt->bindParam(':p_start_time_in', $start_time);
			$stmt->bindParam(':p_bank_code_in', $bank_code);
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$stmt->bindParam(':p_CARD_ISSUER_BANK_in', $card_issuer_bank);
			$stmt->bindParam(':p_card_country_ip_in', $card_country);
			$stmt->bindParam(':p_client_email_in', $client_email);
			$stmt->bindParam(':p_over_limit_in', $over_limit);
			$stmt->bindParam(':p_BANK_AUTH_CODE', $bank_auth_code);
			$stmt->bindParam(':p_apco_sequence_in', $merchant_order_reference_number);
			$stmt->bindParam(':p_source_in', $payment_method_code);
			$stmt->bindParam(':p_site_domen_in', $site_domain);
            $stmt->bindParam(':p_payment_provider_in', $payment_provider);
            $stmt->bindParam(':p_token_in', $token_id);
			$transaction_id_out = "-10";
			$stmt->bindParam(':p_transaction_id_out', $transaction_id_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();

            if($this->DEBUG_DEPOSIT) {
                //DEBUG THIS PART OF CODE - PAYIN PARAMETER VALUES
                $errorHelper = new ErrorHelper();
                $errorHelper->sendMail("MerchantModel::confirmPurchaseForPaymentProvider - CREDIT_TRANSFER.APCO_PAY_IN RETURNS P_TRANSACTION_ID_OUT: " . $transaction_id_out);
            }
			return array("status"=>OK, "transaction_id_out"=>$transaction_id_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $message = "MerchantModel::confirmPurchaseForPaymentProvider <br /> CREDIT_TRANSFER.APCO_PAY_IN(:p_session_id_in = {$pc_session_id}, :p_transaction_id_in = {$transaction_id}, :p_amount_in = {$amount},
            :p_apco_transaction_id = {$apco_transaction_id}, :p_currency_in = {$currency}, :p_credit_card_number_in = {$credit_card_number},
            :p_credit_card_date_expiried_in = {$credit_card_date_expires}, :p_credit_card_holder_in = {$credit_card_holder}, :p_credit_card_country_in = {$credit_card_country},
            :p_credit_card_type_in = {$credit_card_type}, :p_start_time_in = {$start_time}, :p_bank_code_in = {$bank_code}, :p_ip_address_in = {$ip_address},
            :p_CARD_ISSUER_BANK_in = {$card_issuer_bank}, :p_card_country_ip_in = {$card_country}, :p_client_email_in = {$client_email}, :p_over_limit_in = {$over_limit},
            :p_BANK_AUTH_CODE = {$bank_auth_code}, :p_apco_sequence_in = {$merchant_order_reference_number}, :p_source_in = {$payment_method_code}, :p_site_domen_in = {$site_domain},
            :p_payment_provider_in = {$payment_provider}, :p_token_in = {$token_id},
            :p_transaction_id_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper = new ErrorHelper();
			$errorHelper->merchantError($message, $message);

			//exception -20101,'Game session is open !!!!'
			$code = $ex->getCode();
			return array("status"=>NOK, "code"=>$code, "message"=>CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}
	
	//received apco currency code and returns text currency value
	/*public function findCurrency($currency_code){
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$FIND_CURRENCY(:p_currency_code_in, :p_currency_out)');
			$stmt->bindParam(':p_currency_code_in', $currency_code);
			$currency_text = "";
			$stmt->bindParam(':p_currency_out', $currency_text, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $currency_text;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::findCurrency <br /> WEB_REPORTS.M\$FIND_CURRENCY(:p_currency_code_in = {$currency_code}, :p_currency_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			return NOK_EXCEPTION;
		}
	}*/
	
	//returns merchant code and merchant password for apco payment soap web service for checking transactions
    /**
     * @param $site_session_id
     * @return array
     * @throws Zend_Exception
     */
	public function getMerchantCodes($site_session_id){
        $config = Zend_Registry::get('config');
        $merchant_code = $config->apcoMerchantCode;
        $merchant_password = $config->apcoMerchantPassword;
        return array("status"=>OK, "merchant_code"=>$merchant_code, "merchant_password"=>$merchant_password);
		/*$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.M$SECRET_WORDS_sp(:p_session_id_in, :P_Merchant_Code_OUT, :P_Password_OUT)');
			$stmt->bindParam(':p_session_id_in', $site_session_id);
			$merchant_code = "";
			$stmt->bindParam(':P_Merchant_Code_OUT', $merchant_code, SQLT_CHR, 255);
			$merchant_password = "";
			$stmt->bindParam(':P_Password_OUT', $merchant_password, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "merchant_code"=>$merchant_code, "merchant_password"=>$merchant_password);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::getMerchantCodes <br /> SITE_LOGIN.M\$SECRET_WORDS_sp(p_session_id_in = {$site_session_id}, p_Merchant_Code_OUT, p_Password_OUT) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}*/
	}
	
	//returns transaction issued by database for apco payment with credit cards
    /**
     * @param $site_session_id
     * @param $currency
     * @return array
     * @throws Zend_Exception
     */
	public function getTransactionId($site_session_id, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.M$TRANSACTION_ID(:p_session_id_in, :p_currency_in, :p_transaction_id_out, :p_curency_ok_out, :p_apco_trans_pay_in_id_out, :p_apco_trans_pay_out_id_out, :p_oref_out)');
			$stmt->bindParam(':p_session_id_in', $site_session_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$transaction_id = "";
			$stmt->bindParam(':p_transaction_id_out', $transaction_id, SQLT_CHR, 255);
			$currency_ok = "";
			$stmt->bindParam(':p_curency_ok_out', $currency_ok, SQLT_CHR, 255);
			$payment_provider_transaction_id_purchase = "";
			$stmt->bindParam(':p_apco_trans_pay_in_id_out', $payment_provider_transaction_id_purchase, SQLT_CHR, 255);
			$payment_provider_transaction_id_credit = "";
			$stmt->bindParam(':p_apco_trans_pay_out_id_out', $payment_provider_transaction_id_credit, SQLT_CHR, 255);
			$oref_transaction_id = "";
			$stmt->bindParam(':p_oref_out', $oref_transaction_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();

            if($this->DEBUG) {
                //DEBUG THIS PART OF CODE - GET TRANSACTION ID PARAMETERS
                $errorHelper = new ErrorHelper();
                $errorHelper->sendMail("MerchantModel::getTransactionId  SITE_LOGIN.M\$TRASACTION_ID >>> <br /> (site_session_id) p_session_id_in: {$site_session_id} <br /> p_currency_in: {$currency} <br /> p_transaction_id_out: {$transaction_id} <br /> p_currency_ok_out: {$currency_ok}
                <br /> (APCO_TRANSACTION_ID_PURCHASE) p_apco_trans_pay_in_id_out: {$payment_provider_transaction_id_purchase} <br /> (APCO_TRANSACTION_ID_CREDIT) p_apco_trans_pay_out_id_out: {$payment_provider_transaction_id_credit} <br /> (OREF) p_oref_out: {$oref_transaction_id}");
            }

			return array("status"=>OK,
                "transaction_id"=>$transaction_id,
                "currency_ok"=>$currency_ok,
			    "payment_provider_transaction_id_purchase"=>$payment_provider_transaction_id_purchase,
			    "payment_provider_transaction_id_credit"=>$payment_provider_transaction_id_credit,
                "oref_transaction_id"=>$oref_transaction_id
            );
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::getTransactionId <br /> SITE_LOGIN.M\$TRASACTION_ID(:p_session_id_in = {$site_session_id}, :p_currency_in = {$currency}, :p_transaction_id_out, :p_curency_ok_out, :p_apco_trans_pay_in_id_out, :p_apco_trans_pay_out_id_out, :p_oref_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}		
	}
	
	//Receives Apco Payment secret words
    /**
     * @param $site_session_id
     * @return array
     * @throws Zend_Exception
     */
	public function getSecretWords($site_session_id){
        $config = Zend_Registry::get('config');
        $profile_id = $config->apcoProfileID;
        $secret_word = $config->apcoHashingSecretWord;
        return array("status"=>OK, "profile_id"=>$profile_id, "secret_word"=>$secret_word);
		/*$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.M$SECRET_WORDS(:p_session_id_in, :p_profile_id, :p_secret_word)');
			$stmt->bindParam(':p_session_id_in', $site_session_id);
			$profileID = "";
			$stmt->bindParam(':p_profile_id', $profileID, SQLT_CHR, 255);
			$secretWord = "";
			$stmt->bindParam(':p_secret_word', $secretWord, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "profile_id"=>$profileID, "secret_word"=>$secretWord);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::getSecretWords <br /> SITE_LOGIN.M\$SECRET_WORDS(:p_session_id_in = {$site_session_id}, :p_profile_id, :p_secret_word) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}*/
	}

	//does bonus credit deposit if there is bonus campaign code
	//makes payment on player account when he made payment after APCO confirms that he paid to them money
    /**
     * @param $pc_session_id
     * @param $transaction_id
     * @param $amount
     * @param $apco_transaction_id
     * @param $currency
     * @param $credit_card_number
     * @param $credit_card_date_expires
     * @param $credit_card_holder
     * @param $credit_card_country
     * @param $credit_card_type
     * @param $start_time
     * @param $bank_code
     * @param $ip_address
     * @param $card_issuer_bank
     * @param $card_country
     * @param $client_email
     * @param $over_limit
     * @param $bank_auth_code
     * @param $payment_method_code
     * @param $merchant_order_reference_number
     * @param $site_domain
     * @param $bonus_code
     * @param $fee_value
     * @param $payment_provider
     * @param string $token_id
     * @return array
     * @throws Zend_Exception
     */
	public function bonusCreditDeposit($pc_session_id, $transaction_id, $amount, $apco_transaction_id,
	$currency, $credit_card_number, $credit_card_date_expires, $credit_card_holder, $credit_card_country,
	$credit_card_type, $start_time, $bank_code, $ip_address, $card_issuer_bank, $card_country, $client_email, 
	$over_limit, $bank_auth_code, $payment_method_code, $merchant_order_reference_number, $site_domain, $bonus_code,
    $fee_value, $payment_provider, $token_id = ''){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        if($this->DEBUG_DEPOSIT) {
            //DEBUG THIS PART OF CODE - PAYIN RECEIVED PARAMETERS TO DB
            $errorHelper = new ErrorHelper();
            $message = "MerchantModel::bonusCreditDeposit BONUS_SYSTEM.BONUS_CREDITS_DEPOSIT <br /> PC session ID (p_session_id_in): {$pc_session_id} <br /> Transaction ID (p_transaction_id_in): {$transaction_id}" .
            "<br /> Amount (p_amount_in): {$amount} <br /> Apco transaction id (p_apco_transaction_id): {$apco_transaction_id} <br /> Currency (p_currency_in): {$currency}" .
            "<br /> Credit Card number (p_credit_card_number_in): {$credit_card_number} <br /> Credit card date expired (p_credit_card_date_expiried_in): {$credit_card_date_expires}" .
            "<br /> Credit card Holder (p_credit_card_holder_in): {$credit_card_holder} <br /> Credit card country (p_credit_card_country_in): {$credit_card_country}" .
            "<br /> Credit Card Type (p_credit_card_type_in): {$credit_card_type} <br /> Start time (p_start_time_in): {$start_time} <br /> Bank code (p_bank_code_in): {$bank_code}" .
            "<br /> IP address (p_ip_address_in): {$ip_address} <br /> Card issuer bank (p_card_issuer_bank_in): {$card_issuer_bank} <br /> Card country IP (p_card_country_ip_in): {$card_country}" .
            "<br /> Client email (p_client_email_in): {$client_email} <br /> Over limit (p_over_limit_in): {$over_limit} <br /> Bank auth code (p_bank_auth_code): {$bank_auth_code}" .
            "<br /> Merchant order ref number (p_apco_sequence_in): {$merchant_order_reference_number} <br /> Payment method code (p_source_in): {$payment_method_code}" .
            "<br /> Site domain (p_site_domen_in): {$site_domain} <br /> Bonus campaign code (p_bonus_code_in): {$bonus_code} Fee value (p_fee_value_in): {$fee_value}
            <br /> Payment Provider (p_payment_provider_in): {$payment_provider} <br /> Token ID (p_token_in): {$token_id}";
            $errorHelper->merchantError($message, $message);
        }
		try{
			$stmt = $dbAdapter->prepare('CALL BONUS_SYSTEM.BONUS_CREDITS_DEPOSIT(:p_session_id_in, :p_transaction_id_in, :p_amount_in, :p_apco_transaction_id, :p_currency_in, :p_credit_card_number_in, :p_credit_card_date_expiried_in, :p_credit_card_holder_in, :p_credit_card_country_in, :p_credit_card_type_in, :p_start_time_in, :p_bank_code_in, :p_ip_address_in, :p_CARD_ISSUER_BANK_in, :p_card_country_ip_in, :p_client_email_in, :p_over_limit_in, :p_BANK_AUTH_CODE, :p_apco_sequence_in, :p_source_in, :p_site_domen_in, :p_bonus_code_in, :p_fee_value_in, :p_payment_provider_in, :p_token_in, :p_transaction_id_out, :error_message)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$stmt->bindParam(':p_transaction_id_in', $transaction_id);
			$stmt->bindParam(':p_amount_in', $amount);
			$stmt->bindParam(':p_apco_transaction_id', $apco_transaction_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_credit_card_number_in', $credit_card_number);
			$stmt->bindParam(':p_credit_card_date_expiried_in', $credit_card_date_expires);
			$stmt->bindParam(':p_credit_card_holder_in', $credit_card_holder);
			$stmt->bindParam(':p_credit_card_country_in', $credit_card_country);
			$stmt->bindParam(':p_credit_card_type_in', $credit_card_type);
			$stmt->bindParam(':p_start_time_in', $start_time);
			$stmt->bindParam(':p_bank_code_in', $bank_code);
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$stmt->bindParam(':p_CARD_ISSUER_BANK_in', $card_issuer_bank);
			$stmt->bindParam(':p_card_country_ip_in', $card_country);
			$stmt->bindParam(':p_client_email_in', $client_email);
			$stmt->bindParam(':p_over_limit_in', $over_limit);
			$stmt->bindParam(':p_BANK_AUTH_CODE', $bank_auth_code);
			$stmt->bindParam(':p_apco_sequence_in', $merchant_order_reference_number);
			$stmt->bindParam(':p_source_in', $payment_method_code);
			$stmt->bindParam(':p_site_domen_in', $site_domain);
			$stmt->bindParam(':p_bonus_code_in', $bonus_code);
			$stmt->bindParam(':p_fee_value_in', $fee_value);
            $stmt->bindParam(':p_payment_provider_in', $payment_provider);
            $stmt->bindParam(':p_token_in', $token_id);
			$transaction_id_out = "-10";
			$stmt->bindParam(':p_transaction_id_out', $transaction_id_out, SQLT_CHR, 255);
			$error_message = "";
			$stmt->bindParam(':error_message', $error_message, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();

			if($this->DEBUG_DEPOSIT) {
                //DEBUG THIS PART OF CODE - PAYIN PARAMETER VALUES
                $errorHelper = new ErrorHelper();
                $message = "MerchantModel::bonusCreditDeposit - PROCEDURE BONUS_SYSTEM.BONUS_CREDITS_DEPOSIT RETURNS P_TRANSACTION_ID_OUT: " . $transaction_id_out;
                $errorHelper->merchantError($message, $message);
                $message = "MerchantModel::bonusCreditDeposit - PROCEDURE BONUS_SYSTEM.BONUS_CREDITS_DEPOSIT RETURNS ERROR_MESSAGE: " . $error_message;
                $errorHelper->merchantError($message, $message);
            }
			return array("status"=>OK, "transaction_id_out"=>$transaction_id_out, "error_message"=>$error_message);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::bonusCreditDeposit <br /> BONUS_SYSTEM.BONUS_CREDITS_DEPOSIT(:p_session_id_in = {$pc_session_id}, :p_transaction_id_in = {$transaction_id}, :p_amount_in = {$amount},
            :p_apco_transaction_id = {$apco_transaction_id}, :p_currency_in = {$currency}, :p_credit_card_number_in = {$credit_card_number}, :p_credit_card_date_expiried_in = {$credit_card_date_expires},
            :p_credit_card_holder_in = {$credit_card_holder}, :p_credit_card_country_in = {$credit_card_country}, :p_credit_card_type_in = {$credit_card_type}, :p_start_time_in = {$start_time},
            :p_bank_code_in = {$bank_code}, :p_ip_address_in = {$ip_address}, :p_CARD_ISSUER_BANK_in = {$card_issuer_bank}, :p_card_country_ip_in = {$card_country}, :p_client_email_in = {$client_email},
            :p_over_limit_in = {$over_limit}, :p_BANK_AUTH_CODE = {$bank_auth_code}, :p_apco_sequence_in = {$merchant_order_reference_number}, :p_source_in = {$payment_method_code}, :p_site_domen_in = {$site_domain},
            :p_bonus_code_in = {$bonus_code}, :p_fee_value_in = {$fee_value}, :p_payment_provider_in = {$payment_provider}, :p_token_in = {$token_id}
            :p_transaction_id_out, :error_message) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			//exception -20101,'Game session is open !!!!'
			$code = $ex->getCode();
			return array("status"=>NOK, "code"=>$code, "message"=>CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}

    //get payment limits for white label
    /**
     * @param $site_session_id
     * @param $currency
     * @return array
     * @throws Zend_Exception
     */
    public function getPaymentLimitsForWhiteLabel($site_session_id, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        $white_label_id = 0;
        try {
            $stmt = $dbAdapter->prepare('CALL WEB_REPORTS.get_wl_all_payment_limits(:p_session_id_in, :p_wl_id_in, :p_currency_in, :p_credits_out, :p_active_promotion_out, :cur_result_out)');
            $stmt->bindParam(':p_session_id_in', $site_session_id);
            $stmt->bindParam(':p_wl_id_in', $white_label_id);
            $stmt->bindParam(':p_currency_in', $currency);
            $credits_out = "";
            $stmt->bindParam(':p_credits_out', $credits_out, SQLT_CHR, 255);
            $active_promotion_out = "";
            $stmt->bindParam(':p_active_promotion_out', $active_promotion_out, SQLT_CHR, 255);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':cur_result_out', $cursor);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status" => OK, "credits" => $credits_out, "active_promotion" => $active_promotion_out, "cursor" => $cursor);
        } catch (Zend_Exception $ex) {
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $errorHelper = new ErrorHelper();
            $message = "MerchantModel::getPaymentLimitsForWhiteLabel(site_session_id = {$site_session_id}, currency = {$currency}) <br /> WEB_REPORTS.get_wl_all_payment_limits(:p_session_id_in = {$site_session_id}, :p_wl_id_in = 0, :p_currency_in = {$currency}, :cur_result_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->merchantError($message, $message);
            return array("status" => NOK, "message" => NOK_EXCEPTION);
        }
	}

    //update iban/swift to player
    /**
     * @param $player_id
     * @param $iban
     * @param $swift
     * @return array
     * @throws Zend_Exception
     */
    public function setIbanSwiftForPlayer($player_id, $iban, $swift){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGE_SUBJECTS.set_iban_swift(:p_subject_id_in, :p_iban_in, :p_swift_in, :p_status_out)');
			$stmt->bindParam(':p_subject_id_in', $player_id);
			$stmt->bindParam(':p_iban_in', $iban);
            $stmt->bindParam(':p_swift_in', $swift);
            $status_out = "";
            $stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "status_out"=>$status_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::setIbanSwiftForPlayer(player_id = {$player_id}, iban={$iban}, swift={$swift}) <br /> MANAGE_SUBJECTS.set_iban_swift(:p_subject_id_in = {$player_id}, :p_iban_in = {$iban}, :p_swift_in = {$swift}, :p_status_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    //Check open game session for player with site session id * throws exception if there is an game opened
    /**
     * @param $pc_session_id
     * @return array
     * @throws Zend_Exception
     */
	public function closeOpenedGameSession($pc_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL GAME_SESSION.close_noclosed_sess_for_trans(:pc_session_id_in)');
			$stmt->bindParam(':pc_session_id_in', $pc_session_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::closeOpenedGameSession <br /> GAME_SESSION.close_noclosed_sess_for_trans(pc_session_id_in = {$pc_session_id}) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			//$errorHelper->merchantError($message, $message);
            $errorHelper->merchantErrorLog($message);
			return array("status"=>NOK);
		}
	}

    /**
     * @param $player_id
     * @return array
     * @throws Zend_Exception
     */
    public function getLastTokenCreditCardList($player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.get_last_token_and_cc_info(:p_player_id_in, :p_token_out, :p_cc_out)');
			$stmt->bindParam(':p_player_id_in', $player_id);
            $token_id = '';
			$stmt->bindParam(':p_token_out', $token_id, SQLT_CHR, 255);
            $credit_card_number = '';
            $stmt->bindParam(':p_cc_out', $credit_card_number, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "token_id"=>$token_id, "credit_card_number"=>$credit_card_number);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MerchantModel::getLastTokenCreditCardList(player_id = {$player_id}) <br />  MANAGMENT_CORE.get_last_token_and_cc_info(:p_player_id_in = {$player_id}, :p_token_out, :p_cc_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

}