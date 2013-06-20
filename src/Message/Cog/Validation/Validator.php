<?php

namespace Message\Cog\Validation;

/**
 * Validator
 * @package Message\Cog\Validation
 *
 * A wrapper around Respect\Validation which makes using it easier
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Validator
{
	protected $_data;
	protected $_fieldPointer;
	protected $_rulePointer;
	protected $_fields = array();

	/**
	 * @var Loader
	 */
	protected $_loader;

	/**
	 * @var Messages
	 */
	protected $_messages;

	/**
	 * Register rules and pull Messages instance from newly created Loader instance
	 */
	public function __construct(Loader $loader)
	{
		$this->_loader = $loader;
		$this->_messages = $this->_loader->getMessages();
	}

	public function __clone()
	{
		foreach($this as $name => $value) { 
			if(is_object($value)) { 
				$this->$name = clone $this->$name;
			}
		}

		// break the references
		unset($this->_fieldPointer);
		unset($this->_rulePointer);
	}

	/**
	 * Get assigned loader class
	 *
	 * @return Loader
	 */
	public function getLoader()
	{
		return $this->_loader;
	}

	/**
	 * Get data that has been submitted
	 *
	 * @return array        Returns data
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Get fields for data to be passed to
	 *
	 * @return array        Returns fields for validation
	 */
	public function getFields()
	{
		return $this->_fields;
	}

	/**
	 * Add a new field to be validated.
	 *
	 * @param  string $name         Name of the field to be added
	 * @param  bool   $readableName If true, a "readable name" is saved, assuming
	 *                              the name is formatted as camel case
	 *
	 * @return Validator            Returns $this for chainability
	 */
	public function field($name, $readableName = false)
	{
		if (!isset($this->_fields[$name])) {
			$this->_createField($name, $readableName);
		}
		$this->_fieldPointer = &$this->_fields[$name];

		return $this;
	}

	/**
	 * Method used to create custom error messages
	 *
	 * @param string $message       Error message to display
	 *
	 * @return Validator            Returns $this for chainability
	 */
	public function error($message)
	{
		$this->_rulePointer[4] = $message;

		return $this;
	}

	/**
	 * Determine that a field is optional
	 *
	 * @return Validator            Returns $this for chainability
	 */
	public function optional()
	{
		$this->_fieldPointer['optional'] = true;

		return $this;
	}

	/**
	 * Runs through assigned rules and filters for validation
	 * @todo see if we can make this process simpler
	 *
	 * @param $methodName       Rule or filter to be called. The result can be inverted by prepending it with 'not'
	 * @param $args             Arguments for rule or filter
	 *
	 * @return Validator        Returns $this for chainability
	 */
	public function __call($methodName, $args)
	{
		$invertResult = false;
		if (substr($methodName, 0, 3) === 'not') {
			$invertResult = true;
			$methodName = lcfirst(substr($methodName, 3));
		}

		list($methodName, $filterPrecendence) = $this->_beforeOrAfter($methodName);

		$this->_setPointers($methodName, $filterPrecendence, $args, $invertResult);

		return $this;
	}

	/**
	 * Determines order in which filters should be applied.
	 *
	 * @param string $methodName        Filter name. If the $methodName ends with 'After' or 'Before', it will
	 *                                  ensure that the filter will be applied after/before validation
	 *
	 * @return array                    Returns two variables in an array, use list() to make result more manageable
	 *                                  $precendence is passed to the _setPointers()method to determine when
	 *                                  a filter should be applied
	 */
	protected function _beforeOrAfter($methodName)
	{
		$precendence = 'pre';

		if (substr($methodName, -5) === 'After') {
			$precendence = 'post';
			$methodName = lcfirst(substr($methodName, 0, -5));
		}
		elseif (substr($methodName, -6) === 'Before') {
			$methodName = lcfirst(substr($methodName, 0, -6));
		}

		return array($methodName, $precendence);
	}

	/**
	 * Method to arrange rules and filters into the correct sets and order
	 *
	 * @param string $methodName    Name of method to have pointer set
	 * @param string $precendence   Filter precendence i.e. pre or post
	 * @param array $args           Arguments to be passed to $methodName
	 * @param bool $invertResult    Has 'not' been set on the rule?
	 * @throws \Exception           Throws exception of the $methodName does not exist
	 *
	 * @return Validation           Returns $this for chainability
	 */
	protected function _setPointers($methodName, $precendence, array $args, $invertResult)
	{
		if ($rule = $this->_loader->getRule($methodName)) {
			$this->_fieldPointer['rules'][] = array($methodName, $rule, $args, $invertResult, '');

			$end = count($this->_fieldPointer['rules']) - 1;
			$this->_rulePointer = &$this->_fieldPointer['rules'][$end];
		}
		elseif ($filter = $this->_loader->getFilter($methodName)) {
			$this->_fieldPointer['filters'][$precendence][] = array($methodName, $filter, $args);
		}
		else {
			throw new \Exception(sprintf('No rule or filter exists named `%s`.', $methodName));
		}

		return $this;
	}

	/**
	 * Method to actually validate the data
	 *
	 * @param array $data       Data to be validated
	 *
	 * @return bool             Returns true if the data is valid
	 */
	public function validate(array $data)
	{
		$this->_data = $data;
		$this->_messages->clear();

		// Run 'pre' filters first, then validate the data, then run the 'post' filters
		$this->_applyFilters('pre')
			->_applyRules()
			->_applyFilters('post')
			->_cleanData();

		return count($this->getMessages()) == 0;
	}

	public function clear()
	{
		$this->_data         = array();
		$this->_fields       = array();
		$this->_fieldPointer = null;
		$this->_rulePointer  = null;
		$this->_messages->clear();
	}

	/**
	 * Returns any error messages that are generated
	 *
	 * @return array
	 */
	public function getMessages()
	{
		return $this->_messages->get();
	}

	/**
	 * Removes any data that is not validated/necessary for security purposes
	 *
	 * @return Validator        Returns $this for chainability
	 */
	protected function _cleanData()
	{
		$data = array();

		foreach ($this->_data as $key => $value) {
			if (isset($this->_fields[$key])) {
				$data[$key] = $value;
			}
		}

		$this->_data = $data;

		return $this;
	}

	/**
	 * Parse data through filters
	 *
	 * @param string $type      'Pre' or 'post' validation - Determines which set of data should be validated
	 * @throws \Exception       Throws exception if $type is not set to 'pre' or 'post'
	 *
	 * @return Validator        Returns $this for chainability
	 */
	protected function _applyFilters($type)
	{
		if (($type !== 'pre') && ($type !== 'post')) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $type must be either \'pre\' or \'post\', \'' . $type . '\' given');
		}

		foreach($this->_fields as $name => $field) {

			// Escape if data field doesn't exist
			if (!isset($this->_data[$name])) {
				continue;
			}

			foreach($field['filters'][$type] as $filter) {

				list($ruleName, $func, $args) = $filter;

				array_unshift($args, $this->_data[$name]);
				$result = call_user_func_array($filter[1], $args);

				$this->_data[$name] = $result;

			}
		}

		return $this;
	}

	/**
	 * Validate data with rules
	 *
	 * @return Validator        Returns $this for chainability
	 */
	protected function _applyRules()
	{
		foreach($this->_fields as $name => $field) {

			$this->_setRequiredError($name, $field);

			if (isset($this->_data[$name])) {
				$this->_setMessages($name, $field);
			}

		}

		return $this;
	}

	/**
	 * Method to check if a field is required and set an error where appropriate
	 *
	 * @param string $name          Name of field
	 * @param array $field          Array of field data and information
	 *
	 * @return Validator            Returns $this for chainability
	 */
	protected function _setRequiredError($name, $field)
	{
		// Check if data has been submitted
		$notSet = (!isset($this->_data[$name]) || $this->_data[$name] === '');

		if ($notSet && !$field['optional']) {
			$this->_messages->addError($name, $field['readableName'].' is a required field.');
		}

		return $this;
	}

	/**
	 * Method that handles the actual validation, and assigns messages to any fields that do
	 *
	 * @param string $name      Name of field
	 * @param array $field      Array of field information
	 *
	 * @return Validator        Returns $this for chainability
	 */
	protected function _setMessages($name, $field)
	{
		foreach($field['rules'] as $rule) {
			list($ruleName, $func, $args, $invertResult, $error) = $rule;

			array_unshift($args, $this->_data[$name]);
			$result = call_user_func_array($func, $args);

			if ($invertResult) {
				$result = !$result;
			}

			if (!$result) {
				$this->_messages->addFromRule($field, $rule);
			}
		}

		return $this;
	}

	/**
	 * Add a new field to be validated
	 *
	 * @param $name                     Name of new field
	 * @param bool $readableName        Readable name for field. If set to false, method will create a name based on
	 *                                  $name
	 *
	 * @return Validator                Returns $this for chainability
	 */
	protected function _createField($name, $readableName = false)
	{
		if ($readableName === false) {
			$readableName = str_replace('_', ' ', $name);
			$readableName = preg_replace(array('/(?<=[^A-Z])([A-Z])/', '/(?<=[^0-9])([0-9])/'), ' $0', $readableName);
			$readableName = ucwords($readableName);
		}

		$this->_fields[$name] = array(
			'name'          => $name,
			'readableName'  => $readableName,
			'optional'	    => false,
			'rules'         => array(),
			'filters'       => array(
			'pre'	        => array(),
			'post'          => array(),
			),
		);

		return $this;
	}
}