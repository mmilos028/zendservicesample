<?php
//mapped currency_text and currency_code from file with array
class CurrencyListHelper {
	private $arrCurrencyList;
	
	public function __construct(){
		$this->arrCurrencyList = $this->getCurrencyList();
	}
	
	//will return array of currency with elements currency_text and currency_code
	//will return null if no file exists
	public function getCurrencyList(){
		$json_string = file_get_contents(HELPERS_DIR . DS . "currency_list.json");
		if(!$json_string){
			return null;
		}
		$arrCurrencyList = json_decode($json_string, true);
		//return associative array
		//currency_text and currency_code element
		return $arrCurrencyList;
	}
	
	//return currency_text for currency_code, ex. for 978 return EUR
	//return empty if no currency_text exists for currency_code
	public function getCurrencyText($currency_code){
		if(!isset($this->arrCurrencyList)){
			return null;
		}
		foreach($this->arrCurrencyList as $curr){
			if($curr['currency_code'] == $currency_code){
				return $curr['currency_text'];
			}
		}
		return null;
	}
	
	//return currency_code for currency_text, ex. for EUR return 978
	//return empty if no currency_code exists for currency_text
	public function getCurrencyCode($currency_text){
		if(!isset($this->arrCurrencyList)){
			return null;
		}
		foreach($this->arrCurrencyList as $curr){
			if($curr['currency_text'] == $currency_text){
				return $curr['currency_code'];
			}
		}
		return null;
	}
}