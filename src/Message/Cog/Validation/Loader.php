<?php

namespace Message\Cog\Validation;

/**
 * Loader
 * @package Message\Cog\Validation
 *
 * Loads and maintains a set of rules and filters to use in validation
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Loader
{
	/**
	 * @param array $classes        Classes to be registered
	 */
	public function __construct(array $classes = null)
	{
		$this->_messages = new Messages;
		$this->_rules = array();
		$this->_filters = array();

		if($classes) {
			$this->registerClasses($classes);
		}
	}

	/**
	 * Assign rules and filters to loader for use in validation
	 *
	 * @param array $classes    Array of collections to register to loader
	 * @throws \Exception       Throws exception if any classes are not an instance of CollectionInterface
	 *
	 * @return Loader           Returns $this for chainability
	 */
	public function registerClasses(array $classes)
	{
		foreach($classes as $class) {

			if(!$class instanceof CollectionInterface) {
				throw new \Exception(sprintf('%s must implement CollectionInterface.', $class));
			}

			$class->register($this);
		}

		return $this;
	}

	/**
	 * Return instance of registered rule
	 *
	 * @param string $name                      Name of rule to search for
	 *
	 * @return bool | CollectionInterface       Returns rule if found, false if not
	 */
	public function getRule($name)
	{
		if(!isset($this->_rules[$name])) {
			return false;
		}

		return $this->_rules[$name];
	}

	/**
	 * Return instance of registered filter
	 *
	 * @param string $name                      Name of filter to search for
	 *
	 * @return bool | CollectionInterface       Returns filter if found, false if not
	 */
	public function getFilter($name)
	{
		if(!isset($this->_filters[$name])) {
			return false;
		}

		return $this->_filters[$name];
	}

	/**
	 * @return array    Returns array of registered rules
	 */
	public function getRules()
	{
		return $this->_rules;
	}

	/**
	 * @return array    Returns array of registered filters
	 */
	public function getFilters()
	{
		return $this->_filters;
	}

	/**
	 * Assign Messages object to loader
	 *
	 * @param Messages $messages    Messages object to assign to Loader
	 */
	public function setMessages(Messages $messages)
	{
		$this->_messages = $messages;
	}

	/**
	 * Get Messages instance, if not set, creates a new one.
	 *
	 * @return Messages     Return Messages object
	 */
	public function getMessages()
	{
		if (!$this->_messages) {
			$this->setMessages(new Messages);
		}

		return $this->_messages;
	}

	/**
	 * Register a rule to the loader
	 *
	 * @param string $name          Name of rule to register, this is how it will be called by validator i.e.
	 *                              $field('field')->$name()
	 * @param array $func           Callable array for class methods (array(Object, 'methodName'))
	 * @param string $errorMessage  Error message for failed validation, use sprint syntax
	 *
	 * @return Loader               Returns $this for chainability
	 */
	public function registerRule($name, $func, $errorMessage)
	{
		$this->_register('rule', $name, $func);
		$this->_messages->setDefaultErrorMessage($name, $errorMessage);

		return $this;
	}

	/**
	 * Register a filter to the loader
	 *
	 * @param string $name          Name of filter to register, this is how it will be called by validator i.e.
	 *                              $field('field')->$name()
	 * @param array $func           Callable array for class methods (array(Object, 'methodName'))
	 *
	 * @return Loader               Returns $this for chainability
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
	 * @param string $type      Type of collection, i.e. a filter or a rule
	 * @param string $name      Name of collection
	 * @param array $func       Callable array for class methods (array(Object, 'methodName'))
	 * @throws \Exception       Throws exception if $func is not callable or if there is already a collection of that
	 *                          type and name registered
	 *
	 * @return Loader           Returns $this for chainability
	 */
	protected function _register($type, $name, $func)
	{
		$attr = '_' . strtolower($type) . 's';

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