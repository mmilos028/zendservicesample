<?php
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
/** Creates object Sites and its data */
class Site{
	public $id;
	public $name;
	public $status;
	public $url;
	public $logo;
	public $hold;
	public function __construct($id, $name, $status, $url, $logo, $hold){
		$this->id = $id;
		$this->name = $name;
		$this->status = $status;
		$this->url = $url;
		$this->logo = $logo;
		$this->hold = $hold;
	}
}
/**
 * 
 * Web surfing flash application service calls ...
 *
 */
class WebSurfingManager {
	/**
	 * 
	 * reset browser credits from browser surfing application
	 * @param int $session_id
	 * @return mixed
	 */
	public function browserReset($session_id){
		//returns status (success reset, not success), new credit status (0 - reset is success)
		if(!isset($session_id)){
			return null;
		}
		try{
			$session_id = strip_tags($session_id);
			require_once MODELS_DIR . DS . 'TransferCreditModel.php';
			$modelTransferCredits = new TransferCreditModel();
			$arrData = $modelTransferCredits->resetBrowser($session_id);
			unset($modelTransferCredits);
			return $arrData;
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$log_message = $mail_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return null;
		}
	}

	/**
	 * 
	 * terminal payout from browser surfing application
	 * @param int $session_id
	 * @param int $aff_id
	 * @param int $player_id
	 * @param float $amount
	 * @param string $currency
	 * @return mixed
	 */
	public function browserPayout($session_id, $aff_id, $player_id, $amount, $currency){
		$session_id = strip_tags($session_id);
		$aff_id = strip_tags($aff_id);
		$player_id = strip_tags($player_id);
		$amount = strip_tags($amount);
		$currency = strip_tags($currency);
		if(!isset($session_id)){
			$errorHelper = new ErrorHelper();
			$log_message = $mail_message = "Web surfing web player payout, parametar session_id is empty.";
			$errorHelper->serviceError($mail_message, $log_message);
			return null;
		}
		if(!isset($aff_id)){
			$errorHelper = new ErrorHelper();
			$log_message = $mail_message = "Web surfing web player payout, parameter aff_id is empty.";
			$errorHelper->serviceError($mail_message, $log_message);
			return null;
		}
		if(!isset($player_id)){
			$errorHelper = new ErrorHelper();
			$log_message = $mail_message = "Web surfing web player payout, parameter player_id is empty.";
			$errorHelper->serviceError($mail_message, $log_message);
			return null;
		}
		if(!isset($amount)){
			$errorHelper = new ErrorHelper();
			$log_message = $mail_message = "Web surfing web player payout, parametar amount is empty.";
			$errorHelper->serviceError($mail_message, $log_message);
			return null;
		}
		if(!isset($currency)){
			$errorHelper = new ErrorHelper();
			$log_message = $mail_message = "Web surfing web player payout, parameter currency is empty.";
			$errorHelper->serviceError($mail_message, $log_message);
			return null;
		}
		$errorHelper = new ErrorHelper();
		$errorHelper->serviceAccessLog("Web player payout : Session_id = " . $session_id . " Aff ID = " . $aff_id . " Player ID = " . $player_id . " Amount = " . $amount . " Currency = " . $currency);
		try{
			require_once MODELS_DIR . DS . 'TransferCreditModel.php';
			$modelTransferCredits = new TransferCreditModel();
			$modelTransferCredits->transferCreditsFromWebPlayer($session_id, $aff_id, $player_id, $amount, $currency);
			return "1";
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$log_message = $mail_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return INTERNAL_ERROR;
		}
	}
	
	/**
	 * 
	 * payout terminal or pc player
	 * @param int $session_id
	 * @param float $amount
	 * @param string $currency
	 * @param string $duration_limit
     * @return mixed
	 */
	public function terminalPayout($session_id, $amount, $currency, $duration_limit){
		$session_id = strip_tags($session_id);
		$amount = strip_tags($amount);
		$currency = strip_tags($currency);
		$duration_limit = strip_tags($duration_limit);
		if(!isset($session_id)){
			$errorHelper = new ErrorHelper();			
			$log_message = $mail_message = "Web surfing terminal payout, parametar session_id is empty.";
			$errorHelper->serviceError($mail_message, $log_message);
			return null;
		}
		if(!isset($amount)){
			$errorHelper = new ErrorHelper();
			$log_message = $mail_message = "Web surfing terminal payout, parametar amount is empty.";
			$errorHelper->serviceError($mail_message, $log_message);
			return null;
		}
		if(!isset($currency)){
			$errorHelper = new ErrorHelper();
			$log_message = $mail_message = "Web surfing terminal payout, parameter currency is empty.";
			$errorHelper->serviceError($mail_message, $log_message);
			return null;
		}
		$errorHelper = new ErrorHelper();
		$errorHelper->serviceAccessLog("Terminal payout : Session_id = " . $session_id . " Amount = " . $amount . " Currency = " . $currency . " Duration limit = " . $duration_limit);
		try{
			require_once MODELS_DIR . DS . 'TransferCreditModel.php';
			$modelTransferCredits = new TransferCreditModel();
			$web_credits = $modelTransferCredits->terminalPayout($session_id, null, null, $amount, $currency, $duration_limit);
			return $web_credits;
		}catch(Zend_Exception $ex){
			/*if received exception is unknown*/
			$errorHelper = new ErrorHelper();
			$log_message = $mail_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return INTERNAL_ERROR;
		}
	}
	
