<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class WalletModel{

    private $DEBUG = false;

    /**
     * @param $username
     * @param $password
     * @param $player_id
     * @return array
     * @throws Zend_Exception
     */
	public function getCustomerInfo($username, $password, $player_id){
        if($this->DEBUG) {
            //DEBUG
            $message = "CS_USER_UTILITY.get_customer_info_new(:p_username_in = {$username}, :p_password_in = {$password}, :p_player_id_in = {$player_id}, :p_credits_out)";
            $errorHelper = new ErrorHelper();
            $errorHelper->ldcIntegrationAccess($message, $message);
        }
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.get_customer_info(:p_username_in, :p_password_in, :p_player_id_in, :p_credits_out)');
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.get_customer_info_new(:p_username_in, :p_password_in, :p_player_id_in, :p_credits_out)');
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$credits = "0.00";
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "username"=>$username, "password"=>$password, 
			"player_id"=>$player_id, "credits"=>$credits);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$code = $ex->getCode();
			//RAISE_APPLICATION_ERROR (-20121, 'Wrong username or password !!!!');
			if($code == "20121"){
				$mail_message = "WalletModel::getCustomerInfo - CS_USER_UTILITY.get_customer_info_new - Player: {$player_id} for user: {$username} <br /> Wrong username or password !";
				$log_message = "WalletModel::getCustomerInfo - CS_USER_UTILITY.get_customer_info_new - Player: {$player_id} for user: {$username} Wrong username or password !";
				$errorHelper->ldcIntegrationError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_NO_USERNAME);
			}
			//RAISE_APPLICATION_ERROR (-20720, 'Provided player does not exist.');
			if($code == "20720"){
				$mail_message = "WalletModel::getCustomerInfo - CS_USER_UTILITY.get_customer_info_new - Player with player_id: {$player_id} for user: {$username} does not exist !";
				$log_message = "WalletModel::getCustomerInfo - CS_USER_UTILITY.get_customer_info_new - Player with player_id: {$player_id} for user: {$username} does not exist !";
				$errorHelper->ldcIntegrationError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_NO_PLAYER);
			}
			$mail_message = "WalletModel::getCustomerInfo - CS_USER_UTILITY.get_customer_info_new for player with player_id: {$player_id} and user: {$username} - exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WalletModel::getCustomerInfo - CS_USER_UTILITY.get_customer_info_new for player with player_id: {$player_id} and user: {$username} - exception error: Code: {$code} " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->ldcIntegrationError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * PROCEDURE post_transaction_ws(-- intended for our web service exposed to GGL
     p_username_in              IN subjects.name%type,
     p_password_in              IN subjects.password%type, -- md5 hash  given here
     p_player_id_in             IN subjects.id%type,
     p_session_id_in            IN csessions.id%type,
     p_amount_in                IN transactions.amount%type,
     p_transaction_type_name_in IN transaction_types.name%type,
     p_transaction_id_out OUT transactions.id%type)
		Ovde treba koristiti GGL credentials:
	P_USERNAME_IN := 'GGL_Admin_WS';
	P_PASSWORD_IN := 'ad9032ce2e22e6d8798f58c8269662cb';
	p_session_id_in = session id od site sesije
	Ukoliko GGL posalje pogresne credentials dobija se RAISE_APPLICATION_ERROR (-20121, 'Wrong username or password !!!!');
	Ukoliko posalju player_id koji ne postoji dobija se RAISE_APPLICATION_ERROR (-20720, 'Provided player does not exist.');
	Ukoliko se pare uzimaju od playera i prenose u GGL onda treba staviti 
		da p_transaction_type_in managment_types.NAME_IN_CREDITS_TO_GGL   (a to ima vrednost  'CREDITS_TO_GGL');
	Ukoliko se pare uzimaju od GGL-a i vracaju playeru treba staviti:
		managment_types.NAME_IN_CREDITS_FROM_GGL    (a to ima vrednost 'CREDITS_FROM_GGL');
	Ne bi bilo lose da dodas web servis za ovu storku pa da malo probamo da zovemo i vidimo da li se dobro krediti prebacuju.
	Na primer, da prvo pozoves get_customer_info pa onda povuces sve te pare kao ka GGL-u pa da vidimo da li je stanje 0 u bazi, pa onda vratis sve pare (ili deo) pa da vidimo da li je dodato na kredit tom playeru u bazi...
	 */
    /**
     * @param $username
     * @param $password
     * @param $session_id
     * @param $amount
     * @param $transaction_type_name
     * @param null $game_id
     * @return array
     * @throws Zend_Exception
     */
	public function postTransaction($username, $password, 
	$session_id, $amount, $transaction_type_name, $game_id = null){
        if($this->DEBUG) {
            //DEBUG
            $message = "GGL_INTEGRATION.post_transaction_ws_new(:p_username_in = {$username}, :p_password_in = {$password}, :p_session_id_in = {$session_id}, :p_amount_in = {$amount},
        :p_live_casino_game_id = null, :p_transaction_type_name_in = {$transaction_type_name}, :p_transaction_id_out)";
            $errorHelper = new ErrorHelper();
            $errorHelper->ldcIntegrationAccess($message, $message);
        }
		$game_id = null;
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL GGL_INTEGRATION.post_transaction_ws_new(:p_username_in, :p_password_in, :p_session_id_in, :p_amount_in, :p_transaction_type_name_in, :p_transaction_id_out)');
			$stmt = $dbAdapter->prepare('CALL GGL_INTEGRATION.post_transaction_ws_new(:p_username_in, :p_password_in, :p_session_id_in, :p_amount_in, :p_live_casino_game_id, :p_transaction_type_name_in, :p_transaction_id_out)');
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_session_id_in', $session_id);			
			$stmt->bindParam(':p_amount_in', $amount);
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
				$mail_message = "WalletModel::postTransaction - GGL_INTEGRATION.post_transaction_ws_new - ORA 20121 Player {$username} <br /> Wrong username or password ! <br /> sessionID = {$session_id} <br /> <br /> amount = {$amount} <br /> transactionType = {$transaction_type_name} <br /> user = {$username}";
				$log_message = "WalletModel::postTransaction - GGL_INTEGRATION.post_transaction_ws_new - ORA 20121 Player {$username} Wrong username or password ! sessionID = {$session_id} amount = {$amount} transactionType = {$transaction_type_name} user = {$username}";
				$errorHelper->ldcIntegrationError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_NO_USERNAME);
			}
			//RAISE_APPLICATION_ERROR (-20720, 'Provided player does not exist.');
			if($code == "20720"){
				$mail_message = "WalletModel::postTransaction - GGL_INTEGRATION.post_transaction_ws_new - ORA 20720 Player with username {$username} does not exist ! <br /> sessionID = {$session_id} <br /> <br /> amount = {$amount} <br /> transactionType = {$transaction_type_name} <br /> user = {$username}";
				$log_message = "WalletModel::postTransaction - GGL_INTEGRATION.post_transaction_ws_new - ORA 20720 Player with username {$username} does not exist ! sessionID = {$session_id} amount = {$amount} transactionType = {$transaction_type_name} user = {$username}";
				$errorHelper->ldcIntegrationError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_NO_PLAYER);
			}
			//ako je vec uradjen prenos u GGL pa se pokusa ponovo prenos kredita u GGL dobija se
			//raise_application_error(-20723, 'Transaction to GGL already opened!');
			if($code == "20723"){
				$mail_message = "WalletModel::postTransaction - GGL_INTEGRATION.post_transaction_ws_new - ORA 20723 Transaction is already opened for player! <br /> sessionID = {$session_id} <br /> <br /> amount = {$amount} <br /> transactionType = {$transaction_type_name} <br /> user = {$username}";
				$log_message = "WalletModel::postTransaction - GGL_INTEGRATION.post_transaction_ws_new - ORA 20723 Player with username {$username} does not exist ! sessionID = {$session_id} amount = {$amount} transactionType = {$transaction_type_name} user = {$username}";
				$errorHelper->ldcIntegrationError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_TRANSACTION_OPEN);
			}
			//Ako neko pokusa da vrati kredite iz GGL-a a nema zapisa u bazi da su prvo preneti krediti od nas u GGL dobija se
			//raise_application_error(-20724, 'No parent transaction to GGL found!');
			if($code == "20724"){
				$mail_message = "WalletModel::postTransaction - GGL_INTEGRATION.post_transaction_ws_new - ORA 20724 No parent transaction to GGL found!! <br /> sessionID = {$session_id} <br /> amount = {$amount} <br /> transactionType = {$transaction_type_name} <br /> user = {$username}";
				$log_message = "WalletModel::postTransaction - GGL_INTEGRATION.post_transaction_ws_new - ORA 20724 No parent transaction to GGL found! ! sessionID = {$session_id} amount = {$amount} transactionType = {$transaction_type_name} user = {$username}";
				$errorHelper->ldcIntegrationError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_TRANSACTION_OPEN);
			}
			$mail_message = "WalletModel::postTransaction - GGL_INTEGRATION.post_transaction_ws_new - ORA {$code} exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex) . "<br /> <br /> sessionID = {$session_id} <br /> amount = {$amount} <br /> transactionType = {$transaction_type_name} <br /> user = {$username}";
			$log_message = "WalletModel::postTransaction - GGL_INTEGRATION.post_transaction_ws_new - ORA {$code} exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex) . " sessionID = {$session_id} amount = {$amount} transactionType = {$transaction_type_name} user = {$username}";
			$errorHelper->ldcIntegrationError($mail_message, $log_message);
			return array("status"=>NOK, "message"=>NOK_NO_PARENT_TRANSACTION);
		}
	}	
}