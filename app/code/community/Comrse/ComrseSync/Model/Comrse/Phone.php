<?php
class Comrse_ComrseSync_Model_Comrse_Phone {

	private $id;
	private $phone_number;
	private $is_active;
	private $is_default;
	private $context;

	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
		return $this;
	}

	public function getPhoneNumber(){
		return $this->phone_number;
	}

	public function setPhoneNumber($phone_number){
		$this->phone_number = $phone_number;
		return $this;
	}

	public function getIsActive(){
		return $this->is_active;
	}

	public function setIsActive($is_active){
		$this->is_active = $is_active;
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