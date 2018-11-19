<?php
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';
/**
 * 
 * Ticket terminal cashier manager class - game client flash application service calls ...
 * available on /onlinecasinoservice/authorization
 */
class TicketTerminalCashierManager {
	
	/**
	* method is used to test this class response
	* @return mixed
	*/
	public function test(){
		return "1";
	}

    /**
     * method is used to set shop cart to database
     * @param string $xml_content
     * @param string $terminal_mac
     * @param string $buyer_username
     * @return mixed
     */
    public function setShopCart($xml_content, $terminal_mac, $buyer_username){
        //test if parameters are all not empty
        if(!isset($xml_content) || empty($xml_content) || !isset($terminal_mac) || empty($terminal_mac) ||
        !isset($buyer_username) || empty($buyer_username)){
            //return error, all parameters are required to be correct values
            $errorHelper = new ErrorHelper();
			$message = "TicketTerminalCashierModel::setShopCart(xml_content = {$xml_content}, terminal_mac = {$terminal_mac}, buyer_username = {$buyer_username})";
			$errorHelper->serviceError($message, $message);
            return array('status'=>NOK, 'message'=>NOK_INVALID_DATA);
        }
        try{
            //strip tags from input parameters
            $terminal_mac = strip_tags($terminal_mac);
            $buyer_username = strip_tags($buyer_username);
            //extract categories from our database
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'TicketTerminalCashierModel.php';
            $modelTicketTerminalCashier = new TicketTerminalCashierModel();
            $arrData = $modelTicketTerminalCashier->setShopCart($xml_content, $terminal_mac, $buyer_username);
            $rows = array();
            foreach($arrData['product_list'] as $cur){
                $rows[] = array(
                    "product_id"=>$cur['product_id'],
                    "price"=>NumberHelper::convert_double($cur['price']),
                    "price_formatted"=>NumberHelper::format_double($cur['price']),
                    "quantity"=>NumberHelper::convert_integer($cur['quantity']),
                    "quantity_formatted"=>NumberHelper::format_integer($cur['quantity']),
                    "transaction_date"=>$cur['transaction_date'],
                    "currency"=>$cur['currency'],
                    "unit_price"=>NumberHelper::convert_double($cur['unit_price']),
                    "unit_price_formatted"=>NumberHelper::format_double($cur['unit_price']),
                    "affiliate_name"=>$cur['affiliate_name'],
                    "product_name"=>$cur['product_name'],
                    "terminal_name"=>$cur['terminal_name']
                );
            }
            $arrData["product_list"] = $rows;
            return $arrData;
        }catch(Zend_Exception $ex){
            //if any exception is thrown send mail and write to log
            //this part will probably never be called
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "TicketTerminalCashierManager::setShopCart(xml_content = {$xml_content}, terminal_mac = {$terminal_mac}, buyer_username = {$buyer_username}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array('status'=>NOK, 'message'=>NOK_EXCEPTION);
        }
    }

	/**
	 * 
	 * list categories
	 * category_name, category_desc
	 * selected affiliate_id
	 * @param int $affiliate_id
	 * @return mixed
	 */
	public function listCategories($affiliate_id){
		//test if parameters are all not empty
		if(!isset($affiliate_id) || empty($affiliate_id)){
			//return error, all parameters are required to be correct values
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
			//strip tags from input parameters
			$affiliate_id = strip_tags($affiliate_id);
			//extract categories from our database
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'TicketTerminalCashierModel.php';
			$modelTicketTerminalCashier = new TicketTerminalCashierModel();
			$arrData = $modelTicketTerminalCashier->listCategoriesForProducts(-1, $affiliate_id);
			$rows = array();
			//parse response into array from our database
			if($arrData['status'] == OK){
				foreach($arrData['cursor'] as $row){
                    $shop_dtype_str = "Shop";
                    if($row['shop_dtype'] == "1"){
                        $shop_dtype_str = "Coffee";
                    }
					$rows[] = array(
						//fields in database report cursor mapped to report fields returned by this service
						'category_id'=>$row['product_category_id'],
						'category_name'=>$row['category_name'],
						'category_desc'=>$row['category_desc'],
                        //0- Shop, 1-Coffee
                        'shop_dtype'=>$row['shop_dtype'],
                        'shop_dtype_str'=>$shop_dtype_str
					);
				}
				//return list of category_name, category_desc of categories
				return array('status'=>OK, 'report'=>$rows);
			}else{
				//reports exception here, additionally can add message in response from system or database
				return array('status'=>NOK, 'report'=>$rows);
			}			
		}catch(Zend_Exception $ex){
			//if any exception is thrown send mail and write to log
			//this part will probably never be called
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "TicketTerminalCashierManager::listCategories(affiliate_id = {$affiliate_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array('status'=>NOK);
		}
	}

