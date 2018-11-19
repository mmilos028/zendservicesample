<?php
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'betting_integration' . DS . 'walletErrorCode.php';
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'betting_integration' . DS . 'walletExceptionBean.php';

/**
*
* @xsd complexType
* @xsd sequence
*/
class WalletException extends Zend_Exception{
	
	/**
	*
	* @var walletExceptionBean
	*/
	public $faultInfo;
	
	/**
	*
	* @param string $message
	* @param walletExceptionBean $faultInfo
	*/
	public function __construct($message, $faultInfo){
		//parent::__construct($faultInfo->info, $faultInfo->errorCode);
		$this->faultInfo = $faultInfo;
		$this->message = $message;
		$this->code = $faultInfo->errorCode;
		$this->file = $faultInfo->info;
		$this->line = $faultInfo->info;
		$this->string = $faultInfo->info;
		$this->trace = $faultInfo->info;
	}
}
?>