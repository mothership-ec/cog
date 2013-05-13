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
 * This class uses the debug_backtrace() function to provide relevant error messages, i.e. reference the actual class
 * and method being used, and not these ones. Error messages can be improved by adding the second param (or third in
 * the case of checkInstanceOf()) for the variable name
 */

class Type
{

	/**
	 * Method to check if variable is a string
	 *
	 * @param $var              Data being checked
	 * @param string $varName   Variable name as it will appear in error message, defaults to '$var'
	 * @throws \Exception       Throws exception if $var is not string
	 *
	 * @return bool             Returns true if $var is a string
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
	 * Method to check if a variable is either a string or a numeric value that could be parsed as a string
	 *
	 * @param $var              Data being checked
	 * @param string $varName   Variable name as it will appear in error message, defaults to '$var'
	 * @throws \Exception       Throws exception if $var is neither a string. integer nor float
	 *
	 * @return bool             Returns true if $var is a string, integer or float
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
	 * Method to check if a variable is an integer
	 *
	 * @param $var              Data being checked
	 * @param string $varName   Variable name as it will appear in error message, defaults to '$var'
	 * @throws \Exception       Throws exception if $var is not an integer
	 *
	 * @return bool             Returns true if $var is an integer
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
	 * Method to check if a variable is a float
	 *
	 * @param $var              Data being checked
	 * @param string $varName   Variable name as it will appear in error message, defaults to '$var'
	 * @throws \Exception       Throws exception if $var is not a float
	 *
	 * @return bool             Returns true if $var is a float
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
	 * Method to check if a variable is an integer or a float
	 *
	 * @param $var              Data being checked
	 * @param string $varName   Variable name as it will appear in error message, defaults to '$var'
	 * @throws \Exception       Throws exception if $var is neither an integer nor a float
	 *
	 * @return bool             Returns true if $var is an integer or float
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
	 * Method to check if a variable is an integer, float or numeric string
	 *
	 * @param $var              Data being checked
	 * @param string $varName   Variable name as it will appear in error message, defaults to '$var'
	 * @throws \Exception       Throws exception if $var is not numeric
	 *
	 * @return bool             Returns true if $var is numeric
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
	 * Method to check if a variable is an array
	 *
	 * @param $var              Data being checked
	 * @param string $varName   Variable name as it will appear in error message, defaults to '$var'
	 * @throws \Exception       Throws exception if $var is not an array
	 *
	 * @return bool             Returns true if $var is an array
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
	 * Method to check if a variable is a boolean
	 *
	 * @param $var              Data being checked
	 * @param string $varName   Variable name as it will appear in error message, defaults to '$var'
	 * @throws \Exception       Throws exception if $var is not a boolean
	 *
	 * @return bool             Returns true if $var is a boolean
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
	 * Method to check if a variable is an instance of a specific class
	 * The variable can be checked against a class name, or an instance of the class itself
	 *
	 * @param $var                          Data being checked
	 * @param object|string $className      Name or instance of class $var is being checked against
	 * @param string $varName               Variable name as it will appear in error message, defaults to '$var'
	 * @throws \Exception                   Throws exception if $var is not an instance of $className
	 *
	 * @return bool                         Returns true if $var is an instance of $className
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
	 * Method to check if a variable is an object of any kind
	 *
	 * @param $var              Data being checked
	 * @param string $varName   Variable name as it will appear in error message, defaults to '$var'
	 * @throws \Exception       Throws exception if $var is not an object
	 *
	 * @return bool             Returns true if $var is an object
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
	 * Method to check if a variable is null
	 *
	 * @param $var              Data being checked
	 * @param string $varName   Variable name as it will appear in error message, defaults to '$var'
	 * @throws \Exception       Throws exception if $var is not null
	 *
	 * @return bool             Returns true if $var is null
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