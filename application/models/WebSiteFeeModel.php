<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class WebSiteFeeModel{
	public function __construct(){
	}	
		
	/* THIS PROCEDURE IS USED TO CHARGE FEE BEFORE PLAYER TRIES TO DEPOSIT MONEY, TO RETURN FINAL DEPOSIT AMOUNT
	procedura part1 sadrzi provere neophodne za odredjivanje iznosa takse koji ce se naplatiti uz ulozeni depozit
	Moguce poruke:
	Fee percent and Fee fix amount can not be defined both at the same time!;
	Fee percent and Fee fix amount are both empty! It must be defined eather Fee percent or Fee fix amount!;
	WL does not have active profile for Deposit Fee!;
	There are more then one Deposit Fee profiles for WL for this currency!;
	Player is free of fee payment! Deposit is grather than || V_NO_FEE_MIN_DEPOSIT;
	Unhandled exception! ||sqlerrm(sqlcode); --za eventualno nepredvidjene greske
	OK; --AKO NEMA GRESAKA
	NOVO:
	A lista gresaka ovako:
      EXCEPTION 
      WHEN TOO_MANY_PROFILES
      THEN
              RAISE_APPLICATION_ERROR(-20300,'There are more then one Deposit Fee profiles for WL for this currency!');
      WHEN FEE_PERCENT_AND_AMOUNT_DEFINED
      THEN
             RAISE_APPLICATION_ERROR(-20301,'Fee percent and Fee fix amount can not be defined both at the same time!');
      WHEN FEE_PERCENT_AND_AMOUNT_EMPTY
      THEN
             RAISE_APPLICATION_ERROR (-20302, 'Fee percent and Fee fix amount are both empty! It must be defined eather Fee percent or Fee fix amount!');    
      WHEN FREE_OF_PAYMENT
      THEN
             RAISE_APPLICATION_ERROR (-20303,'Player is free of fee payment! Deposit is grather than '|| V_NO_FEE_MIN_DEPOSIT);
     WHEN NOT_ACTIVE_DEPOSIT_FEE
     THEN
              RAISE_APPLICATION_ERROR (-20304, 'WL does not have active profile for Deposit Fee!');
     WHEN OTHERS
     THEN 
              RAISE_APPLICATION_ERROR (-20305, 'Unhandled exception!');
	*/
    /**
     * @param $player_id
     * @param $deposit_amount
     * @param $currency
     * @param $payment_method_id
     * @return array
     * @throws Zend_Exception
     */
	public function depositFeePart1($player_id, $deposit_amount, $currency, $payment_method_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL FEE_SYSTEM.DEPOSIT_FEE_PART1(:p_player_id_in, :p_payment_method_id_in, :p_deposit_amount_in, :p_currency_in, :p_deposit_amount_out, :p_fee_value_out)');
			$stmt->bindParam(':p_player_id_in', $player_id);
            $stmt->bindParam(':p_payment_method_id_in', $payment_method_id);
			$stmt->bindParam(':p_deposit_amount_in', $deposit_amount);
			$stmt->bindParam(':p_currency_in', $currency);
			$deposit_amount_out = "";
			$stmt->bindParam(':p_deposit_amount_out', $deposit_amount_out, SQLT_CHR, 255);
			$fee_value_out = "";
			$stmt->bindParam(':p_fee_value_out', $fee_value_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "player_id"=>$player_id, "payment_method_id"=>$payment_method_id,
                "deposit_amount"=>$deposit_amount, "currency"=>$currency, "deposit_amount_out"=>$deposit_amount_out, "fee_value_out"=>$fee_value_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			$code = $ex->getCode();
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "player_id"=>$player_id, "payment_method_id"=>$payment_method_id,
                "deposit_amount"=>$deposit_amount, "currency"=>$currency, "deposit_amount_out"=>$deposit_amount, "fee_value_out"=>0,
                "error_code"=>$code, "error_message"=>$ex->getMessage());
		}		
	}
	
	/* THIS PROCEDURE IS USED WHEN PLAYER DEPOSITED TO CHARGE FEE
	  Spisak gresaka:
		EXCEPTION
		WHEN EXP_NOT_ENOUGH_FOUNDS
		THEN
			RAISE_APPLICATION_ERROR (-20306, 'There are not enough funds on account to charge Deposit Fee.');
		WHEN OTHERS THEN
			RAISE_APPLICATION_ERROR (-20307, 'Unhandled exception!');
	*/
    /**
     * @param $session_id
     * @param $player_id
     * @param $currency
     * @param $fee_value
     * @param $deposit_amount
     * @return array
     * @throws Zend_Exception
     */
	public function depositFeePart2($session_id, $player_id, $currency, $fee_value, $deposit_amount){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL FEE_SYSTEM.DEPOSIT_FEE_PART2(:p_session_id_in, :p_currency_in, :p_subject_id_in, :p_fee_value_in, :p_deposit_amount_in, :p_transaction_id_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_currency_in', $currency);
			$stmt->bindParam(':p_subject_id_in', $player_id);
			$stmt->bindParam(':p_fee_value_in', $fee_value);
			$stmt->bindParam(':p_deposit_amount_in', $deposit_amount);			
			$transaction_id_out = "";
			$stmt->bindParam(':p_transaction_id_out', $transaction_id_out, SQLT_CHR, 255);			
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "session_id"=>$session_id, "player_id"=>$player_id, "deposit_amount"=>$deposit_amount, "currency"=>$currency, "fee_value"=>$fee_value, "transaction_id_out"=>$transaction_id_out); 
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->merchantError($message, $message);
			$code = $ex->getCode();			
			if($code == 20306){
			//RAISE_APPLICATION_ERROR (-20306, 'There are not enough funds on account to charge Deposit Fee.');
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "session_id"=>$session_id, "player_id"=>$player_id, "deposit_amount"=>$deposit_amount, "currency"=>$currency, "fee_value"=>$fee_value, "error_code"=>$code, "error_message"=>'There are not enough funds on account to charge Deposit Fee.');
			}else{
				//RAISE_APPLICATION_ERROR (-20307, 'Unhandled exception!');
				return array("status"=>NOK, "message"=>NOK_EXCEPTION, "session_id"=>$session_id, "player_id"=>$player_id, "deposit_amount"=>$deposit_amount, "currency"=>$currency, "fee_value"=>$fee_value, "error_code"=>$code, "error_message"=>$ex->getMessage());
			}
		}
	}
	
	/* THIS PROCEDURE IS USED WITH PLAYER PAYOUT (WITHDRAW) TO CHARGE FEE BEFORE WITHDRAW
	Spisak predefinisanih gresaka je:
			EXCEPTION 
		  WHEN NOT_ACTIVE_DEPOSIT_FEE
		 THEN
				RAISE_APPLICATION_ERROR (-20308, 'WL does not have active profile for Withdraw Fee!');		  
		  WHEN FEE_PERCENT_AND_AMOUNT_DEFINED
		  THEN
				RAISE_APPLICATION_ERROR (-20309, 'Fee percent and Fee fix amount can not be defined both at the same time!');					
		  WHEN FEE_PERCENT_AND_AMOUNT_EMPTY
		  THEN
				RAISE_APPLICATION_ERROR (-20310, 'Fee percent and Fee fix amount are both empty! It must be defined eather Fee percent or Fee fix amount!');					
		  WHEN EXP_NOT_ENOUGH_FUNDS
		  THEN
				RAISE_APPLICATION_ERROR (-20311, 'There are not enough funds on account to charge Withdraw and Withdraw Fee.');
		  WHEN NO_FUNDS_FOR_FEE
		  THEN
				RAISE_APPLICATION_ERROR (-20312, 'There are not enough funds on account to charge Withdraw Fee.');
		  WHEN TOO_MANY_PROFILES
		  THEN 
				RAISE_APPLICATION_ERROR (-20313, 'There are more then one Withdraw Fee profiles for WL for this currency!');	  
		 WHEN OTHERS
		 THEN 
				RAISE_APPLICATION_ERROR (-20314, 'Unhandled exception!');
	*/
    /**
     * @param $session_id
     * @param $player_id
     * @param $withdraw_amount
     * @param $currency
     * @return array
     * @throws Zend_Exception
     */
	public function withdrawFee($session_id, $player_id, $withdraw_amount, $currency){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL FEE_SYSTEM.WITHDRAW_FEE(:p_session_id_in, :p_player_id_in, :p_withdraw_amount_in, :p_currency_in, :p_withdraw_amount_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_withdraw_amount_in', $withdraw_amount);
			$stmt->bindParam(':p_currency_in', $currency);
			$withdraw_amount_out = "";
			$stmt->bindParam(':p_withdraw_amount_out', $withdraw_amount_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();			
			//DEBUG THIS PART OF CODE - CREATE PLAYER VIA WEB SERVICE
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("FEE_SYSTEM.WITHDRAW_FEE(:p_session_id_in = {$session_id}, :p_player_id_in = {$player_id}, :p_withdraw_amount_in = {$withdraw_amount}, :p_currency_in = {$currency}, :p_withdraw_amount_out = {$withdraw_amount_out}))");			
			return array("status"=>OK, "session_id"=>$session_id, "player_id"=>$player_id, "withdraw_amount"=>$withdraw_amount, "currency"=>$currency, 
			"withdraw_amount_out"=>$withdraw_amount_out); 
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();			
			$code = $ex->getCode();
			switch($code){
				case 20308: //this message is cancelled in database
					$message = 'WL does not have active profile for Withdraw Fee!';
				break;
				case 20309:
					$message = 'Fee percent and Fee fix amount can not be defined both at the same time!';
				break;
				case 20310:
					$message = 'Fee percent and Fee fix amount are both empty! It must be defined eather Fee percent or Fee fix amount!';
				break;
				case 20311:
					$message = 'There are not enough funds on account to charge Withdraw and Withdraw Fee.';
				break;
				case 20312:
					$message = 'There are not enough funds on account to charge Withdraw Fee.';
				break;
				case 20313:
					$message = 'There are more then one Withdraw Fee profiles for WL for this currency!';
				break;
				case 20314:
					$message = 'Unhandled exception!';
				break;
				default:
					$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			}
			$errorHelper = new ErrorHelper();			
			$errorHelper->merchantError(CursorToArrayHelper::getExceptionTraceAsString($ex), CursorToArrayHelper::getExceptionTraceAsString($ex));
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "session_id"=>$session_id, "player_id"=>$player_id, "withdraw_amount"=>$withdraw_amount, "currency"=>$currency, "error_code"=>$code, "error_message"=>$message);
		}
	}
	/*
	Razbijena je Withdraw procedura na dve po dogovoru sa Ljubom, radi po principu kao deposit u dva dela. Poziva se Part1 pa Withdraw pa Part2
	Stuktura
	--prvi deo procedure odradi samo logiku koliko ce iznositi taksa
      EXCEPTION 
      WHEN FEE_PERCENT_AND_AMOUNT_DEFINED --definisana oba i procenat i iznos, greska
      THEN
                RAISE_APPLICATION_ERROR (-20309, 'Fee percent and Fee fix amount can not be defined both at the same time!');                 
      WHEN FEE_PERCENT_AND_AMOUNT_EMPTY --nije definisano nijedno ni procenat ni iznos, greska
      THEN
                RAISE_APPLICATION_ERROR (-20310, 'Fee percent and Fee fix amount are both empty! It must be defined eather Fee percent or Fee fix amount!');        
      WHEN TOO_MANY_PROFILES --previse profila sa tom valutom za taj tip takse za tog wl
      THEN 
                RAISE_APPLICATION_ERROR (-20313, 'There are more then one Withdraw Fee profiles for WL for this currency!');  
     WHEN OTHERS --druge nedefinisane greske
     THEN 
                RAISE_APPLICATION_ERROR (-20314, 'Unhandled exception!');
	*/
    //create_transaction - 0 with transaction, 1 - only checking and no transaction
    /**
     * @param $pc_session_id
     * @param $player_id
     * @param $payment_method_id
     * @param $withdraw_amount
     * @param $currency
     * @param int $create_transaction
     * @return array
     * @throws Zend_Exception
     */
	public function withdrawFeePart1($pc_session_id, $player_id, $payment_method_id, $withdraw_amount, $currency, $create_transaction = 0){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL FEE_SYSTEM.WITHDRAW_FEE_PART1(:p_session_id_in, :p_player_id_in, :p_payment_method_id_in, :p_withdraw_amount_in, :p_currency_in, :p_is_checking_in, :p_withdraw_amount_out, :p_fee_value_out, :p_fee_transaction_id_out)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
            $stmt->bindParam(':p_payment_method_id_in', $payment_method_id);
			$stmt->bindParam(':p_withdraw_amount_in', $withdraw_amount);
			$stmt->bindParam(':p_currency_in', $currency);
            $stmt->bindParam(':p_is_checking_in', $create_transaction);
			$withdraw_amount_out = "";
			$stmt->bindParam(':p_withdraw_amount_out', $withdraw_amount_out, SQLT_CHR, 255);
			$fee_amount_out = "";
			$stmt->bindParam(':p_fee_value_out', $fee_amount_out, SQLT_CHR, 255);
            $fee_transaction_id = "";
            $stmt->bindParam(':p_fee_transaction_id_out', $fee_transaction_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG THIS PART OF CODE - CREATE PLAYER VIA WEB SERVICE
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("FEE_SYSTEM.WITHDRAW_FEE_PART1(:p_session_id_in = {$pc_session_id}, :p_player_id_in = {$player_id}, :p_withdraw_amount_in = {$withdraw_amount}, :p_currency_in = {$currency}, :p_is_checking_in = {$create_transaction}, :p_withdraw_amount_out = {$withdraw_amount_out}, :p_fee_value_out = {$fee_amount_out})");
			return array("status"=>OK, "session_id"=>$pc_session_id, "player_id"=>$player_id, "payment_method_id"=>$payment_method_id, "withdraw_amount"=>$withdraw_amount, "currency"=>$currency,
			"withdraw_amount_out"=>$withdraw_amount_out, "fee_amount_out"=>$fee_amount_out, "fee_transaction_id"=>$fee_transaction_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			switch($code){
				case 20309:
					$message = 'Fee percent and Fee fix amount can not be defined both at the same time!';
				break;
				case 20310:
					$message = 'Fee percent and Fee fix amount are both empty! It must be defined eather Fee percent or Fee fix amount!';
				break;
                case 20311:
                    $message = 'There are not enough funds on account to charge Withdraw and Withdraw Fee.';
                break;
                case 20312:
                    $message = 'There are not enough funds on account to charge Withdraw Fee.';
                    break;
				case 20313:
					$message = 'There are more then one Withdraw Fee profiles for WL for this currency!';
				break;
				case 20314:
					$message = 'Unhandled exception!';
				break;
				default:
					$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			}
			$errorHelper = new ErrorHelper();
			$errorHelper->merchantError(CursorToArrayHelper::getExceptionTraceAsString($ex), CursorToArrayHelper::getExceptionTraceAsString($ex));
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "session_id"=>$pc_session_id, "player_id"=>$player_id, "payment_method_id"=>$payment_method_id, "withdraw_amount"=>$withdraw_amount, "currency"=>$currency, "error_code"=>$code, "error_message"=>$message);
		}	
	}
	
	/*drugi deo procedure radi transakciju internu za prikaz iznosa takse
	--vraca Pass/Fail sa raise greske
	EXCEPTION
	WHEN EXP_NOT_ENOUGH_FUNDS --nema dovoljno ni za isplatu a ni za fee
	THEN
	p_creation_status_out := 'Fail';
	RAISE_APPLICATION_ERROR (-20311, 'There are not enough funds on account to charge Withdraw and Withdraw Fee.');
	WHEN EXP_NO_FUNDS_FOR_FEE --nema dovoljno i za fee
	THEN
	p_creation_status_out := 'Fail';
	RAISE_APPLICATION_ERROR (-20312, 'There are not enough funds on account to charge Withdraw Fee.');	  
	WHEN OTHERS --druge neobradjene poruke
	THEN 
	p_creation_status_out := 'Fail';
	RAISE_APPLICATION_ERROR (-20314, 'Unhandled exception!');
	*/
    /**
     * @param $backoffice_session_id
     * @param $player_id
     * @param $transaction_id
     * @return array
     * @throws Zend_Exception
     */
	public function withdrawFeePart2($backoffice_session_id, $player_id, $transaction_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL FEE_SYSTEM.WITHDRAW_FEE_PART2(:p_session_id_in, :p_currency_in, :p_subject_id_in, :p_transaction_id_in, :p_withdraw_amount_in, :p_fee_value_out, :p_creation_status_out)');
            $stmt = $dbAdapter->prepare('CALL FEE_SYSTEM.WITHDRAW_FEE_PART2(:p_session_id_in, :p_subject_id_in, :p_transaction_id_in, :p_fee_value_out, :p_creation_status_out)');
			$stmt->bindParam(':p_session_id_in', $backoffice_session_id);
			$stmt->bindParam(':p_subject_id_in', $player_id);
			$stmt->bindParam(':p_transaction_id_in', $transaction_id);
			$fee_amount = "";
			$stmt->bindParam(':p_fee_value_out', $fee_amount, SQLT_CHR, 255);
			$creation_status = "";
			$stmt->bindParam(':p_creation_status_out', $creation_status, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG THIS PART OF CODE - CREATE PLAYER VIA WEB SERVICE
			//$errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("FEE_SYSTEM.WITHDRAW_FEE_PART2(:p_subject_id_in = {$pc_session_id}, :p_transaction_id_in = {$transaction_id}, :p_fee_value_out = {$fee_amount}, :p_creation_status_out = {$creation_status})");
			return array("status"=>OK, "session_id"=>$backoffice_session_id, "player_id"=>$player_id, "fee_amount"=>$fee_amount);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			switch($code){
				case 20311:
					$message = 'There are not enough funds on account to charge Withdraw and Withdraw Fee.';
				break;
				case 20312:
					$message = 'There are not enough funds on account to charge Withdraw Fee.';
				break;
				case 20314:
					$message = 'Unhandled exception!';
				break;
				default:
					$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			}
			$errorHelper = new ErrorHelper();
			$errorHelper->merchantError(CursorToArrayHelper::getExceptionTraceAsString($ex), CursorToArrayHelper::getExceptionTraceAsString($ex));
			return array("status"=>NOK, "message"=>NOK_EXCEPTION, "session_id"=>$backoffice_session_id, "player_id"=>$player_id, "error_code"=>$code, "error_message"=>$message);
		}
	}
}