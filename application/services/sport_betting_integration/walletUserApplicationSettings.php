<?php
require_once SERVICES_DIR . DS . 'sport_betting_integration' . DS . 'setting.php';
/**
* @xsd complexType
* @xsd sequence
*/
class walletUserApplicationSettings{
	/**
	* @var string
	*/
	public $application = '';
	
	/**
	*
	* @var setting
	*/
	public $settings;
}