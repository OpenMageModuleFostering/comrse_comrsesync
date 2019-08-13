<?php
class Comrse_ComrseSync_Model_Comrse_Geo {

	private $name;
	private $abbreviation;
	private $context;

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
		return $this;
	}

	public function getAbbreviation(){
		return $this->abbreviation;
	}

	public function setAbbreviation($abbreviation){
		$this->abbreviation = $abbreviation;
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