    /**
     *
     * list products for affiliate
     * selected affiliate_id
     * @param int $affiliate_id
     * @param string $mac_address
     * @return mixed
     */
    public function listProductsForAffiliate($affiliate_id, $mac_address){
        //test if parameters are all not empty
        if(!isset($affiliate_id) || empty($affiliate_id) || !isset($mac_address) || empty($mac_address)){
            //return error, all parameters are required to be correct values
            return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
        }
        try{
            //strip tags from input parameters
            $affiliate_id = strip_tags($affiliate_id);
            $mac_address = strip_tags($mac_address);
            //extract products from our database
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'TicketTerminalCashierModel.php';
            $modelTicketTerminalCashier = new TicketTerminalCashierModel();
            $arrData = $modelTicketTerminalCashier->listProductsPerAffiliate(-1, $affiliate_id, $mac_address);
            $rows_products = array();
            $rows_commercials = array();
            $rows_parameters = array();
            //parse response into array from our database
            if($arrData['status'] == OK){
                //list products for affiliate and terminal
                foreach($arrData['cursor_products'] as $row){
                    $shop_dtype_str = "Shop";
                    if($row['shop_dtype'] == "1"){
                        $shop_dtype_str = "Coffee";
                    }
                    $rows_products[] = array(
                        //fields in database report cursor mapped to report fields returned by this service
                        'product_id'=>$row['product_id'],
                        'product_name'=>$row['product_name'],
                        'product_desc'=>$row['product_desc'],
                        'category_name'=>$row['category_name'],
                        'price'=>NumberHelper::convert_double($row['price']),
                        'price_formatted'=>NumberHelper::format_double($row['price']),
                        'tax_amount'=>NumberHelper::convert_double($row['tax_amount']),
                        'tax_amount_formatted'=>NumberHelper::format_double($row['tax_amount']),
                        'tax_percent'=>NumberHelper::convert_double($row['tax_percent']),
                        'tax_percent_formatted'=>NumberHelper::format_double($row['tax_percent']),
                        'currency'=>$row['currency'],
                        //picture information for product
                        'image_id'=>$row['tt_images_id'],
                        'image_name'=>$row['image_name'],
                        'image_real_file_name'=>$row['image_uq_name'],
                        'image_site'=>$row['image_site'],
                        'image_location'=>$row['image_location'],
                        'image_relative_location'=>$row['image_relative_location'],
                        'image_url'=>($row['image_site'] . '/' . $row['image_relative_location'] . '/' . $row['image_uq_name']),
                        //0- Shop, 1-Coffee
                        'shop_dtype'=>$row['shop_dtype'],
                        'shop_dtype_str'=>$shop_dtype_str,
                        'aff_shop_id'=>$row['aff_shop_id']
                    );
                }
                //list commercials (screen savers) for affiliate and terminal
                foreach($arrData['cursor_commercials'] as $row){
                    $rows_commercials[] = array(
                        //fields in database report cursor mapped to report fields returned by this service
                        "image_name"=>$row['image_name'],
                        "image_real_file_name"=>$row['image_uq_name'],
                        "image_site"=>$row['image_site'],
                        "image_location"=>$row['image_location'],
                        "image_relative_location"=>$row['image_relative_location'],
                        'image_url'=>($row['image_site'] . '/' . $row['image_relative_location'] . '/' . $row['image_uq_name']),
                        "image_id"=>$row['tt_images_id'],
                        "affiliate_id"=>$row['affiliate_id'],
                        "active_in_seconds"=>$row['active_in_seconds'],
                        "change_in_seconds"=>$row['change_in_seconds'],
                        "commercial_id"=>$row['tt_commercial_id']
                    );
                }
                //list parameters for affiliate and terminal
                foreach($arrData['cursor_parameters'] as $row){
                    $rows_parameters[] = array(
                        "button_settings_id"=>$row['tt_buttons_settings_id'],
                        "language"=>$row['tt_language'],
                        "ticket_in"=>$row['ticket_in'],
                        "ticket_out"=>$row['ticket_out'],
                        "deposit"=>$row['deposit'],
                        "withdraw"=>$row['withdraw'],
                        "coffee_shop"=>$row['coffee_shop'],
                        "shop"=>$row['shop'],
                        "screen_saver"=>$row['screen_saver'],
                        "all_after_login"=>$row['all_after_login'],
                        "your_account" => $row['your_account'],
                        "voucher" => $row['voucher'],
                        "empty"=> $row['empty'],
                        "refill"=> $row['refill'],
                        "transactions"=>$row['transactions'],
                    );
                }
                //return list of list products, list of commercials (screensavers) and terminal from mac-address
                return array('status'=>OK, 'report'=>$rows_products, 'terminal_name'=>$arrData['terminal_name'], 'language'=>$arrData['language'], 'commercials'=>$rows_commercials, "parameters"=>$rows_parameters);
            }else{
                //reports exception here, additionally can add message in response from system or database
                return array('status'=>NOK, 'report'=>$rows_products, 'commercials'=>$rows_commercials, 'parameters'=>$rows_parameters);
            }
        }catch(Zend_Exception $ex){
            //if any exception is thrown send mail and write to log
            //this part will probably never be called
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "TicketTerminalCashierManager::listProductsForAffiliate(affiliate_id = {$affiliate_id}, mac_address = {$mac_address}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array('status'=>NOK);
        }
    }

