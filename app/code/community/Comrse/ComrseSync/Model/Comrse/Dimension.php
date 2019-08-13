<?php

class Comrse_ComrseSync_Model_Comrse_Dimension
{

	//-----------------------------------------------------
	// VARS & CONSTANTS
	//-----------------------------------------------------
	private $height;
	private $width;
	private $depth;
	private $girth;
	private $container;
	private $size;
	private $dimension_unit_of_measure;

	//-----------------------------------------------------
	// GETTERS & SETTERS
	//-----------------------------------------------------
	public function getHeight(){
		return $this->height;
	}

	public function setHeight($height){
		$this->height = $height;
		return $this;
	}

	public function getWidth(){
		return $this->width;
	}

	public function setWidth($width){
		$this->width = $width;
		return $this;
	}

	public function getDepth(){
		return $this->depth;
	}

	public function setDepth($depth){
		$this->depth = $depth;
		return $this;
	}

	public function getGirth(){
		return $this->girth;
	}

	public function setGirth($girth){
		$this->girth = $girth;
		return $this;
	}

	public function getContainer(){
		return $this->container;
	}

	public function setContainer($container){
		$this->container = $container;
		return $this;
	}

	public function getSize(){
		return $this->size;
	}

	public function setSize($size){
		$this->size = $size;
	}

	public function getDimensionUnitOfMeasure(){
		return $this->dimension_unit_of_measure;
	}

	public function setDimensionUnitOfMeasure($dimension_unit_of_measure){
		$this->dimension_unit_of_measure = $dimension_unit_of_measure;
	}

	//-----------------------------------------------------
	// SETUP
	//-----------------------------------------------------
	public function __construct() {
		$this->height = "1";
		$this->width = "1";
		$this->depth = "1";
		$this->girth = "1";
		$this->container = null;
		$this->size = "REGULAR";
		$this->dimension_unit_of_measure = "INCHES";
	}

	//-----------------------------------------------------
	// PUBLIC METHODS
	//-----------------------------------------------------
	public function toArray() {
		return get_object_vars($this);
	}


}