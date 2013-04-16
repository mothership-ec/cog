<?php

namespace Message\Cog\Validation;


/**
* Loads and maintains a set of rules and filters to use in validation
*/
class Loader
{
	
	public function __construct(Validator $validator, Messages $messages, array $classes = null)
	{
		$this->_validator = $validator;
		$this->_messages = $messages;
		$this->_rules = array();
		$this->_filters = array();

		if($classes) {
			$this->registerClasses($classes);
		}
	}

	/**
	 * @param array $classes
	 * @return $this
	 * @throws \Exception
	 */
	public function registerClasses(array $classes)
	{
		foreach($classes as $class) {
			$collection = new $class;

			if(!$collection instanceof CollectionInterface) {
				throw new \Exception(sprintf('%s must implement CollectionInterface.', $class));
			}

			$collection->register($this);
		}

		return $this;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	public function getRule($name)
	{
		if(!isset($this->_rules[$name])) {
			return false;
		}

		return $this->_rules[$name];
	}

	/**
	 * @param $name
	 * @return bool
	 */
	public function getFilter($name)
	{
		if(!isset($this->_filters[$name])) {
			return false;
		}

		return $this->_filters[$name];
	}

	/**
	 * @return array
	 */
	public function getRules()
	{
		return $this->_rules;
	}

	/**
	 * @return array
	 */
	public function getFilters()
	{
		return $this->_filters;
	}

	/**
	 * @return Validator
	 */
	public function getValidator()
	{
		return $this->_validator;
	}

	/**
	 * @param string $name
	 * @param array $func - callable array for class methods (array(Object, 'methodName'))
	 * @param string $errorMessage
	 * @return $this
	 */
	public function registerRule($name, $func, $errorMessage)
	{
		$this->_register('rule', $name, $func);
		$this->_messages->setDefaultErrorMessage($name, $errorMessage);

		return $this;
	}

	/**
	 * @param string $name
	 * @param array $func - callable array for class methods (array(Object, 'methodName'))
	 * @return $this
	 */
	public function registerFilter($name, $func)
	{
		$this->_register('filter', $name, $func);

		return $this;
	}

	/**
	 * Method to register filters and rules to the loader.
	 * It validates that the rule/filter does not already exist in the register and that they are valid
	 * i.e. callable
	 *
	 * @param string $type - is it a filter or a rule?
	 * @param string $name
	 * @param array $func - callable array for class methods (array(Object, 'methodName'))
	 * @throws \Exception
	 * @return $this
	 */
	protected function _register($type, $name, $func)
	{
		$attr = '_' . $type . 's';

		if(!is_callable($func)) {
			throw new \Exception(sprintf('Cannot register %s `%s`; Second parameter must be callable.', $type, $name));
		}

		if(isset($this->{$attr}[$name])) {
			throw new \Exception(sprintf('A %s with the name `%s` has already been registered.', $type, $name));
		}

		$this->{$attr}[$name] = $func;

		return $this;
	}

}