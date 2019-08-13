<?php

class Comrse_ComrseSync_Model_Comrse_Image
{

	//-----------------------------------------------------
	// VARS & CONSTANTS
	//-----------------------------------------------------
  private $id;
  private $title;
  private $url;
  private $alt_text;
  private $orgId;

  //-----------------------------------------------------
	// GETTERS & SETTERS
	//-----------------------------------------------------
	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
		return $this;
	}

	public function getTitle(){
		return $this->title;
	}

	public function setTitle($title){
		$this->title = $title;
		return $this;
	}

	public function getUrl(){
		return $this->url;
	}

	public function setUrl($url){
		$this->url = $url;
		return $this;
	}

	public function getAltText(){
		return $this->alt_text;
	}

	public function setAltText($alt_text){
		$this->alt_text = $alt_text;
		return $this;
	}

	public function getOrgId(){
		return $this->orgId;
	}

	public function setOrgId($orgId){
		$this->orgId = $orgId;
		return $this;
	}

	//-----------------------------------------------------
	// SETUP
	//-----------------------------------------------------
	public function __construct() {
		// do nothing
	}

	//-----------------------------------------------------
	// PUBLIC METHODS
	//-----------------------------------------------------
	public function toArray() {
		return get_object_vars($this);
	}

}