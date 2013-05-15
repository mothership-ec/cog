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
	 * @return array        Return array of fields
	 */
	public function get()
	{
		return $this->_fields;
	}

	/**
	 * Resets array of fields
	 *
	 * @return Messages     Returns $this for chainability
	 */
	public function clear()
	{
		$this->_fields = array();

		return $this;
	}

	/**
	 * Get the default error message for a certain rule
	 *
	 * @param string $ruleName      The rule whom the error message belongs to
	 *
	 * @return string               Returns the error message for $ruleName
	 */
	public function getDefaultErrorMessage($ruleName)
	{
		return $this->_defaults[$ruleName]; 
	}

	/**
	 * Set the default error message for a certain rule
	 *
	 * @param string $ruleName      The rule whom the error message belongs to
	 * @param string $message       The error message to set
	 * @return Messages             Returns $this for chainability
	 */
	public function setDefaultErrorMessage($ruleName, $message)
	{
		$this->_defaults[$ruleName] = $message;

		return $this;
	}

	/**
	 * Adds an error from a rule to a field
	 *
	 * @param string $field     Field to add error message to
	 * @param string $rule      Rule to pull error message from
	 * @return Messages         Returns self for chainability
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
	 * Add an error message to an invalid field
	 *
	 * @param string $fieldName         Field that is invalid
	 * @param string $error             Error message to be added
	 *
	 * @return Messages                 Returns $this for chainability
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