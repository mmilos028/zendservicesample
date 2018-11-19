<?php
/**
*
* @xsd simpleType
* @xsd restriction array('base' => 'xsd:string')
*/
class walletErrorCode
{

	const UNKNOWN_USERID_CODE = 10;
	const UNKNOWN_USERID = "UNKNOWN_USERID";

	const EXPIRED_SESSIONID_CODE = 11;
	const EXPIRED_SESSIONID = "EXPIRED_SESSIONID";

	const INSUFFICIENT_CREDIT_CODE = 12;
	const INSUFFICIENT_CREDIT = "INSUFFICIENT_CREDIT";

	const UNEXPECTED_ERROR_CODE = 13;
	const UNEXPECTED_ERROR = "UNEXPECTED_ERROR";

	const LOCKED_USER_CODE = 14;
	const LOCKED_USER = "LOCKED_USER";

	const UNKNOWN_BOOKID_CODE = 15;
	const UNKNOWN_BOOKID = "UNKNOWN_BOOKID";

	const UNKNOWN_TICKETID_CODE = 16;
	const UNKNOWN_TICKETID = "UNKNOWN_TICKETID";
	/**
	 * @xsd enumeration array('value' => 'UNKNOWN_USERID')
	 * @xsd enumeration array('value' => 'EXPIRED_SESSIONID')
	 * @xsd enumeration array('value' => 'INSUFFICIENT_CREDIT')
	 * @xsd enumeration array('value' => 'UNEXPECTED_ERROR')
	 * @xsd enumeration array('value' => 'LOCKED_USER')
	 * @xsd enumeration array('value' => 'UNKNOWN_BOOKID')
	 * @xsd enumeration array('value' => 'UNKNOWN_TICKETID')
	 */
	public $enumeration;
}