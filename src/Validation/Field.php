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
	public $requiredError;
	public $repeatable = false;
	public $rules = array();
	public $filters = array('pre' => array(), 'post' => array());
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

	public function merge(Field $field)
	{
		$this->readableName = $field->readableName ?: $this->readableName;
		$this->requiredError = $field->requiredError ?: $this->requiredError;
		$this->optional     = $field->optional   || $this->optional;
		$this->repeatable   = $field->repeatable || $this->repeatable;

		$this->rules    = array_merge($this->rules, $field->rules);
		$this->filters  = array_merge_recursive($this->filters, $field->filters);
		$this->children = array_merge($this->children, $field->children);

		return $this;
	}
}