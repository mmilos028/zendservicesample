<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class AndroidCashierModel {
	//error constants
	public function __construct(){
	}
	/** Open BO session, login into bo session for android client */
    /**
     * @param $username
     * @param $password
     * @param $ip_address
     * @return array
     * @throws Zend_Exception
     */
	public function openBoSession($username, $password, $ip_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$config = Zend_Registry::get("config");
		$dbAdapter->beginTransaction();
		$result = -100000;
        $session_type_name_in = BACKOFFICE_SESSION;
        $origin_site = $config->origin_site;
        $country = "";
        $city = "";
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$LOGIN_USER(:p_username_in, :p_password_in, :p_ip_address_in, :p_country_name_in, :p_city_in, :p_session_type_name_in, :p_origin_in, :p_session_out, :p_currency_out, :p_multi_currency_out, :p_auto_credit_increment_out, :p_auto_credit_increment_y_out, :p_subject_type_id_out, :p_subject_type_name_out, :p_subject_super_type_id_out, :p_subject_super_type_name_out, :p_session_type_id_out, :p_session_type_name_out, :p_first_name_out, :p_last_name_out, :p_last_time_collect_out, :p_online_casino_out)');
			$stmt->bindParam(":p_username_in", $username);
			$stmt->bindParam(":p_password_in", $password);
			$stmt->bindParam(":p_ip_address_in", $ip_address);
			$stmt->bindParam(":p_country_name_in", $country);
			$stmt->bindParam(":p_city_in", $city);
			$stmt->bindParam(":p_session_type_name_in", $session_type_name_in, SQLT_CHR, 255);
			$stmt->bindParam(":p_origin_in", $origin_site);
			$stmt->bindParam(":p_session_out", $result, SQLT_CHR, 255);
			$currency = "";
			$stmt->bindParam(":p_currency_out", $currency, SQLT_CHR, 10);
			$multi_currency = "";
			//logged user is multicurrency
			$stmt->bindParam(":p_multi_currency_out", $multi_currency, SQLT_CHR, 255);
			$auto_credit_increment = "-100000000000000000000";
			//enabled autoincrement credits amount
			$stmt->bindParam(":p_auto_credit_increment_out", $auto_credit_increment, SQLT_CHR, 255);
			$auto_credit_increment_y = NO;
			//if Y then autocredits is enabled if N then autocredits is disabled
			$stmt->bindParam(":p_auto_credit_increment_y_out", $auto_credit_increment_y, SQLT_CHR, 10);
			$subject_type_id = 0;
			//number of affiliate that is logging in
			$stmt->bindParam(":p_subject_type_id_out", $subject_type_id, SQLT_CHR, 255);
			$subject_type_name = "";
			//affiliates name that is logging in
			$stmt->bindParam(":p_subject_type_name_out", $subject_type_name, SQLT_CHR, 255);
			//number of parent affiliate that logged affiliate belongs to
			$subject_super_type_id = 0;
			$stmt->bindParam(":p_subject_super_type_id_out", $subject_super_type_id, SQLT_CHR, 255);
			//name of parent affiliate
			$subject_super_type_name= "";
			$stmt->bindParam(':p_subject_super_type_name_out', $subject_super_type_name, SQLT_CHR, 255);
			//session type number for logged in user
			$session_type_id = 0;
			$stmt->bindParam(":p_session_type_id_out", $session_type_id, SQLT_CHR, 255);
			//session type name from logged in user
			$session_type_name = "";
			$stmt->bindParam(":p_session_type_name_out", $session_type_name, SQLT_CHR, 255);
			$first_name = "";
			$stmt->bindParam(":p_first_name_out", $first_name, SQLT_CHR, 255);
			$last_name = "";
			$stmt->bindParam(":p_last_name_out", $last_name, SQLT_CHR, 255);
			$last_time_collect = "";
			$stmt->bindParam(":p_last_time_collect_out", $last_time_collect, SQLT_CHR, 255);
			$is_online_casino = "";
			$stmt->bindParam(":p_online_casino_out", $is_online_casino, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			if($result == -1 || $result == -2 || $result == -3 || $result == -4 || $result == -100000){
				//DEBUG THIS PART OF CODE
				/*
				$errorHelper = new ErrorHelper();
				$message = "AuthorizationModel - openBoSession - MANAGMENT_CORE.M_LOGIN_USER returns backoffice session: {$result}"; 
				$errorHelper->serviceError($message);
				*/
				return array("status"=>NOK);
			}
			if($result >= 1){
				return array("status"=>OK, "session_id"=>$result, "currency"=>$currency, 
				"username"=>$username, "first_name"=>$first_name, "last_name"=>$last_name, 
				"rola"=>$subject_type_name, "auto_credit_increment_amount"=>$auto_credit_increment,
				"auto_credit_increment_enabled"=>$auto_credit_increment_y);
			}
			return array("status"=>NOK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MANAGMENT_CORE.M\$LOGIN_USER(:p_username_in = {$username}, :p_password_in=, :p_ip_address_in={$ip_address}, :p_country_name_in = {$country}, :p_city_in = {$city}, :p_session_type_name_in = {$session_type_name_in}, :p_origin_in={$origin_site}, :p_session_out, :p_currency_out, :p_multi_currency_out, :p_auto_credit_increment_out, :p_auto_credit_increment_y_out, :p_subject_type_id_out, :p_subject_type_name_out, :p_subject_super_type_id_out, :p_subject_super_type_name_out, :p_session_type_id_out, :p_session_type_name_out, :p_first_name_out, :p_last_name_out, :p_last_time_collect_out, :p_online_casino_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	/** Close BO session, logout from bo session for android client */
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
	public function closeBoSession($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$CLOSE_SESSION(:p_session_id_in, :p_subject_id_in, :p_broken_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$subject_id = 0;
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$broken= YES;
			$stmt->bindParam(':p_broken_in', $broken, SQLT_CHR, 5);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "session_id"=>$session_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "MANAGMENT_CORE.M\$CLOSE_SESSION(:p_session_id_in={$session_id}, :p_subject_id_in=0, :p_broken_in=Y) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	//list terminals for credit transfers
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return array
     * @throws Zend_Exception
     */
	public function getDirectTerminalPlayers($session_id, $page_number = 1, $hits_per_page = 25, $order_by = 1, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        $p_id = 0;
        require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
        $modelSubjectTypes = new SubjectTypesModel();
        $player_type = $modelSubjectTypes->getSubjectType("MANAGMENT_TYPES.NAME_IN_TERMINAL_PLAYER");
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_id_in', $p_id);
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
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
			return array("status"=>OK, "table"=>$table, "info"=>$info);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "CREDIT_TRANSFER.M\$LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in = {$session_id}, :p_id_in = {$p_id}, :p_player_type_name_in={$player_type}, :p_page_number_in = {$page_number}, :p_hits_per_page_in={$hits_per_page}, :p_order_by_in = {$order_by}, :p_sort_order_in = {$sort_order}, :list_direct_pc_players_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	//list players for credit transfers
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return array
     * @throws Zend_Exception
     */
	public function getDirectPlayers($session_id, $page_number = 1, $hits_per_page = 25, $order_by = 1, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        $p_id = 0;
        require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
        $modelSubjectTypes = new SubjectTypesModel();
        $player_type = $modelSubjectTypes->getSubjectType("MANAGMENT_TYPES.NAME_IN_PC_PLAYER");
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.M$LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in, :p_id_in, :p_player_type_name_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :list_direct_pc_players_out)');
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_id_in', $p_id);
			$stmt->bindParam(':p_player_type_name_in', $player_type);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
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
			return array("status"=>OK, "table"=>$table, "info"=>$info);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "CALL CREDIT_TRANSFER.M\$LIST_DIRECT_PC_TM_PLAYERS(:p_session_id_in={$session_id}, :p_id_in={$p_id}, :p_player_type_name_in={$player_type}, :p_page_number_in={$page_number}, :p_hits_per_page_in={$hits_per_page}, :p_order_by_in={$order_by}, :p_sort_order_in={$sort_order}, :list_direct_pc_players_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK_EXCEPTION);
		}
	}

    /**
     * @param $session_id
     * @param $subject_name
     * @param $start_date
     * @param $end_date
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return array
     * @throws Zend_Exception
     */
	public function getPlayerCreditTransfers($session_id, $subject_name, $start_date, $end_date, $page_number = 1, $hits_per_page = 25, $order_by = 1, $sort_order = 'asc'){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$LIST_CREDIT_TRANSFERS(:p_session_id_in,' . "to_date(:p_start_time_in, 'DD-Mon-YYYY')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_transaction_types_name_in, :p_subject_name_in, :p_list_transactions_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_start_time_in', $start_date);
			$stmt->bindParam(':p_end_time_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$transaction_type_name = ALL;
			$stmt->bindParam(':p_transaction_types_name_in', $transaction_type_name);
			$stmt->bindParam(':p_subject_name_in', $subject_name);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_transactions_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();
			return array("status"=>OK, "table"=>$table, "info"=>$info);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "WEB_REPORTS.M\$LIST_CREDIT_TRANSFERS(:p_session_id_in={$session_id}, to_date(:p_start_time_in={$start_date}, DD-Mon-YYYY), to_date(:p_end_time_in={$end_date}, DD-Mon-YYYY), :p_page_number_in = {$page_number}, :p_hits_per_page_in = {$hits_per_page}, :p_order_by_in = {$order_by}, :p_sort_order_in = {$sort_order}, :p_transaction_types_name_in = {$transaction_type_name}, :p_subject_name_in = {$subject_name}, :p_list_transactions_out) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
}