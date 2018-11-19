<?php
class PlayerSessionRowTotal {
	public $totalGames;
	public $cashIn;
	public $cashOut;
	public $gameIn;
	public $gameOut;
	public $netto;
	public $paybackPercent;
	public function __construct($totalGames, $cashIn, $cashOut, $gameIn, $gameOut, $netto, $paybackPercent){
		$this->totalGames = $totalGames;
		$this->cashIn = $cashIn;
		$this->cashOut = $cashOut;
		$this->gameIn = $gameIn;
		$this->gameOut = $gameOut;
		$this->netto = $netto;
		$this->paybackPercent = $paybackPercent;
	}
}