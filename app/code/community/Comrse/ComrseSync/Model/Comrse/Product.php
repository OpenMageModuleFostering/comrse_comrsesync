<?php

class Comrse_ComrseSync_Model_Comrse_Product
{

	//-----------------------------------------------------
	// VARS & CONSTANTS
	//-----------------------------------------------------
	private $id;
	private $external_id;
	private $name;
	private $long_description;
	private $dimension;
	private $weight;
	private $retail_price;
	private $sale_price;
	private $primary_media;
	private $active;
	private $active_start_date;
	private $active_end_date;
	private $manufacturer;
	private $default_category_id;
	private $product_attributes;
	private $product_options;
	private $media;

	//-----------------------------------------------------
	// GETTERS & SETTERS
	//-----------------------------------------------------
	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
		return $this;
	}

	public function getExternalId(){
		return $this->external_id;
	}

	public function setExternalId($external_id){
		$this->external_id = $external_id;
		return $this;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
		return $this;
	}

	public function getLongDescription(){
		return $this->long_description;
	}

	public function setLongDescription($long_description){
		$this->long_description = $long_description;
		return $this;
	}

	public function getDimension(){
		return $this->dimension;
	}

	public function setDimension($dimension){
		$this->dimension = $dimension;
		return $this;
	}

	public function getWeight(){
		return $this->weight;
	}

	public function setWeight($weight){
		$this->weight = $weight;
		return $this;
	}

	public function getRetailPrice(){
		return $this->retail_price;
	}

	public function setRetailPrice($retail_price){
		$this->retail_price = $retail_price;
		return $this;
	}

	public function getSalePrice(){
		return $this->sale_price;
	}

	public function setSalePrice($sale_price){
		$this->sale_price = $sale_price;
		return $this;
	}

	public function getPrimaryMedia(){
		return $this->primary_media;
	}

	public function setPrimaryMedia($primary_media){
		$this->primary_media = $primary_media;
		return $this;
	}

	public function getActive(){
		return $this->active;
	}

	public function setActive($active){
		$this->active = $active;
		return $this;
	}

	public function getActiveStartDate(){
		return $this->active_start_date;
	}

	public function setActiveStartDate($active_start_date){
		$this->active_start_date = $active_start_date;
		return $this;
	}

	public function getActiveEndDate(){
		return $this->active_end_date;
	}

	public function setActiveEndDate($active_end_date){
		$this->active_end_date = $active_end_date;
		return $this;
	}

	public function getManufacturer(){
		return $this->manufacturer;
	}

	public function setManufacturer($manufacturer){
		$this->manufacturer = $manufacturer;
		return $this;
	}

	public function getDefaultCategoryId(){
		return $this->default_category_id;
	}

	public function setDefaultCategoryId($default_category_id){
		$this->default_category_id = $default_category_id;
		return $this;
	}

	public function getProductAttributes(){
		return $this->product_attributes;
	}

	public function setProductAttributes($product_attributes){
		$this->product_attributes = $product_attributes;
		return $this;
	}

	public function getProductOptions(){
		return $this->product_options;
	}

	public function setProductOptions($product_options){
		$this->product_options = $product_options;
		return $this;
	}

	public function getMedia(){
		return $this->media;
	}

	public function setMedia($media){
		$this->media = $media;
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
	public function toArray($encode = false) {
		if ($encode)
			return json_encode(get_object_vars($this));

		return get_object_vars($this);
	}

}