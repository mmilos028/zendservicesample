<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class WebSiteReportsManager {

    /**
	 *
	 * List limits ...
	 * @return mixed
	 */
	public static function listLimits($session_id){
		if(!isset($session_id) || strlen($session_id) == 0 || $session_id == "null"){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		try{
			$session_id = strip_tags($session_id);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$cursor = $modelWebSite->listLimits($session_id);
			$arrData = CursorToArrayHelper::cursorToArray($cursor);
			$resArray = array();
			foreach($arrData as $row){
				$resArray[] = array(
                    "name"=>$row['name'],
                    "start_time"=>$row['start_time'],
				    "end_time"=>$row['end_time'],
                    "limit"=>NumberHelper::convert_double($row['limit']),
				    "limit_formatted"=>NumberHelper::format_double($row['limit']),
				    "duration"=>$row['duration'],
                    "remaining"=>$row['remain']
                );
			}
            $json_message = Zend_Json::encode(array("status"=>OK, "result"=>$resArray));
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::listLimits(session_id = {$session_id}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

    /**
	 *
	 * List limits with fixed response ...
	 * @return mixed
	 */
	public static function listLimitsNew($session_id){
		if(!isset($session_id) || strlen($session_id) == 0 || $session_id == "null"){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		try{
			$session_id = strip_tags($session_id);
			require_once MODELS_DIR . DS . 'WebSiteModel.php';
			$modelWebSite = new WebSiteModel();
			$cursor = $modelWebSite->listLimits($session_id);
			$arrData = CursorToArrayHelper::cursorToArray($cursor);
			$resArray = array();
			foreach($arrData as $row){
				$resArray[] = array(
                    "name"=>$row['name'],
                    "start_time"=>$row['start_time'],
				    "end_time"=>$row['end_time'],
                    "limit"=>NumberHelper::convert_double($row['limit']),
				    "limit_formatted"=>NumberHelper::format_double($row['limit']),
				    "duration"=>$row['duration'],
                    "remaining"=>$row['remain']
                );
			}
            $json_message = Zend_Json::encode(array("status"=>OK, "result"=>$resArray));
            exit($json_message);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::listLimitsNew(session_id = {$session_id}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

    /**
	 *
	 * List player history method ...
	 * @return mixed
	 */
	public static function listPlayerHistory($session_id, $start_date, $end_date, $page_number, $per_page, $column, $order){
		if(!isset($session_id) || strlen($session_id) == 0 || $session_id == "null" || !isset($start_date) || !isset($end_date)){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
        try{
            require_once MODELS_DIR . DS . 'WebSiteModel.php';
            $modelWebSite = new WebSiteModel();
            $res = $modelWebSite->sessionIdToPlayerId($session_id);
            $player_name = $res['player_name'];
            $total_pages = 0;
            $total_items = 0;
            $actual_credits = 0.0;
            $arrData = array();
            require_once MODELS_DIR . DS . 'ReportsModel.php';
            $modelReports = new ReportsModel();
            if($page_number == 1){
                $arrData = $modelReports->listPlayerHistory($player_name, $start_date, $end_date, $page_number, $per_page, $column, $order);
                if($arrData == false){
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			        exit($json_message);
                }
                $total_items = (int)$arrData[1][0]["cnt"];
                $total_pages = (int)ceil($total_items / $per_page);
                $actual_credits = $arrData[2];
            }else{
                if($page_number >= $total_pages){
                    $arrData = $modelReports->listPlayerHistory($player_name, $start_date, $end_date, $page_number, $per_page, $column, $order);
                    if($arrData == false){
                        $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			            exit($json_message);
                    }
                    $total_items = (int)$arrData[1][0]["cnt"];
                    $total_pages = (int)ceil($total_items / $per_page);
                    $actual_credits = $arrData[2];
                }
            }
            if(count($arrData[0]) == 0){
                $pageNo = 1;
                $arrData = $modelReports->listPlayerHistory($player_name, $start_date, $end_date, $page_number, $per_page, $column, $order);
                if($arrData == false){
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			        exit($json_message);
                }
                $total_items = (int)$arrData[1][0]["cnt"];
                $total_pages = (int)ceil($total_items / $per_page);
                $actual_credits = $arrData[2];
            }
            $resArray = array();
            foreach($arrData[0] as $row){
                $flagShowDetails = (!is_null($row['duration']) && !is_null($row['total_games'])) ? "1" : "0";
                $resArray[] = array(
                    "id" => $row['id'],
                    "game_name_transactions" => $row['game_name_transactions'],
                    "start_time" => $row['start_time'],
                    "duration" => $row['duration'],
                    "total_games"=> NumberHelper::convert_integer($row['total_games']),
                    "total_games_formatted"=> NumberHelper::format_integer($row['total_games']),
                    "cash_in"=> NumberHelper::convert_double($row['cash_in']),
                    "cash_in_formatted"=> NumberHelper::format_double($row['cash_in']),
                    "cash_out"=> NumberHelper::convert_double(-1 * $row['cash_out']),
                    "cash_out_formatted"=> NumberHelper::format_double(-1 * $row['cash_out']),
                    "start_credits"=> NumberHelper::convert_double($row['start_credits']),
                    "start_credits_formatted"=> NumberHelper::format_double($row['start_credits']),
                    "end_credits"=> NumberHelper::convert_double($row['end_credits']),
                    "end_credits_formatted"=> NumberHelper::format_double($row['end_credits']),
                    "game_in"=> NumberHelper::convert_double($row['game_in']),
                    "game_in_formatted"=> NumberHelper::format_double($row['game_in']),
                    "game_out"=>NumberHelper::convert_double($row['game_out']),
                    "game_out_formatted"=>NumberHelper::format_double($row['game_out']),
                    "game_win"=>NumberHelper::convert_double($row['game_win']),
                    "game_win_formatted"=>NumberHelper::format_double($row['game_win']),
                    "game_payback"=>$row['game_payback'],
                    "show_details"=>$flagShowDetails
                );
            }
            $json_message = Zend_Json::encode(
                array(
                    "status"=>OK,
                    "total_items"=>$total_items,
                    "total_pages"=>$total_pages,
                    "data"=>$resArray,
                    "actual_credits"=>NumberHelper::convert_double($actual_credits),
                    "actual_credits_formatted"=>NumberHelper::format_double($actual_credits),
                )
            );
            exit($json_message);
        }catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::ListPlayerHistory(session_id = {$session_id}, start_date = {$start_date}, end_date = {$end_date}, page_number = {$page_number}, per_page = {$per_page}, column = {$column}, order = {$order}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

    /**
	*
	* List player history details method ...
	* @return mixed
	*/
	public static function listPlayerHistoryDetails($session_id, $page_number, $per_page, $column, $order){
		if(!isset($session_id) || strlen($session_id) == 0 || $session_id == "null"){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		$total_pages = 0;
		$total_items = 0;
		$arrData = array();
        try {
            require_once MODELS_DIR . DS . 'ReportsModel.php';
            $modelReports = new ReportsModel();
            if ($page_number == 1) {
                $arrData = $modelReports->listGameSessionDetails($session_id, $page_number, $per_page, $column, $order);
                if ($arrData == false) {
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			        exit($json_message);
                }
                $total_items = (int)$arrData["info"][0]["cnt"];
                $total_pages = (int)ceil($total_items / $per_page);
            } else {
                if ($page_number >= $total_pages) {
                    $arrData = $modelReports->listGameSessionDetails($session_id, $page_number, $per_page, $column, $order);
                    if ($arrData == false) {
                        $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			            exit($json_message);
                    }
                    $total_items = (int)$arrData["info"][0]["cnt"];
                    $total_pages = (int)ceil($total_items / $per_page);
                }
            }
            if (count($arrData["table"]) == 0) {
                $page_number = 1;
                $arrData = $modelReports->listGameSessionDetails($session_id, $page_number, $per_page, $column, $order);
                if ($arrData == false) {
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			        exit($json_message);
                }
                $total_items = (int)$arrData["info"][0]["cnt"];
                $total_pages = (int)ceil($total_items / $per_page);
            }
            $resArray = array();
            foreach ($arrData["table"] as $row) {
                $resArray[] = array(
                    "uid" => $row['id'],
                    "game_name" => $row['game_name'],
                    "session_type" => $row['st_name'],
                    "player_name" => $row['player_name'],
                    "start_date" => $row['start_date'],
                    "start_time" => $row['start_time'],
                    "end_time" => $row['end_time'],
                    "game_duration" => $row['game_duration'],
                    "game_in" => (strlen($row['game_in']) == 0 ? "" : NumberHelper::format_double($row['game_in'])),
                    "game_out" => (strlen($row['game_out']) == 0 ? "" : NumberHelper::format_double($row['game_out'])),
                    "game_win" => (strlen($row['game_out']) == 0 ? "" : NumberHelper::format_double($row['game_out'])),
                    "cash_in" => (strlen($row['cash_in']) == 0 ? "" : NumberHelper::format_double($row['cash_in'])),
                    "cash_out" => (strlen($row['cash_out']) == 0 ? "" : NumberHelper::format_double($row['cash_out'])),
                    "start_credits" => (strlen($row['start_credits']) == 0 ? "" : NumberHelper::format_double($row['start_credits'])),
                    "end_credits" => (strlen($row['end_credits']) == 0 ? "" : NumberHelper::format_double($row['end_credits'])),
                    "currency" => $row['currency'],
                    "session_state" => $row['session_state'],
                    "total_games"=>(strlen($row['total_games']) == 0 ? "" : NumberHelper::format_integer($row['total_games'])),
                    "id" => $row['id'],
                );
            }
            $json_message = Zend_Json::encode(array("total_items" => $total_items, "total_pages" => $total_pages, "data" => $resArray,
                "session_type_name" => $arrData['session_type_name'], "player_name" => $arrData['player_name'], "affiliate_name" => $arrData['affiliate_name'],
                "ip_address" => $arrData['ip_address'], "country_name" => StringHelper::filterCountry($arrData['country_name'])));
			exit($json_message);
        }catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::listPlayerHistoryDetails(session_id = {$session_id}, page_number = {$page_number}, per_page = {$per_page}, column={$column}, order={$order}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

    /**
	*
	* List player history subdetails method ...
	* PC session ID is expected here in session_id
	* @param int $session_id
	* @param int $pageNo
	* @param int $perPage
	* @param int $column
	* @param string $order
	* @return mixed
	*/
	public static function listPlayerHistorySubdetails($session_id, $pageNo, $perPage, $column, $order){
		if(!isset($session_id) || strlen($session_id) == 0 || $session_id == "null"){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		if(!isset($pageNo)){
			$pageNo = 1;
		}
		if(!isset($perPage)){
			$perPage = 50;
		}
		if(!isset($column)){
			$column = 1;
		}
		if(!isset($order)){
			$order = 'asc';
		}
		$session_id = intval(strip_tags($session_id));
		$pageNo = intval(strip_tags($pageNo));
		$perPage = intval(strip_tags($perPage));
		$column = intval(strip_tags($column));
		$order = strip_tags($order);
		$total_pages = 0;
		$total_items = 0;
		$arrData = array();
        try{
            require_once MODELS_DIR . DS . 'ReportsModel.php';
            $modelReports = new ReportsModel();
            if($pageNo == 1){
                $arrData = $modelReports->listGameSessionSubdetails($session_id, $pageNo, $perPage, $column, $order);
                if($arrData == false){
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			        exit($json_message);
                }
                $total_items = (int)$arrData["info"][0]["cnt"];
                $total_pages = (int)ceil($total_items / $perPage);
            }else{
                if($pageNo >= $total_pages){
                    $arrData = $modelReports->listGameSessionSubdetails($session_id, $pageNo, $perPage, $column, $order);
                    if($arrData == false){
                        $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			            exit($json_message);
                    }
                    $total_items = (int)$arrData["info"][0]["cnt"];
                    $total_pages = (int)ceil($total_items / $perPage);
                }
            }
            if(count($arrData["table"]) == 0){
                $pageNo = 1;
                $arrData = $modelReports->listGameSessionSubdetails($session_id, $pageNo, $perPage, $column, $order);
                if($arrData == false){
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			        exit($json_message);
                }
                $total_items = (int)$arrData["info"][0]["cnt"];
                $total_pages = (int)ceil($total_items / $perPage);
            }
            $resArray = array();
            $currency = "";
            foreach($arrData['table'] as $row){
                $resArray[] = array(
                    "transaction_id"=>$row['transaction_id'],
                    "game_id"=>$row['move_id'],
                    "type_name"=>$row['session_type'],
                    "start_time"=>$row['start_time'],
                    "end_time"=>$row['end_time'],
                    "start_credits"=>(strlen($row['start_credits']) == 0 ? "" : NumberHelper::format_double($row['start_credits'])),
                    "minus_bet"=>(strlen($row['bet']) == 0 ? "" : NumberHelper::format_double($row['bet'])),
                    "plus_win"=>(strlen($row['win']) == 0 ? "" : NumberHelper::format_double($row['win'])),
                    "result"=>(strlen($row['result']) == 0 ? "" : (NumberHelper::format_double(-1 * $row['result']))),
                    "minus_in"=>(strlen($row['game_in']) == 0 ? "" : NumberHelper::format_double($row['game_in'])),
                    "plus_collect"=>(strlen($row['collect']) == 0 ? "" : NumberHelper::format_double($row['collect'])),
                    "netto"=>(strlen($row['netto']) == 0 ? "" : (NumberHelper::format_double(-1 * $row['netto']))),
                    "cash_in"=>(strlen($row['cash_in']) == 0 ? "" : NumberHelper::format_double($row['cash_in'])),
                    "cash_out"=>(strlen($row['cash_out']) == 0 ? "" : NumberHelper::format_double($row['cash_out'])),
                    "end_credits"=>(strlen($row['end_credits']) == 0 ? "" : NumberHelper::format_double($row['end_credits'])),
                    "currency"=>$row['currency'],
                );
                $currency = $row['currency'];
            }
            if(count($resArray) > 0){
                foreach($arrData['tableTotal'] as $row){
                    $resArray[] = array(
                        "transaction_id"=>"",
                        "game_id"=>"",
                        "type_name"=>"",
                        "start_time"=>"",
                        "end_time"=>"TOTAL",
                        "start_credits"=>"",
                        "minus_bet"=>(strlen($row['total_bet']) == 0 ? "" : NumberHelper::format_double($row['total_bet'])),
                        "plus_win"=>(strlen($row['total_win']) == 0 ? "" : NumberHelper::format_double($row['total_win'])),
                        "result"=>(strlen($row['total_result']) == 0 ? "" : NumberHelper::format_double(-1 * $row['total_result'])),
                        "minus_in"=>(strlen($row['total_game_in']) == 0 ? "" : NumberHelper::format_double($row['total_game_in'])),
                        "plus_collect"=>(strlen($row['total_collect']) == 0 ? "" : NumberHelper::format_double($row['total_collect'])),
                        "netto"=>(strlen($row['total_netto']) == 0 ? "" : NumberHelper::format_double(-1 * $row['total_netto'])),
                        "cash_in"=>(strlen($row['total_cash_in']) == 0 ? "" : NumberHelper::format_double($row['total_cash_in'])),
                        "cash_out"=>(strlen($row['total_cash_out']) == 0 ? "" : NumberHelper::format_double($row['total_cash_out'])),
                        "end_credits"=>"",
                        "currency"=>$currency
                    );
                }
            }

            $json_message = Zend_Json::encode(
                array(
                    "total_items"=>$total_items,
                    "total_pages"=>$total_pages,
                    "data"=>$resArray,
                    "session_type_name"=>$arrData['session_type_name'],
                    "player_name"=>$arrData['player_name'],
                    "affiliate_name"=>$arrData['affiliate_name'],
                    "ip_address"=>$arrData['ip_address'],
                    "country_name"=>StringHelper::filterCountry($arrData['country_name'])
                )
            );
            exit($json_message);
        }catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::listPlayerHistorySubdetails(session_id = {$session_id}, pageNo = {$pageNo}, perPage = {$perPage}, column = {$column}, order={$order}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

    public static function listCreditTransfers($session_id, $startdate, $enddate, $pageNo, $perPage, $column, $order){
		if(!isset($session_id) || strlen($session_id) == 0 || $session_id == "null" || !isset($startdate) || !isset($enddate)){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		if(!isset($pageNo)){
			$pageNo = 1;
		}
		if(!isset($perPage)){
			$perPage = 50;
		}
		if(!isset($column)){
			$column = 1;
		}
		if(!isset($order)){
			$order = 'asc';
		}
		$session_id = intval(strip_tags($session_id));
		$startdate = strip_tags($startdate);
		$enddate = strip_tags($enddate);
		$pageNo = intval(strip_tags($pageNo));
		$perPage = intval(strip_tags($perPage));
		$column = intval(strip_tags($column));
		$order = strip_tags($order);
        try {
            require_once MODELS_DIR . DS . 'WebSiteModel.php';
            $modelWebSite = new WebSiteModel();
            $res = $modelWebSite->sessionIdToPlayerId($session_id);
            $subject_name = $res['player_name'];
            $transaction_type_name = ALL;
            $total_pages = 0;
            $total_items = 0;
            $arrData = array();
            require_once MODELS_DIR . DS . 'ReportsModel.php';
            $modelReports = new ReportsModel();
            if ($pageNo == 1) {
                $arrData = $modelReports->listCreditTransfers($startdate, $enddate, $pageNo, $perPage, $column, $order, $transaction_type_name, $subject_name);
                if ($arrData == false) {
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			        exit($json_message);
                }
                $total_items = (int)$arrData["info"][0]["cnt"];
                $total_pages = (int)ceil($total_items / $perPage);
            } else {
                if ($pageNo >= $total_pages) {
                    $arrData = $modelReports->listCreditTransfers($startdate, $enddate, $pageNo, $perPage, $column, $order, $transaction_type_name, $subject_name);
                    if ($arrData == false) {
                        $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			            exit($json_message);
                    }
                    $total_items = (int)$arrData["info"][0]["cnt"];
                    $total_pages = (int)ceil($total_items / $perPage);
                }
            }
            if (count($arrData["table"]) == 0) {
                $pageNo = 1;
                $arrData = $modelReports->listCreditTransfers($startdate, $enddate, $pageNo, $perPage, $column, $order, $transaction_type_name, $subject_name);
                if ($arrData == false) {
                    $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			        exit($json_message);
                }
                $total_items = (int)$arrData["info"][0]["cnt"];
                $total_pages = (int)ceil($total_items / $perPage);
            }
            $resArray = array();
            foreach ($arrData["table"] as $row) {
                $resArray[] = array(
                    "date_time" => $row['date_time'],
                    "commited_by" => $row['commited_by'],
                    "name_from" => $row['name_from'],
                    "name_to" => $row['name_to'],
                    "amount" => $row['amount'],
                    "currency" => $row['currency'],
                    "transaction_type" => $row['transaction_type'],
                    "ip_country" => StringHelper::filterCountry($row['ip_country']),
                    "city" => StringHelper::filterCountry($row['city']),
                    "amount_sign" => $row['amount_sign']
                );
            }
            $json_message = Zend_Json::encode(
                array("status"=>OK, "total_items" => $total_items, "total_pages" => $total_pages, "data" => $resArray)
            );
            exit($json_message);
        }catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::listCreditTransfers(session_id = {$session_id}, startdate = {$startdate}, enddate = {$enddate}, pageNo = {$pageNo}, perPage = {$perPage}, column = {$column}, order = {$order}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

    /**
	*
	* List available bonus campaigns
	* @param string $affiliate_username
	* @param int $player_id
	* @return mixed
	*/
	public static function listAvailableBonusCampaigns($affiliate_username, $player_id){

		$affiliate_username = strip_tags($affiliate_username);
        if(!isset($affiliate_username) || strlen($affiliate_username) == 0 || $affiliate_username == "null"){
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
        }
        $player_id = strip_tags($player_id);
        if(!isset($player_id) || strlen($player_id) == 0 || $player_id == "null"){
            $player_id = 0;
        }
        try {
            require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
            $modelWebSiteBonus = new WebSiteBonusModel();
            $arrData = $modelWebSiteBonus->listBonusCampaignsAvailable($affiliate_username, $player_id);
            if ($arrData['status'] == OK) {
                $report = array();
                foreach ($arrData['cursor'] as $c) {
                    $report[] = array(
                        'campaign_name' => $c['campaign_name'],
                        'campagin_code' => $c['campaign_code'],
                        'bonus_profile_id' => $c['bonus_profile_id'],
                        'currency' => $c['currency'],
                        'bonus_multiplier' => $c['bonus_multiplier'],
                        'wagering_multiplier' => $c['wagering_multiplier'],
                        'restricted_period' => $c['restricted_period'],
                        'bonus_max_amount' => $c['bonus_max_amount'],
                        'minimum_deposit' => $c['minimum_deposit'],
                        'promotion_amount' => $c['promotion_amount']
                    );
                }
                $json_message = Zend_Json::encode(
                    array("status" => OK, "affiliate_username" => $affiliate_username, "player_id" => $player_id, "report" => $report)
                );
                exit($json_message);
            } else {
                $json_message = Zend_Json::encode(
                    array("status" => NOK, "report" => array())
                );
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::listAvailableBonusCampaigns(affiliate_username = {$affiliate_username}, player_id = {$player_id}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

    public static function listPlayerAvailableBonusCampaigns($player_id){

        $player_id = strip_tags($player_id);
        if(!isset($player_id) || strlen($player_id) == 0 || $player_id == "null"){
            $player_id = 0;
        }
        try {
            require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
            $modelWebSiteBonus = new WebSiteBonusModel();
            $arrData = $modelWebSiteBonus->listPlayerBonusCampaignsAvailable($player_id);
            if ($arrData['status'] == OK) {
                $report = array();
                foreach ($arrData['cursor'] as $c) {
                    $report[] = array(
                        'credits_restricted' => $c['credits_restricted'],
                        'bonus_restricted' => $c['bonus_restricted'],
                        'bonus_win_restricted' => $c['bonus_win_restricted'],
                        'wagering_required' => $c['wagering_required'],
                        'wagering_played' => $c['wagering_played'],
                        'wager_to_release' => $c['wager_to_release']
                    );
                }
                $json_message = Zend_Json::encode(
                    array("status" => OK, "player_id" => $player_id, "report" => $report)
                );
                exit($json_message);
            } else {
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
                exit($json_message);
            }
        }catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::listAvailableBonusCampaigns(player_id = {$player_id}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

    public static function listPlayerActiveBonusesAndPromotions($player_id){
        $player_id = strip_tags($player_id);
        if(!isset($player_id) || strlen($player_id) == 0 || $player_id == "null"){
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
        }
        try {
            require_once MODELS_DIR . DS . 'WebSiteBonusModel.php';
            $modelWebSiteBonus = new WebSiteBonusModel();
            $arrData = $modelWebSiteBonus->listPlayerActiveBonusesAndPromotions($player_id);
            if ($arrData['status'] == OK) {
                $report = array();
                foreach ($arrData['cursor'] as $c) {
                    $report[] = array(
                        'campaign_name' => $c['campaign_name'],
                        'campaign_end_date' => $c['campaign_end_date'],
                        'campaign_repeat' => $c['campaign_repeat'],
                        'bonus_promotion' => NumberHelper::format_double($c['bonus_promotion']),
                        'credits_restricted' => NumberHelper::format_double($c['credits_restricted']),
                        'bonus_restricted' => NumberHelper::format_double($c['bonus_restricted']),
                        'bonus_win_restricted' => NumberHelper::format_double($c['bonus_win_restricted']),
                        'wagering_required' => NumberHelper::format_double($c['wagering_required']),
                        'wagering_played' => NumberHelper::format_double($c['wagering_played']),
                        'wager_to_release' => NumberHelper::format_double($c['wager_to_release'])
                    );
                }
                $json_message = Zend_Json::encode(
                    array("status" => OK, "player_id" => $player_id, "report" => $report)
                );
                exit($json_message);
            } else {
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			    exit($json_message);
            }
        }catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::listPlayerActiveBonusesAndPromotions(player_id = {$player_id}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
	}

    public static function listTopWonJackpots($affiliate_name){
        $affiliate_name = strip_tags($affiliate_name);
        if(!isset($affiliate_name) || strlen($affiliate_name) == 0 || $affiliate_name == "null"){
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
        }
        try {
            require_once MODELS_DIR . DS . 'ReportsModel.php';
            $modelReports = new ReportsModel();
            $arrData = $modelReports->getTopWonJackpots($affiliate_name);
            if ($arrData['status'] == OK) {
                $report = array();
                foreach ($arrData['cursor'] as $c) {
                    $report[] = array(
                        'start_time' => $c['start_time'],
                        'subject_name' => $c['subject_name'],
                        'jp_amount' => NumberHelper::convert_double($c['jp_amount']),
                        'jp_amount_formatted' => NumberHelper::format_double($c['jp_amount']),
                        'currency' => $c['currency'],
                        'jp_level' => $c['jp_level']
                    );
                }
                $json_message = Zend_Json::encode(
                    array("status" => OK, "affiliate_name" => $affiliate_name, "report" => $report)
                );
                exit($json_message);
            } else {
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			    exit($json_message);
            }
        }catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::listTopWonJackpots(affiliate_name = {$affiliate_name}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
    }

    public static function listCurrentJackpotLevels($affiliate_name, $currency){
        $affiliate_name = strip_tags($affiliate_name);
        $currency = strip_tags($currency);
        if(!isset($affiliate_name) || strlen($affiliate_name) == 0 || $affiliate_name == "null" ||
           !isset($currency) || strlen($currency) == 0 || $currency == "null"
        ){
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
        }
        try {
            require_once MODELS_DIR . DS . 'ReportsModel.php';
            $modelReports = new ReportsModel();
            $arrData = $modelReports->getCurrentJackpotLevels($affiliate_name, $currency);
            if ($arrData['status'] == OK) {
                $report = array();
                foreach ($arrData['cursor'] as $c) {
                    $report[] = array(
                        'current_bronze_level' => NumberHelper::convert_double($c['current_bronze_level']),
                        'current_bronze_level_formatted' => NumberHelper::format_double($c['current_bronze_level']),
                        'current_silver_level' => NumberHelper::convert_double($c['current_silver_level']),
                        'current_silver_level_formatted' => NumberHelper::format_double($c['current_silver_level']),
                        'current_gold_level' => NumberHelper::convert_double($c['current_gold_level']),
                        'current_gold_level_formatted' => NumberHelper::format_double($c['current_gold_level']),
                        'current_platinum_level' => NumberHelper::convert_double($c['current_platinum_level']),
                        'current_platinum_level_formatted' => NumberHelper::format_double($c['current_platinum_level']),
                    );
                }
                $json_message = Zend_Json::encode(
                    array("status" => OK, "affiliate_name" => $affiliate_name, "report" => $report)
                );
                exit($json_message);
            } else {
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			    exit($json_message);
            }
        }catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::listTopWonJackpots(affiliate_name = {$affiliate_name}, currency = {$currency}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
    }

    /**
     * @param integer $affiliate_name
     * @return mixed
     */
    public static function listHighWins($affiliate_name){
        $affiliate_name = strip_tags($affiliate_name);
        if(!isset($affiliate_name) || strlen($affiliate_name) == 0 || $affiliate_name == "null"){
            $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
        }
        try {
            require_once MODELS_DIR . DS . 'ReportsModel.php';
            $modelReports = new ReportsModel();
            $arrData = $modelReports->getHighWinList($affiliate_name);
            if ($arrData['status'] == OK) {
                $report = array();
                foreach ($arrData['cursor'] as $c) {
                    $report[] = array(
                        'player_name' => $c['player_name'],
                        'win_amount' => NumberHelper::convert_double($c['win_amount']),
                        'win_amount_formatted' => NumberHelper::format_double($c['win_amount']),
                        'date_time' => $c['formated_date'],
                        'currency' => $c['currency'],
                        'game_name' => $c['game_name']
                    );
                }
                $json_message = Zend_Json::encode(
                    array("status" => OK, "affiliate_name" => $affiliate_name, "report" => $report)
                );
                exit($json_message);
            } else {
                $json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			    exit($json_message);
            }
        }catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSiteReportsManager::listHighWins(affiliate_name = {$affiliate_name}) Exception: <br /> " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
			exit($json_message);
		}
    }
}