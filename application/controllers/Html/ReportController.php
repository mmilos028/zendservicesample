<?php
/**
	Game client HTML5 implementation of Report controller
*/
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 * Performes player account actions trough web service
 */
class Html_ReportController extends Zend_Controller_Action {

	public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		//set output header Content-Type to application/json
		header('Content-Type: application/json');
	}
	
	public function preDispatch(){
        header("Access-Control-Allow-Origin: *");
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			$config = Zend_Registry::get('config');
			if($config->onlinecasinoserviceWSDLMode == "false" || $config->onlinecasinoserviceWSDLMode == false){ // not in wsdl mode
				$response = array(
					"status"=>NOK,
					"message"=>NOK_POST_METHOD_MESSAGE
				);
				exit(Zend_Json::encode($response));
			}else{
				$message = 
				"\n\n /onlinecasinoservice/html_report " .
				"\n\n player-sessions(name)";
				exit($message);
			}
		}
	}
	
	public function indexAction(){
		$config = Zend_Registry::get('config');
		if($config->onlinecasinoserviceWSDLMode == "false" || $config->onlinecasinoserviceWSDLMode == false){ // not in wsdl mode
			header('Location: http://www.google.com/');
		}
	}
	
	/**
	 * 
	 * generates player sessions total for name can be mac address or player name
	 * @param string $name
	 * @return mixed
	 */
	public function playerSessionsAction(){
		$name = strip_tags($this->getRequest()->getParam('name', null));
		if(strlen($name) == 0 || !isset($name) || $name == 'null'){
			$message = array(
				"status"=>NOK,
				"message"=>PARAMETER_MISSING_MESSAGE,
				"name"=>$name
			);
			exit(Zend_Json::encode($message));
		}
		try{
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
			$reportTotalArr = array();
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
			$message = array(
				"status"=>OK,
				"startdate"=>$startdate,
				"enddate"=>$enddate,
				"result"=>$reportTotalArr
			);
			exit(Zend_Json::encode($message));
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			$message = array(
				"status"=>NOK,
				"message"=>INTERNAL_ERROR_MESSAGE
			);
			exit(Zend_Json::encode($message));
		}
	}
}