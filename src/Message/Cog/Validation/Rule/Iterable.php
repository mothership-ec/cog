<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Validator;
use Message\Cog\Validation\Loader;

/**
* Rules
*/
class Iterable implements CollectionInterface
{
	public function register(Loader $loader)
	{
		$loader->registerRule('each', array($this, 'each'), '%s must%s be valid.')
			->registerRule('validateEach', array($this, 'validateEach'), '%s must%s be valid.');
	}

	public function each($var, $func)
	{
		foreach($var as $key => $value) {
			if(!$func($value, $key)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param array $var
	 * @param $func
	 * @return bool
	 * @throws \Exception
	 */
	public function validateEach($var, $func)
	{
		$validator = new Validator();
		$validator = $func($validator);

		if(!($validator instanceof Validator)) {
			throw new \Exception('Callback must return an instance of Validator');
		}

		foreach($var as $key => $value) {
			if(!$validator->validate($value)) {
				return false;
			}
		}

		return true;
	}

}