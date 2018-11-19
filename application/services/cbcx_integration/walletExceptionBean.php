<?php
require_once SERVICES_DIR . DS . 'cbcx_integration' . DS . 'walletErrorCode.php';
/**
*
* @xsd complexType
* @xsd sequence
*/
class walletExceptionBean{
	/**
	*	@var walletErrorCode
	*/
	public $errorCode = '';
	/**
	*	@var string
	*/
	public $info = '';
	/**
	*	@var string
	*/
	public $message = '';
}