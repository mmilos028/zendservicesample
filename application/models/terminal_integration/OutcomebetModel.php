<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class OutcomebetModel{
	public function __construct(){
	}

    public static $withdraw_transaction = "MANAGMENT_TYPES.NAME_IN_CREDITS_TO_OUTCOME";
    public static $deposit_transaction = "MANAGMENT_TYPES.NAME_IN_CREDITS_FROM_OUTCOME";

    public static $NOT_VALID_DATA = array("code"=>-1, "message"=>"Invalid Request", "data"=>"Invalid data");
    public static $CASINO_ID_NOT_MATCH = array("code"=>-2, "message"=>"Invalid Request", "data"=>"Casino ID not match");
    public static $AMOUNT_NEGATIVE_VALUE = array("code"=>-3, "message"=>"Invalid Request", "data"=>"Amount is negative value");
    public static $INTERNAL_ERROR = array("code"=>-4, "message"=>"Invalid Request", "data"=>"Internal error");
    public static $INVALID_BET_WIN = array("code"=>-5, "message"=>"Invalid Request", "data"=>"Invalid operation, not bet or win");
    public static $INVALID_PLAYER_ID = array("code"=>-6, "message"=>"Invalid Request", "data"=>"Invalid player id sent");
    public static $INVALID_OPERATION = array("code"=>-7, "message"=>"Invalid Request", "data"=>"Invalid operation selected");

    public static function balanceGet($playerId, $casinoId = null, $gameId = null, $sessionId = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL OUTCOMEBET.GET_BALANCE(:p_player_id_in, :p_credits_out)');
			$stmt->bindParam(':p_player_id_in', $playerId);
            $credits = "";
            $stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "amount"=>$credits);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK);
		}
    }

    public static function balanceChange($playerId, $operationAmount, $gameId, $transactionTypeName,
        $sessionId = null, $opid = null, $casinoId = null, $betType = null, $operationId = null, $operationType = null, $operationReason = null, $operationEventId = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL OUTCOMEBET.post_transaction_ws(:p_player_id_in, :p_amount_in, :p_live_casino_game_id, :p_transaction_type_name_in, :p_transaction_id_out)');
			$stmt->bindParam(':p_player_id_in', $playerId);
			$stmt->bindParam(':p_amount_in', $operationAmount);
			$stmt->bindParam(':p_live_casino_game_id', $gameId);
			$stmt->bindParam(':p_transaction_type_name_in', $transactionTypeName);
			$transactionId = "";
			$stmt->bindParam(':p_transaction_id_out', $transactionId, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "player_id"=>$playerId,
			"session_id"=>$sessionId, "transaction_id"=>$transactionId);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$code = $ex->getCode();
			$mail_message = "OutcomebetModel::postTransaction - OUTCOMEBET.post_transaction_ws(:p_player_id_in = {$playerId}, :p_amount_in = {$operationAmount}, :p_live_casino_game_id = {$gameId}, :p_transaction_type_name_in = {$transactionTypeName}, :p_transaction_id_out) exception error: <br /> ORA {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex) . "<br />";
			$log_message = "OutcomebetModel::postTransaction - OUTCOMEBET.post_transaction_ws(:p_player_id_in = {$playerId}, :p_amount_in = {$operationAmount}, :p_live_casino_game_id = {$gameId}, :p_transaction_type_name_in = {$transactionTypeName} - ORA {$code} exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_NO_PARENT_TRANSACTION);
		}
    }

     public static function balanceChangeComplete($playerId, $operations, $gameId, $sessionId = null, $opid = null, $casinoId = null, $betType = null){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
            $balanceDetails = OutcomebetModel::balanceGet($playerId);
            if ($balanceDetails["status"] != OK) {
                return array("status"=>NOK, "result"=>OutcomebetModel::$INTERNAL_ERROR);
            }
            $playerCreditsBefore = (NumberHelper::convert_double($balanceDetails["amount"])) * 100;
            foreach($operations as $operation) {
                $operationId = $operation["Id"];
                $operationType = $operation["Type"]; //Win or Bet
                $operationAmount = $operation["Amount"];
                $operationReason = $operation["Reason"];
                $operationEventId = $operation["EventId"];
                if ($operationAmount < 0 || strlen($operationAmount) == 0) {
                    return array("status"=>NOK, "result"=>OutcomebetModel::$AMOUNT_NEGATIVE_VALUE);
                }
                if($operationAmount != 0) {
                    $operationAmount = $operationAmount / 100;
                    if ($operationType == "Win") {
                        //do deposit
                        $gameId = null;
                        $modelSubjectTypes = new SubjectTypesModel();
                        $transactionTypeName = $modelSubjectTypes->getSubjectType(OutcomebetModel::$deposit_transaction);
                        $stmt = $dbAdapter->prepare('CALL OUTCOMEBET.post_transaction_ws(:p_player_id_in, :p_amount_in, :p_live_casino_game_id, :p_transaction_type_name_in, :p_transaction_id_out)');
                        $stmt->bindParam(':p_player_id_in', $playerId);
                        $stmt->bindParam(':p_amount_in', $operationAmount);
                        $stmt->bindParam(':p_live_casino_game_id', $gameId);
                        $stmt->bindParam(':p_transaction_type_name_in', $transactionTypeName);
                        $transactionId = "";
                        $stmt->bindParam(':p_transaction_id_out', $transactionId, SQLT_CHR, 255);
                        $stmt->execute();
                    } else if ($operationType == "Bet") {
                        //do withdraw
                        $gameId = null;
                        $modelSubjectTypes = new SubjectTypesModel();
                        $transactionTypeName = $modelSubjectTypes->getSubjectType(OutcomebetModel::$withdraw_transaction);
                        $stmt = $dbAdapter->prepare('CALL OUTCOMEBET.post_transaction_ws(:p_player_id_in, :p_amount_in, :p_live_casino_game_id, :p_transaction_type_name_in, :p_transaction_id_out)');
                        $stmt->bindParam(':p_player_id_in', $playerId);
                        $stmt->bindParam(':p_amount_in', $operationAmount);
                        $stmt->bindParam(':p_live_casino_game_id', $gameId);
                        $stmt->bindParam(':p_transaction_type_name_in', $transactionTypeName);
                        $transactionId = "";
                        $stmt->bindParam(':p_transaction_id_out', $transactionId, SQLT_CHR, 255);
                        $stmt->execute();
                    } else {
                        $dbAdapter->rollBack();
			            $dbAdapter->closeConnection();
                        return array("status"=>NOK, "result"=>OutcomebetModel::$INVALID_BET_WIN);
                    }
                }
            }
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
            $afterBalanceDetails = OutcomebetModel::balanceGet($playerId);
            $playerCreditsAfter = (NumberHelper::convert_double($afterBalanceDetails["amount"])) * 100;
            $resultArray = array(
                "BalanceBefore" => $playerCreditsBefore,
                "BalanceAfter" => $playerCreditsAfter
            );
			return array("status"=>OK, "result"=>$resultArray);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$code = $ex->getCode();
            $mail_message = "";
            $log_message = "";
            foreach($operations as $operation) {
                $operationId = $operation["Id"];
                $operationType = $operation["Type"]; //Win or Bet
                $operationAmount = $operation["Amount"];
                $operationReason = $operation["Reason"];
                $operationEventId = $operation["EventId"];
                $transactionTypeName = $operationType == "Bet" ? OutcomebetModel::$withdraw_transaction : OutcomebetModel::$deposit_transaction;
                $mail_message .= "OutcomebetModel::postTransaction - OUTCOMEBET.post_transaction_ws(:p_player_id_in = {$playerId}, :p_amount_in = {$operationAmount}, :p_live_casino_game_id = {$gameId}, :p_transaction_type_name_in = {$transactionTypeName}, :p_transaction_id_out) exception error: <br /> ORA {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex) . "<br />";
                $log_message .= "OutcomebetModel::postTransaction - OUTCOMEBET.post_transaction_ws(:p_player_id_in = {$playerId}, :p_amount_in = {$operationAmount}, :p_live_casino_game_id = {$gameId}, :p_transaction_type_name_in = {$transactionTypeName} - ORA {$code} exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            }
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>OutcomebetModel::$INTERNAL_ERROR);
		}
    }

    /**
        Close Outcomebet session
        p_closed_session_id_out vraca id game sesije koja je ugasena
        ukoliko ne postoji game sesija za outcomebet koja je otvorena vraca -1
        ukoliko je bilo vise od 1 otvorene game sesije za outcomebet vratice null
    */
    public function closeOutcomebetSession($player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL OUTCOMEBET.close_outcomebet_session(:p_player_id_in, :p_closed_session_id_out)');
            $stmt->bindParam(':p_player_id_in', $player_id);
            $closed_session_id_out = "";
            $stmt->bindParam(':p_closed_session_id_out', $closed_session_id_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "player_id"=>$player_id, "closed_session_id_out"=>$closed_session_id_out);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            require_once HELPERS_DIR . DS . 'IPHelper.php';
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "OUTCOMEBET INTEGRATION Error <br /> OUTCOMEBET.close_outcomebet_session(:p_player_id_in = {$player_id}, :p_closed_session_id_out) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }
}

