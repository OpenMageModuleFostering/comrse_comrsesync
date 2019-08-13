<?php

class Comrse_ComrseSync_Model_Comrse_AllowedValue
{

	//-----------------------------------------------------
	// VARS & CONSTANTS
	//-----------------------------------------------------
  private $external_id;
  private $attribute_value;
  private $price_adjustment;
  private $product_option_id;
  private $display_order;


  //-----------------------------------------------------
	// GETTERS & SETTERS
	//-----------------------------------------------------
	public function getExternalId(){
		return $this->external_id;
	}

	public function setExternalId($external_id){
		$this->external_id = $external_id;
		return $this;
	}

	public function getAttributeValue(){
		return $this->attribute_value;
	}

	public function setAttributeValue($attribute_value){
		$this->attribute_value = $attribute_value;
		return $this;
	}

	public function getPriceAdjustment(){
		return $this->price_adjustment;
	}

	public function setPriceAdjustment($price_adjustment){
		$this->price_adjustment = $price_adjustment;
		return $this;
	}

	public function getProductOptionId(){
		return $this->product_option_id;
	}

	public function setProductOptionId($product_option_id){
		$this->product_option_id = $product_option_id;
		return $this;
	}

	public function getDisplayOrder(){
		return $this->display_order;
	}

	public function setDisplayOrder($display_order){
		$this->display_order = $display_order;
		return $this;
	}

	//-----------------------------------------------------
	// SETUP
	//-----------------------------------------------------
	public function __construct() {
		// do nothing
	}

	//-----------------------------------------------------
	// PUBLIC METHODS
	//-----------------------------------------------------
	public function toArray() {
		return get_object_vars($this);
	}

}