    /**
	 *
	 * get player withdrawal amount
	 * @param int $player_id
	 * @return mixed
	 */
	public function getPlayerAllowedWithdrawal($player_id){
		//test if parameters are all not empty
		if(!isset($player_id) || empty($player_id)){
			//return error, all parameters are required to be correct values
			return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
		}
		try{
			//strip tags from input parameters
			$player_id = strip_tags($player_id);
			//extract categories from our database
			require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'TicketTerminalCashierModel.php';
			$modelTicketTerminalCashier = new TicketTerminalCashierModel();
			$arrData = $modelTicketTerminalCashier->getPlayerAllowedWithdrawal($player_id);
            if($arrData['status'] == OK){
                return array('status'=>OK, 'player_id'=>$player_id, 'allowed_amount'=>$arrData['allowed_amount']);
            }else{
                return array('status'=>NOK, 'player_id'=>$player_id, 'message'=>NOK_EXCEPTION);
            }
		}catch(Zend_Exception $ex){
			//if any exception is thrown send mail and write to log
			//this part will probably never be called
            $detected_ip_address = IPHelper::getRealIPAddress();
            $errorHelper = new ErrorHelper();
            $message = "TicketTerminalCashierManager::getPlayerAllowedWithdrawal(player_id = {$player_id}) <br /> Detected IP Address = {$detected_ip_address} <br /> Exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return array('status'=>NOK, 'player_id'=>$player_id, 'message'=>NOK_EXCEPTION);
		}
	}

    /**
     *
     * get affiliate parameters setup
     * @param int $affiliate_id
     * @return mixed
     */
    public function getAffiliateParameters($affiliate_id){
        //test if parameters are all not empty
        if(!isset($affiliate_id) || empty($affiliate_id)){
            //return error, all parameters are required to be correct values
            return array("status"=>NOK, "message"=>NOK_INVALID_DATA);
        }
        try{
            require_once MODELS_DIR . DS . 'terminal_integration' . DS . 'TicketTerminalCashierModel.php';
            $modelTicketTerminalCashier = new TicketTerminalCashierModel();
            $arrData = $modelTicketTerminalCashier->getAffiliateParameters($affiliate_id);
            if($arrData['status'] != OK){
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }else {
                $arrData['cursor_list_aff_parameters'] = CursorToArrayHelper::cursorToArray($arrData['cursor_list_aff_parameters']);
                $selected = $arrData['cursor_list_aff_parameters'][0];
                $button_settings_id = ($selected['tt_buttons_settings_id'] == null) ? 0 : $selected['tt_buttons_settings_id'];
                $affiliate_name = $selected['affiliate_name'];
                $language_ticket = ($selected['tt_language'] == null) ? 0 : $selected['tt_language'];
                $ticket_in = ($selected['ticket_in'] == null) ? 0 : $selected['ticket_in'];
                $ticket_out = ($selected['ticket_out'] == null) ? 0 : $arrData['cursor_list_aff_parameters'][0]['ticket_out'];
                $deposit = ($selected['deposit'] == null) ? 0 : $selected['deposit'];
                $withdraw = ($selected['withdraw'] == null) ? 0 : $selected['withdraw'];
                $coffee_shop = ($selected['coffee_shop'] == null) ? 0 : $selected['coffee_shop'];
                $shop = ($selected['shop'] == null) ? 0 : $selected['shop'];
                $all_after_login = ($selected['all_after_login'] == null) ? 0 : $selected['all_after_login'];
                $screen_saver = ($selected['screen_saver'] == null) ? 0 : $selected['screen_saver'];
                $your_account = ($selected['your_account'] == null) ? 0 : $selected['your_account'];
                $voucher = ($selected['voucher'] == null) ? 0 : $selected['voucher'];

                $empty = ($selected['empty'] == null) ? 0 : $selected['empty'];
                $refill = ($selected['refill'] == null) ? 0 : $selected['refill'];
                $transactions = ($selected['transactions'] == null) ? 0 : $selected['transactions'];

                return array(
                    "status" => OK,
                    "parameters"=>array(
                        "all_after_login_out" => $arrData['all_after_login_out'],
                        "button_settings_id" => $button_settings_id,
                        "affiliate_name" => $affiliate_name,
                        "language_ticket" => $language_ticket,
                        "ticket_in" => $ticket_in,
                        "ticket_out" => $ticket_out,
                        "deposit" => $deposit,
                        "withdraw" => $withdraw,
                        "coffee_shop" => $coffee_shop,
                        "shop" => $shop,
                        "all_after_login" => $all_after_login,
                        "screen_saver" => $screen_saver,
                        "your_account" => $your_account,
                        "voucher" => $voucher,
                        "empty"=> $empty,
                        "refill"=>$refill,
                        "transactions"=>$transactions
                    )
                );
            }
        }catch(Zend_Exception $ex){
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
    }

}