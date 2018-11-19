<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class BettingWalletIntegrationModel{
	
	public function getCustomerInfo($username, $password, $player_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL BETTING_INTEGRATION.get_customer_info(:p_username_in, :p_password_in, :p_player_id_in, :p_credits_out)');
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$credits = 0.00;
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "username"=>$username, "password"=>$password, 
			"player_id"=>$player_id, "credits"=>doubleval($credits));
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$code = $ex->getCode();
			//RAISE_APPLICATION_ERROR (-20121, 'Wrong username or password !!!!');
			if($code == "20121"){
				$mail_message = "BettingIntegrationModel::getCustomerInfo - Betting_INTEGRATION.get_customer_info - Player: {$player_id} for user: {$username} <br /> Wrong username or password !";
				$log_message = "BettingIntegrationModel::getCustomerInfo - Betting_INTEGRATION.get_customer_info - Player: {$player_id} for user: {$username} Wrong username or password !";
				$errorHelper->serviceError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_NO_USERNAME);
			}
			//RAISE_APPLICATION_ERROR (-20720, 'Provided player does not exist.');
			if($code == "20720"){
				$mail_message = "BettingIntegrationModel::getCustomerInfo - Betting_INTEGRATION.get_customer_info - Player with player_id: {$player_id} for user: {$username} does not exist !";
				$log_message = "BettingIntegrationModel::getCustomerInfo - Betting_INTEGRATION.get_customer_info - Player with player_id: {$player_id} for user: {$username} does not exist !";
				$errorHelper->serviceError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_NO_PLAYER);
			}
			$mail_message = "BettingIntegrationModel::getCustomerInfo - Betting_INTEGRATION.get_customer_info - Player with player_id: {$player_id} and user: {$username} - exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "BettingIntegrationModel::getCustomerInfo - Betting_INTEGRATION.get_customer_info - Player with player_id: {$player_id} and user: {$username} - exception error: Code: {$code} " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * PROCEDURE post_transaction_ws(-- intended for our web service exposed to Betting
     p_username_in              IN subjects.name%type,
     p_password_in              IN subjects.password%type, -- md5 hash  given here
     p_player_id_in             IN subjects.id%type,
     p_session_id_in            IN csessions.id%type,
     p_amount_in                IN transactions.amount%type,
     p_transaction_type_name_in IN transaction_types.name%type,
     p_transaction_id_out OUT transactions.id%type)
		Ovde treba koristiti Betting credentials:
	P_USERNAME_IN := 'Betting_Admin_WS';
	P_PASSWORD_IN := 'ad9032ce2e22e6d8798f58c8269662cb';
	p_session_id_in = session id od site sesije
	Ukoliko Betting posalje pogresne credentials dobija se RAISE_APPLICATION_ERROR (-20121, 'Wrong username or password !!!!');
	Ukoliko posalju player_id koji ne postoji dobija se RAISE_APPLICATION_ERROR (-20720, 'Provided player does not exist.');
	Ukoliko se pare uzimaju od playera i prenose u Betting onda treba staviti
		da p_transaction_type_in managment_types.NAME_IN_CREDITS_TO_Betting   (a to ima vrednost  'CREDITS_TO_Betting');
	Ukoliko se pare uzimaju od Betting-a i vracaju playeru treba staviti:
		managment_types.NAME_IN_CREDITS_FROM_Betting    (a to ima vrednost 'CREDITS_FROM_Betting');
	Ne bi bilo lose da dodas web servis za ovu storku pa da malo probamo da zovemo i vidimo da li se dobro krediti prebacuju.
	Na primer, da prvo pozoves get_customer_info pa onda povuces sve te pare kao ka Betting-u pa da vidimo da li je stanje 0 u bazi, pa onda vratis sve pare (ili deo) pa da vidimo da li je dodato na kredit tom playeru u bazi...
	 */	
	public function postTransaction($username, $password, $session_id, $amount, $transaction_type_name, $game_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL BETTING_INTEGRATION.post_transaction_ws(:p_username_in, :p_password_in, :p_session_id_in, :p_amount_in, :p_live_casino_game_id, :p_transaction_type_name_in, :p_transaction_id_out)');
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_session_id_in', $session_id);			
			$stmt->bindParam(':p_amount_in', $amount);
			$game_id = null;
			$stmt->bindParam(':p_live_casino_game_id', $game_id);
			$stmt->bindParam(':p_transaction_type_name_in', $transaction_type_name);
			$transaction_id = "";
			$stmt->bindParam(':p_transaction_id_out', $transaction_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "username"=>$username,
			"session_id"=>$session_id, "transaction_id"=>$transaction_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$code = $ex->getCode();
			//RAISE_APPLICATION_ERROR (-20121, 'Wrong username or password !!!!');
			if($code == "20121"){ 
				$mail_message = "BettingIntegrationModel::postTransaction - Betting_INTEGRATION.post_transaction_ws - ORA 20121 Player {$username} <br /> Wrong username or password ! <br /> sessionID = {$session_id} <br /> amount = {$amount} <br /> transactionType = {$transaction_type_name} <br /> user = {$username} <br /> game_id = {$game_id}";
				$log_message = "BettingIntegrationModel::postTransaction - Betting_INTEGRATION.post_transaction_ws - ORA 20121 Player {$username} Wrong username or password ! sessionID = {$session_id} amount = {$amount} transactionType = {$transaction_type_name} user = {$username} game_id = {$game_id}";
				$errorHelper->serviceError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_NO_USERNAME);
			}
			//RAISE_APPLICATION_ERROR (-20720, 'Provided player does not exist.');
			if($code == "20720"){
				$mail_message = "BettingIntegrationModel::postTransaction - Betting_INTEGRATION.post_transaction_ws - ORA 20720 Player with username {$username} does not exist ! <br /> sessionID = {$session_id} <br /> amount = {$amount} <br /> transactionType = {$transaction_type_name} <br /> user = {$username} <br /> game_id = {$game_id}";
				$log_message = "BettingIntegrationModel::postTransaction - Betting_INTEGRATION.post_transaction_ws - ORA 20720 Player with username {$username} does not exist ! sessionID = {$session_id} amount = {$amount} transactionType = {$transaction_type_name} user = {$username} game_id = {$game_id}";
				$errorHelper->serviceError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_NO_PLAYER);
			}
			//ako je vec uradjen prenos u Betting pa se pokusa ponovo prenos kredita u Betting dobija se
			//raise_application_error(-20723, 'Transaction to Betting already opened!');
			if($code == "20723"){
				$mail_message = "BettingIntegrationModel::postTransaction - Betting_INTEGRATION.post_transaction_ws - ORA 20723 Transaction is already opened for player! <br /> sessionID = {$session_id} <br /> amount = {$amount} <br /> transactionType = {$transaction_type_name} <br /> user = {$username} <br /> game_id = {$game_id}";
				$log_message = "BettingIntegrationModel::postTransaction - Betting_INTEGRATION.post_transaction_ws - ORA 20723 Transaction is already opened for player! sessionID = {$session_id} amount = {$amount} transactionType = {$transaction_type_name} user = {$username} game_id = {$game_id}";
				$errorHelper->serviceError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_TRANSACTION_OPEN);
			}
			//Ako neko pokusa da vrati kredite iz Betting-a a nema zapisa u bazi da su prvo preneti krediti od nas u Betting dobija se
			//raise_application_error(-20724, 'No parent transaction to Betting found!');
			if($code == "20724"){
				$mail_message = "BettingIntegrationModel::postTransaction - Betting_INTEGRATION.post_transaction_ws - ORA 20724 No parent transaction to Betting found!! <br /> sessionID = {$session_id} <br /> amount = {$amount} <br /> transactionType = {$transaction_type_name} <br /> user = {$username} <br /> game_id = {$game_id}";
				$log_message = "BettingIntegrationModel::postTransaction - Betting_INTEGRATION.post_transaction_ws - ORA 20724 No parent transaction to Betting found! ! sessionID = {$session_id} amount = {$amount} transactionType = {$transaction_type_name} user = {$username} game_id = {$game_id}";
				$errorHelper->serviceError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_TRANSACTION_OPEN);
			}
			$mail_message = "BettingIntegrationModel::postTransaction - Betting_INTEGRATION.post_transaction_ws - ORA {$code} exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex) . "<br /> <br /> sessionID = {$session_id} <br /> amount = {$amount} <br /> transactionType = {$transaction_type_name} <br /> user = {$username} <br /> game_id = {$game_id}";
			$log_message = "BettingIntegrationModel::postTransaction - Betting_INTEGRATION.post_transaction_ws - ORA {$code} exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex) . " sessionID = {$session_id} amount = {$amount} transactionType = {$transaction_type_name} user = {$username} game_id = {$game_id}";
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_NO_PARENT_TRANSACTION);
		}
	}	
}