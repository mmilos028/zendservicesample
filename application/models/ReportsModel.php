<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class ReportsModel{
	public function __construct(){
	}

    /**
     * @param $player_id
     * @return array
     * @throws Zend_Exception
     */
	public function listPendingPaymentProviderPayOuts($player_id){

	    $config = Zend_Registry::get('config');
	    if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }

        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$LIST_PENDING_APCO_PAY_OUTS(:p_player_id_in, :p_transaction_sum_out, :p_transaction_count_out, :p_transaction_list_out)');
			$stmt->bindParam(':p_player_id_in', $player_id);
			$transaction_sum = "0.00";
			$stmt->bindParam(':p_transaction_sum_out', $transaction_sum, SQLT_CHR, 255);
			$transaction_count = "0.00";
			$stmt->bindParam(':p_transaction_count_out', $transaction_count, SQLT_CHR, 255);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_transaction_list_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$report = CursorToArrayHelper::cursorToArray($cursor);

			if($config->measureSpeedPerformance == "true") {
                $after_time = microtime(true);
                $difference_time = number_format(($after_time-$before_time), 4);
                $errorHelper = new ErrorHelper();
                $measure_time_message = "WEB_REPORTS.M\$LIST_PENDING_APCO_PAY_OUTS(:p_player_id_in = {$player_id}, :p_transaction_sum_out, :p_transaction_count_out, :p_transaction_list_out)";
                $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                $errorHelper->siteAccessLog($measure_time_message);
            }

			return array("status"=>OK, "transaction_sum"=>$transaction_sum, "transaction_count"=>$transaction_count, "report"=>$report);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	//list credit transfers for player
    /**
     * @param $start_date
     * @param $end_date
     * @param $page_number
     * @param $hits_per_page
     * @param $order_by
     * @param $sort_order
     * @param $transaction_type_name
     * @param $subject_name
     * @return array|bool
     * @throws Zend_Exception
     */
	public function listCreditTransfers($start_date, $end_date, $page_number, $hits_per_page, $order_by, $sort_order, $transaction_type_name, $subject_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.LIST_CREDIT_TRANSFERS(' . "to_date(:p_start_time_in, 'DD-Mon-YYYY')," . "to_date(:p_end_time_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_transaction_types_name_in, :p_subject_name_in, :p_list_transactions_out)');
			$stmt->bindParam(':p_start_time_in', $start_date);
			$stmt->bindParam(':p_end_time_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
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
			return array("table"=>$table, "info"=>$info);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return false;
		}
	}
	
	//list player history total for game client
    /**
     * @param $player_name
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public function listPlayerHistoryTotal($player_name, $start_date, $end_date){

	    $config = Zend_Registry::get('config');
	    if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }

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

			if($config->measureSpeedPerformance == "true") {
                $after_time = microtime(true);
                $difference_time = number_format(($after_time-$before_time), 4);
                $errorHelper = new ErrorHelper();
                $measure_time_message = "REPORTS.M\$LIST_PLAYER_HISTORY_T(:p_player_name_in = {$player_name},' . \"to_date(:p_start_date_in = {$start_date}, 'DD-Mon-YYYY'),\" . \"to_date(:p_end_date_in = {$end_date}, 'DD-Mon-YYYY')\" . ', :p_players_sum_list_out)";
                $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                $errorHelper->siteAccessLog($measure_time_message);
            }

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
	
	//list player history report for site
    /**
     * @param $player_name
     * @param $start_date
     * @param $end_date
     * @param int $page_number
     * @param int $hits_per_page
     * @param int $order_by
     * @param string $sort_order
     * @return array|bool
     * @throws Zend_Exception
     */
	public function listPlayerHistory($player_name, $start_date, $end_date, $page_number = 1, $hits_per_page = 25, $order_by = 1, $sort_order = 'asc'){
	    $config = Zend_Registry::get('config');
	    if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }

        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$LIST_PLAYER_HISTORY(:p_player_name_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_players_list_out, :p_player_credits_out)');
			$stmt->bindParam(':p_player_name_in', $player_name);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $hits_per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_players_list_out', $cursor);
			$actual_credits = "0.00";
			$stmt->bindParam(':p_player_credits_out', $actual_credits, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();

			if($config->measureSpeedPerformance == "true") {
                $after_time = microtime(true);
                $difference_time = number_format(($after_time-$before_time), 4);
                $errorHelper = new ErrorHelper();
                $measure_time_message = "WEB_REPORTS.M\$LIST_PLAYER_HISTORY(:p_player_name_in = {$start_date},' . \"to_date(:p_start_date_in = {$start_date}, 'DD-Mon-YYYY'),\" . \"to_date(:p_end_date_in = {$end_date}, 'DD-Mon-YYYY')\" . ', :p_page_number_in = {$page_number}, :p_hits_per_page_in = {$hits_per_page}, :p_order_by_in = {$order_by}, :p_sort_order_in = {$sort_order}, :p_players_list_out, :p_player_credits_out)";
                $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                $errorHelper->siteAccessLog($measure_time_message);
            }

			return array($table, $info, $actual_credits);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return false;
		}
	}
		
	//list player history details report for site
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $per_page
     * @param int $order_by
     * @param string $sort_order
     * @return array|bool
     * @throws Zend_Exception
     */
	public function listGameSessionDetails($session_id, $page_number = 1, $per_page = 25, $order_by = 1, $sort_order = 'asc'){
	    $config = Zend_Registry::get('config');
	    if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }

        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$SESSION_SESSION_DETAILS(:p_session_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_details_out, :p_player_name_out, :p_aff_name_out, :p_ip_address_out, :p_country_name)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_details_out', $cursor);
			$player_name = '';
			$stmt->bindParam(':p_player_name_out', $player_name, SQLT_CHR, 255);
			$aff_name = '';
			$stmt->bindParam(':p_aff_name_out', $aff_name, SQLT_CHR, 255);
			$ip_address = '';
			$stmt->bindParam(':p_ip_address_out', $ip_address, SQLT_CHR, 255);
			$country_name = '';
			$stmt->bindParam(':p_country_name', $country_name, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$help = new CursorToArrayHelper($cursor);
			$table = $help->getTableRows();
			$info = $help->getPageRow();

			if($config->measureSpeedPerformance == "true") {
                $after_time = microtime(true);
                $difference_time = number_format(($after_time-$before_time), 4);
                $errorHelper = new ErrorHelper();
                $measure_time_message = "WEB_REPORTS.M\$SESSION_SESSION_DETAILS(:p_session_id_in = {$session_id}, :p_page_number_in = {$page_number}, :p_hits_per_page_in = {$per_page}, :p_order_by_in = {$order_by}, :p_sort_order_in = {$sort_order}, :p_details_out, :p_player_name_out, :p_aff_name_out, :p_ip_address_out, :p_country_name)";
                $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                $errorHelper->siteAccessLog($measure_time_message);
            }

			return array("table"=>$table, "info"=>$info, "session_type_name"=>"",
			"player_name"=>$player_name, "affiliate_name"=>$aff_name, "ip_address"=>$ip_address, 
			"country_name"=>$country_name);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return false;
		}
	}
	
	//list player history subdetails report for site
    /**
     * @param $session_id
     * @param int $page_number
     * @param int $per_page
     * @param int $order_by
     * @param string $sort_order
     * @return array|bool
     * @throws Zend_Exception
     */
	public function listGameSessionSubdetails($session_id, $page_number = 1, $per_page = 25, $order_by = 1, $sort_order = 'asc'){
	    $config = Zend_Registry::get('config');
	    if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }

        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$SESSION_DETAILS(:p_session_id_in, :p_page_number_in, :p_hits_per_page_in, :p_order_by_in, :p_sort_order_in, :p_details_out, :p_session_type_name, :p_player_name_out, :p_aff_name_out, :p_ip_address_out, :p_country_name, :p_totals_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_page_number_in', $page_number);
			$stmt->bindParam(':p_hits_per_page_in', $per_page);
			$stmt->bindParam(':p_order_by_in', $order_by);
			$stmt->bindParam(':p_sort_order_in', $sort_order);
			$cursorReport = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_details_out', $cursorReport);
			$session_type_name = '';
			$stmt->bindParam(':p_session_type_name', $session_type_name, SQLT_CHR, 255);
			$player_name = '';
			$stmt->bindParam(':p_player_name_out', $player_name, SQLT_CHR, 255);
			$aff_name = '';
			$stmt->bindParam(':p_aff_name_out', $aff_name, SQLT_CHR, 255);
			$ip_address = '';
			$stmt->bindParam(':p_ip_address_out', $ip_address, SQLT_CHR, 255);
			$country_name = '';
			$stmt->bindParam(':p_country_name', $country_name, SQLT_CHR, 255);
			$cursorTotal = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_totals_out', $cursorTotal);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursorReport->execute();
			$cursorTotal->execute();
			$cursorReport->free();
			$cursorTotal->free();
			$dbAdapter->closeConnection();
			$helpReport = new CursorToArrayHelper($cursorReport);
			$tableReport = $helpReport->getTableRows();
			$infoReport = $helpReport->getPageRow();

			if($config->measureSpeedPerformance == "true") {
                $after_time = microtime(true);
                $difference_time = number_format(($after_time-$before_time), 4);
                $errorHelper = new ErrorHelper();
                $measure_time_message = "WEB_REPORTS.M\$SESSION_DETAILS(:p_session_id_in = {$session_id}, :p_page_number_in = {$page_number}, :p_hits_per_page_in = {$per_page}, :p_order_by_in = {$order_by}, :p_sort_order_in = {$sort_order}, :p_details_out, :p_session_type_name, :p_player_name_out, :p_aff_name_out, :p_ip_address_out, :p_country_name, :p_totals_out)";
                $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                $errorHelper->siteAccessLog($measure_time_message);
            }

			return array("table"=>$tableReport, "info"=>$infoReport, "session_type_name"=>$session_type_name,
				"player_name"=>$player_name, "affiliate_name"=>$aff_name, "ip_address"=>$ip_address, 
				"country_name"=>$country_name, "tableTotal"=>$cursorTotal);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return false;
		}
	}

    //list top won jackpots report
    /**
     * @param $affiliate_name
     * @return array
     * @throws Zend_Exception
     */
	public function getTopWonJackpots($affiliate_name){
	    $config = Zend_Registry::get('config');

	    if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }

	    if($config->db->enable_cache == "true"){
	        $cacheObj = Zend_Registry::get('db_cache');
			$cache_key_name = "WEB_REPORTS__GET_TOP_WON_JACKPOTS_p_wl_name_{$affiliate_name}";
			$cache_key_name = str_replace(array("."), "_", $cache_key_name);
		    $result = unserialize($cacheObj->load($cache_key_name) );
		    if(!isset($result) || $result == null || !$result) {
		       /* @var $dbAdapter Zend_Db_Adapter_Oracle */
                $dbAdapter = Zend_Registry::get('db_auth');
                $dbAdapter->beginTransaction();
                try {
                    $stmt = $dbAdapter->prepare('CALL WEB_REPORTS.get_top_won_jackpots(:p_wl_name_in, :cur_result)');
                    $stmt->bindParam(':p_wl_name_in', $affiliate_name);
                    $cursorReport = new Zend_Db_Cursor_Oracle($dbAdapter);
                    $stmt->bindCursor(':cur_result', $cursorReport);
                    $stmt->execute(null, false);
                    $dbAdapter->commit();
                    $cursorReport->execute();
                    $cursorReport->free();
                    $dbAdapter->closeConnection();
                    $result = array("status" => OK, "cursor" => $cursorReport);

                    $cache_key_name = "WEB_REPORTS__GET_TOP_WON_JACKPOTS_p_wl_name_{$affiliate_name}";
					$cache_key_name = str_replace(array("."), "_", $cache_key_name);
                    $cacheObj->save(serialize($result), $cache_key_name);

                    if($config->measureSpeedPerformance == "true") {
                        $after_time = microtime(true);
                        $difference_time = number_format(($after_time-$before_time), 4);
                        $errorHelper = new ErrorHelper();
                        $measure_time_message = "WEB_REPORTS.get_top_won_jackpots(:p_wl_name_in = {$affiliate_name}, :cur_result)";
                        $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                        $errorHelper->siteAccessLog($measure_time_message);
                    }

                    return $result;
                } catch (Zend_Exception $ex) {
                    $dbAdapter->rollBack();
                    $dbAdapter->closeConnection();
                    $errorHelper = new ErrorHelper();
                    $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
                    $errorHelper->siteError($message, $message);
                    return array("status" => NOK);
                }
            }else{
		        return $result;
            }
        }else {
            /* @var $dbAdapter Zend_Db_Adapter_Oracle */
            $dbAdapter = Zend_Registry::get('db_auth');
            $dbAdapter->beginTransaction();
            try {
                $stmt = $dbAdapter->prepare('CALL WEB_REPORTS.get_top_won_jackpots(:p_wl_name_in, :cur_result)');
                $stmt->bindParam(':p_wl_name_in', $affiliate_name);
                $cursorReport = new Zend_Db_Cursor_Oracle($dbAdapter);
                $stmt->bindCursor(':cur_result', $cursorReport);
                $stmt->execute(null, false);
                $dbAdapter->commit();
                $cursorReport->execute();
                $cursorReport->free();
                $dbAdapter->closeConnection();

                if($config->measureSpeedPerformance == "true") {
                    $after_time = microtime(true);
                    $difference_time = number_format(($after_time-$before_time), 4);
                    $errorHelper = new ErrorHelper();
                    $measure_time_message = "WEB_REPORTS.get_top_won_jackpots(:p_wl_name_in = {$affiliate_name}, :cur_result)";
                    $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                    $errorHelper->siteAccessLog($measure_time_message);
                }

                return array("status" => OK, "cursor" => $cursorReport);
            } catch (Zend_Exception $ex) {
                $dbAdapter->rollBack();
                $dbAdapter->closeConnection();
                $errorHelper = new ErrorHelper();
                $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->siteError($message, $message);
                return array("status" => NOK);
            }
        }
	}

    //list current jackpot levels report
    /**
     * @param $affiliate_name
     * @param $currency
     * @return array
     * @throws Zend_Exception
     */
    public function getCurrentJackpotLevels($affiliate_name, $currency){
        $config = Zend_Registry::get('config');

        if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }

	    if($config->db->enable_cache == "true"){
	        $cacheObj = Zend_Registry::get('db_cache');
			$cache_key_name = "WEB_REPORTS__GET_CURRENT_JP_LEVELS_p_wl_name_{$affiliate_name}_p_currency_in_{$currency}";
			$cache_key_name = str_replace(array("."), "_", $cache_key_name);
		    $result = unserialize($cacheObj->load($cache_key_name) );
		    if(!isset($result) || $result == null || !$result) {
		        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
                $dbAdapter = Zend_Registry::get('db_auth');
                $dbAdapter->beginTransaction();
                try {
                    $stmt = $dbAdapter->prepare('CALL WEB_REPORTS.get_current_jp_levels(:p_wl_name_in, :p_currency_in, :cur_result)');
                    $stmt->bindParam(':p_wl_name_in', $affiliate_name);
                    $stmt->bindParam(':p_currency_in', $currency);
                    $cursorReport = new Zend_Db_Cursor_Oracle($dbAdapter);
                    $stmt->bindCursor(':cur_result', $cursorReport);
                    $stmt->execute(null, false);
                    $dbAdapter->commit();
                    $cursorReport->execute();
                    $cursorReport->free();
                    $dbAdapter->closeConnection();

                    $result = array("status" => OK, "cursor" => $cursorReport);

                    $cache_key_name = "WEB_REPORTS__GET_CURRENT_JP_LEVELS_p_wl_name_{$affiliate_name}_p_currency_in_{$currency}";
					$cache_key_name = str_replace(array("."), "_", $cache_key_name);
                    $cacheObj->save(serialize($result), $cache_key_name);

                    if($config->measureSpeedPerformance == "true") {
                        $after_time = microtime(true);
                        $difference_time = number_format(($after_time-$before_time), 4);
                        $errorHelper = new ErrorHelper();
                        $measure_time_message = "WEB_REPORTS.get_current_jp_levels(:p_wl_name_in = {$affiliate_name}, :p_currency_in = {$currency}, :cur_result)";
                        $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                        $errorHelper->siteAccessLog($measure_time_message);
                    }

                    return $result;
                } catch (Zend_Exception $ex) {
                    $dbAdapter->rollBack();
                    $dbAdapter->closeConnection();
                    $errorHelper = new ErrorHelper();
                    $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
                    $errorHelper->siteError($message, $message);
                    return array("status" => NOK);
                }
            }else{
		        return $result;
            }
        }else {
            /* @var $dbAdapter Zend_Db_Adapter_Oracle */
            $dbAdapter = Zend_Registry::get('db_auth');
            $dbAdapter->beginTransaction();
            try {
                $stmt = $dbAdapter->prepare('CALL WEB_REPORTS.get_current_jp_levels(:p_wl_name_in, :p_currency_in, :cur_result)');
                $stmt->bindParam(':p_wl_name_in', $affiliate_name);
                $stmt->bindParam(':p_currency_in', $currency);
                $cursorReport = new Zend_Db_Cursor_Oracle($dbAdapter);
                $stmt->bindCursor(':cur_result', $cursorReport);
                $stmt->execute(null, false);
                $dbAdapter->commit();
                $cursorReport->execute();
                $cursorReport->free();
                $dbAdapter->closeConnection();

                if($config->measureSpeedPerformance == "true") {
                    $after_time = microtime(true);
                    $difference_time = number_format(($after_time-$before_time), 4);
                    $errorHelper = new ErrorHelper();
                    $measure_time_message = "WEB_REPORTS.get_current_jp_levels(:p_wl_name_in = {$affiliate_name}, :p_currency_in = {$currency}, :cur_result)";
                    $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                    $errorHelper->siteAccessLog($measure_time_message);
                }

                return array("status" => OK, "cursor" => $cursorReport);
            } catch (Zend_Exception $ex) {
                $dbAdapter->rollBack();
                $dbAdapter->closeConnection();
                $errorHelper = new ErrorHelper();
                $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->siteError($message, $message);
                return array("status" => NOK);
            }
        }
	}

    //list high wins report
    /**
     * @param $affiliate_name
     * @return array
     * @throws Zend_Exception
     */
    public function getHighWinList($affiliate_name){
        $config = Zend_Registry::get('config');
	    if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }

        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.show_high_win_list(:p_site_aff_name, :p_list_wins)');
            $stmt->bindParam(':p_site_aff_name', $affiliate_name);
			$cursorReport = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_wins', $cursorReport);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursorReport->execute();
			$cursorReport->free();
			$dbAdapter->closeConnection();

			if($config->measureSpeedPerformance == "true") {
                $after_time = microtime(true);
                $difference_time = number_format(($after_time-$before_time), 4);
                $errorHelper = new ErrorHelper();
                $measure_time_message = "WEB_REPORTS.show_high_win_list(:p_site_aff_name = {$affiliate_name}, :p_list_wins)";
                $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                $errorHelper->siteAccessLog($measure_time_message);
            }

			return array("status"=>OK, "cursor"=>$cursorReport);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK);
		}
    }

}