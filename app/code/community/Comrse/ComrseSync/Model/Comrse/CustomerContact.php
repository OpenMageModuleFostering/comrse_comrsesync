<?php

class CustomerContact{
	private $external_source;
	private $id;
	private $customer;
	private $addresses;


	public function getId() {
		return $this->id;
	}


	function __construct() {
		$this->id = "1";
	}


	public function setExternalSource($externalSource) {
		$this->externalSource = $externalSource;
	}
	public function setId($id) {
		$this->id = $id;
	}
	public function setAddresses($addresses) {
		$this->addresses = new stdClass();
		$this->addresses->address = $addresses;
	}
	public function getAddresses() {
		return $this->addresses;
	}

	public function addresses() {
		$addresses = array("addresses" => get_object_vars($this->getAddresses()));
		return $addresses;
	}

	public function setCustomerAttributes($customerAttributes) {
		$this->customerAttributes = $customerAttributes;
	}

	public function setCustomerInfo($id, $firstName, $lastName, $emailAddress, $customerAttributes = array()) {
		$this->customer = array(
			"id" => $id,
			"firstName" => $firstName,
			"lastName" => $lastName,
			"emailAddress" => $emailAddress,
			"customerAttributes" => $customerAttributes
		);
	}

	public function getAddressByType($addressType) {
		if (is_object($this->addresses) && !empty($this->addresses)) {
			foreach($this->addresses->address as $address) {
				if($address['addressType'] == $addressType) {
					return $address;
				}
			}
		}
	}



	public function toArray() {
		return get_object_vars($this);
	}

}
