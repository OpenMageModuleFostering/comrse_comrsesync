<?php

class Comrse_ComrseSync_Model_Comrse_CustomerAddress {
	private $first_name;
	private $last_name;
	private $address_line1;
	private $address_line2;
	private $address_line3;
	private $address_type;
	private $city;
	private $state;
	private $country;
	private $postal_code;
	private $phone_primary;
	private $phone_secondary;
	private $phone_fax;
	private $company_name;
	private $is_business;
	private $is_default;
	private $context;


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

	public function getAddressLine1(){
		return $this->address_line1;
	}

	public function setAddressLine1($address_line1){
		$this->address_line1 = $address_line1;
		return $this;
	}

	public function getAddressLine2(){
		return $this->address_line2;
	}

	public function setAddressLine2($address_line2){
		$this->address_line2 = $address_line2;
		return $this;
	}

	public function getAddressLine3(){
		return $this->address_line3;
	}

	public function setAddressLine3($address_line3){
		$this->address_line3 = $address_line3;
		return $this;
	}

	public function getAddressType(){
		return $this->address_type;
	}

	public function setAddressType($address_type){
		$this->address_type = $address_type;
		return $this;
	}

	public function getCity(){
		return $this->city;
	}

	public function setCity($city){
		$this->city = $city;
		return $this;
	}

	public function getState(){
		return $this->state;
	}

	public function setState($stateId, $stateName = NULL) {
		$this->state = Mage::getModel('comrsesync/Comrse_Geo')->setName($stateName)->setAbbreviation($stateId)->toArray();
		return $this;
	}

	public function getCountry(){
		return $this->country;
	}

	public function setCountry($countryId, $countryName = NULL) {
		$this->country = Mage::getModel('comrsesync/Comrse_Geo')->setName($countryName)->setAbbreviation($countryId)->toArray();
		return $this;
	}

	public function getPostalCode(){
		return $this->postal_code;
	}

	public function setPostalCode($postal_code){
		$this->postal_code = $postal_code;
		return $this;
	}

	public function getPhonePrimary($phone_primary){
		return $phone_primary;
	}

	public function setPhonePrimary($phone_primary) {
		$this->phone_primary = Mage::getModel('comrsesync/Comrse_Phone')->setPhoneNumber($phone_primary)->toArray();
		return $this;
	}

	public function getPhoneSecondary($phone_secondary){
		return $this->phone_secondary;
	}

	public function setPhoneSecondary($phone_secondary){
		$this->phone_secondary = Mage::getModel('comrsesync/Comrse_Phone')->setPhoneNumber($phone_secondary)->toArray();
		return $this;
	}

	public function getPhoneFax($phone_fax){
		return $this->phone_fax;
	}

	public function setPhoneFax($phone_fax){
		$this->phone_fax = Mage::getModel('comrsesync/Comrse_Phone')->setPhoneNumber($phone_fax)->toArray();
		return $this;
	}

	public function getCompanyName(){
		return $this->company_name;
	}

	public function setCompanyName($company_name){
		$this->company_name = $company_name;
		return $this;
	}

	public function getIsBusiness(){
		return $this->is_business;
	}

	public function setIsBusiness($is_business){
		$this->is_business = $is_business;
		return $this;
	}

	public function getIsDefault(){
		return $this->is_default;
	}

	public function setIsDefault($is_default){
		$this->is_default = $is_default;
		return $this;
	}

	public function getContext(){
		return $this->context;
	}

	public function setContext($context){
		$this->context = $context;
		return $this;
	}



	public function toArray($encode = false) {
		if ($encode)
			return json_encode(get_object_vars($this));

		return get_object_vars($this);
	}

}

?>
