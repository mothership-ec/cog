<?php

namespace Message\Cog\Validation;

/**
 * A wrapper around Respect\Validation which makes using it easier
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

	public function __construct()
	{
		$this->_messages = new Messages;
		$this->_loadRules();
	}

	/**
	 * Configures Loader class
	 *
	 * @return $this
	 */
	protected function _loadRules()
	{
		$this->_loader = new Loader($this, $this->_messages, array(
			'Message\\Cog\\Validation\\Rule\\Date',
			'Message\\Cog\\Validation\\Rule\\Number',
			'Message\\Cog\\Validation\\Rule\\Iterable',
			'Message\\Cog\\Validation\\Rule\\Text',
			'Message\\Cog\\Validation\\Rule\\Other',
			'Message\\Cog\\Validation\\Filter\\Text',
			'Message\\Cog\\Validation\\Filter\\Type',
			'Message\\Cog\\Validation\\Filter\\Other',
		));

		return $this;
	}

	/**
	 * @return Loader
	 */
	public function getLoader()
	{
		return $this->_loader;
	}

	/**
	 * Get data that has been submitted
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Get fields for data to be passed to
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->_fields;
	}

	/**
	 * Add a field to be validated
	 *
	 * @param string $name
	 * @param bool $readableName - how the name will appear in error messages etc.
	 * @return $this
	 */
	public function field($name, $readableName = false)
	{
		if(!isset($this->_fields[$name])) {
			$this->_createField($name, $readableName);
		}
		$this->_fieldPointer = &$this->_fields[$name];

		return $this;
	}

	/**
	 * @param string $message
	 * @return $this
	 */
	public function error($message)
	{
		$this->_rulePointer[4] = $message;

		return $this;
	}

	/**
	 * Determine that a field is optional
	 *
	 * @return $this
	 */
	public function optional()
	{
		$this->_fieldPointer['optional'] = true;

		return $this;
	}

	/**
	 * Runs through assigned rules and filters for validation
	 * @todo work on making this process simpler
	 *
	 * @param $methodName
	 * @param $args
	 * @return $this
	 * @throws \Exception
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
	 * @param string $methodName
	 * @return array - returns two variables in an array, use list() to make result more manageable
	 *      $filterPrecendence is passed to the _setPointers() method to determine when a filter should be applied
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
	 * @param string $methodName
	 * @param string $precendence - Filter precendence i.e. pre or post
	 * @param array $args
	 * @param bool $invertResult - has 'not' been set on the rule?
	 * @return $this
	 * @throws \Exception
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
	 * @param array $data
	 * @return bool
	 */
	public function validate(array $data)
	{
		$this->_data = $data;
		$this->_messages->clear();
		$this->_applyFilters('pre')
			->_applyRules()
			->_applyFilters('post')
			->_cleanData();

		return count($this->getMessages()) == 0;
	}

	/**
	 * @return array
	 */
	public function getMessages()
	{
		return $this->_messages->get();
	}

	/**
	 * @return $this
	 */
	protected function _cleanData()
	{
		$data = array();

		foreach($this->_data as $key => $value) {
			if(isset($this->_fields[$key])) {
				$data[$key] = $value;
			}
		}

		$this->_data = $data;

		return $this;
	}

	/**
	 * @param $type
	 * @return $this
	 */
	protected function _applyFilters($type)
	{
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
	 * @todo refactor into something less complex and more readable
	 *
	 * @return $this
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
	 * @param $name
	 * @param $field
	 * @return $this
	 */
	protected function _setRequiredError($name, $field)
	{
		// Check if data has been submitted
		$notSet = (!isset($this->_data[$name]) || $this->_data[$name] == '');

		if($notSet && !$field['optional']) {
			$this->_messages->addError($name, $field['readableName'].' is a required field.');
		}

		return $this;
	}

	/**
	 * @param $name
	 * @param $field
	 * @return $this
	 */
	protected function _setMessages($name, $field)
	{
		foreach($field['rules'] as $rule) {
			list($ruleName, $func, $args, $invertResult, $error) = $rule;

			array_unshift($args, $this->_data[$name]);
			$result = call_user_func_array($func, $args);

			if($invertResult) {
				$result = !$result;
			}

			if(!$result) {
				$this->_messages->addFromRule($field, $rule);
			}
		}

		return $this;
	}

	/**
	 * @param $name
	 * @param bool $readableName
	 * @return $this
	 */
	protected function _createField($name, $readableName = false)
	{
		if($readableName === false) {
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