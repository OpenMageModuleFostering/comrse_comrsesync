<?php

class Comrse_ComrseSync_Model_Comrse_ProductCategory
{

  //-----------------------------------------------------
  // VARS & CONSTANTS
  //-----------------------------------------------------
  private $id;
  private $external_id;
  private $name;
  private $description;
  private $active;
  private $url;
  private $url_key;
  private $active_start_date;
  private $active_end_date;
  private $subcategories;
  private $products;
  private $category_attributes;
  private $organization_id;


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

  public function getExternalId(){
    return $this->external_id;
  }

  public function setExternalId($external_id){
    $this->external_id = $external_id;
    return $this;
  }

  public function getName(){
    return $this->name;
  }

  public function setName($name){
    $this->name = $name;
    return $this;
  }

  public function getDescription(){
    return $this->description;
  }

  public function setDescription($description){
    $this->description = $description;
    return $this;
  }

  public function getActive(){
    return $this->active;
  }

  public function setActive($active){
    $this->active = $active;
    return $this;
  }

  public function getUrl(){
    return $this->url;
  }

  public function setUrl($url){
    $this->url = $url;
    return $this;
  }

  public function getUrlKey(){
    return $this->url_key;
  }

  public function setUrlKey($url_key){
    $this->url_key = $url_key;
    return $this;
  }

  public function getActiveStartDate(){
    return $this->active_start_date;
  }

  public function setActiveStartDate($active_start_date){
    $this->active_start_date = $active_start_date;
    return $this;
  }

  public function getActiveEndDate(){
    return $this->active_end_date;
  }

  public function setActiveEndDate($active_end_date){
    $this->active_end_date = $active_end_date;
    return $this;
  }

  public function getSubcategories(){
    return $this->subcategories;
  }

  public function setSubcategories($subcategories){
    $this->subcategories = $subcategories;
    return $this;
  }

  public function getProducts(){
    return $this->products;
  }

  public function setProducts($products){
    $this->products = $products;
    return $this;
  }

  public function getCategoryAttributes(){
    return $this->category_attributes;
  }

  public function setCategoryAttributes($category_attributes){
    $this->category_attributes = $category_attributes;
    return $this;
  }

  public function getOrganizationId(){
    return $this->organization_id;
  }

  public function setOrganizationId($organization_id){
    $this->organization_id = $organization_id;
    return $this;
  }

  public function addSubCategory($subCategory) {
    $this->subcategories[] = $subCategory;
    return $this;
  }

  public function addProduct($productId) {
    $this->products[] = array("external_id" => $productId);
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
  public function toArray($encode = false) {
    if ($encode)
      return json_encode(get_object_vars($this));

    return get_object_vars($this);
  }

}