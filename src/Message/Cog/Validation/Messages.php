<?php

namespace Message\Cog\Validation;

/**
* Messages
*/
class Messages
{
	protected $_defaults = array();
	protected $_fields = array();

	public function get()
	{
		return $this->_fields;
	}

	public function clear()
	{
		$this->_fields = array();
	}

	public function getDefaultErrorMessage($ruleName)
	{
		return $this->_defaults[$ruleName]; 
	}

	public function setDefaultErrorMessage($ruleName, $message)
	{
		$this->_defaults[$ruleName] = $message; 
	}

	public function addFromRule($field, $rule)
	{
		list($ruleName, $func, $args, $invertResult, $error) = $rule;

		if(!$error) {
			$error = $this->_defaults[$ruleName];
		}

		$params = array_merge(array($field['readableName'], ($invertResult ? ' not': '')), $args);

		// Parse the error
		$formatted = vsprintf($error, $params);

		$this->addError($field['name'], $formatted);
	}

	public function addError($fieldName, $error)
	{
		if(!isset($this->_fields[$fieldName])) {
			$this->_fields[$fieldName] = array();
		}

		$this->_fields[$fieldName][] = $error;

		return $this;
	}
}