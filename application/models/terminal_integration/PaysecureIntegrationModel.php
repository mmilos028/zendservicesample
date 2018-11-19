<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class PaysecureIntegrationModel{

    /**
     * @param $web_domain_name
     * @param $voucher_code
     * @param $invoice_number
     * @param $amount
     * @param $currency
     * @param $ip_address
     * @param $transaction_sign
     * @return array
     * @throws Zend_Exception
     */
	public static function voucherActivationAuthorization($web_domain_name, $voucher_code, $invoice_number, $amount, $currency, $ip_address, $transaction_sign){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.voucher_activation_author(:p_web_domain_name, :p_voucher_code, :p_invoice_number,
			    :p_amount, :p_currency, :p_ip_address, :p_transaction_sign, :p_transaction_id_out)');
			$stmt->bindParam(':p_web_domain_name', $web_domain_name);
			$stmt->bindParam(':p_voucher_code', $voucher_code);
            $stmt->bindParam(':p_invoice_number', $invoice_number);
            $stmt->bindParam(':p_amount', $amount);
            $stmt->bindParam(':p_currency', $currency);
            $stmt->bindParam(':p_ip_address', $ip_address);
            $stmt->bindParam(':p_transaction_sign', $transaction_sign);
            $transaction_id = "";
            $stmt->bindParam(':p_transaction_id_out', $transaction_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "web_domain_name"=>$web_domain_name,
			    "voucher_code"=>$voucher_code, "invoice_number"=>$invoice_number, "amount"=>$amount, "currency"=>$currency,
                "ip_address"=>$ip_address, "transaction_sign"=>$transaction_sign,
                "transaction_id_out"=>$transaction_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$code = $ex->getCode();
            if($code == 20300){
                $message = "PaysecureIntegrationModel::voucherActivationAuthorization - Unhandled exception - CREDIT_TRANSFER.voucher_activation_author(:p_web_domain_name = {$web_domain_name},
                :p_voucher_code = {$voucher_code}, :p_invoice_number = {$invoice_number},
			    :p_amount = {$amount}, :p_currency = {$currency}, :p_ip_address = {$ip_address}, :p_transaction_sign = {$transaction_sign}, :p_transaction_id_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceErrorLog($message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION, "code"=>$code);
            }
            else if($code == 20301){
                $message = "PaysecureIntegrationModel::voucherActivationAuthorization - Invalid credentials - CREDIT_TRANSFER.voucher_activation_author(:p_web_domain_name = {$web_domain_name},
                :p_voucher_code = {$voucher_code}, :p_invoice_number = {$invoice_number},
			    :p_amount = {$amount}, :p_currency = {$currency}, :p_ip_address = {$ip_address}, :p_transaction_sign = {$transaction_sign}, :p_transaction_id_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceErrorLog($message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION, "code"=>$code);
            }
            else if($code == 20302){
                $message = "PaysecureIntegrationModel::voucherActivationAuthorization - Not enough credits for transaction - CREDIT_TRANSFER.voucher_activation_author(:p_web_domain_name = {$web_domain_name},
                :p_voucher_code = {$voucher_code}, :p_invoice_number = {$invoice_number},
			    :p_amount = {$amount}, :p_currency = {$currency}, :p_ip_address = {$ip_address}, :p_transaction_sign = {$transaction_sign}, :p_transaction_id_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceErrorLog($message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION, "code"=>$code);
            }
            else if($code == 20303){
                $message = "PaysecureIntegrationModel::voucherActivationAuthorization - Invalid currency - CREDIT_TRANSFER.voucher_activation_author(:p_web_domain_name = {$web_domain_name},
                :p_voucher_code = {$voucher_code}, :p_invoice_number = {$invoice_number},
			    :p_amount = {$amount}, :p_currency = {$currency}, :p_ip_address = {$ip_address}, :p_transaction_sign = {$transaction_sign}, :p_transaction_id_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceErrorLog($message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION, "code"=>$code);
            }
            else {
                $message = "PaysecureIntegrationModel::voucherActivationAuthorization - Unhandled exception - CREDIT_TRANSFER.voucher_activation_author(:p_web_domain_name = {$web_domain_name},
                :p_voucher_code = {$voucher_code}, :p_invoice_number = {$invoice_number},
			    :p_amount = {$amount}, :p_currency = {$currency}, :p_ip_address = {$ip_address}, :p_transaction_sign = {$transaction_sign}, :p_transaction_id_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceErrorLog($message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
		}
	}

    /**
     * @param $transaction_id
     * @param $transaction_status
     * @param $ip_address
     * @return array
     * @throws Zend_Exception
     */
    public static function voucherActivationCapture($transaction_id, $transaction_status, $ip_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.transaction_capture(:p_transaction_id, :p_ip_address, :p_transaction_status, :p_status_out)');
			$stmt->bindParam(':p_transaction_id', $transaction_id);
            $stmt->bindParam(':p_ip_address', $ip_address);
			$stmt->bindParam(':p_transaction_status', $transaction_status);
            $status_out = "";
            $stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "transaction_id"=>$transaction_id, "ip_address"=>$ip_address,
			    "transaction_status"=>$transaction_status, "status_out"=>$status_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$code = $ex->getCode();
			$message = "PaysecureIntegrationModel::voucherActivationCapture - CREDIT_TRANSFER.transaction_capture(:p_transaction_id = {$transaction_id}, :p_ip_address = {$ip_address},
            :p_transaction_status = {$transaction_status}, :p_status_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceErrorLog($message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
    }

    /**
     * @param $web_domain_name
     * @param $username
     * @param $password
     * @param $invoice_number
     * @param $amount
     * @param $currency
     * @param $ip_address
     * @param $transaction_sign
     * @return array
     * @throws Zend_Exception
     */
	public static function directTransactionAuthorization($web_domain_name, $username, $password, $invoice_number, $amount, $currency, $ip_address, $transaction_sign){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.direct_trans_authorization(:p_web_domain_name, :p_username, :p_password, :p_invoice_number,
			    :p_amount, :p_currency, :p_ip_address, :p_transaction_sign, :p_transaction_id_out)');
			$stmt->bindParam(':p_web_domain_name', $web_domain_name);
			$stmt->bindParam(':p_username', $username);
            $stmt->bindParam(':p_password', $password);
            $stmt->bindParam(':p_invoice_number', $invoice_number);
            $stmt->bindParam(':p_amount', $amount);
            $stmt->bindParam(':p_currency', $currency);
            $stmt->bindParam(':p_ip_address', $ip_address);
            $stmt->bindParam(':p_transaction_sign', $transaction_sign);
            $transaction_id = "";
            $stmt->bindParam(':p_transaction_id_out', $transaction_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "web_domain_name"=>$web_domain_name,
			    "username"=>$username, "invoice_number"=>$invoice_number, "amount"=>$amount, "currency"=>$currency,
                "ip_address"=>$ip_address, "transaction_sign"=>$transaction_sign,
                "transaction_id_out"=>$transaction_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$code = $ex->getCode();
            if($code == 20300){
                $message = "PaysecureIntegrationModel::directTransactionAuthorization - Unhandled exception - CREDIT_TRANSFER.direct_trans_authorization(:p_web_domain_name = {$web_domain_name},
                :p_username = {$username}, :p_password=, :p_invoice_number = {$invoice_number},
			    :p_amount = {$amount}, :p_currency = {$currency}, :p_ip_address = {$ip_address}, :p_transaction_sign = {$transaction_sign}, :p_transaction_id_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceErrorLog($message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION, "code"=>$code);
            }
            else if($code == 20301){
                $message = "PaysecureIntegrationModel::directTransactionAuthorization - Invalid credentials - CREDIT_TRANSFER.direct_trans_authorization(:p_web_domain_name = {$web_domain_name},
                :p_username = {$username}, :p_password= {$password}, :p_invoice_number = {$invoice_number},
			    :p_amount = {$amount}, :p_currency = {$currency}, :p_ip_address = {$ip_address}, :p_transaction_sign = {$transaction_sign}, :p_transaction_id_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceErrorLog($message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION, "code"=>$code);
            }
            else if($code == 20302){
                $message = "PaysecureIntegrationModel::directTransactionAuthorization - Not enough credits for transaction - CREDIT_TRANSFER.direct_trans_authorization(:p_web_domain_name = {$web_domain_name},
                :p_username = {$username}, :p_password=, :p_invoice_number = {$invoice_number},
			    :p_amount = {$amount}, :p_currency = {$currency}, :p_ip_address = {$ip_address}, :p_transaction_sign = {$transaction_sign}, :p_transaction_id_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceErrorLog($message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION, "code"=>$code);
            }
            else if($code == 20303){
                $message = "PaysecureIntegrationModel::directTransactionAuthorization - Invalid currency - CREDIT_TRANSFER.direct_trans_authorization(:p_web_domain_name = {$web_domain_name},
                :p_username = {$username}, :p_password=, :p_invoice_number = {$invoice_number},
			    :p_amount = {$amount}, :p_currency = {$currency}, :p_ip_address = {$ip_address}, :p_transaction_sign = {$transaction_sign}, :p_transaction_id_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceErrorLog($message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION, "code"=>$code);
            }
            else {
                $message = "PaysecureIntegrationModel::directTransactionAuthorization - Unhandled exception - CREDIT_TRANSFER.direct_trans_authorization(:p_web_domain_name = {$web_domain_name},
                :p_username = {$username}, :p_password=, :p_invoice_number = {$invoice_number},
			    :p_amount = {$amount}, :p_currency = {$currency}, :p_ip_address = {$ip_address}, :p_transaction_sign = {$transaction_sign}, :p_transaction_id_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceErrorLog($message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
		}
	}

    /**
     * @param $transaction_id
     * @param $transaction_status
     * @param $ip_address
     * @return array
     * @throws Zend_Exception
     */
    public static function directTransactionCapture($transaction_id, $transaction_status, $ip_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CREDIT_TRANSFER.transaction_capture(:p_transaction_id, :p_ip_address, :p_transaction_status, :p_status_out)');
			$stmt->bindParam(':p_transaction_id', $transaction_id);
            $stmt->bindParam(':p_ip_address', $ip_address);
			$stmt->bindParam(':p_transaction_status', $transaction_status);
            $status_out = "";
            $stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "transaction_id"=>$transaction_id, "ip_address"=>$ip_address,
			    "transaction_status"=>$transaction_status, "status_out"=>$status_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$code = $ex->getCode();
			$message = "PaysecureIntegrationModel::directTransactionCapture - CREDIT_TRANSFER.transaction_capture(:p_transaction_id = {$transaction_id}, :p_ip_address = {$ip_address},
            :p_transaction_status = {$transaction_status}, :p_status_out)
			    <br /> Exception error: <br /> Code: {$code} <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceErrorLog($message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
    }
}