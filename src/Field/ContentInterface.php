<?php

namespace Message\Cog\Field;

interface ContentInterface extends \IteratorAggregate, \Countable
{

	/**
	 * Gets the field at value $key
	 * 
	 * @param  String $key                     The key to get the value for
	 * @return FieldInterface|RepeatableGroup  The field or group
	 */
	public function get($key);

	/**
	 * Set the value at Key
	 * 
	 * @param String                         $key The key to set
	 * @param FieldInterface|RepeatableGroup $val The Field or group to set the field to
	 */
	public function set($key, $val);
}