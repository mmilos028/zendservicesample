<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
class ShopIntegrationModel{
	public function __construct(){
	}

	/**
        Performes opening shop integration session by pc session id when player clicks on SHOP Integration Button
     *
    */
    public function openShopIntegrationSession($pc_session_id, $ip_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL SHOP_INTEGRATION.OPEN_SHOP_INTEGRATION_SESSION(:p_session_id_in, :p_ip_address_in, :p_session_id_out)');
            $stmt->bindParam(':p_session_id_in', $pc_session_id);
            $stmt->bindParam(':p_ip_address_in', $ip_address);
            $session_id_out="";
            $stmt->bindParam(':p_session_id_out', $session_id_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            /*
            $errorHelper = new ErrorHelper();
            $message = "SHOP INTEGRATION Error <br /> SHOP_INTEGRATION.OPEN_SHOP_INTEGRATION_SESSION(p_session_id_in = {$pc_session_id}, p_ip_address_in = {$ip_address}, p_session_id_out = {$session_id_out}) <br />";
            $errorHelper->shopIntegrationError($message, $message);
            */
            return array("status"=>OK, "pc_session_id"=>$pc_session_id, "session_id_out"=>$session_id_out);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SHOP INTEGRATION Error <br /> SHOP_INTEGRATION.OPEN_SHOP_INTEGRATION_SESSION(p_session_id_in = {$pc_session_id}, p_ip_address_in = {$ip_address}, p_session_id_out =) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
        Close Shop Integration Game Window
     *
    */
    /**
     * @param $session_id
     * @return array
     */
    public function closeShopIntegrationWindow($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL SHOP_INTEGRATION.close_shop_session(:p_session_id_in, :p_status)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $status = "";
            $stmt->bindParam(':p_status', $status, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "session_id"=>$session_id, "status_out"=>$status);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SHOP INTEGRATION Error <br /> SHOP_INTEGRATION.close_shop_session(p_session_id_in = {$session_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
     * @param $subject_id
     * @return array
     */
    public static function getShopIntegrationBalance($subject_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL SHOP_INTEGRATION.get_balance(:p_subject_id, :p_credits_out)');
            $stmt->bindParam(':p_subject_id', $subject_id);
            $credits = "";
            $stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "subject_id"=>$subject_id, "credits"=>NumberHelper::convert_double($credits), "credits_formatted"=>NumberHelper::format_double($credits));
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SHOP INTEGRATION Error <br /> SHOP_INTEGRATION.get_balance(p_subject_id = {$subject_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
     * @param $user_id
     * @param $amount
     * @param $transaction_sign
     * @param $session_id
     * @param $shop_transaction_id
     * @return array
     */
    public static function changeShopIntegrationBalance($user_id, $amount, $transaction_sign, $session_id, $shop_transaction_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL SHOP_INTEGRATION.change_balance(:p_shop_transaction_id, :p_user_id, :p_amount, :p_transaction_sign, :p_session_id, :p_balance_out, :p_transaction_id_out)');
            $stmt->bindParam(':p_shop_transaction_id', $shop_transaction_id);
            $stmt->bindParam(':p_user_id', $user_id);
            $stmt->bindParam(':p_amount', $amount);
            $stmt->bindParam(':p_transaction_sign', $transaction_sign);
            $stmt->bindParam(':p_session_id', $session_id);
            $balance = "";
            $stmt->bindParam(':p_balance_out', $balance, SQLT_CHR, 255);
            $transaction_id_out = "";
            $stmt->bindParam(':p_transaction_id_out', $transaction_id_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array(
                "status"=>OK, "user_id"=>$user_id, "amount"=>$amount,
                "transaction_sign"=>$transaction_sign, "shop_transaction_id" => $shop_transaction_id,
                "balance"=>NumberHelper::convert_double($balance),
                "balance_formatted"=>NumberHelper::format_double($balance), "transaction_id"=>$transaction_id_out
            );
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SHOP INTEGRATION Error <br /> SHOP_INTEGRATION.get_balance(p_shop_transaction_id = {$shop_transaction_id}, p_user_id = {$user_id}, p_amount = {$amount}, p_transaction_sign = {$transaction_sign},
            p_session_id = {$session_id}, p_balance_out = {$balance}, p_transaction_id_out =) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);

            $code = $ex->getCode();
            if($code == "20302"){
                return array("status" => NOK, "message" => "INSUFFICIENT_CREDITS");
            }else {
                //20301 or other unknown
                return array("status" => NOK, "message" => NOK_EXCEPTION);
            }
        }
    }

    /**
     * @param $session_id
     * @return array
     */
    public static function getUserDetails($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL SHOP_INTEGRATION.get_subject_details(:p_session_id, :cur_result_out)');
            $stmt->bindParam(':p_session_id', $session_id);

            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':cur_result_out', $cursor);

            $stmt->execute(null, false);
            $dbAdapter->commit();

            $cursor->execute();
            $cursor->free();

            $dbAdapter->closeConnection();

            return array(
                "status"=>OK, "cursor" => $cursor
            );
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SHOP INTEGRATION Error <br /> SHOP_INTEGRATION.get_subject_details(p_session_id = {$session_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

    /**
     * @param $shop_transaction_id
     * @return array
     */
    public static function checkTransactionStatus($shop_transaction_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL SHOP_INTEGRATION.check_transaction_existence(:p_transaction_id, :p_status_out)');
            $stmt->bindParam(':p_transaction_id', $shop_transaction_id);
            $status_out = "";
            $stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "shop_transaction_id"=>$shop_transaction_id, "status_out"=>$status_out);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "SHOP INTEGRATION Error <br /> SHOP_INTEGRATION.check_transaction_existence(p_transaction_id = {$shop_transaction_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }
}