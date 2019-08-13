<?php
class Comrse_ComrseSync_Model_Comrse_Total {

	private $amount;
	private $currency;

	function __construct(){
		$this->setCurrency();
	}

	public function getAmount(){
		return $this->amount;
	}

	public function setAmount($amount){
		$this->amount = number_format($amount, 2);
		return $this;
	}

	public function getCurrency(){
		return $this->currency;
	}

	public function setCurrency($currency){
		$this->currency = Mage::getModel('comrsesync/Comrse_Currency')->toArray();
		return $this;
	}

	public function toArray($encode = false) {
		if ($encode)
			return json_encode(get_object_vars($this));

		return get_object_vars($this);
	}

}