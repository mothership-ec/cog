<?php

namespace Message\Cog\Validation\Check;

/**
 * Class Type
 * @package Message\Cog\Validation\Check
 *
 * Class of static methods to check data types and throw exceptions if invalid.
 * May be an idea to move this out of Validation and into Functions or something.
 *
 * All methods return true if no exception is thrown, to allow for easy unit testing.
 *
 * This class uses the debug_backtrace() function to provide relevant error messages, i.e. not
 * reference the actual class and method being used, and not these ones. Error messages can be
 * improved by adding the second param (or third in the case of checkInstanceOf()) of the variable
 * name
 */

class Type
{

	/**
	 * @param $var
	 * @param string $varName
	 * @return bool
	 * @throws \Exception
	 */
	static function checkString($var, $varName = '$var')
	{
		$varName = (string) $varName;

		if (!is_string($var)) {
			$callers = debug_backtrace();
			throw new \Exception($callers[1]['class'] . '::' . $callers[1]['function'] . '() - ' . $varName . ' must be a string, ' . gettype($var) . ' given');
		}

		return true;

	}

	/**
	 * @param $var
	 * @param string $varName
	 * @return bool
	 * @throws \Exception
	 */
	static function checkStringOrNumeric($var, $varName = '$var')
	{
		$varName = (string) $varName;

		if (is_int($var) || is_float($var)) {
			return true;
		}

		if (!is_string($var)) {
			$callers = debug_backtrace();
			throw new \Exception($callers[1]['class'] . '::' . $callers[1]['function'] . '() - ' . $varName . ' must be a string, ' . gettype($var) . ' given');
		}

		return true;
	}

	/**
	 * @param $var
	 * @param string $varName
	 * @return bool
	 * @throws \Exception
	 */
	static function checkInt($var, $varName = '$var')
	{
		$varName = (string) $varName;

		if(!is_int($var)) {
			$callers = debug_backtrace();
			throw new \Exception($callers[1]['class'] . '::' . $callers[1]['function'] . '() - ' . $varName. ' must be an integer, ' . gettype($var) . ' given');
		}

		return true;
	}

	/**
	 * @param $var
	 * @param string $varName
	 * @return bool
	 * @throws \Exception
	 */
	static function checkFloat($var, $varName = '$var')
	{
		$varName = (string) $varName;

		if (!is_float($var)) {
			$callers = debug_backtrace();
			throw new \Exception($callers[1]['class'] . '::' . $callers[1]['function'] . '() - ' . $varName . ' must be a float, ' .  gettype($var) . ' given');
		}

		return true;
	}

	/**
	 * @param $var
	 * @param string $varName
	 * @return bool
	 * @throws \Exception
	 */
	static function checkIntOrFloat($var, $varName = '$var')
	{
		$varName = (string) $varName;

		if (!is_int($var) && !is_float($var)) {
			$callers = debug_backtrace();
			throw new \Exception($callers[1]['class'] . '::' . $callers[1]['function'] . '() - ' . $varName . ' must be either an int or a float, ' . gettype($var) . ' given');
		}

		return true;
	}

	/**
	 * @param $var
	 * @param string $varName
	 * @return bool
	 * @throws \Exception
	 */
	static function checkNumeric($var, $varName = '$var')
	{
		$varName = (string) $varName;

		if (!is_numeric($var)) {
			$callers = debug_backtrace();
			throw new \Exception($callers[1]['class'] . '::' . $callers[1]['function'] . '() - ' . $varName . ' must be numeric');
		}

		return true;
	}

	/**
	 * @param $var
	 * @param string $varName
	 * @return bool
	 * @throws \Exception
	 */
	static function checkArray($var, $varName = '$var')
	{
		$varName = (string) $varName;

		if (!is_array($var)) {
			$callers = debug_backtrace();
			throw new \Exception($callers[1]['class'] . '::' . $callers[1]['function'] . '() - ' . $varName . ' must be an array, ' . gettype($var) . ' given');
		}

		return true;
	}

	/**
	 * @param $var
	 * @param string $varName
	 * @return bool
	 * @throws \Exception
	 */
	static function checkBool($var, $varName = '$var')
	{
		$varName = (string) $varName;

		if (!is_bool($var)) {
			$callers = debug_backtrace();
			throw new \Exception($callers[1]['class'] . '::' . $callers[1]['function'] . '() - ' . $varName . ' must be a boolean, ' . gettype($var) . ' given');
		}

		return true;
	}

	/**
	 * @param $var
	 * @param object|string $className
	 * @param string $varName
	 * @return bool
	 * @throws \Exception
	 */
	static function checkInstanceOf($var, $className, $varName = '$var')
	{
		if (!is_object($className)) {
			$className = (string) $className;
		} else {
			$className = get_class($className);
		}

		$varName = (string) $varName;

		if (!$var instanceof $className) {
			$callers = debug_backtrace();
			throw new \Exception($callers[1]['class'] . '::' . $callers[1]['function'] . '() - ' . $varName . ' must be an instance of ' . $className . ',  ' . gettype($var) . ' given');
		}

		return true;
	}

	/**
	 * @param $var
	 * @param string $varName
	 * @return bool
	 * @throws \Exception
	 */
	static function checkObject($var, $varName = '$var')
	{
		$varName = (string) $varName;

		if (!is_object($var)) {
			$callers = debug_backtrace();
			throw new \Exception($callers[1]['class'] . '::' . $callers[1]['function'] . '() - ' . $varName . ' must be an object, ' . gettype($var) . ' given');
		}

		return true;
	}

	/**
	 * @param $var
	 * @param string $varName
	 * @return bool
	 * @throws \Exception
	 */
	static function checkNull($var, $varName = '$var')
	{
		$varName = (string) $varName;

		if (!is_null($var)) {
			$callers = debug_backtrace();
			throw new \Exception($callers[1]['class'] . '::' . $callers[1]['function'] . '() - ' . $varName . ' must be null, ' . gettype($var) . ' given');
		}

		return true;
	}

}