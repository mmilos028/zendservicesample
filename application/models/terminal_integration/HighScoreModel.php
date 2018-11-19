<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';

class HighScoreModel{
	public function __construct(){
	}

    //add high score on site
    /**
     * @param $subject_id
     * @param $name
     * @param $score
     * @param $like_flag
     * @return array
     * @throws Zend_Exception
     */
	public function addHighScore($subject_id, $name, $score, $like_flag){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.add_high_score(:p_subject_id, :p_name, :p_score, :p_like_flag, :p_status_out)');
			$stmt->bindParam(':p_subject_id', $subject_id);
            $stmt->bindParam(':p_name', $name);
            $stmt->bindParam(':p_score', $score);
            $stmt->bindParam(':p_like_flag', $like_flag);
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
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->sendMail($message);
			$errorHelper->serviceErrorLog($message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    //list high scores
    /**
     * @param $session_id
     * @param $sort_method
     * @param int $page_number
     * @param int $hits_per_page
     * @return array
     * @throws Zend_Exception
     */
	public function listHighScore($session_id, $sort_method, $page_number = 1, $hits_per_page = 200){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.list_high_score_web(:p_session_id, :p_sort_metod, :p_page_number_in, :p_hits_per_page_in, :p_high_list_out)');
            $stmt->bindParam(":p_session_id", $session_id);
            $stmt->bindParam(":p_sort_metod", $sort_method);
            $stmt->bindParam(":p_page_number_in", $page_number);
            $stmt->bindParam(":p_hits_per_page_in", $hits_per_page);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_high_list_out", $cursor);
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
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->sendMail($message);
			$errorHelper->serviceErrorLog($message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
     * @param $subject_id
     * @return array
     * @throws Zend_Exception
     */
    public function checkScore($subject_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.check_score(:p_subject_id, :p_score_out)');
			$stmt->bindParam(':p_subject_id', $subject_id);
			$score_out = "";
			$stmt->bindParam(':p_score_out', $score_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "score"=>$score_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->sendMail($message);
			$errorHelper->serviceErrorLog($message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
    }

    /**
     * check score position
     * @param $session_id
     * @param $score
     * @return array
     * @throws Zend_Exception
     */
	public function checkScorePosition($session_id, $score){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.CHECK_SCORE_POSITION(:p_session_id, :p_score_in, :p_score_position, :p_position_down, :p_position_up)');
            $stmt->bindParam(':p_session_id', $session_id);
			$stmt->bindParam(':p_score_in', $score);
            $score_position = "";
            $stmt->bindParam(':p_score_position', $score_position, SQLT_CHR, 255);

            $cursor_position_down = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_position_down', $cursor_position_down);

            $cursor_position_up = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_position_up', $cursor_position_up);

			$stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor_position_down->execute();
            $cursor_position_up->execute();
            $cursor_position_down->free();
            $cursor_position_up->free();
            $dbAdapter->closeConnection();

            $position_up_array = array();
            foreach($cursor_position_up as $cpu){
                $position_up_array[] = array(
                    'id' => $cpu['id'],
                    'subject_id' => $cpu['subject_id'],
                    'name' => $cpu['name'],
                    'score_date_time' => $cpu['date_time_format'],
                    'score' => $cpu['score'],
                    'like_flag' => $cpu['like_flag'],
                    'rownum' => $cpu['row1'],
                    'row_id' => $cpu['row_id']
                );
            }

            $position_down_array = array();
            foreach($cursor_position_down as $cpd){
                $position_down_array[] = array(
                    'id' => $cpd['id'],
                    'subject_id' => $cpd['subject_id'],
                    'name' => $cpd['name'],
                    'score_date_time' => $cpd['date_time_format'],
                    'score' => $cpd['score'],
                    'like_flag' => $cpd['like_flag'],
                    'rownum' => $cpd['row1'],
                    'row_id' => $cpd['row_id']
                );
            }

			return array(
                "status"=>OK,
                "score"=>$score,
                "score_position"=>$score_position,
                "position_up_list" => $position_up_array,
                "position_down_list" => $position_down_array
            );
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $code = $ex->getCode();

            $errorHelper = new ErrorHelper();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            return array("status"=>NOK, "message"=>INTERNAL_ERROR);
		}
	}
}
