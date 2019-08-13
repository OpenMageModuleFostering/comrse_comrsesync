<?php

class Comrse_ComrseSync_Model_Comrse_Customer {

  private $first_name;
  private $last_name;
  private $email_address;

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

	public function toArray($encode = false) {
		if ($encode)
			return json_encode(get_object_vars($this));

		return get_object_vars($this);
	}

}