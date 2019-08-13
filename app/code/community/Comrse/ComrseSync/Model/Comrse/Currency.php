<?php
class Comrse_ComrseSync_Model_Comrse_Currency
{

	private $currency_code;

	public function getCurrencyCode(){
		return $this->currency_code;
	}

	public function setCurrencyCode($currency_code){
		$this->currency_code = $currency_code;
		return $this;
	}

	//-----------------------------------------------------
	// SETUP
	//-----------------------------------------------------
	public function __construct() {
		$this->currency_code = "USD";
	}

	public function toArray($encode = false) {
		if ($encode)
			return json_encode(get_object_vars($this));

		return get_object_vars($this);
	}

}