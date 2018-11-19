<?php
require_once 'Zend/Registry.php';
require_once 'Zend/Date.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
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
			require_once MODELS_DIR . DS . 'DateTimeModel.php';
			$modelDateTime = new DateTimeModel();
			$startdate = $modelDateTime->firstDayInMonth();
			$date2 = new Zend_Date();
			$now_in_month = $date2->now(en_US);
			$enddate = $now_in_month->toString("d-MMM-Y");
			unset($modelDateTime);
			require_once MODELS_DIR . DS . 'ReportsModel.php';
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
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);			
			return INTERNAL_ERROR;
		}
	}
}