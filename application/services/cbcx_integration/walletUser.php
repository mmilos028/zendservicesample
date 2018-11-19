<?php
/**
* @xsd complexType
* @xsd sequence
*/
class walletUser{
	/**
	*	@var double
	*/
	public $balance = 0.00;
	/**
	*	@var string
	*/
	public $currencyID = '';
	/**
	*	@var float
	*/
	public $exchangeRate = 0.00;
	/**
	*	@var string
	*/
	public $firstName = '';
	/**
	*	@var string
	*/
	public $languageID = '';
	/**
	*	@var string
	*/
	public $lastName = '';
	/**
	*	@var string
	*/
	public $title = '';
	/**
	*	@var string
	*/
	public $userID = '';
	/**
	*	@var string
	*/
	public $webshopUID = '';
}