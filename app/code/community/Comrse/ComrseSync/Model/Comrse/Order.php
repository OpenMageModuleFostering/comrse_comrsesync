<?php

class Comrse_ComrseSync_Model_Comrse_Order {

  private $id;
  private $external_source;
  private $external_id;
  private $status;
  private $submit_date;
  private $ip_address;
  private $total_tax;
  private $total_shipping;
  private $sub_total;
  private $total;
  private $customer;
  private $order_items;
  private $order_attributes;
  private $addresses;

  public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
		return $this;
	}

	public function getExternalSource(){
		return $this->external_source;
	}

	public function setExternalSource($external_source){
		$this->external_source = $external_source;
		return $this;
	}

	public function getExternalId(){
		return $this->external_id;
	}

	public function setExternalId($external_id){
		$this->external_id = $external_id;
		return $this;
	}

	public function getStatus(){
		return $this->status;
	}

	public function setStatus($status){
		$this->status = $status;
		return $this;
	}

	public function getSubmitDate(){
		return $this->submit_date;
	}

	public function setSubmitDate($submit_date){
		$this->submit_date = date(Comrse_ComrseSync_Model_Config::DATE_FORMAT, strtotime($submit_date));
		return $this;
	}

	public function getIpAddress(){
		return $this->ip_address;
	}

	public function setIpAddress($ip_address){
		$this->ip_address = $ip_address;
		return $this;
	}

	public function getTotalTax(){
		return $this->total_tax;
	}

	public function setTotalTax($total_tax){
		$this->total_tax = Mage::getModel('comrsesync/Comrse_Total')->setAmount($total_tax)->toArray();
		return $this;
	}

	public function getTotalShipping(){
		return $this->total_shipping;
	}

	public function setTotalShipping($total_shipping){
		$this->total_shipping = Mage::getModel('comrsesync/Comrse_Total')->setAmount($total_shipping)->toArray();
		return $this;
	}

	public function getSubTotal(){
		return $this->sub_total;
	}

	public function setSubTotal($sub_total){
		$this->sub_total = Mage::getModel('comrsesync/Comrse_Total')->setAmount($sub_total)->toArray();
		return $this;
	}

	public function getTotal(){
		return $this->total;
	}

	public function setTotal($total){
		$this->total = Mage::getModel('comrsesync/Comrse_Total')->setAmount($total)->toArray();
		return $this;
	}

	public function getCustomer(){
		return $this->customer;
	}

	public function setCustomer($customer){
		$this->customer = $customer;
		return $this;
	}

	public function getOrderItems(){
		return $this->order_items;
	}

	public function setOrderItems($order_items){
		$this->order_items = $order_items;
		return $this;
	}

	public function getAddresses(){
		return $this->addresses;
	}

	public function setAddresses($addresses){
		$this->addresses = $addresses;
		return $this;
	}

	public function addOrderItem($order_item) {
		$this->order_items[] = $order_item;
	}

	public function addAddress($address) {
		$this->addresses[] = $address;
	}

	public function __construct() {
		$this->external_source = "DotCom";
		$this->status = "SUBMITTED";
		$this->order_items = array();
	}

	public function toArray($encode = false) {
		if ($encode)
			return json_encode(get_object_vars($this));

		return get_object_vars($this);
	}

}