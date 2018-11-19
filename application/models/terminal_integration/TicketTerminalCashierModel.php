<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class TicketTerminalCashierModel{
	public function __construct(){
	}
		
	//list categories for products from
	//terminal_session_id and affiliate_id
	public function listCategoriesForProducts($session_id, $affiliate_id){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL ticket_terminal_nv200.LIST_AFF_CATEGORIES(:p_session_id_in, :p_aff_id_in, :cur_result)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_aff_id_in', $affiliate_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':cur_result', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "TicketTerminalCashierModel::listCategoriesForProducts(session_id = {$session_id}, affiliate_id = {$affiliate_id}) <br /> ticket_terminal_nv200.LIST_AFF_CATEGORIES(:p_session_id_in = {$session_id}, :p_aff_id_in = {$affiliate_id}, :cur_result) <br /> Exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array("status"=>NOK, "cursor"=>array());
		}
	}

    //list products from affiliate
    //terminal_session_id and affiliate_id
    /*
    PROCEDURE list_products_per_affiliate(p_session_id_in csessions.ID%TYPE,
    p_aff_id_in     subjects.ID%TYPE,
    p_mac_address_in subjects.mac_address%TYPE,
    p_terminal_name_out OUT subjects.name%type,
    cur_result      OUT SYS_REFCURSOR)
    */
    public function listProductsPerAffiliate($session_id, $affiliate_id, $mac_address){
        /*
		$message = "ticket_terminal_nv200.list_products_per_affiliate(:p_session_id_in = {$session_id}, :p_aff_id_in = {$affiliate_id}, :cur_result)";
		$errorHelper = new ErrorHelper();
		$errorHelper->serviceError($message, $message);
        */
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL ticket_terminal_nv200.list_products_per_affiliate(:p_session_id_in, :p_aff_id_in, :p_mac_address_in, :p_terminal_name_out, :p_language_out, :cur_result_products, :cur_result_commercials, :cur_result_parameters)');
            $stmt->bindParam(':p_session_id_in', $session_id);
            $stmt->bindParam(':p_aff_id_in', $affiliate_id);
            $stmt->bindParam(':p_mac_address_in', $mac_address);
            $terminal_name = "";
            $stmt->bindParam(':p_terminal_name_out', $terminal_name, SQLT_CHR, 255);
            $language = "";
            $stmt->bindParam(':p_language_out', $language, SQLT_CHR, 255);
            $cursor_products = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':cur_result_products', $cursor_products);
            $cursor_commercials = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':cur_result_commercials', $cursor_commercials);
            $cursor_parameters = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':cur_result_parameters', $cursor_parameters);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor_products->execute();
            $cursor_products->free();
            $cursor_commercials->execute();
            $cursor_commercials->free();
            $cursor_parameters->execute();
            $cursor_parameters->free();
            $dbAdapter->closeConnection();
            return array("status"=>OK, "cursor_products"=>$cursor_products, "language"=>$language, "cursor_commercials"=>$cursor_commercials, "cursor_parameters"=>$cursor_parameters, "terminal_name"=>$terminal_name);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $errorHelper = new ErrorHelper();
            $message = "TicketTerminalCashierModel::listProductsPerAffiliate(session_id = {$session_id}, affiliate_id = {$affiliate_id}, mac_address = {$mac_address}) <br /> ticket_terminal_nv200.list_products_per_affiliate(p_session_id_in = {$session_id}, p_aff_id_in = {$affiliate_id}, p_mac_address_in = {$mac_address}, p_terminal_name_out, p_language_out, :cur_result_products, :cur_result_commercials, :cur_result_parameters) <br /> Exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status"=>NOK, "cursor"=>array());
        }
    }

    /*
        PROCEDURE parse_xml(p_xml_content_in     IN VARCHAR2,
       p_terminal_mac_in subjects.mac_address%TYPE,
       p_buyer_username_in VARCHAR2,
       p_buyer_id_out       OUT NUMBER,
       p_affiliate_id_out   OUT NUMBER,
       p_currency_out       OUT VARCHAR2,
       p_sum_amount_out     out NUMBER,
       p_status_out         out VARCHAR2,
       p_credits_out out NUMBER,
       p_terminal_name_out out VARCHAR2,
       p_transaction_date_out out TIMESTAMP,
       p_purchase_number_out OUT NUMBER,
       cur_result OUT SYS_REFCURSOR,
       p_error_message_desc OUT VARCHAR2,
       p_error_message_code OUT VARCHAR2)
    */
    public function setShopCart($xml_content, $terminal_mac, $buyer_username){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL ticket_terminal_nv200.parse_xml(:p_xml_content_in, :p_terminal_mac_in,
            :p_buyer_username_in, :p_buyer_id_out, :p_affiliate_id_out, :p_currency_out, :p_sum_amount_out, :p_status_out, :p_credits_out, :p_terminal_name_out, :p_transaction_date_out, :p_purchase_number_out, :cur_result, :p_error_message_desc, :p_error_message_code)');
            $stmt->bindParam(':p_xml_content_in', $xml_content);
            $stmt->bindParam(':p_terminal_mac_in', $terminal_mac);
            $stmt->bindParam(':p_buyer_username_in', $buyer_username);
            $buyer_id = "";
            $stmt->bindParam(':p_buyer_id_out', $buyer_id, SQLT_CHR, 255);
            $affiliate_id = "";
            $stmt->bindParam(':p_affiliate_id_out', $affiliate_id, SQLT_CHR, 255);
            $currency = "";
            $stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
            $sum_amount = "";
            $stmt->bindParam(':p_sum_amount_out', $sum_amount, SQLT_CHR, 255);
            $status = "";
            $stmt->bindParam(':p_status_out', $status, SQLT_CHR, 255);
            $credits = "";
            $stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
            $terminal_name = "";
            $stmt->bindParam(':p_terminal_name_out', $terminal_name, SQLT_CHR, 255);
            $transaction_date = "";
            $stmt->bindParam(':p_transaction_date_out', $transaction_date, SQLT_CHR, 255);
            $purchase_number = "";
            $stmt->bindParam(':p_purchase_number_out', $purchase_number, SQLT_CHR, 255);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':cur_result', $cursor);
            $error_message_desc = "";
            $stmt->bindParam(':p_error_message_desc', $error_message_desc, SQLT_CHR, 255);
            $error_message_code = "";
            $stmt->bindParam(':p_error_message_code', $error_message_code, SQLT_CHR, 255);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
			//DEBUG HERE
			/*
			$errorHelper = new ErrorHelper();
			$message = "TicketTerminalCashierModel::setShopCart(xml_content = {$xml_content}, terminal_mac = {$terminal_mac}, buyer_username = {$buyer_username}) <br /> ticket_terminal_nv200.parse_xml(:p_xml_content_in = {$xml_content}, :p_terminal_mac_in = {$terminal_mac},
            :p_buyer_username_in = {$buyer_username}, :p_buyer_id_out = {$buyer_id}, :p_affiliate_id_out = {$affiliate_id}, :p_currency_out = {$currency}, :p_sum_amount_out = {$sum_amount},
			:p_status_out = {$status}, :p_credits_out = {$credits}, :p_terminal_name_out = {$terminal_name}, :p_transaction_date_out = {$transaction_date}, :p_purchase_number_out = {$purchase_number},
			:cur_result, :p_error_message_desc = {$error_message_desc}, :p_error_message_code = {$error_message_code})";
			$errorHelper->serviceError($message, $message);
			*/
            return array("status"=>OK, "buyer_username"=>$buyer_username, "buyer_id"=>$buyer_id,
                "affiliate_id"=>$affiliate_id, "credits"=>$credits, "currency"=>$currency, "sum_amount"=>$sum_amount,
                "product_list"=>$cursor, "status_desc"=>$status, "terminal_name"=>$terminal_name, "transaction_date"=>$transaction_date,
                "purchase_number"=>$purchase_number, "error_message_desc"=>$error_message_desc, "error_message_code"=>$error_message_code);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "TicketTerminalCashierModel::setShopCart(xml_content = {$xml_content}, terminal_mac = {$terminal_mac}, buyer_username = {$buyer_username}) <br /> ticket_terminal_nv200.parse_xml(p_xml_content_in = {$xml_content}, p_terminal_mac_in = {$terminal_mac}, p_buyer_username_in = {$buyer_username} :p_product_id_out, :p_buyer_id_out, :p_affiliate_id_out, :p_price_out, :p_quantity_out,
            :p_currency_out, :p_sum_amount_out, :p_status_out, :p_error_message_desc, :p_error_message_code) <br /> Detected IP Address: {$detected_ip_address} <br /> Exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            $code = $ex->getCode();
            if($code == "20304" || $code == 20304){
                return array('status'=>NOK, 'message'=>"Buyer does not have enough credits for this purchase.", "error_code"=>$code);
            }else{
                return array('status'=>NOK, 'message'=>NOK_EXCEPTION);
            }
        }
    }

    /*
    PROCEDURE get_players_allowed_withdrawal(p_player_id_in       subjects.ID%TYPE,
    p_allowed_amount_out OUT subjects.credits%TYPE)
    */
     public function getPlayerAllowedWithdrawal($player_id){
         /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL ticket_terminal_nv200.get_players_allowed_withdrawal(:p_player_id_in, :p_allowed_amount_out)');
            $stmt->bindParam(':p_player_id_in', $player_id);
            $allowed_amount = "";
            $stmt->bindParam(':p_allowed_amount_out', $allowed_amount, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array('status'=>OK, 'player_id'=>$player_id, 'allowed_amount'=>$allowed_amount);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "TicketTerminalCashierModel::getPlayerAllowedWithdrawal(player_id = {$player_id}) <br /> ticket_terminal_nv200.get_players_allowed_withdrawal(p_player_id_in = {$player_id}, p_allowed_amount_out) <br /> Detected IP Address: {$detected_ip_address} <br /> Exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array('status'=>NOK, 'message'=>NOK_EXCEPTION);
        }
    }

    /*
    PROCEDURE get_aff_parameters(
    p_aff_id_in             subjects.id%TYPE,
    p_all_after_login_out   OUT tt_buttons_settings.all_after_login%TYPE,
    cur_list_aff_parameters OUT SYS_REFCURSOR);
    */
    public function getAffiliateParameters($affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL ticket_terminal_nv200.get_aff_parameters(:p_aff_id_in, :cur_list_aff_parameters)');
            $stmt->bindParam(':p_aff_id_in', $affiliate_id);
            $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindCursor(':cur_list_aff_parameters', $cursor);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursor->execute();
            $cursor->free();
            $dbAdapter->closeConnection();
            $arrData = CursorToArrayHelper::cursorToArray($cursor);
            return array("status"=>OK, "cursor_list_aff_parameters"=>$arrData);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "TicketTerminalCashierModel::getAffiliateParameters(affiliate_id={$affiliate_id}) <br /> ticket_terminal_nv200.get_aff_parameters(:p_aff_id_in = {$affiliate_id}, :cur_list_aff_parameters) <br /> Detected IP Address: {$detected_ip_address} <br /> Exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array('status'=>NOK, 'message'=>NOK_EXCEPTION);
        }
    }
}