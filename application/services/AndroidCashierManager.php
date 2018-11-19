<?php
require_once 'Zend/Registry.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
/**
 * 
 * web service for cashier through android application
 *
 */
class AndroidCashierManager {
	
	/**
	 * 
	 * login cashier or affiliate and open backoffice session
	 * @param string $username
	 * @param string $password
	 * @param string $ip_address
	 * @return mixed
	 */
	public function login($username, $password, $ip_address){
		if(!isset($username) || !isset($password) || !isset($ip_address)){
			return array("status"=>NOK_INVALID_DATA);
		}
		try{
			$username = strip_tags($username);
			$password = strip_tags($password);
			$ip_address = strip_tags($ip_address);
			$password = md5($password);
			//if there are ip addresses with , separated as CSV string
			$ip_addresses = explode(",", $ip_address);
			$ip_address = $ip_addresses[0];
			require_once MODELS_DIR . DS . 'AndroidCashierModel.php';
			$modelAndroidCashier = new AndroidCashierModel();
			$arrData = $modelAndroidCashier->openBoSession($username, $password, $ip_address);
			return $arrData;
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "Error while opening backoffice session for cashier on android cashier web service. <br /> Username: {$username} <br /> Sent IP address: {$ip_address} <br /> Received IP address: " . IPHelper::getRealIPAddress() . ' <br /> Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex); 
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * 
	 * close opened backoffice session for android cashier application
	 * @param int $session_id
	 * @return mixed
	 */
	public function logout($session_id){
		if(!isset($session_id)){
			return array("status"=>NOK_INVALID_DATA);
		}
		try{
			$session_id = strip_tags($session_id);
			require_once MODELS_DIR . DS . 'AndroidCashierModel.php';
			$modelAndroidCashier = new AndroidCashierModel();
			$arrData = $modelAndroidCashier->closeBoSession($session_id);
			return $arrData;
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "Error while closing backoffice session for cashier on android cashier web service. <br /> Received IP address: " . IPHelper::getRealIPAddress() . ' <br /> Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * 
	 * list players for logged in user (with backoffice session) for credit transfer
	 * @param int $session_id
	 * @param int $page_number
	 * @param int $per_page
	 * @param int $order_by
	 * @param string $sort_order
     * @return mixed
	 */
	public function listPlayers($session_id, $page_number, $per_page, $order_by, $sort_order){
		if(!isset($session_id) || !isset($page_number) || !isset($per_page) || 
		!isset($order_by) || !isset($sort_order)){
			return array("status"=>NOK_INVALID_DATA);
		}
		try{
			$session_id = intval(strip_tags($session_id));
			$page_number = intval(strip_tags($page_number));
			$per_page = intval(strip_tags($per_page));
			$order_by = intval(strip_tags($order_by));
			$sort_order = strip_tags($sort_order);
			if($page_number < 0)
			$page_number = 1;
			if($per_page < 10)
			$per_page = 10;
			if($order_by < 1)
			$order_by = 1;
			if($sort_order != "asc" || $sort_order != "desc")
				$sort_order = "asc";
			require_once MODELS_DIR . DS . 'AndroidCashierModel.php';
			$modelAndroidCashier = new AndroidCashierModel();
			$arrData = $modelAndroidCashier->getDirectPlayers($session_id, $page_number, 
			$per_page, $order_by, $sort_order);
			$total_items = 0;
			$total_pages = 1;
			if($page_number == 1){
				if($arrData['status'] != OK){
					return array("status"=>NOK_EXCEPTION);
				}
				$total_items = (int)$arrData["info"][0]["cnt"];
				$total_pages = (int)ceil($total_items / $per_page);
			}else{
				if($page_number >= $total_pages){
					$arrData = $modelAndroidCashier->getDirectPlayers($session_id, $page_number,
					 $per_page, $order_by, $sort_order);
					if($arrData['status'] != OK){
						return array("status"=>NOK_EXCEPTION);
					}
					$total_items = (int)$arrData["info"][0]["cnt"];
					$total_pages = (int)ceil($total_items / $per_page);
				}
			}
			if(count($arrData[0]) == 0){
				$page_number = 1;
				$arrData = $modelAndroidCashier->getDirectPlayers($session_id, $page_number, 
				$per_page, $order_by, $sort_order);
				if($arrData['status'] != OK){
					$errorHelper = new ErrorHelper();
					$mail_message = "Error while closing backoffice session for cashier on android cashier web service. <br /> Received IP address: " . IPHelper::getRealIPAddress();
					$log_message = "Error while closing backoffice session for cashier on android cashier web service. Received IP address: " . IPHelper::getRealIPAddress();
					$errorHelper->serviceError($mail_message, $log_message);
					return array("status"=>NOK_EXCEPTION);
				}
				$total_items = (int)$arrData["info"][0]["cnt"];
				$total_pages = (int)ceil($total_items / $per_page);
			}
			$resArray = array();
			$no_items = count($arrData["table"]);
			for($i = 0; $i<$no_items; $i++){
				$resArray[] = array(
					"player_id" => $arrData["table"][$i]['player_id'], 
					"player_name" => $arrData["table"][$i]['player_name'], 
					"player_credits" => number_format($arrData["table"][$i]['player_credits'], 2), 
					"currency" => $arrData["table"][$i]['currency'],
					"voucher_serial_number"=>$arrData["table"][$i]["serial_number"],
					"voucher_prepaid_code"=>$arrData["table"][$i]["prepaid_code"]
				);
			}
			unset($arrData);
			return array("status"=>OK, "total_items"=>$total_items, "total_pages"=>$total_pages, 
			"report"=>$resArray);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = "Error while closing backoffice session for cashier on android cashier web service. <br /> Received IP address: " . IPHelper::getRealIPAddress() . ' <br /> Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "Error while closing backoffice session for cashier on android cashier web service. Received IP address: " . IPHelper::getRealIPAddress() . ' Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * 
	 * list terminal players for credit transfer (under user with backoffice session session_id)
	 * @param int $session_id
	 * @param int $page_number
	 * @param int $per_page
	 * @param int $order_by
	 * @param string $sort_order
	 * @return mixed
	 */
	public function listTerminalPlayers($session_id, $page_number, $per_page, 
	$order_by, $sort_order){
		if(!isset($session_id) || !isset($page_number) || !isset($per_page) || 
		!isset($order_by) || !isset($sort_order)){
			return array("status"=>NOK_INVALID_DATA);
		}
		try{
			$session_id = intval(strip_tags($session_id));
			$page_number = intval(strip_tags($page_number));
			$per_page = intval(strip_tags($per_page));
			$order_by = intval(strip_tags($order_by));
			$sort_order = strip_tags($sort_order);
			if($page_number < 0)
				$page_number = 1;
			if($per_page < 10)
				$per_page = 10;
			if($order_by < 1)
				$order_by = 1;
			if($sort_order != "asc" || $sort_order != "desc")
				$sort_order = "asc";
			require_once MODELS_DIR . DS . 'AndroidCashierModel.php';
			$modelAndroidCashier = new AndroidCashierModel();
			$arrData = $modelAndroidCashier->getDirectTerminalPlayers($session_id, $page_number, 
			$per_page, $order_by, $sort_order);
			$total_items = 0;
			$total_pages = 1;
			if($page_number == 1){
				if($arrData['status'] != OK){
					$errorHelper = new ErrorHelper();
					$mail_message = "Error while listing terminal players for cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Received IP address: " . IPHelper::getRealIPAddress();
					$log_message = "Error while listing terminal players for cashier on android cashier web service. Session id: {$session_id} Received IP address: " . IPHelper::getRealIPAddress();
					$errorHelper->serviceError($mail_message, $log_message);
					return array("status"=>NOK_EXCEPTION);
				}
				$total_items = (int)$arrData["info"][0]["cnt"];
				$total_pages = (int)ceil($total_items / $per_page);
			}else{
				if($page_number >= $total_pages){
					$arrData = $modelAndroidCashier->getDirectTerminalPlayers($session_id, $page_number, 
					$per_page, $order_by, $sort_order);
					if($arrData['status'] != OK){
						$errorHelper = new ErrorHelper();					
						$mail_message = "Error while listing terminal players for cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Received IP address: " . IPHelper::getRealIPAddress();
						$log_message = "Error while listing terminal players for cashier on android cashier web service. Session id: {$session_id} Received IP address: " . IPHelper::getRealIPAddress();
						$errorHelper->serviceError($mail_message, $log_message);
						return array("status"=>NOK_EXCEPTION);
					}
					$total_items = (int)$arrData["info"][0]["cnt"];
					$total_pages = (int)ceil($total_items / $per_page);
				}
			}
			if(count($arrData[0]) == 0){
				$page_number = 1;
				$arrData = $modelAndroidCashier->getDirectTerminalPlayers($session_id, $page_number, 
				$per_page, $order_by, $sort_order);
				if($arrData['status'] != OK){
					$errorHelper = new ErrorHelper();
					$mail_message = "Error while listing terminal players for cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Received IP address: " . IPHelper::getRealIPAddress();
					$log_message = "Error while listing terminal players for cashier on android cashier web service. Session id: {$session_id} Received IP address: " . IPHelper::getRealIPAddress();
					$errorHelper->serviceError($mail_message, $log_message);
					return array("status"=>NOK_EXCEPTION);
				}
				$total_items = (int)$arrData["info"][0]["cnt"];
				$total_pages = (int)ceil($total_items / $per_page);
			}
			$resArray = array();
			$no_items = count($arrData["table"]);
			for($i = 0; $i<$no_items ; $i++){
				$resArray[] = array("player_id" => $arrData["table"][$i]['player_id'], 
				"player_name" => $arrData["table"][$i]['player_name'], 
				"player_credits" => number_format($arrData["table"][$i]['player_credits'], 2), 
				"currency" => $arrData["table"][$i]['currency']);
			}
			unset($arrData);
			return array("status"=>OK, "total_items"=>$total_items, "total_pages"=>$total_pages, 
			"report"=>$resArray);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();			
			$mail_message = "Error while listing terminal players for cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Received IP address: " . IPHelper::getRealIPAddress() . ' <br /> Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "Error while listing terminal players for cashier on android cashier web service. Session id: {$session_id} Received IP address: " . IPHelper::getRealIPAddress() . ' Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK_EXCEPTION);
		}		
	}
	
	/**
	 * 
	 * find information to terminal player for credit transfer
	 * @param int $session_id
	 * @param int $player_id
	 * @return mixed
	 */
	public function fillAffiliateToTerminal($session_id, $player_id){
		if(!isset($session_id) || !isset($player_id)){
			return array("status"=>NOK_INVALID_DATA);
		}
		try{
			$session_id = intval(strip_tags(trim($session_id)));
			$player_id = intval(strip_tags(trim($player_id)));
			require_once MODELS_DIR . DS . 'TransferCreditModel.php';
			$modelTransferCredit = new TransferCreditModel();
			$arrData = $modelTransferCredit->fillInAffiliateToTerminalPlayer($session_id, $player_id);
			$row = $arrData[0][0];
			$player_name = $row['player_name'];
			$player_credit_status = $row['player_credits'];
			$player_currency = $row['currency'];
			$player_aff_id = $row['affiliate_id'];
			return array("status"=>OK, "session_id"=>$session_id, "player_id"=>$player_id, 
			"player_name"=>$player_name, "player_credit_status"=>$player_credit_status,
			"player_currency"=>$player_currency, "player_aff_id"=>$player_aff_id);			
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();		
			$mail_message = "Error while receiving player information for credit transfer for cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Player id: {$player_id} <br /> Received IP address: " . IPHelper::getRealIPAddress() . ' <br /> Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "Error while receiving player information for credit transfer for cashier on android cashier web service. Session id: {$session_id} Player id: {$player_id} Received IP address: " . IPHelper::getRealIPAddress() . ' Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);			
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * 
	 * find information for player for credit transfer
	 * @param int $session_id
	 * @param int $player_id
	 * @return mixed
	 */
	public function fillAffiliateToPlayer($session_id, $player_id){
		if(!isset($session_id) || !isset($player_id)){
			return array("status"=>NOK_INVALID_DATA);
		}
		try{
			$session_id = intval(strip_tags(trim($session_id)));
			$player_id = intval(strip_tags(trim($player_id)));
			require_once MODELS_DIR . DS . 'TransferCreditModel.php';
			$modelTransferCredit = new TransferCreditModel();
			$arrData = $modelTransferCredit->fillInAffiliateToPlayer($session_id, $player_id);
			$row = $arrData[0][0];
			$player_name = $row['player_name'];
			$player_credit_status = $row['player_credits'];
			$player_currency = $row['currency'];
			$player_aff_id = $row['affiliate_id'];
			return array("status"=>OK, "session_id"=>$session_id, "player_id"=>$player_id, 
			"player_name"=>$player_name, "player_credit_status"=>$player_credit_status,
				"player_currency"=>$player_currency, "player_aff_id"=>$player_aff_id);			
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();			
			$mail_message = "Error while receiving player information for credit transfer for cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Player id: {$player_id} <br /> Received IP address: " . IPHelper::getRealIPAddress() . ' <br /> Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "Error while receiving player information for credit transfer for cashier on android cashier web service. Session id: {$session_id} Player id: {$player_id} Received IP address: " . IPHelper::getRealIPAddress() . ' Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * 
	 * find affiliate balance for credit transfer to his players
	 * @param int $session_id
	 * @param string $currency
	 * @return mixed
	 */
	public function findAffiliateCreditStatus($session_id, $currency){
		if(!isset($session_id) || !isset($currency)){
			return array("status"=>NOK_INVALID_DATA);
		}
		try{
			$session_id = intval(strip_tags($session_id));
			$currency = trim(strip_tags($currency));
			require_once MODELS_DIR . DS . 'TransferCreditModel.php';
			$modelTransferCredit = new TransferCreditModel();
			$affAm = $modelTransferCredit->findCreditStatus($session_id, $currency);
			return array("status"=>OK, "credit_status"=>$affAm);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error while receiving affiliate credit status for credit transfer for cashier on android cashier web service. <br /> Session id: ' . $session_id . ' <br /> Currency: ' . $currency . ' <br /> Received IP address: ' . IPHelper::getRealIPAddress() . ' <br /> Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = 'Error while receiving affiliate credit status for credit transfer for cashier on android cashier web service. Session id: ' . $session_id . ' Currency: ' . $currency . ' Received IP address: ' . IPHelper::getRealIPAddress() . ' Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * 
	 * transfer credits to terminal player
	 * @param int $session_id
	 * @param int $affiliate_id
	 * @param int $player_id
	 * @param float $amount
	 * @param float $affiliate_credit_status
	 * @param float $auto_credit_increment_amount
	 * @param string $enabled_auto_credit_increment
	 * @param string $currency
	 * @return mixed
	 */
	public function transferCreditsToTerminal($session_id, $affiliate_id, $player_id, $amount, 
	$affiliate_credit_status, $auto_credit_increment_amount, $enabled_auto_credit_increment, $currency){
		if(!isset($session_id) || !isset($affiliate_id) || !isset($player_id) || !isset($amount) || 
		!isset($affiliate_credit_status) || !isset($auto_credit_increment_amount) ||
		!isset($enabled_auto_credit_increment) || !isset($currency)){
			return array("status"=>NOK_INVALID_DATA);
		}
		try{
			$session_id = intval(strip_tags($session_id));
			$affiliate_id = intval(strip_tags($affiliate_id));
			$player_id = intval(strip_tags($player_id));
			$amount = strip_tags($amount);
			$affiliate_credit_status = strip_tags($affiliate_credit_status);
			$auto_credit_increment_amount = strip_tags($auto_credit_increment_amount);
			$enabled_auto_credit_increment = strip_tags($enabled_auto_credit_increment);
			$currency = trim(strip_tags($currency));
			require_once MODELS_DIR . DS . 'TransferCreditModel.php';
			$modelTransferCredit = new TransferCreditModel();
			if($amount <= 0){
				//amount is negative value
				return array("status"=>NOK_INVALID_AMOUNT);
			}else{
				if($amount <= $affiliate_credit_status){
					$auto_credit_increment_amount = 0;
					$res = $modelTransferCredit->transferAffiliateToPlayer($session_id, $affiliate_id, $player_id, $amount, $affiliate_credit_status, $auto_credit_increment_amount, $enabled_auto_credit_increment, $currency);
					if($res['status'] != OK){
						//database error while transfer
						$errorHelper = new ErrorHelper();
						$mail_message = "Error while receiving affiliate credit status for credit transfer for cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Currency: {$currency} <br /> Received IP address: " . IPHelper::getRealIPAddress() . " <br /> Database message: {$res['message']}";
						$log_message = "Error while receiving affiliate credit status for credit transfer for cashier on android cashier web service. Session id: {$session_id} Currency: {$currency} Received IP address: " . IPHelper::getRealIPAddress() . " Database message: {$res['message']}";
						$errorHelper->serviceError($mail_message, $log_message);
						return array("status"=>NOK_EXCEPTION);
					}else{
						//transfer success
						return array("status"=>OK);
					}
				}else{
					if($enabled_auto_credit_increment == YES && $amount <= $auto_credit_increment_amount){
						//autoincrement enabled to transfer
						$res = $modelTransferCredit->transferAffiliateToPlayer($session_id, $affiliate_id, $player_id, $amount, $affiliate_credit_status, $auto_credit_increment_amount, $enabled_auto_credit_increment, $currency);
						if($res['status'] != OK){
							//database error while transfer
							$errorHelper = new ErrorHelper();
							$mail_message = "Error while receiving affiliate credit status for credit transfer for cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Currency: {$currency} <br /> Received IP address: " . IPHelper::getRealIPAddress() . " <br /> Database message: {$res['message']}";
							$log_message = "Error while receiving affiliate credit status for credit transfer for cashier on android cashier web service. Session id: {$session_id} Currency: {$currency} Received IP address: " . IPHelper::getRealIPAddress() . " Database message: {$res['message']}";
							$errorHelper->serviceError($mail_message, $log_message);
							return array("status"=>NOK_EXCEPTION);
						}else{
							//transfer with autoincrement success
							return array("status"=>OK);
						}
					}else{
						//amount larger than affiliate credit status
						return array("status"=>NOK);
					}
				}
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error while receiving affiliate credit status for credit transfer for cashier on android cashier web service. <br /> Session id: ' . $session_id . ' <br /> Currency: ' . $currency . ' <br /> Received IP address: ' . IPHelper::getRealIPAddress() . ' <br /> Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = 'Error while receiving affiliate credit status for credit transfer for cashier on android cashier web service. Session id: ' . $session_id . ' Currency: ' . $currency . ' Received IP address: ' . IPHelper::getRealIPAddress() . ' Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * 
	 * transfer credits to player
	 * @param int $session_id
	 * @param int $affiliate_id
	 * @param int $player_id
	 * @param float $amount
	 * @param float $affiliate_credit_status
	 * @param float $auto_credit_increment_amount
	 * @param string $enabled_auto_credit_increment
	 * @param string $currency
	 * @return mixed
	 */
	public function transferCreditsToPlayer($session_id, $affiliate_id, $player_id, $amount, 
	$affiliate_credit_status, $auto_credit_increment_amount, $enabled_auto_credit_increment, $currency){
		if(!isset($session_id) || !isset($affiliate_id) || !isset($player_id) || !isset($amount) || 
		!isset($affiliate_credit_status) || !isset($auto_credit_increment_amount)
		|| !isset($enabled_auto_credit_increment) || !isset($currency)){
			return array("status"=>NOK_INVALID_DATA);
		}
		try{
			$session_id = intval(strip_tags($session_id));
			$affiliate_id = intval(strip_tags($affiliate_id));
			$player_id = intval(strip_tags($player_id));
			$amount = strip_tags($amount);
			$affiliate_credit_status = strip_tags($affiliate_credit_status);
			$auto_credit_increment_amount = strip_tags($auto_credit_increment_amount);
			$enabled_auto_credit_increment = strip_tags($enabled_auto_credit_increment);
			$currency = trim(strip_tags($currency));
			require_once MODELS_DIR . DS . 'TransferCreditModel.php';
			$modelTransferCredit = new TransferCreditModel();
			if($amount <= 0){
				//amount is negative value
				return array("status"=>NOK_INVALID_AMOUNT);
			}else{
				if($amount <= $affiliate_credit_status){
					$auto_credit_increment_amount = 0;
					$res = $modelTransferCredit->transferAffiliateToPlayer($session_id, $affiliate_id, 
					$player_id, $amount, $affiliate_credit_status, $auto_credit_increment_amount, $enabled_auto_credit_increment, $currency);
					if($res['status'] != OK){
						//database error while transfer
						$errorHelper = new ErrorHelper();
						$ip_address = IPHelper::getRealIPAddress();
						$mail_message = "Error while transfering credits to player from cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Affiliate id: {$affiliate_id} <br /> Player id: {$player_id} <br /> Currency: {$currency} <br /> Received IP address: {$ip_address} <br /> Database message: {$res['message']}";
						$log_message = "Error while transfering credits to player from cashier on android cashier web service. Session id: {$session_id} Affiliate id: {$affiliate_id} Player id: {$player_id} Currency: {$currency} Received IP address: {$ip_address} Database message: {$res['message']}";
						$errorHelper->serviceError($mail_message, $log_message);
						return array("status"=>NOK_EXCEPTION);
					}else{
						//transfer success
						return array("status"=>OK);
					}
				}else{
					if($enabled_auto_credit_increment == YES && $amount <= $auto_credit_increment_amount){
						//autoincrement enabled to transfer
						$res = $modelTransferCredit->transferAffiliateToPlayer($session_id, $affiliate_id, 
						$player_id, $amount, $affiliate_credit_status, $auto_credit_increment_amount, 
						$enabled_auto_credit_increment, $currency);
						if($res['status'] != OK){
							//database error while transfer
							$errorHelper = new ErrorHelper();
							$ip_address = IPHelper::getRealIPAddress();
							$mail_message = "Error while transfering credits to player from cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Affiliate id: {$affiliate_id} <br /> Player id: {$player_id} <br /> Currency: {$currency} <br /> Received IP address: {$ip_address} <br /> Database message: {$res['message']}";
							$log_message = "Error while transfering credits to player from cashier on android cashier web service. Session id: {$session_id} Affiliate id: {$affiliate_id} Player id: {$player_id} Currency: {$currency} Received IP address: {$ip_address} Database message: {$res['message']}";
							$errorHelper->serviceError($mail_message, $log_message);
							return array("status"=>NOK_EXCEPTION);
						}else{
							//transfer with autoincrement success
							return array("status"=>OK);
						}
					}else{
						//amount larger than affiliate credit status
						return array("status"=>NOK);
					}
				}
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$ip_address = IPHelper::getRealIPAddress();
			$mail_message = "Error while transfering credits to player from cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Affiliate id: {$affiliate_id} <br /> Player id: {$player_id} <br /> Currency: {$currency} <br /> Received IP address: {$ip_address} <br /> Android cashier exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "Error while transfering credits to player from cashier on android cashier web service. Session id: {$session_id} Affiliate id: {$affiliate_id} Player id: {$player_id} Currency: {$currency} Received IP address: {$ip_address} Android cashier exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * 
	 * performes pc player payout here
	 * @param int $session_id
	 * @param int $affiliate_id
	 * @param int $player_id
	 * @param float $amount
	 * @param string $currency
	 * @return mixed
	 */
	public function playerPayout($session_id, $affiliate_id, $player_id, $amount, $currency){
		if(!isset($session_id) || !isset($affiliate_id) || !isset($player_id) || 
		!isset($amount) || !isset($currency)){
			return array("status"=>NOK_INVALID_DATA);
		}	
		require_once MODELS_DIR . DS . 'TransferCreditModel.php';
		$modelTransferCredit = new TransferCreditModel();
		try{
		//form is posted and form is validated
		if($amount <= 0){
			//amount is negative value
			return array("status"=>NOK_INVALID_AMOUNT);
		}else{
			$arrData = $modelTransferCredit->fillInAffiliateToPlayer($session_id, $player_id);
			$row = $arrData[0][0];
			$player_credit_status = $row['player_credits'];
			if($amount > $player_credit_status){
				//if entered value is larger than player has on its account
				$errorHelper = new ErrorHelper();
				$ip_address = IPHelper::getRealIPAddress();
				$mail_message = "Error while doing player payout to cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Affiliate id: {$affiliate_id} <br /> Player id: {$player_id} <br /> Currency: {$currency} <br /> Received IP address: {$ip_address}";
				$log_message = "Error while doing player payout to cashier on android cashier web service. Session id: {$session_id} Affiliate id: {$affiliate_id} Player id: {$player_id} Currency: {$currency} Received IP address: {$ip_address}";
				$errorHelper->serviceError($mail_message, $log_message);
				return array("status"=>NOK);
			}else{ 
				//if entered value is smaller or equal than player has on its account
				//DOES PLAYER PAYOUT CREDIT AMOUNT
				$res = $modelTransferCredit->playerPayout($session_id, $affiliate_id, $player_id, $amount, $currency);
				if(isset($res)){
					//database error while transfer
					$errorHelper = new ErrorHelper();
					$ip_address = IPHelper::getRealIPAddress();
					$mail_message = "Error while doing player payout to cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Affiliate id: {$affiliate_id} <br /> Player id: {$player_id} <br /> Currency: {$currency} <br /> Received IP address: {$ip_address}";
					$log_message = "Error while doing player payout to cashier on android cashier web service. Session id: {$session_id} Affiliate id: {$affiliate_id} Player id: {$player_id} Currency: {$currency} Received IP address: {$ip_address}";
					$errorHelper->serviceError($mail_message, $log_message);
					return array("status"=>NOK_EXCEPTION);
				}else{
					//payout success
					return array("status"=>OK);
				}
			}
		}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$ip_address = IPHelper::getRealIPAddress();			
			$mail_message = "Error while doing player payout to cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Affiliate id: {$affiliate_id} <br /> Player_id: {$player_id} <br /> Currency: {$currency} <br /> Received IP address: {$ip_address} <br /> Android cashier exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "Error while doing player payout to cashier on android cashier web service. Session id: {$session_id} Affiliate id: {$affiliate_id} Player id: {$player_id} Currency: {$currency} Received IP address: {$ip_address} Android cashier exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * 
	 * performes terminal player payout here
	 * @param int $session_id
	 * @param int $affiliate_id
	 * @param int $player_id
	 * @param float $amount
	 * @param string $currency
	 * @return mixed
	 */
	public function terminalPayout($session_id, $affiliate_id, $player_id, $amount, $currency){
		if(!isset($session_id) || !isset($affiliate_id) || !isset($player_id) || 
		!isset($amount) || !isset($currency)){
			return array("status"=>NOK_INVALID_DATA);
		}	
		require_once MODELS_DIR . DS . 'TransferCreditModel.php';
		$modelTransferCredit = new TransferCreditModel();
		try{
		//form is posted and form is validated
		if($amount <= 0){
			//amount is negative value
			return array("status"=>NOK_INVALID_AMOUNT);
		}else{
			$arrData = $modelTransferCredit->fillInAffiliateToTerminalPlayer($session_id, $player_id);
			$row = $arrData[0][0];
			$player_credit_status = $row['player_credits'];
			if($amount > $player_credit_status){
				//if entered value is larger than player has on its account
				$errorHelper = new ErrorHelper();
				$ip_address = IPHelper::getRealIPAddress();				
				$mail_message = "Error while doing player payout to cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Affiliate id: {$affiliate_id} <br /> Player id: {$player_id} <br /> Currency: {$currency} <br /> Received IP address: {$ip_address}";
				$log_message = "Error while doing player payout to cashier on android cashier web service. Session id: {$session_id} Affiliate id: {$affiliate_id} Player id: {$player_id} Currency: {$currency} Received IP address: {$ip_address}";
				$errorHelper->serviceError($mail_message, $log_message);
				return array("status"=>NOK);
			}else{ 
				//if entered value is smaller or equal than player has on its account
				//DOES PLAYER PAYOUT CREDIT AMOUNT				
				$res = $modelTransferCredit->playerPayout($session_id, $affiliate_id, $player_id, $amount, $currency);
				if(isset($res)){
					//database error while transfer
					$errorHelper = new ErrorHelper();
					$ip_address = IPHelper::getRealIPAddress();
					$mail_message = "Error while doing player payout to cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Affiliate id: {$affiliate_id} <br /> Player id: {$player_id} <br /> Currency: {$currency} <br /> Received IP address: {$ip_address}";
					$log_message = "Error while doing player payout to cashier on android cashier web service. Session id: {$session_id} Affiliate id: {$affiliate_id} Player id: {$player_id} Currency: {$currency} Received IP address: {$ip_address}";
					$errorHelper->serviceError($mail_message, $log_message);
					return array("status"=>NOK_EXCEPTION);
				}else{
					//payout success
					return array("status"=>OK);
				}
			}
		}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$ip_address = IPHelper::getRealIPAddress();			
			$mail_message = "Error while doing player payout to cashier on android cashier web service. <br /> Session id: {$session_id} <br /> Affiliate id: {$affiliate_id} <br /> Player_id: {$player_id} <br /> Currency: {$currency} <br /> Received IP address: {$ip_address} <br /> Android cashier exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "Error while doing player payout to cashier on android cashier web service. Session id: {$session_id} Affiliate id: {$affiliate_id} Player id: {$player_id} Currency: {$currency} Received IP address: {$ip_address} Android cashier exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return array("status"=>NOK_EXCEPTION);
		}
	}
	
	/**
	 * 
	 * list player history
	 * @param int $session_id
	 * @param int $player_id
	 * @param string $start_date
	 * @param string $end_date
	 * @param int $page_number
	 * @param int $per_page
	 * @param int $order_by
	 * @param string $sort_order
	 * @return mixed
	 */
	public function playerCreditTransfers($session_id, $player_id, $start_date, $end_date, $page_number, $per_page,  $order_by, $sort_order){
		if(!isset($session_id) || !isset($page_number) || !isset($per_page) ||
		!isset($start_date) || !isset($end_date) || !isset($player_id) ||
		!isset($order_by) || !isset($sort_order)){
			return array("status"=>NOK_INVALID_DATA);
		}
		try{
			$session_id = intval(strip_tags($session_id));
			$player_id = intval(strip_tags($player_id));
			$page_number = intval(strip_tags($page_number));
			$per_page = intval(strip_tags($per_page));
			$order_by = intval(strip_tags($order_by));
			$sort_order = strip_tags($sort_order);
			if($page_number < 0)
				$page_number = 1;
			if($per_page < 10)
				$per_page = 10;
			if($order_by < 1)
				$order_by = 1;
			if($sort_order != "asc" || $sort_order != "desc")
				$sort_order = "asc";
			$subject_name = ALL;
			if($player_id != ALL){
				require_once MODELS_DIR . DS . 'PlayerModel.php';
				$modelPlayer = new PlayerModel();
				$playerDetails = $modelPlayer->getPlayerDetailsMalta($session_id, $player_id);
				if($playerDetails['status'] != OK){
					return array("status"=>NOK_EXCEPTION);
				}
				$details = $playerDetails['details'];
				$subject_name = $details['user_name'];
			}else{
				$subject_name = ALL;
			}
			require_once MODELS_DIR . DS . 'AndroidCashierModel.php';
			$modelAndroidCashier = new AndroidCashierModel();			
			$arrData = $modelAndroidCashier->getPlayerCreditTransfers($session_id, $subject_name, $start_date, $end_date, $page_number, $per_page, $order_by, $sort_order);
			require_once MODELS_DIR . DS . 'ReportsModel.php';
			$modelReports = new ReportsModel();
			$total_items = 0;
			$total_pages = 1;
			if($page_number == 1){
				if($arrData['status'] != OK){
					$errorHelper = new ErrorHelper();					
					$mail_message = 'Error while closing backoffice session for cashier on android cashier web service. <br /> Session id: ' . $session_id . '<br /> Player id: ' . $player_id . ' <br /> Received IP address: ' . IPHelper::getRealIPAddress();
					$log_message = 'Error while closing backoffice session for cashier on android cashier web service. Session id: ' . $session_id . ' Player id: ' . $player_id . ' Received IP address: ' . IPHelper::getRealIPAddress();
					$errorHelper->serviceError($mail_message, $log_message);
					return array("status"=>NOK_EXCEPTION);
				}
				$total_items = (int)$arrData["info"][0]["cnt"];
				$total_pages = (int)ceil($total_items / $per_page);
			}else{
				if($page_number >= $total_pages){
					$arrData = $modelAndroidCashier->getPlayerCreditTransfers($session_id, $subject_name, $start_date, $end_date, $page_number, $per_page, $order_by, $sort_order);
					if($arrData['status'] != OK){
						return array("status"=>NOK_EXCEPTION);
					}
					$total_items = (int)$arrData["info"][0]["cnt"];
					$total_pages = (int)ceil($total_items / $per_page);
				}
			}
			if(count($arrData[0]) == 0){
				$page_number = 1;
				$arrData = $modelAndroidCashier->getPlayerCreditTransfers($session_id, $subject_name, $start_date, $end_date, $page_number, $per_page, $order_by, $sort_order);
				if($arrData['status'] != OK){
					$errorHelper = new ErrorHelper();					
					$mail_message = 'Error while closing backoffice session for cashier on android cashier web service. <br /> Session id: ' . $session_id . '<br /> Player id: ' . $player_id . ' <br /> Received IP address: ' . IPHelper::getRealIPAddress();
					$log_message = 'Error while closing backoffice session for cashier on android cashier web service. Session id: ' . $session_id . ' Player id: ' . $player_id . ' Received IP address: ' . IPHelper::getRealIPAddress();
					$errorHelper->serviceError($mail_message, $log_message);
					return array("status"=>NOK_EXCEPTION);
				}
				$total_items = (int)$arrData["info"][0]["cnt"];
				$total_pages = (int)ceil($total_items / $per_page);
			}
			$resArray = array();
			$no_items = count($arrData["table"]);
			for($i = 0; $i<$no_items; $i++){
				$resArray[] = array(
				"date_time" => $arrData["table"][$i]['date_time'],
				"commited_by" => $arrData["table"][$i]['commited_by'],
				"name_from" => $arrData["table"][$i]['name_from'],
				"name_to" => $arrData["table"][$i]["name_to"],
				"amount" => number_format($arrData["table"][$i]['amount'], 2),
				"currency" => $arrData["table"][$i]['currency'],
				"transaction_type" => $arrData["table"][$i]['transaction_type'],
				"ip_country"=> $arrData["table"][$i]['ip_country'],
				"city" => $arrData["table"][$i]['city'],
				"sign" => $arrData["table"][$i]['amount_sign']);
			}
			unset($arrData);
			return array("status"=>OK, "total_items"=>$total_items, "total_pages"=>$total_pages,
				"report"=>$resArray);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();			
			$mail_message = 'Error while closing backoffice session for cashier on android cashier web service. <br /> Received IP address: ' . IPHelper::getRealIPAddress() . ' <br /> Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = 'Error while closing backoffice session for cashier on android cashier web service. Received IP address: ' . IPHelper::getRealIPAddress() . ' Android cashier exception message: ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);			
			return array("status"=>NOK_EXCEPTION);
		}
	}
}