<?php

class Comrse_ComrseSync_Model_Comrse_OrderItemAttribute {

	private $id;
	private $name;
	private $value;
	private $order_item_id;
	private $context;

	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
		return $this;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
		return $this;
	}

	public function getValue(){
		return $this->value;
	}

	public function setValue($value){
		$this->value = $value;
		return $this;
	}

	public function getOrderItemId(){
		return $this->order_item_id;
	}

	public function setOrderItemId($order_item_id){
		$this->order_item_id = $order_item_id;
	}

	public function getContext(){
		return $this->context;
	}

	public function setContext($context){
		$this->context = $context;
	}

	public function toArray($encode = false) {
		if ($encode)
			return json_encode(get_object_vars($this));

		return get_object_vars($this);
	}
}