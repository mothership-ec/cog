<?php

namespace Message\Cog\Helper;

use Message\Cog\Exception\InvalidTypeException;

/**
 * Class TypeValidatorTrait
 * @package Message\Cog\Helper
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Trait for easy type validation and exception throwing in classes. Throws InvalidTypeExceptions
 */
trait TypeValidatorTrait
{
	/**
	 * @param $x
	 * @param string $name
	 * @throws InvalidTypeException
	 */
	public function checkNumeric($x, $name = 'Value')
	{
		$this->_validateVarName($name);
		if (!is_numeric($x)) {
			throw new InvalidTypeException($name . ' should be numeric, non-numeric ' . gettype($x) . ' given');
		}
	}

	/**
	 * @param $x
	 * @param string $name
	 * @throws InvalidTypeException
	 */
	public function checkWholeNumber($x, $name = 'Value')
	{
		$this->_validateVarName($name);
		$this->checkNumeric($x, $name);

		if ((int) $x != $x) {
			throw new InvalidTypeException($name . ' should be a whole number, ' . $x . ' given');
		}
	}

	/**
	 * @param $x
	 * @param string $name
	 * @throws InvalidTypeException
	 */
	public function checkScalar($x, $name = 'Value')
	{
		$this->_validateVarName($name);

		// PHP doesn't consider NULL to be scalar, but I do because it can be cast to a string
		if ($x !== null && !is_scalar($x)) {
			throw new InvalidTypeException($name . ' should be scalar, ' . gettype($x) . ' given');
		}
	}

	/**
	 * @param $x
	 * @param $class
	 * @param string | object $name
	 * @throws InvalidTypeException
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
			throw new InvalidTypeException($name . ' should be an instance of ' . $classname . ', ' . $type . ' given');
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