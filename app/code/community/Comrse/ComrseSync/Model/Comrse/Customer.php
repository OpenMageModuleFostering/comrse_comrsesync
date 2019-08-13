<?php

class Comrse_ComrseSync_Model_Comrse_Customer {

  protected $first_name;
  protected $last_name;
  protected $email_address;
  protected $customer_attributes;
  protected $context;
  protected $external_id;
  protected $org_id;

  public function getFirstName(){
		return $this->first_name;
	}

	public function setFirstName($first_name){
		$this->first_name = $first_name;
		return $this;
	}

	public function getLastName(){
		return $this->last_name;
	}

	public function setLastName($last_name){
		$this->last_name = $last_name;
		return $this;
	}

	public function getEmailAddress(){
		return $this->email_address;
	}

	public function setEmailAddress($email_address){
		$this->email_address = $email_address;
		return $this;
	}

	public function getCustomerAttributes(){
		return $this->customer_attributes;
	}

	public function setCustomerAttributes($customer_attributes){
		$this->customer_attributes = $customer_attributes;
	}

	public function getContext(){
		return $this->context;
	}

	public function setContext($context){
		$this->context = $context;
	}

	public function getExternalId(){
		return $this->external_id;
	}

	public function setExternalId($external_id){
		$this->external_id = $external_id;
		return $this;
	}

	public function getOrgId(){
		return $this->org_id;
	}

	public function setOrgId($org_id){
		$this->org_id = $org_id;
		return $this;
	}

	public function toArray($encode = false) {
		if ($encode)
			return json_encode(get_object_vars($this));

		return get_object_vars($this);
	}

}