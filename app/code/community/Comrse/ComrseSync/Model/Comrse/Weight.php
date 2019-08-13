<?php

class Comrse_ComrseSync_Model_Comrse_Weight
{

	//-----------------------------------------------------
	// VARS & CONSTANTS
	//-----------------------------------------------------
	private $weight;
	private $unit_of_measure;

	//-----------------------------------------------------
	// GETTERS & SETTERS
	//-----------------------------------------------------
	public function getWeight(){
		return $this->weight;
	}

	public function setWeight($weight){
		$this->weight = $weight;
		return $this;
	}

	public function getUnitOfMeasure(){
		return $this->unit_of_measure;
	}

	public function setUnitOfMeasure($unit_of_measure){
		$this->unit_of_measure = $unit_of_measure;
		return $this;
	}

	//-----------------------------------------------------
	// SETUP
	//-----------------------------------------------------
	public function __construct() {
		// do nothing
		$this->unit_of_measure = "POUNDS";
	}

	//-----------------------------------------------------
	// PUBLIC METHODS
	//-----------------------------------------------------
	public function toArray() {
		return get_object_vars($this);
	}


}