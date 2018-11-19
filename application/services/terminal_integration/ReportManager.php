<?php
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
/**
 * 
 * Performes player account actions through web service
 *
 */
class ReportManager {
	/**
	 * 
	 * generates player sessions total for name can be mac address or player name
	 * @param string $name
	 * @return mixed
	 */
	public function playerSessions($name){
		if(!isset($name)){
			return null;
		}		
		try{
			$name = strip_tags($name);
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'DateTimeModel.php';
			$modelDateTime = new DateTimeModel();
			$startdate = $modelDateTime->firstDayInMonth();
			$date2 = new Zend_Date();
			$now_in_month = $date2->now(en_US);
			$enddate = $now_in_month->toString("d-MMM-Y");
			unset($modelDateTime);
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'ReportsModel.php';
			$modelReports = new ReportsModel();		
			$reportTotal = $modelReports->listPlayerHistoryTotal($name, $startdate, $enddate);		
			require_once HELPERS_DIR . DS . 'PlayerSessionRowTotal.php';
			$reportTotalArr = null;
			foreach($reportTotal as $row){
				$reportTotalArr = new PlayerSessionRowTotal(
					((strlen($row['total_games']) == 0) ? "" : $row['total_games']), 
					((strlen($row['cash_in']) == 0) ? "" : number_format($row['cash_in'], 2)), 
					((strlen($row['cash_out']) == 0) ? "" : number_format($row['cash_out'], 2)),
					((strlen($row['game_in']) == 0) ? "" : number_format($row['game_in'], 2)), 
					((strlen($row['game_out']) == 0) ? "" : number_format($row['game_out'], 2)), 
					((strlen($row['game_win']) == 0) ? "" : number_format($row['game_win'], 2)), 
					((strlen($row['game_payback']) == 0) ? "" : number_format($row['game_payback'], 2))
				);
				unset($row);
			}
			unset($reportTotal);
			unset($modelReports);
			return $reportTotalArr;
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
            $message = "ReportManager::playerSession(name = {$name}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);			
			return INTERNAL_ERROR;
		}
	}
	
