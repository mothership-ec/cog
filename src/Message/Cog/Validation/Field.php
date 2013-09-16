<?php

namespace Message\Cog\Validation;

/**
 * Validator
 * @package Message\Cog\Validation
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Field
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

	public function optional()
	{
		$this->optional = true;

		return $this;
	}
}