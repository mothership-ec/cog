<?php

namespace Message\Cog\Helper;

use Message\Cog\Exception\InvalidVariableException;

/**
 * Class ValidatorTrait
 * @package Message\Cog\Helper
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Trait for easy type validation and exception throwing in classes. Throws InvalidVariableExceptions
 */
trait ValidatorTrait
{
	/**
	 * @param $x
	 * @param string $name
	 * @throws InvalidVariableException
	 */
	public function checkNumeric($x, $name = 'Value')
	{
		$this->_validateVarName($name);
		if (!is_numeric($x)) {
			throw new InvalidVariableException($name . ' should be numeric, non-numeric ' . gettype($x) . ' given');
		}
	}

	/**
	 * @param $x
	 * @param string $name
	 * @throws InvalidVariableException
	 */
	public function checkWholeNumber($x, $name = 'Value')
	{
		$this->_validateVarName($name);
		$this->checkNumeric($x, $name);

		if ((int) $x != $x) {
			throw new InvalidVariableException($name . ' should be a whole number, ' . $x . ' given');
		}
	}

	/**
	 * @param $x
	 * @param string $name
	 * @throws InvalidVariableException
	 */
	public function checkScalar($x, $name = 'Value')
	{
		$this->_validateVarName($name);

		// PHP doesn't consider NULL to be scalar, but I do because it can be cast to a string
		if ($x !== null && !is_scalar($x)) {
			throw new InvalidVariableException($name . ' should be scalar, ' . gettype($x) . ' given');
		}
	}

	/**
	 * @param $x
	 * @param string $name
	 * @param bool $allowEmpty
	 * @throws InvalidVariableException
	 */
	public function checkString($x, $allowEmpty = false, $name = 'Value')
	{
		$this->_validateVarName($name);

		if (!is_string($x)) {
			throw new InvalidVariableException($name . ' should be a ' . ($allowEmpty ? '' : 'non-empty ') . 'string, ' . gettype($x) . ' given');
		}

		if (!$allowEmpty && !$x) {
			throw new InvalidVariableException($name . ' must be a non-empty string');
		}
	}

	/**
	 * @param $x
	 * @param $class
	 * @param string | object $name
	 * @throws InvalidVariableException
	 * @throws \InvalidArgumentException
	 * @throws \LogicException
	 */
	public function checkInstance($x, $class, $name = 'Value')
	{
		$this->_validateVarName($name);

		if (!is_object($class) && !is_string($class)) {
			throw new \InvalidArgumentException('Second parameter must be an object or a string, ' . gettype($class) . ' given');
		}

		if (is_string($class) && !class_exists($class) && !interface_exists($class)) {
			throw new \LogicException('Class `' . $class . '` does not exist');
		}

		if (!$x instanceof $class) {
			$classname = is_object($class) ? get_class($class) : $class;
			$type = is_object($x) ? get_class($x) : gettype($x);
			throw new InvalidVariableException($name . ' should be an instance of ' . $classname . ', ' . $type . ' given');
		}
	}

	/**
	 * @param $name
	 */
	private function _validateVarName($name)
	{
		if (!is_scalar($name)) {
			throw new \InvalidArgumentException('Name should be scalar, ' . gettype($name) . ' given');
		}
	}
}