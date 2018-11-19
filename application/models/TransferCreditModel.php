<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class TransferCreditModel{
	public function __construct(){
	}
	//reset browser credits
    /**
     * @param $session_id
     * @return string
     * @throws Zend_Exception
     */
	public function resetBrowser($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_CASH.M$RESET_TERMINAL(:p_session_id_in, :p_status_out, :p_credits_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$status_out = "-1000000000000";
			$stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
			$credits_out = "-1000000000000";
			$stmt->bindParam(':p_credits_out', $credits_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>$status_out, "credits_out"=>$credits_out);
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
			return INTERNAL_ERROR;
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex2);
			$errorHelper->serviceError($message, $message);
			return INTERNAL_ERROR;
		}		
	}
	
	//transfer credits from web player
    /**
     * @param $session_id
     * @param $aff_id
     * @param $player_id
     * @param $amount
     * @param $currency
     * @return array|string
     * @throws Zend_Exception
     */
	public function transferCreditsFromWebPlayer($session_id, $aff_id, $player_id, $amount, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_CASH.M$TRANSFER_CREDIT_FROM_WEB_PL(:p_session_id_in, :p_transaction_type_name_in, :p_aff_id_in, :p_player_id_in, :p_amount_in, :p_currency_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$transaction_type_name = null;
			$stmt->bindParam(':p_transaction_type_name_in', $transaction_type_name);
			$stmt->bindParam(':p_aff_id_in', $aff_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_amount_in', $amount);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return INTERNAL_ERROR;
		}
	}
	
	//usb transfer credit for surfing
    /**
     * @param $session_id
     * @param $credits
     * @return array|string
     * @throws Zend_Exception
     */
	public function usbCreditTransfer($session_id, $credits){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_CASH.M$PAY_IN_PAY_OUT_USB(:p_session_id_in, :p_credits_in, :p_status_out, :p_credits_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_credits_in', $credits);
			$status_out = NO;  //Y or N
			$stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
			$credits_out = "0.00";
			$stmt->bindParam(':p_credits_out', $credits_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array($status_out, $credits_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return INTERNAL_ERROR;
		}
	}

	//terminal and pc player payout
    /**
     * @param $session_id
     * @param null $affiliate_id
     * @param null $player_id
     * @param $amount
     * @param $currency
     * @param $duration_limit
     * @return string
     * @throws Zend_Exception
     */
	public function terminalPayout($session_id, $affiliate_id = null, $player_id = null, $amount, $currency, $duration_limit){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_CASH.M$TRNS_CREDIT_FROM_DIR_WEB_PL(:p_session_id_in, :p_transaction_type_name_in, :p_aff_id_NEW_in, :p_player_id_NEW_in, :p_amount_in, :p_currency_in, :p_duration_limit_in, :p_web_credits_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$transaction_type = null;
			$stmt->bindParam(':p_transaction_type_name_in', $transaction_type);
			$stmt->bindParam(':p_aff_id_NEW_in', $affiliate_id);
			$stmt->bindParam(':p_player_id_NEW_in', $player_id);
			$stmt->bindParam(':p_amount_in', $amount);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_duration_limit_in', $duration_limit);
			$web_credits = "0.00";
			$stmt->bindParam(':p_web_credits_out', $web_credits, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $web_credits;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();			
			$code = $ex->getCode();			
			if($code == "20111"){
				$errorHelper = new ErrorHelper();
				$message = "Not enough credits!!! session_id = " . $session_id;
				//$errorHelper->serviceError($message, $message);
				$errorHelper->serviceErrorLog($message);
				return NOT_ENOUGH_CREDITS;
			}else{
				$errorHelper = new ErrorHelper();
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->serviceError($message, $message);
				return INTERNAL_ERROR;
			}
		}
	}
	
	//ZA ANDROID CASHIER APP - OVO ISPOD
	//transfer credits with backoffice session from affiliate to player
    /**
     * @param $session_id
     * @param $aff_id
     * @param $player_id
     * @param $amount
     * @param $credit_status
     * @param $auto_credit_increment
     * @param $enabled_increment
     * @param $currency
     * @return array
     * @throws Zend_Exception
     */
	public function transferAffiliateToPlayer($session_id, $aff_id, $player_id, $amount, $credit_status, $auto_credit_increment, $enabled_increment, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$TRANSFER_CREDIT_TO_DIR_PL(:p_session_id_in, :p_aff_id_in, :p_player_id_in, :p_amount_in, :p_credit_status_aff_in, :p_auto_credits_increment_in, :p_enabled_auto_increment, :p_currency_in, :p_apco_transaction_id, :p_credit_card_number_in, :p_credit_card_date_expiried_in, :p_credit_card_holder_in, :p_credit_card_country_in, :p_credit_card_type_in, :p_start_time_in, :p_bank_code_in, :p_ip_address_in, :p_CARD_ISSUER_BANK_in, :p_card_country_ip_in, :p_transaction_id_in, :p_client_email_in, :p_BANK_AUTH_CODE_in, :p_source_in, :p_apco_sequence_in, :p_apco_user_trans_id_out, :p_transaction_id_for)');
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.TRANSFER_CREDIT_TO_DIR_PL(:p_session_id_in, :p_aff_id_in, :p_player_id_in, :p_amount_in, :p_credit_status_aff_in, :p_auto_credits_increment_in,
			:p_enabled_auto_increment, :p_currency_in, :p_apco_transaction_id, :p_credit_card_number_in, :p_credit_card_date_expiried_in, :p_credit_card_holder_in, :p_credit_card_country_in,
			:p_credit_card_type_in, :p_start_time_in, :p_bank_code_in, :p_ip_address_in, :p_CARD_ISSUER_BANK_in, :p_card_country_ip_in, :p_transaction_id_in, :p_client_email_in, :p_BANK_AUTH_CODE_in,
			:p_source_in, :p_apco_sequence_in, :p_site_domen_in, :p_payment_provider_in, :p_token_in, :p_apco_user_trans_id_out, :p_transaction_id_for)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $aff_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_amount_in', $amount);
			$stmt->bindParam(':p_credit_status_aff_in', $credit_status);
			$stmt->bindParam(':p_auto_credits_increment_in', $auto_credit_increment);
			$stmt->bindParam(':p_enabled_auto_increment', $enabled_increment);
			$stmt->bindParam(':p_currency_in', $currency);
			$apco_transaction_id = null;
			$stmt->bindParam(':p_apco_transaction_id', $apco_transaction_id);
			$credit_card_number = null;
			$stmt->bindParam(':p_credit_card_number_in', $credit_card_number);
			$credit_card_date_expired = null;
			$stmt->bindParam(':p_credit_card_date_expiried_in', $credit_card_date_expired);
			$credit_card_holder = null;
			$stmt->bindParam(':p_credit_card_holder_in', $credit_card_holder);
			$credit_card_country = null;
			$stmt->bindParam(':p_credit_card_country_in', $credit_card_country);
			$credit_card_type = null;
			$stmt->bindParam(':p_credit_card_type_in', $credit_card_type);
			$start_time_in = null;
			$stmt->bindParam(':p_start_time_in', $start_time_in);
			$bank_code = null;
			$stmt->bindParam(':p_bank_code_in', $bank_code);
			$ip_address = null;
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$card_issuer_bank = null;
			$stmt->bindParam(':p_CARD_ISSUER_BANK_in', $card_issuer_bank);
			$card_country_ip = null;
			$stmt->bindParam(':p_card_country_ip_in', $card_country_ip);
			$transaction_id = null;
			$stmt->bindParam(':p_transaction_id_in', $transaction_id);
			$client_email = null;
			$stmt->bindParam(':p_client_email_in', $client_email);
			$bank_auth_code = null;
			$stmt->bindParam(':p_BANK_AUTH_CODE_in', $bank_auth_code);
			$source = null;
			$stmt->bindParam(':p_source_in', $source);
			$apco_sequence = null;
			$stmt->bindParam(':p_apco_sequence_in', $apco_sequence);
            $site_domain = null;
			$stmt->bindParam(':p_site_domen_in', $site_domain);
            $payment_provider = null;
			$stmt->bindParam(':p_payment_provider_in', $payment_provider);
            $token_id = null;
			$stmt->bindParam(':p_token_in', $token_id);
			$apco_transaction_id_out = "";
			$stmt->bindParam(':p_apco_user_trans_id_out', $apco_transaction_id_out, SQLT_CHR, 255);
			$transaction_id_for = null;
			$stmt->bindParam(':p_transaction_id_for', $transaction_id_for, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			if($code == "20200"){
				return array("status"=>NOK, "message"=>"Not enough credits. No communication between servers!!!");
			}
			else if($code == "20201"){
				return array("status"=>NOK, "message"=>"Limit has been reached!!!");
			}
			else if($code == "20710"){
				//return $this->translate->_("Limit has been reached!!!");
				$pos1 = strpos($ex->getMessage(), "ORA");
				$pos2 = strpos($ex->getMessage(), "ORA", $pos1 + strlen("ORA"));
				$message1 = trim(substr($ex->getMessage(), 0, $pos2));
				$message2 = trim(substr($message1, strpos($message1, "Maximum deposit limit reached!"), strlen($message1)));
				$amount = trim(substr($message2, strlen("Maximum deposit limit reached!"), strlen($message2)));
				return array("status"=>NOK, "message"=>"Maximum deposit limit reached!" . " " . 'Available credits ' . $amount);
			}
			else if($code == "20666"){
				return array("status"=>NOK, "message"=>"Unexpected error!");
			}
			else if($code == "20224"){
				return array("status"=>NOK, "message"=>"Autoincrement is set low");
			}
			else{
				return array("status"=>NOK, "message"=>CursorToArrayHelper::getExceptionTraceAsString($ex));
			}
		}
	}
	
	//list terminal player details from parent affiliate
    /**
     * @param $session_id
     * @param $terminal_player_id
     * @return mixed
     * @throws Zend_Exception
     */
	public function fillInAffiliateToTerminalPlayer($session_id, $terminal_player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_id_in', $terminal_player_id);
			require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
			$modelSubjectTypes = new SubjectTypesModel();
			$player_type = $modelSubjectTypes->getSubjectType("MANAGMENT_TYPES.NAME_IN_TERMINAL_PLAYER");
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$page_number = 1;
			$stmt->bindParam(':p_page_number_in', $page_number);
			$hits_per_page = 25;
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$order_by = 1;
			$stmt->bindParam(':p_order_by_in', $order_by);
			$sort_order = "asc";
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array($table, $info);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}
	
	//find credit status from affiliate and affiliates used currency
    /**
     * @param $session_id
     * @param $currency
     * @return mixed
     * @throws Zend_Exception
     */
	public function findCreditStatus($session_id, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$FIND_CREDIT_STATUS(:p_session_id_in, :p_currency_in, :p_credits_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$credits = "0.00";
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $credits;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}

	//list pc player details from parent affiliate
    /**
     * @param $session_id
     * @param $player_id
     * @return mixed
     * @throws Zend_Exception
     */
	public function fillInAffiliateToPlayer($session_id, $player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{	
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_id_in', $player_id);
			require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
			$modelSubjectTypes = new SubjectTypesModel();
			$player_type = $modelSubjectTypes->getSubjectType("MANAGMENT_TYPES.NAME_IN_PC_PLAYER");
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$page_number = 1;
			$stmt->bindParam(':p_page_number_in', $page_number);
			$hits_per_page = 25;
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$order_by = 1;
			$stmt->bindParam(':p_order_by_in', $order_by);
			$sort_order = "asc";
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$stmt->bindCursor(':list_direct_pc_players_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array($table, $info);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}
	
	//terminal and pc player payout
    /**
     * @param $session_id
     * @param $affiliate_id
     * @param $player_id
     * @param $amount
     * @param $currency
     * @throws Zend_Exception
     */
	public function playerPayout($session_id, $affiliate_id, $player_id, $amount, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$TRANSFER_CREDIT_FROM_DIR_PL(:p_session_id_in, :p_transaction_type_name_in,
				:p_aff_id_in, :p_player_id_in, :p_amount_in, :p_currency_in,
				:p_apco_transaction_id, :p_credit_card_number_in, :p_credit_card_date_expiried_in,
				:p_credit_card_holder_in, :p_credit_card_country_in, :p_credit_card_type_in,
				:p_start_time, :p_bank_code, :p_ip_address_in, :p_card_bank_issuer_in,
				:p_card_country_ip_in, :p_transaction_id_in, :p_client_email_in, :p_bank_auth_code_in,
				:p_source_in, :p_token_in, :p_apco_user_trans_id_out, :p_transaction_id_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$transaction_type = null;
			$stmt->bindParam(':p_transaction_type_name_in', $transaction_type);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_amount_in', $amount);
			$stmt->bindParam(':p_currency_in', $currency);
			$apco_transaction_id = null;
			$stmt->bindParam(':p_apco_transaction_id', $$apco_transaction_id);
			$credit_card_number = null;
			$stmt->bindParam(':p_credit_card_number_in', $credit_card_number);
			$credit_card_expired = null;
			$stmt->bindParam(':p_credit_card_date_expiried_in', $credit_card_expired);
			$credit_card_holder = null;
			$stmt->bindParam(':p_credit_card_holder_in', $credit_card_holder);
			$credit_card_country = null;
			$stmt->bindParam(':p_credit_card_country_in', $credit_card_country);
			$credit_card_type = null;
			$stmt->bindParam(':p_credit_card_type_in', $credit_card_type);
			$start_time = null;
			$stmt->bindParam(':p_start_time', $start_time);
			$bank_code = null;
			$stmt->bindParam(':p_bank_code', $bank_code);
			$ip_address = null;
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$card_bank_issuer = null;
			$stmt->bindParam(':p_card_bank_issuer_in', $card_bank_issuer);
			$card_country_ip = null;
			$stmt->bindParam(':p_card_country_ip_in', $card_country_ip);
			$transaction_id = null;
			$stmt->bindParam(':p_transaction_id_in', $transaction_id);
			$client_email = null;
			$stmt->bindParam(':p_client_email_in', $client_email);
			$bank_auth_code = null;
			$stmt->bindParam(':p_bank_auth_code_in', $bank_auth_code);
			$source = null;
			$stmt->bindParam(':p_source_in', $source);
            $token_id = null;
			$stmt->bindParam(':p_token_in', $token_id);
			$apco_user_trans_id_out = "";
			$stmt->bindParam(':p_apco_user_trans_id_out', $apco_user_trans_id_out, SQLT_CHR, 255);
			$transaction_id_out = "";
			$stmt->bindParam(':p_transaction_id_out', $transaction_id_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}
}