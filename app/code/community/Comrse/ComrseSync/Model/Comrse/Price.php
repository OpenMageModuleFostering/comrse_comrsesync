<?php

class Comrse_ComrseSync_Model_Comrse_Price
{

	//-----------------------------------------------------
	// VARS & CONSTANTS
	//-----------------------------------------------------
	private $amount;
	private $currency;

	//-----------------------------------------------------
	// GETTERS & SETTERS
	//-----------------------------------------------------
	public function getAmount(){
		return $this->amount;
	}

	public function setAmount($amount){
		$this->amount = number_format($amount, 2, '.', '');
		return $this;
	}

	public function getCurrency(){
		return $this->currency;
	}

	public function setCurrency($currency){
		$this->currency = $currency;
		return $this;
	}

	//-----------------------------------------------------
	// SETUP
	//-----------------------------------------------------
	public function __construct() {
		$this->currency = Mage::getModel('comrsesync/Comrse_Currency')->toArray();
	}

	//-----------------------------------------------------
	// PUBLIC METHODS
	//-----------------------------------------------------
	public function toArray() {
		return get_object_vars($this);
	}


}