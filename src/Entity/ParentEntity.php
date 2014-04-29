<?php

class ParentEntity
{

	public function setEntities(array $)

	// a way to be told about my entities and their factories
	//
	// a way to get them on request
	//
	public function getEntities($name)
	{

	}
}

class Order extends ParentEntity
{
	public function __get($var)
	{
		return $this->getEntities($var);
	}
}

class OrderFactory
{
	public function create()
	{
		$order = new Order;
		$order->setEntities($this->_entities);
	}
}