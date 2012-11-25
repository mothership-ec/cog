<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Validator;

/**
* Rules
*/
class Iterable implements CollectionInterface
{
	public function register($loader)
	{
		$loader->registerRule('each', array($this, 'each'), '%s must%s be valid.');
		$loader->registerRule('validateEach', array($this, 'validateEach'), '%s must%s be valid.');
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