<?php
class Comrse_ComrseSync_Model_Comrse_MetricEvent {

	private $brand_id;
	private $activity_group_id;
	private $activity_source;
	private $activity_channel;
	private $activity;
	private $activity_timestamp;
	private $ip_address;
	private $product_id;
	private $external_product_id;
	private $product_name;
	private $product_price;
	private $product_sale_price;
	private $product_quantity;
	private $product_options;
	private $email;
	private $customer_name;


	public function getBrandId(){
		return $this->brand_id;
	}

	public function setBrandId($brand_id){
		$this->brand_id = $brand_id;
		return $this;
	}

	public function getActivityGroupId(){
		return $this->activity_group_id;
	}

	public function setActivityGroupId($activity_group_id){
		$this->activity_group_id = $activity_group_id;
		return $this;
	}

	public function getActivitySource(){
		return $this->activity_source;
	}

	public function setActivitySource($activity_source){
		$this->activity_source = $activity_source;
		return $this;
	}

	public function getActivityChannel(){
		return $this->activity_channel;
	}

	public function setActivityChannel($activity_channel){
		$this->activity_channel = $activity_channel;
		return $this;
	}

	public function getActivity(){
		return $this->activity;
	}

	public function setActivity($activity){
		$this->activity = $activity;
		return $this;
	}

	public function getActivityTimestamp(){
		return $this->activity_timestamp;
	}

	public function setActivityTimestamp($activity_timestamp){
		$this->activity_timestamp = $activity_timestamp;
		return $this;
	}

	public function getIpAddress(){
		return $this->ip_address;
	}

	public function setIpAddress($ip_address){
		$this->ip_address = $ip_address;
		return $this;
	}

	public function getProductId(){
		return $this->product_id;
	}

	public function setProductId($product_id){
		$this->product_id = $product_id;
		return $this;
	}

	public function getExternalProductId(){
		return $this->external_product_id;
	}

	public function setExternalProductId($external_product_id){
		$this->external_product_id = $external_product_id;
		return $this;
	}

	public function getProductName(){
		return $this->product_name;
	}

	public function setProductName($product_name){
		$this->product_name = $product_name;
		return $this;
	}

	public function getProductPrice(){
		return $this->product_price;
	}

	public function setProductPrice($product_price){
		$this->product_price = number_format($product_price, 2);
		return $this;
	}

	public function getProductSalePrice(){
		return $this->product_sale_price;
	}

	public function setProductSalePrice($product_sale_price){
		$this->product_sale_price =  number_format($product_sale_price, 2);
		return $this;
	}

	public function getProductQuantity(){
		return $this->product_quantity;
	}

	public function setProductQuantity($product_quantity){
		$this->product_quantity = number_format($product_quantity);
		return $this;
	}

	public function getProductOptions(){
		return $this->product_options;
	}

	public function setProductOptions($product_options){
		$this->product_options = $product_options;
		return $this;
	}

	public function getEmail(){
		return $this->email;
	}

	public function setEmail($email){
		$this->email = $email;
		return $this;
	}

	public function getCustomerName(){
		return $this->customer_name;
	}

	public function setCustomerName($customer_name){
		$this->customer_name = $customer_name;
		return $this;
	}

	public function addProductOption($mageProductOption) {
		$this->product_options[$mageProductOption['label']] = $mageProductOption['value'];
		return $this;
	}

	public function send($orgData) {
		#if ($orgData)
		#	$sendMetrics = Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::MCS_API_PATH, $orgData, $this->toArray(true));
	}


	function __construct() {
		$this->setActivitySource("Platform");
		$this->setActivityChannel("DotCom");
		$this->setActivity("Buy");
	}

	public function toArray($encode = false) {
		if ($encode)
			return json_encode(get_object_vars($this));

		return get_object_vars($this);
	}
}