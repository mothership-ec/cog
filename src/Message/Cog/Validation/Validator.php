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
	protected $_loader;
	protected $_messages;

	public function __construct()
	{
		$this->_messages = new Messages;
		$this->_loadRules();
	}

	protected function _loadRules()
	{
		$this->_loader = new Loader($this->_messages, array(
		//	'Message\\Cog\\Validation\\Rules\\Date',
		  	'Message\\Cog\\Validation\\Rules\\Number',
		  	'Message\\Cog\\Validation\\Rules\\Iterable',
		  	'Message\\Cog\\Validation\\Rules\\Text',
		  	'Message\\Cog\\Validation\\Filter\\Text',
		  	'Message\\Cog\\Validation\\Filter\\Type',
		  	'Message\\Cog\\Validation\\Filter\\Other',
		));
	}

	public function getLoader()
	{
		return $this->_loader;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function field($name, $readableName = false)
	{
		if(!isset($this->_fields[$name])) {
			$this->_createField($name, $readableName);
		}
		$this->_fieldPointer = &$this->_fields[$name];

		return $this;
	}

	public function error($message)
	{
		$this->_rulePointer[4] = $message;

		return $this;
	}

	public function optional()
	{
		$this->_fieldPointer['optional'] = true;

		return $this;
	}

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
		} else if(substr($methodName, -6) === 'Before') {
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

	public function validate($data)
	{
		$this->_data = $data;
		$this->_messages->clear();
		$this->_applyFilters('pre');
		$this->_applyRules();
		$this->_applyFilters('post');
		$this->_cleanData();


		return count($this->getMessages()) == 0;
	}

	public function getMessages()
	{
		return $this->_messages->get();
	}

	protected function _cleanData()
	{
		$data = array();

		foreach($this->_data as $key => $value) {
			if(isset($this->_fields[$key])) {
				$data[$key] = $value;
			}
		}

		$this->_data = $data;
	}

	protected function _applyFilters($type)
	{
		foreach($this->_fields as $name => $field) {
			foreach($field['filters'][$type] as $filter) {
				list($ruleName, $func, $args) = $filter;
				array_unshift($args, $this->_data[$name]);
				$result = call_user_func_array($filter[1], $args);
				$this->_data[$name] = $result;
			}
		}
	}

	protected function _applyRules()
	{
		foreach($this->_fields as $name => $field) {

			// Check emptyness if optional
			if((!isset($this->_data[$name]) || $this->_data[$name] == '') && !$field['optional']) {
				$this->_messages->addError($name, $field['readableName'].' is a required field.');
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
	}

	protected function _createField($name, $readableName = false)
	{
		if($readableName === false) {
			$readableName = str_replace('_', ' ', $name);
			$readableName = preg_replace(array('/(?<=[^A-Z])([A-Z])/', '/(?<=[^0-9])([0-9])/'), ' $0', $readableName);
			$readableName = ucwords($readableName);
		}

		$this->_fields[$name] = array(
			'name'         => $name,
			'readableName' => $readableName,
			'optional'	   => false,
			'rules'        => array(),
			'filters'      => array(
				'pre'	=> array(),
				'post'  => array(),
			),
		);
	}



}


/*
$validator = new \Mothership\Framework\Validator;

$validator
	->field('first_name') // Add a required field to the validator.
		->optional() // This makes it optional if the field is empt
		->alnum() // must be alpha numeric
		->length(3, 15) // between 3 and 15 characters
		->capitalize() // capitalise each word before validation runs
		->trimAfter() // trim the field after its been validated
	->field('email', 'Email Address') // second parameter is the human readable field name, when ommited the human readable name is generated from the field name.
		->email() // field must be in the format of an email
	->field('username')
		->length(1, 16) // must be between 1 and 15 chars
		->trim() // trim the field before validation.
		->match('/[A-Za-z_]/') // validated the field against a regex
			->error('Your username can only contain letters and underscores') // a custom error message should the validation fail
		->custom(function($value, $fields, $validator){ // custom validation rules
			if(user_exists($value)) {
				return 'This username is already taken.';
			}

			return true;
		})
	->field('units')
		->each(function($key, $value){
			
		})


$validator->setData($_POST); // sets the data to be input into the validator.
$validator->validate(); // returns true or false
$validator->getData(); // gets the validated (and filtered) data
$validator->getErrors(); // returns an array of the errors that might have occured.
*/