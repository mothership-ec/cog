<?php

namespace Message\Cog\Validation;

/**
* Messages
*/
class Messages
{
	protected $_defaults = array();
	protected $_fields = array();

	/**
	 * @return array
	 */
	public function get()
	{
		return $this->_fields;
	}

	/**
	 * @return $this
	 */
	public function clear()
	{
		$this->_fields = array();

		return $this;
	}

	/**
	 * @param string $ruleName
	 * @return string
	 */
	public function getDefaultErrorMessage($ruleName)
	{
		return $this->_defaults[$ruleName]; 
	}

	/**
	 * @param string $ruleName
	 * @param string $message
	 * @return $this
	 */
	public function setDefaultErrorMessage($ruleName, $message)
	{
		$this->_defaults[$ruleName] = $message;

		return $this;
	}

	/**
	 * @param $field
	 * @param $rule
	 * @return $this
	 */
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

		return $this;
	}

	/**
	 * @param $fieldName
	 * @param $error
	 * @return $this
	 */
	public function addError($fieldName, $error)
	{
		if(!isset($this->_fields[$fieldName])) {
			$this->_fields[$fieldName] = array();
		}

		$this->_fields[$fieldName][] = $error;

		return $this;
	}
}