	/**
	 * 
	 * generates recycler balance report for NV200 ticket terminal
	 * @param string $serial_number
     * @param string $mac_address
	 * @return mixed
	 */
	public function recyclerBalanceReport($serial_number, $mac_address){
		if(!isset($serial_number) || strlen($serial_number) == 0 || !isset($mac_address) || strlen($mac_address) == 0){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
			$serial_number = strip_tags($serial_number);
            $mac_address = strip_tags($mac_address);
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'ReportsModel.php';
			$modelReports = new ReportsModel();
            //report
			$reportResult = $modelReports->recyclerBalanceReport($serial_number, $mac_address);
			if($reportResult['status'] != OK){
				return $reportResult;
			}

			if($reportResult['amounts_per_banknote_type'] != "-1"){
				$reportArray = array();
                //list recycler report
				foreach($reportResult['amounts_per_banknote_type'] as $row){
					$reportArray[] = array(
						'in_banknote_type'=>$row['in_banknote_type'],
						'in_per_banknote_type_pieces'=>$row['in_per_banknote_type_pieces'],
						'in_per_banknote_type_amount'=>$row['in_per_banknote_type_amount'],
						'out_banknote_type'=>$row['out_banknote_type'],
						'out_per_banknote_type_pieces'=>$row['out_per_banknote_type_pieces'],
						'out_per_banknote_type_amount'=>$row['out_per_banknote_type_amount'],
                        ////new columns for report
                        'recycler_pieces'=>$row['recycler_pcs'],
                        'recycler_amount'=>$row['recycler_amount'],
                        'cashbox_pieces'=>$row['cashbox_pcs'],
                        'cashbox_amount'=>$row['cashbox_amount'],
                        'recycler_balance'=>$row['recycler_balance']
					);
				}
				return array("status"=>OK, "serial_number"=>$serial_number, "mac_address"=>$mac_address,
                //recycler values and report
                "recycler_total_in_amount"=>$reportResult['recycler_total_in_amount'], "recycler_total_out_amount"=>$reportResult['recycler_total_out_amount'],
                //cashbox values
                "cashbox_total_in_amount"=>$reportResult['cashbox_total_in_amount'], "cashbox_total_out_amount"=>$reportResult['cashbox_total_out_amount'],
                "cashbox_balance_amount"=>$reportResult['cashbox_balance_amount'],
                "total_r_amount"=>$reportResult['total_r_amount'], "total_rc_amount"=>$reportResult['total_rc_amount'], "recycler_balance"=>$reportResult['recycler_balance_out'],
				"currency"=>$reportResult['currency'], "last_discharge_date_time"=>$reportResult['last_discharge_date_time'],
				"amounts_per_banknote_type"=>$reportArray
                );
			}else{
				$reportArray = array();
				return array("status"=>OK, "serial_number"=>$serial_number, "mac_address"=>$mac_address,
                //recycler values and report
                "recycler_total_in_amount"=>$reportResult['recycler_total_in_amount'], "recycler_total_out_amount"=>$reportResult['recycler_total_out_amount'],
                //cashbox values
                "cashbox_total_in_amount"=>$reportResult['cashbox_total_in_amount'], "cashbox_total_out_amount"=>$reportResult['cashbox_total_out_amount'],
                "cashbox_balance_amount"=>$reportResult['cashbox_balance_amount'],
                "total_r_amount"=>$reportResult['total_r_amount'], "total_rc_amount"=>$reportResult['total_rc_amount'], "recycler_balance"=>$reportResult['recycler_balance_out'],
				"currency"=>$reportResult['currency'], "last_discharge_date_time"=>$reportResult['last_discharge_date_time'],
				"amounts_per_banknote_type"=>$reportArray
                );
			}
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
            $message = "ReportManager::recyclerBalanceReport(serial_number = {$serial_number}, mac_address = {$mac_address}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>INTERNAL_ERROR, "details"=>$message, "serial_number"=>$serial_number, "mac_address"=>$mac_address);
		}
	}
	
	/**
	 * 
	 * generates recycler balance report - top transactions for NV200 ticket terminal
	 * @param string $recycler_name
     * @param string $mac_address
	 * @return mixed
	 */
	public function recyclerBalanceTopTransactionsReport($recycler_name, $mac_address){
		if(!isset($recycler_name) || strlen($recycler_name) == 0){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
			$recycler_name = strip_tags($recycler_name);
            $mac_address = strip_tags($mac_address);
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'ReportsModel.php';
			$modelReports = new ReportsModel();
			$reportResult = $modelReports->recyclerBalanceTopTransactionsReport($recycler_name, $mac_address);
			if($reportResult['status'] != OK){
				return $reportResult;
			}
			$reportArray = array();
			foreach($reportResult['report'] as $row){
				$reportArray[] = array(
					'transaction_date'=>$row['transaction_date'],
					'transaction_time'=>$row['transaction_time'],
					'transaction_type'=>$row['transaction_type'],
					'name'=>$row['name'],
					'amount'=>$row['amount'],
					'currency'=>$row['currency'],
                    'tax_percent'=>NumberHelper::format_double($row['tax_percent']),
                    'tax_amount'=>NumberHelper::format_double($row['tax_amount']),
                    'recycler'=>$row['recycler'],
                    'cashbox'=>$row['cashbox'],
                    'recycler_total'=>$row['recycler_total'],
                    'cashbox_total'=>$row['cashbox_total']
				);
			}
			return array("status"=>OK, "report"=>$reportArray, "recycler_total"=>$reportResult['recycler_total'], "cashbox_total"=>$reportResult['cashbox_total']);
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "ReportManager::recyclerBalanceTopTransactionsReport(recycler_name = {$recycler_name}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>INTERNAL_ERROR, "details"=>$message, "recycler_name"=>$recycler_name);
		}
	}
}