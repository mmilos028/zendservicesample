<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';

class VivoGamingIntegrationModel{
	private $DEBUG = false;

	public function __construct(){
	}
    /**
     * @param $session_id
     * @param $subject_id
     * @param $credits
     * @param $game_id
     * @return mixed
     * @throws Zend_Exception
     */
	public function getVivoGamingIntegrationToken($session_id, $subject_id, $credits, $game_id){
         /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();

		try{
			$stmt = $dbAdapter->prepare('CALL vivo_gaming_integration.get_token(:p_subject_id, :p_session_id, :p_credits, :p_game_id, :p_token_out)');
            $stmt->bindParam(':p_subject_id', $subject_id);
			$stmt->bindParam(':p_session_id', $session_id);
			$stmt->bindParam(':p_credits', $credits);
            $stmt->bindParam(':p_game_id', $game_id);
			$token = "";
			$stmt->bindParam(':p_token_out', $token, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();

			if($this->DEBUG){
                $errorHelper = new ErrorHelper();
                $message = "vivo_gaming_integration.get_token(:p_subject_id = {$subject_id}, :p_session_id = {$session_id}, :p_credits = {$credits}, :p_game_id = {$game_id}, :p_token_out = {$token})";
                $errorHelper->vivoGamingIntegrationAccessLog($message);
			}

			return array("status"=>OK, "token"=>$token);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->vivoGamingIntegrationError($message, $message);
			throw new Zend_Exception($message);
		}
	}

    /**
     * @param $token
     * @return array
     * @throws Zend_Exception
     */
    public function authenticate($token){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();

		try{
			$stmt = $dbAdapter->prepare('CALL vivo_gaming_integration.validate_token(:p_token, :p_username_out, :p_subject_id_out, :p_first_name_out, :p_last_name_out, :p_email_out, :p_currency_out,
			:p_credits_out, :p_game_session_id_out, :p_err_code_out, :p_err_msg_out)');
            $stmt->bindParam(':p_token', $token);
            $username = "";
			$stmt->bindParam(':p_username_out', $username, SQLT_CHR, 255);
            $subject_id = "";
			$stmt->bindParam(':p_subject_id_out', $subject_id, SQLT_CHR, 255);
			$first_name = "";
			$stmt->bindParam(':p_first_name_out', $first_name, SQLT_CHR, 255);
            $last_name = "";
			$stmt->bindParam(':p_last_name_out', $last_name, SQLT_CHR, 255);
            $email = "";
			$stmt->bindParam(':p_email_out', $email, SQLT_CHR, 255);
            $currency = "";
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
            $credits = "";
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
            $game_session_id = "";
			$stmt->bindParam(':p_game_session_id_out', $game_session_id, SQLT_CHR, 255);
            $error_code = "";
			$stmt->bindParam(':p_err_code_out', $error_code, SQLT_CHR, 255);
            $error_message = "";
			$stmt->bindParam(':p_err_msg_out', $error_message, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();

			if($this->DEBUG){
                $errorHelper = new ErrorHelper();
                $message = "vivo_gaming_integration.validate_token(:p_token = {$token}, :p_username_out = {$username}, :p_subject_id_out = {$subject_id}, :p_first_name_out = {$first_name}, :p_last_name_out = {$last_name}, :p_email_out = {$email}, :p_currency_out = {$currency}, :p_credits_out = {$credits}, :p_game_session_id_out = {$game_session_id}, :p_err_code_out = {$error_code}, :p_err_msg_out = {$error_message})";
                $errorHelper->vivoGamingIntegrationAccessLog($message);
			}

            if($error_code == "") {
                return array("status" => OK, "username" => $username, "subject_id" => $subject_id, "first_name" => $first_name, "last_name" => $last_name, "email" => $email,
                    "currency" => $currency, "credits" => $credits, "game_session_id" => $game_session_id, "error_code" => $error_code, "error_message" => $error_message);
            }
            if($error_code == "-1"){
                return array("status" => NOK, "error_code"=>$error_code, "error_message"=>$error_message);
            }
            if($error_code == "-99"){
                return array("status" => NOK, "error_code"=>$error_code, "error_message"=>$error_message);
            }else{
                return array("status" => NOK, "error_code"=>$error_code, "error_message"=>$error_message);
            }
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->vivoGamingIntegrationError($message, $message);
            $error_code = "-99";
            $error_message = "Unhandled exception.";
			return array("status" => NOK, "error_code"=>$error_code, "error_message"=>$error_message);
		}
    }

    /**
     * @param $subject_id
     * @return array
     * @throws Zend_Exception
     */
    public function getBalance($subject_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL vivo_gaming_integration.get_balance(:p_subject_id, :p_credits_out)');
            $stmt->bindParam(':p_subject_id', $subject_id);
            $credits = "";
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            if($credits == "-1"){
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
			return array("status"=>OK, "credits"=>$credits);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status" => NOK, "message"=>NOK_EXCEPTION);
		}
    }

    /**
     * @param $user_id
     * @param $amount
     * @param $transaction_id
     * @param $transaction_type
     * @param $transaction_description
     * @param $round_id
     * @param $game_id
     * @param $session_id
     * @param $history
     * @param $is_round_finished
     * @return array
     * @throws Zend_Exception
     */
    public function changeBalance($user_id, $amount, $transaction_id, $transaction_type, $transaction_description, $round_id, $game_id, $session_id, $history, $is_round_finished){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();

		if($this->DEBUG){
            $errorHelper = new ErrorHelper();
            $message = "CALL vivo_gaming_integration.change_balance(:p_user_id = {$user_id}, :p_amount = {$amount}, :p_transaction_id = {$transaction_id},
            :p_transaction_type = {$transaction_type}, :p_transaction_description = {$transaction_description}, :p_round_id = {$round_id}, :p_game_id = {$game_id},
            :p_session_id = {$session_id}, :p_history = {$history}, :p_is_round_finished = {$is_round_finished}, :p_balance_out =, :p_transaction_id_out=)";
            $errorHelper->vivoGamingIntegrationAccessLog($message);
		}

		try{
			$stmt = $dbAdapter->prepare('CALL vivo_gaming_integration.change_balance(:p_user_id, :p_amount, :p_transaction_id, :p_transaction_type, :p_transaction_description, :p_round_id, :p_game_id,
			:p_session_id, :p_history, :p_is_round_finished, :p_balance_out, :p_transaction_id_out)');
            $stmt->bindParam(':p_user_id', $user_id);
			$stmt->bindParam(':p_amount', $amount);
			$stmt->bindParam(':p_transaction_id', $transaction_id);
			$stmt->bindParam(':p_transaction_type', $transaction_type);
			$stmt->bindParam(':p_transaction_description', $transaction_description);
			$stmt->bindParam(':p_round_id', $round_id);
			$stmt->bindParam(':p_game_id', $game_id);
			$stmt->bindParam(':p_session_id', $session_id);
			$stmt->bindParam(':p_history', $history);
			$stmt->bindParam(':p_is_round_finished', $is_round_finished);
            $balance_out = "";
            $stmt->bindParam(':p_balance_out', $balance_out, SQLT_CHR, 255);
            $ec_system_transaction_id_out = "";
            $stmt->bindParam(':p_transaction_id_out', $ec_system_transaction_id_out, SQLT_CHR, 255);
            $stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            return array("status" => OK, "ec_system_transaction_id"=>$ec_system_transaction_id_out, "balance"=>$balance_out, "user_id"=>$user_id, "amount"=>$amount, "transaction_id"=>$transaction_id, "transaction_type"=>$transaction_type,
                "transaction_description"=>$transaction_description, "round_id"=>$round_id, "game_id"=>$game_id, "session_id"=>$session_id,
                "history"=>$history, "is_round_finished"=>$is_round_finished);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$error_code = $ex->getCode();
            // WHEN ex_no_active_session THEN raise_application_error(-20399, 'User does not have any active session.');
            $error_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper = new ErrorHelper();
			$errorHelper->vivoGamingIntegrationError($error_message, $error_message);
			return array("status" => NOK, "error_code"=>$error_code, "error_message"=>$error_message);
		}
    }

    /**
     * @param $user_id
     * @param $casino_transaction_id
     * @return array
     * @throws Zend_Exception
     */
    public function checkTransactionStatus($user_id, $casino_transaction_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL vivo_gaming_integration.get_status(:p_user_id, :p_vivo_transaction_id, :p_status_out, :p_transaction_id_out)');
            $stmt->bindParam(':p_user_id', $user_id);
			$stmt->bindParam(':p_vivo_transaction_id', $casino_transaction_id);
            $status_out = "";
            $stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
            $ec_system_transaction_id_out = "";
            $stmt->bindParam(':p_transaction_id_out', $ec_system_transaction_id_out, SQLT_CHR, 255);
            $stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            return array(
                "status" => OK,
                "status_out"=>$status_out, "ec_system_transaction_id"=>$ec_system_transaction_id_out,
                "user_id"=>$user_id, "casino_transaction_id"=>$casino_transaction_id,
            );
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$error_code = $ex->getCode();
            $error_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper = new ErrorHelper();
			$errorHelper->vivoGamingIntegrationError($error_message, $error_message);
			return array("status" => NOK, "error_code"=>$error_code, "error_message"=>$error_message);
		}
    }

    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
    public function closeVivoIntegrationSession($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        //DEBUG MESSAGES
		/*
		$errorHelper = new ErrorHelper();
		$mail_message = $log_message = "Close Vivo Gaming integration pc_session_id = {$session_id}";
		$errorHelper->vivoGamingIntegrationError($mail_message, $log_message);
		*/
		try{
			$stmt = $dbAdapter->prepare('CALL vivo_gaming_integration.close_pc_vevo_session(:p_session_id_in, :p_status_out)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $status_out = "";
            $stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
            $stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            return array(
                "status" => OK,
                "status_out"=>$status_out
            );
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$error_code = $ex->getCode();
            $error_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper = new ErrorHelper();
			$errorHelper->vivoGamingIntegrationError($error_message, $error_message);
			return array("status" => NOK, "error_code"=>$error_code, "error_message"=>$error_message);
		}
    }
}
