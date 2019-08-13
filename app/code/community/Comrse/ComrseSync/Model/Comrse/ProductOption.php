<?php

class Comrse_ComrseSync_Model_Comrse_ProductOption
{

	//-----------------------------------------------------
	// VARS & CONSTANTS
	//-----------------------------------------------------
  private $attribute_name;
  private $label;
  private $external_id;
  private $required;
  private $product_option_type;
  private $allowed_values;
  private $display_order;

  //-----------------------------------------------------
	// GETTERS & SETTERS
	//-----------------------------------------------------
	public function getAttributeName(){
		return $this->attribute_name;
	}

	public function setAttributeName($attribute_name){
		$this->attribute_name = $attribute_name;
		return $this;
	}

	public function getLabel(){
		return $this->label;
	}

	public function setLabel($label){
		$this->label = $label;
		return $this;
	}

	public function getExternalId(){
		return $this->external_id;
	}

	public function setExternalId($external_id){
		$this->external_id = $external_id;
		return $this;
	}

	public function getRequired(){
		return $this->required;
	}

	public function setRequired($required){
		$this->required = $required;
		return $this;
	}

	public function getProductOptionType(){
		return $this->product_option_type;
	}

	public function setProductOptionType($product_option_type){
		$this->product_option_type = $product_option_type;
		return $this;
	}

	public function getAllowedValues(){
		return $this->allowed_values;
	}

	public function setAllowedValues($allowed_values){
		$this->allowed_values = $allowed_values;
		return $this;
	}

	public function getDisplayOrder(){
		return $this->displayOrder;
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