<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'HighScoreModel.php';

/**
 *
 * High Score Manager class ...
 *
 */

class HighScoreManager {

	/**
	 *
	 * List high score ...
     * @param $session_id
     * @param $sort_method
     * @param $page_number
     * @param $hits_per_page
	 * @return mixed
	 */
	public function listHighScore($session_id, $sort_method, $page_number, $hits_per_page){
        if(!isset($sort_method) || strlen($sort_method) == 0)
        {
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
            $sort_method = strip_tags($sort_method);
			$modelHighScore = new HighScoreModel();
			$result = $modelHighScore->listHighScore($session_id, $sort_method, $page_number, $hits_per_page);
            if($result['status'] != OK){
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
			$report = array();
			foreach($result['table'] as $row){
				$report[] = array(
                    "id"=>$row['id'],
                    "subject_id"=>$row['subject_id'],
				    "name"=>$row['name'],
                    "score"=>$row['score'],
				    "date_time"=>$row['date_time_format'],
                    "like_flag"=>$row['like_flag']
                );
			}
            $total_items = $result['info'][0]['cnt'];
            $total_pages = ceil($total_items / $hits_per_page);
			return array("status"=>OK, "report"=>$report, "total_pages"=>$total_pages);
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			$errorHelper = new ErrorHelper();
            $message =  CursorToArrayHelper::getExceptionTraceAsString($ex);
			$mail_message = "HighScoreManager::listHighScore(sort_method = {sort_method = {$sort_method}, page_number = {$page_number}, hits_per_page = {$hits_per_page}) Exception: <br /> " . $message . " <br /> Detected IP Address = {$detected_ip_address}";
			$log_message = $message;
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	 *
	 * add high score
	 * @param $subject_id
	 * @param $name
     * @param $score
     * @param $like_flag
	 * @return mixed
	 */
	public function addHighScore($subject_id, $name, $score, $like_flag){

		if(!isset($subject_id) || strlen($subject_id) == 0 ||
            !isset($name) || strlen($name) == 0 ||
            !isset($score) || strlen($score) == 0
        ){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
			$subject_id = strip_tags($subject_id);
			$name = strip_tags($name);
            $score = strip_tags($score);
            $like_flag = strip_tags($like_flag);
			$modelHighScore = new HighScoreModel();
			$arrData = $modelHighScore->addHighScore($subject_id, $name, $score, $like_flag);
			if($arrData['status'] != OK){
                $detected_ip_address = IPHelper::getRealIPAddress();
				//sends email when usb credit transaction fails
				$errorHelper = new ErrorHelper();
				$mail_message = $log_message = "HighScoreManager::addHighScore(subject_id = {$subject_id}, name={$name}, score={$score}, like_flag={$like_flag}) <br /> Detected IP Address = {$detected_ip_address}";
				$errorHelper->serviceError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}else {
                return $arrData;
            }
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			//sends email when usb credit transaction fails
			$errorHelper = new ErrorHelper();
			$message = "HighScoreManager::addHighScore(subject_id = {$subject_id}, name={$name}, score={$score}, like_flag={$like_flag}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
	 *
	 * add high score
	 * @param $subject_id
	 * @return mixed
	 */
	public function checkScore($subject_id){

		if(!isset($subject_id) || strlen($subject_id) == 0){
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
			$subject_id = strip_tags($subject_id);
			$modelHighScore = new HighScoreModel();
			$arrData = $modelHighScore->checkScore($subject_id);
			if($arrData['status'] != OK){
                $detected_ip_address = IPHelper::getRealIPAddress();
				//sends email when usb credit transaction fails
				$errorHelper = new ErrorHelper();
				$mail_message = $log_message = "HighScoreManager::checkScore(subject_id = {$subject_id}) <br /> Detected IP Address = {$detected_ip_address}";
				$errorHelper->serviceError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}else {
                return $arrData;
            }
		}catch(Zend_Exception $ex){
            $detected_ip_address = IPHelper::getRealIPAddress();
			//sends email when usb credit transaction fails
			$errorHelper = new ErrorHelper();
			$message = "HighScoreManager::checkScore(subject_id = {$subject_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
     * check terminal date code from his affiliate
     * @param $session_id
     * @param $score
     * @return array
     */
	public function checkScorePosition($session_id, $score){
        $session_id = strip_tags($session_id);
		$score = strip_tags($score);
		if(strlen($session_id) == 0 || strlen($score) == 0){
			//$errorHelper = new ErrorHelper();
            //$detected_ip_address = IPHelper::getRealIPAddress();
			//$message = "HighScoreManager::checkScorePosition(session_id = {$session_id}, score = {$score}) <br /> Detected IP Address = {$detected_ip_address} <br /> Parameters to check score position are not sent!!!";
			//$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'HighScoreModel.php';
			$modelHighScore = new HighScoreModel();
			return $modelHighScore->checkScorePosition($session_id, $score);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $message = "HighScoreManager::checkScorePosition(session_id = {$session_id}, score = {$score}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "message"=>INTERNAL_ERROR);
		}
	}


}