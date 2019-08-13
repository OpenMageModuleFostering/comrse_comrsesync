<?php

class Comrse_ComrseSync_Model_Comrse_OrderItem {

	private $id;
	private $order_id;
	private $external_id;
	private $name;
	private $quantity;
	private $retail_price;
	private $sale_price;
	private $category_id;
	private $is_bundle;
	private $sku_id;
	private $sku_external_id;
	private $product_id;
	private $status;
	private $order_item_attributes;
	private $order_item_price_details;
	private $bundle_items;
	private $qualifiers;
	private $context;

    

	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
		return $this;
	}

	public function getOrderId(){
		return $this->order_id;
	}

	public function setOrderId($order_id){
		$this->order_id = $order_id;
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

	public function getQuantity(){
		return $this->quantity;
	}

	public function setQuantity($quantity){
		$this->quantity = $quantity;
		return $this;
	}

	public function getRetailPrice(){
		return $this->retail_price;
	}

	public function setRetailPrice($retail_price){
		$this->retail_price = Mage::getModel('comrsesync/Comrse_Total')->setAmount($retail_price)->toArray();
		return $this;
	}

	public function getSalePrice(){
		return $this->sale_price;
	}

	public function setSalePrice($sale_price){
		$this->sale_price = Mage::getModel('comrsesync/Comrse_Total')->setAmount($sale_price)->toArray();
		return $this;
	}

	public function getCategoryId(){
		return $this->category_id;
	}

	public function setCategoryId($category_id){
		$this->category_id = $category_id;
		return $this;
	}

	public function getIsBundle(){
		return $this->is_bundle;
	}

	public function setIsBundle($is_bundle){
		$this->is_bundle = $is_bundle;
		return $this;
	}

	public function getSkuId(){
		return $this->sku_id;
	}

	public function setSkuId($sku_id){
		$this->sku_id = $sku_id;
		return $this;
	}

	public function getSkuExternalId(){
		return $this->sku_external_id;
	}

	public function setSkuExternalId($sku_external_id){
		$this->sku_external_id = $sku_external_id;
		return $this;
	}

	public function getProductId(){
		return $this->product_id;
	}

	public function setProductId($product_id){
		$this->product_id = $product_id;
		return $this;
	}

	public function getStatus(){
		return $this->status;
	}

	public function setStatus($status){
		$this->status = $status;
		return $this;
	}

	public function getOrderItemAttributes(){
		return $this->order_item_attributes;
	}

	public function setOrderItemAttributes($order_item_attributes){
		$this->order_item_attributes = $order_item_attributes;
		return $this;
	}

	public function getOrderItemPriceDetails(){
		return $this->order_item_price_details;
	}

	public function setOrderItemPriceDetails($order_item_price_details){
		$this->order_item_price_details = $order_item_price_details;
		return $this;
	}

	public function getBundleItems(){
		return $this->bundle_items;
	}

	public function setBundleItems($bundle_items){
		$this->bundle_items = $bundle_items;
		return $this;
	}

	public function getQualifiers(){
		return $this->qualifiers;
	}

	public function setQualifiers($qualifiers){
		$this->qualifiers = $qualifiers;
		return $this;
	}

	public function getContext(){
		return $this->context;
	}

	public function setContext($context){
		$this->context = $context;
		return $this;
	}

	public function addOrderItemAttribute($itemOption){
		$this->order_item_attributes[] = Mage::getModel('comrsesync/Comrse_OrderItemAttribute')->setName($itemOption['label'])->setValue($itemOption['value'])->toArray();
		return $this;
	}

	public function toArray($encode = false) {
		if ($encode)
			return json_encode(get_object_vars($this));

		return get_object_vars($this);
	}

	public function __construct() {
		$this->is_bundle = "false";
	}

}