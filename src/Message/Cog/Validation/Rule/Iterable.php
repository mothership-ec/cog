<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Validator;
use Message\Cog\Validation\Loader;

/**
 * Iterable rule
 * @package Message\Cog\Validation\Rule
 *
 * Class for looping through values for validation
 *
 * @todo get working
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Iterable implements CollectionInterface
{
//	/**
//	 * Register rules
//	 *
//	 * @param Loader $loader
//	 *
//	 * @return void
//	 */
//	public function register(Loader $loader)
//	{
//		$loader->registerRule('each', array($this, 'each'), '%s must%s be valid.')
//			->registerRule('validateEach', array($this, 'validateEach'), '%s must%s be valid.');
//	}
//
//	/**
//	 * Checks an array of values against a callable function
//	 *
//	 * @param array $var        Array of values to be validated
//	 * @param string $func      Name of callable function to use for validation
//	 *
//	 * @return bool             Returns true if each value is valid
//	 */
//	public function each(array $var, $func)
//	{
//		foreach($var as $key => $value) {
//			if(!$func($value, $key)) {
//				return false;
//			}
//		}
//
//		return true;
//	}
//
//	/**
//	 * Checks an array of values against fields set up in an anonymous function
//	 *
//	 * @param array $var        Array of values to be validated
//	 * @param callback $func    An anonymous function that sets up validation fields. It must take a parameter of an
//	 *                          instance of \Message\Cog\Validation\Validator and return it
//	 * @throws \Exception       Throws exception if $func doesn't return instance of \Message\Cog\Validation\Validator
//	 *
//	 * @return bool             Returns true if all values in $var are valid
//	 */
//	public function validateEach(array $var, $func)
//	{
//		// @todo clone instance of parent Validator and clear rules and filters
//		$validator = new Validator();
//		$validator = $func($validator);
//
//		if(!($validator instanceof Validator)) {
//			throw new \Exception('Callback must return an instance of Validator');
//		}
//
//		foreach($var as $key => $value) {
//			if(!$validator->validate($value)) {
//				return false;
//			}
//		}
//
//		return true;
//	}

}