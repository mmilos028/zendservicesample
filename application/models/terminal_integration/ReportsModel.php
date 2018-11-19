<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class ReportsModel{
	public function __construct(){
	}

	//list player history total for game client
	public function listPlayerHistoryTotal($player_name, $start_date, $end_date){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$LIST_PLAYER_HISTORY_T(:p_player_name_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_players_sum_list_out)');
			$stmt->bindParam(':p_player_name_in', $player_name);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_players_sum_list_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return $cursor;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}
	}

	//recycler balance report
	public function recyclerBalanceReport($serial_number, $mac_address){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		$recycler_balance_out = "";
		$recycler_total_in_amount = "";
        $recycler_total_out_amount = "";
        $cashbox_total_in_amount = "";
        $cashbox_total_out_amount = "";
        $cashbox_balance_amount = "";
		$currency = "";
		$last_discharge_date_time = "";
        $total_r_out = "";
        $total_rc_out = "";
		try{
			$stmt = $dbAdapter->prepare('CALL TICKET_TERMINAL_NV200.RECYCLER_BALANCE(:p_serial_number_in, :p_mac_address_in, :p_recycler_balance_out, :p_r_total_in_amount_out,
			:p_r_total_out_amount_out, :p_c_total_in_amount_out, :p_c_total_out_amount_out, :p_cashbox_balance_out, :p_total_r_out, :p_total_rc_out,
			:p_currency_out, :p_last_discharge_date_time, :p_amounts_per_banknote_type)');
			$stmt->bindParam(':p_serial_number_in', $serial_number);
            $stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_recycler_balance_out', $recycler_balance_out, SQLT_CHR, 255);

            $stmt->bindParam(':p_r_total_in_amount_out', $recycler_total_in_amount, SQLT_CHR, 255);
            $stmt->bindParam(':p_r_total_out_amount_out', $recycler_total_out_amount, SQLT_CHR, 255);
            $stmt->bindParam(':p_c_total_in_amount_out', $cashbox_total_in_amount, SQLT_CHR, 255);
            $stmt->bindParam(':p_c_total_out_amount_out', $cashbox_total_out_amount, SQLT_CHR, 255);
            $stmt->bindParam(':p_cashbox_balance_out', $cashbox_balance_amount, SQLT_CHR, 255);
            $stmt->bindParam(':p_total_r_out', $total_r_out, SQLT_CHR, 255);
            $stmt->bindParam(':p_total_rc_out', $total_rc_out, SQLT_CHR, 255);
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
			$stmt->bindParam(':p_last_discharge_date_time', $last_discharge_date_time, SQLT_CHR, 255);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_amounts_per_banknote_type", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			/*
            $errorHelper = new ErrorHelper();
			$message = "TICKET_TERMINAL_NV200.RECYCLER_BALANCE(:p_serial_number_in = {$serial_number}, :p_mac_address_in = {$mac_address}, :p_recycler_balance_out = {$recycler_balance_out},
			:p_r_total_in_amount_out = {$recycler_total_in_amount}, :p_r_total_out_amount_out = {$recycler_total_out_amount},
			:p_c_total_in_amount_out = {$cashbox_total_in_amount}, :p_c_total_out_amount_out = {$cashbox_total_out_amount},
			:p_cashbox_balance_out = {$cashbox_balance_amount}, :p_total_r_out = {$total_r_out}, :p_total_rc_out = {$total_rc_out},
			:p_currency_out = {$currency}, :p_last_discharge_date_time = {$last_discharge_date_time}, :p_amounts_per_banknote_type =)";
            $errorHelper->serviceAccess($message, $message);
			*/
			return array("status"=>OK, "recycler_balance_out"=>$recycler_balance_out,
                "recycler_total_in_amount"=>$recycler_total_in_amount, "recycler_total_out_amount"=>$recycler_total_out_amount,
                "cashbox_total_in_amount"=>$cashbox_total_in_amount, "cashbox_total_out_amount"=>$cashbox_total_out_amount,
                "cashbox_balance_amount"=>$cashbox_balance_amount, "total_r_amount"=>$total_r_out, "total_rc_amount"=>$total_rc_out,
			    "currency"=>$currency, "last_discharge_date_time"=>$last_discharge_date_time, "amounts_per_banknote_type"=>$cursor);
		}catch(Zend_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>INTERNAL_ERROR, "details"=>$message);
		}
	}
	
	//recycler balance report - list top transactions
	public function recyclerBalanceTopTransactionsReport($recycler_name, $mac_address){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL TICKET_TERMINAL_NV200.LIST_HISTORY_TOP_TRANSACTIONS(:p_recycler_name, :p_mac_address_in, :p_recycler_total_out, :p_cashbox_total_out, :p_cur_tt_top_transactions)');
			$stmt->bindParam(':p_recycler_name', $recycler_name);
            $stmt->bindParam(':p_mac_address_in', $mac_address);
            $recycler_total = "";
            $stmt->bindParam(':p_recycler_total_out', $recycler_total, SQLT_CHR, 255);
            $cashbox_total = "";
            $stmt->bindParam(':p_cashbox_total_out', $cashbox_total, SQLT_CHR, 255);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_cur_tt_top_transactions", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "report"=>$cursor, "recycler_total"=>$recycler_total, "cashbox_total"=>$cashbox_total);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>INTERNAL_ERROR, "details"=>$message, "recycler_name"=>$recycler_name);
		}
	}
}