	/**
	 * 
	 * login to web surfing app
	 * @param string $mac_address
	 * @return mixed
	 */
	public function loginWebLobby($mac_address){
		if(!isset($mac_address)){
			return null;
		}		
		try{
			$mac_address = strip_tags($mac_address);
			require_once MODELS_DIR . DS . 'AuthorizationModel.php';
			$modelAuthorization = new AuthorizationModel();
			$result = $modelAuthorization->loginWebLobby($mac_address);
			if($result == "20173"){
				$errorHelper = new ErrorHelper();
				$log_message = "Bad Mac Address, terminal not registrated or rejected!!! Mac Address = " . $mac_address;
				$mail_message = "Bad Mac Address, terminal not registrated or rejected!!! <br /> Mac Address = " . $mac_address;
				$errorHelper->serviceError($mail_message, $log_message);
				return BAD_MAC_ADDRESS;
			}
			if(is_null($result)){
				return INTERNAL_ERROR;
			}
			$list_urls = array();
			$has_panic = false;
			//if list of urls has www.panic.com then casino is in panic don't show url www.panic.com
			foreach($result[0] as $url){
				if(strtoupper($url['url']) == strtoupper("WWW.PANIC.COM")){
					$has_panic = true;
				}
				else{
					$list_urls[] = new Site($url['id'], $url['name'], $url['status'], $url['url'], $url['url_logo'], $url['hold']);
				}
			}
		$list_urls_copy = array();
		if($has_panic){
			//if casino is in panic then eliminate url casino for games
			$no_items = count($list_urls);
			for($i=0;$i<$no_items;$i++){
				if(strtoupper($list_urls[$i]->url) != strtoupper("CASINO")){
					$list_urls_copy[] = $list_urls[$i];
				}
			}
		}else {
			$list_urls_copy = $list_urls;
		}
		return array("list_urls"=>$list_urls_copy, "duration"=>$result[1], "price"=>$result[2], "currency"=>$result[3], "skin"=>$result[4],
		"started_session_id"=>$result[5], "credit_status"=>$result[6], "show_hide"=>$result[7], "hidden_position"=>$result[8], "language"=>$result[9], "panic_status"=>$result[10]);
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex) . " Mac Address = " . $mac_address;
			$mail_message = CursorToArrayHelper::getExceptionTraceAsString($ex) . " <br /> Mac Address = " . $mac_address;
			$errorHelper->serviceError($mail_message, $log_message);
			return INTERNAL_ERROR;
		}		
	}
	
	/**
	 * 
	 * transfer credits via usb key to terminal
	 * @param int $session_id
	 * @param float $credits
	 * @return mixed
	 */
	public function usbCreditTransfer($session_id, $credits){
		require_once MODELS_DIR . DS . 'TransferCreditModel.php';
		if(!isset($session_id) || !isset($credits)){
			return null;
		}		
		try{	
			$session_id = strip_tags($session_id);
			$credits = strip_tags($credits);
			$modelTransferCredit = new TransferCreditModel();
			$arrData = $modelTransferCredit->usbCreditTransfer($session_id, $credits);
			if($arrData == INTERNAL_ERROR){
				return INTERNAL_ERROR;
			}
			unset($modelTransferCredit);
			if(is_null($arrData)){
				//sends email when usb credit transaction fails
				$errorHelper = new ErrorHelper();
				$log_message = "USB credit transaction for web surfing with Session_id = " . $session_id . " has failed!!!";
				$mail_message = "USB credit transaction for web surfing with Session_id = " . $session_id . " has failed!!!";
				$errorHelper->serviceError($mail_message, $log_message);
				return null;
			}
			return $arrData;
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = $log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return INTERNAL_ERROR;
		}		
	}
}