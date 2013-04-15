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
	 * @return array
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->_fields;
	}

	/**
	 * @param string $name
	 * @param bool $readableName
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
	 * @return $this
	 */
	public function optional()
	{
		$this->_fieldPointer['optional'] = true;

		return $this;
	}

	/**
	 * @param $methodName
	 * @param $args
	 * @return $this
	 * @throws \Exception
	 */
	public function __call($methodName, $args)
	{
		$invertResult = false;
		if(substr($methodName, 0, 3) === 'not') {
			$invertResult = true;
			$methodName = lcfirst(substr($methodName, 3));
		}

		$filterPrecendence = 'pre';
		if(substr($methodName, -5) === 'After') {
			$filterPrecendence = 'post';
			$methodName = lcfirst(substr($methodName, 0, -5));
		} else if (substr($methodName, -6) === 'Before') {
			$methodName = lcfirst(substr($methodName, 0, -6));
		}

		if($rule = $this->_loader->getRule($methodName)) {
			$this->_fieldPointer['rules'][] = array($methodName, $rule, $args, $invertResult, '');
			// A convoluted way to get a reference to the last array element
			end($this->_fieldPointer['rules']);
			$this->_rulePointer = &$this->_fieldPointer['rules'][key($this->_fieldPointer['rules'])];
		} else if($filter = $this->_loader->getFilter($methodName)) {
			$this->_fieldPointer['filters'][$filterPrecendence][] = array($methodName, $filter, $args);
		} else {
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
	 * @return $this
	 */
	protected function _applyRules()
	{
		foreach($this->_fields as $name => $field) {

			// Check emptyness if optional
			if((!isset($this->_data[$name]) || $this->_data[$name] == '') && !$field['optional']) {
				$this->_messages->addError($name, $field['readableName'].' is a required field.');
			}

			if (!isset($this->_data[$name])) {
				continue;
			}

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