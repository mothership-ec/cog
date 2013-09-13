<?php

namespace Message\Cog\Validation;

/**
 * Validator
 * @package Message\Cog\Validation
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Field implements \Iterator
{
	public $name;
	public $readableName;
	public $optional = false;
	public $rules = array();
	public $filters = array('pre' => array(), 'post' => array());
	public $pre = array();
	public $post = array();
	public $children = array();

	public function __construct($name, $readableName = false)
	{
		if ($readableName === false) {
			$readableName = str_replace('_', ' ', $name);
			$readableName = preg_replace(array('/(?<=[^A-Z])([A-Z])/', '/(?<=[^0-9])([0-9])/'), ' $0', $readableName);
			$readableName = ucwords($readableName);
		}

		$this->name = $name;
		$this->readableName = $readableName;

		return $this;
	}

	public function getIterator()
	{
		return new \RecursiveArrayIterator($children);
	}

	public function getFilter($type)
	{
		if ($type === 'pre') {
			return $this->pre;
		} elseif ($type === 'post') {
			return $this->post;
		} else {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $type must be either \'pre\' or \'post\', \'' . $type . '\' given');
		}
	}

	public function optional()
	{
		$this->optional = true;

		return $this;
	}

	public function current()
	{
		return current($this->children);
	}

	public function key()
	{
		return key($this->children);
	}

	public function next()
	{
		return next($this->children);
	}

	public function rewind()
	{
		return reset($this->children);
	}

	public function valid()
	{
		return array_key_exists(key($this->children), $this->children);
	}
}